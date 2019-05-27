#!/bin/bash

export OPENCART_CONTAINER_NAME=opencart

docker-compose build --no-cache --build-arg OPENCART_CONTAINER_NAME=${OPENCART_CONTAINER_NAME} \
                                --build-arg OPENCART_CONTAINER_VERSION=${OPENCART_VERSION} \
                                opencart
docker-compose up --force-recreate -d
#docker-compose up -d

# wait for shop system to initialize
while ! $(curl --silent --output /dev/null --head --fail "${OPENCART_URL}/admin"); do
    echo "Waiting for docker container to initialize"
    sleep 5
done

sleep 5
docker exec --env GATEWAY=${GATEWAY} ${OPENCART_CONTAINER_NAME} ./plugin/.bin/install-extension.sh
