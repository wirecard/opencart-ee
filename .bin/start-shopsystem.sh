#!/bin/bash

export OPENCART_CONTAINER_NAME=opencart
export OPENCART_CONTAINER_VERSION=${OPENCART_VERSION}

docker-compose up -d

# wait for shop system to initialize
while ! $(curl --silent --output /dev/null --head --fail "${OPENCART_URL}/admin"); do
    echo "Waiting for docker container to initialize"
    sleep 5
done

sleep 5
docker exec --env GATEWAY=${GATEWAY} ${OPENCART_CONTAINER_NAME} ./plugin/.bin/install-extension.sh
