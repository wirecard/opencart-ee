#!/bin/bash

export OPENCART_CONTAINER_NAME=opencart
#export VERSION=`jq .[0].release SHOPVERSIONS`


docker-compose up -d

#wait for shop system to initialize
#while ! $(curl --output /dev/null --silent --head --fail "${OPENCART_URL}/admin"); do

while ! $(curl --silent --output /dev/null --head --fail "${OPENCART_URL}/admin"); do
    echo "Waiting for docker container to initialize"
    sleep 5
done

sleep 5
docker exec ${OPENCART_CONTAINER_NAME} ./plugin/.bin/install-extension.sh

##run installation from UI
#sleep 5
#system/library/bin/codecept run acceptance -g installator --steps



