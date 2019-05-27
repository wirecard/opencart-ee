#!/bin/bash

export OPENCART_CONTAINER_NAME=opencart
export OPENCART_CONTAINER_VERSION=${OPENCART_VERSION}

docker volume create --name opencart_data
docker run -d --name opencart -p 80:80 -p 443:443 \
  -e MARIADB_HOST=mariadb \
  -e MARIADB_PORT_NUMBER=3306 \
  -e OPENCART_DATABASE_USER=bn_opencart \
  -e OPENCART_DATABASE_NAME=bitnami_opencart \
  --net opencart-tier \
  --volume /path/to/opencart-persistence:/bitnami \
  bitnami/opencart:${PRESTASHOP_CONTAINER_VERSION}
#docker-compose up -d

# wait for shop system to initialize
while ! $(curl --silent --output /dev/null --head --fail "${OPENCART_URL}/admin"); do
    echo "Waiting for docker container to initialize"
    sleep 5
done

sleep 5
docker exec --env GATEWAY=${GATEWAY} ${OPENCART_CONTAINER_NAME} ./plugin/.bin/install-extension.sh
