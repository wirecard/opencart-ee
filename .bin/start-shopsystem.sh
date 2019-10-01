#!/bin/bash

set -a # automatically export all variables from .env file
source .env
set +a

#build opencart container
docker build --build-arg OPENCART_VERSION=${OPENCART_VERSION} . -t opencart:${OPENCART_VERSION}
#create network and volume
docker network create opencart-tier
docker volume create --name mariadb_data
#start database
docker run -d --name mariadb  -p 3306:3306 \
 -e ALLOW_EMPTY_PASSWORD=${ALLOW_EMPTY_PASSWORD} \
 -e MARIADB_USER=${MARIADB_USER} \
 -e MARIADB_PASSWORD=${MARIADB_PASSWORD} \
 -e MARIADB_DATABASE=${MARIADB_DATABASE} \
 --net opencart-tier \
 --volume mariadb_data:/bitnami \
 bitnami/mariadb:latest

docker volume create --name opencart_data
#start opencart container
docker run -d --name ${OPENCART_CONTAINER_NAME} -p 80:80 -p 443:443 \
 -e ALLOW_EMPTY_PASSWORD=${ALLOW_EMPTY_PASSWORD} \
 -e OPENCART_DATABASE_USER=${MARIADB_USER} \
 -e OPENCART_DATABASE_NAME=${MARIADB_DATABASE} \
 -e OPENCART_DATABASE_PASSWORD=${MARIADB_PASSWORD} \
 -e OPENCART_HOST=${OPENCART_HOST} \
 -e OPENCART_USERNAME=${OPENCART_USERNAME} \
 -e OPENCART_PASSWORD=${OPENCART_PASSWORD} \
 --net opencart-tier \
 --volume opencart_data:/bitnami \
 -v $(pwd):/plugin \
 bitnami/opencart:${OPENCART_VERSION}

# wait for shop system to initialize
while ! $(curl --silent --output /dev/null --head --fail "${OPENCART_URL}/admin"); do
    echo "Waiting for docker container to initialize"
    sleep 5
done
#install extension and configure payment method (part1)
sleep 5
docker exec --env GATEWAY=${GATEWAY} ${OPENCART_CONTAINER_NAME} ./plugin/.bin/install-extension.sh