#!/bin/bash

set -a # automatically export all variables from .env file
source .env
set +a

docker build --build-arg OPENCART_VERSION=${OPENCART_VERSION} . -t opencart:${OPENCART_VERSION}

docker network create opencart-tier
docker volume create --name mariadb_data
docker run -d --name mariadb \
 -e ALLOW_EMPTY_PASSWORD=${ALLOW_EMPTY_PASSWORD} \
 -e MARIADB_USER=${MARIADB_USER} \
 -e MARIADB_DATABASE=${MARIADB_DATABASE} \
 --net opencart-tier \
 --volume mariadb_data:/bitnami \
 bitnami/mariadb:latest

docker volume create --name opencart_data

docker run -d --name ${OPENCART_CONTAINER_NAME} -p 80:80 -p 443:443 \
 -e ALLOW_EMPTY_PASSWORD=${ALLOW_EMPTY_PASSWORD} \
 -e OPENCART_DATABASE_USER=${MARIADB_USER} \
 -e OPENCART_DATABASE_NAME=${MARIADB_DATABASE} \
 -e OPENCART_HOST=${OPENCART_HOST} \
 -e OPENCART_USERNAME=${OPENCART_USERNAME} \
 -e OPENCART_PASSWORD=${OPENCART_PASSWORD} \
 --net opencart-tier \
 --volume opencart_data:/bitnami \
 -v $(pwd):/plugin \
 opencart:${OPENCART_VERSION}

# wait for shop system to initialize
while ! $(curl --silent --output /dev/null --head --fail "${OPENCART_URL}/admin"); do
    echo "Waiting for docker container to initialize"
    sleep 5
done

sleep 5
docker exec --env GATEWAY=${GATEWAY} ${OPENCART_CONTAINER_NAME} ./plugin/.bin/install-extension.sh