#!/bin/bash

export OPENCART_CONTAINER_NAME=opencart

docker-compose up -d

echo "${OPENCART_URL}/admin"
# wait for shop system to initialize
while ! $(curl --silent --output /dev/null --head --fail "${OPENCART_URL}/admin"); do
    echo "Waiting for docker container to initialize"
    sleep 5
done

sleep 5
docker exec --env GATEWAY=${GATEWAY} ${OPENCART_CONTAINER_NAME} ./plugin/.bin/install-extension.sh
