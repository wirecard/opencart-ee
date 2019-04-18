#!/bin/bash

#this will be set by travis
export NGROK_URL=http://c596a2a9.ngrok.io
export GATEWAY=API-TEST
#chmod +x .bin/*

# in future browserstack tunnel will be used instead of ngrok
curl -s https://bin.equinox.io/c/4VmDzA7iaHb/ngrok-stable-linux-386.zip > ngrok.zip
unzip ngrok.zip
chmod +x $PWD/ngrok
$PWD/ngrok authtoken ${NGROK_AUTHTOKEN}
$PWD/ngrok http 80 -subdomain=c596a2a9 > /dev/null &


export OPENCART_CONTAINER_NAME=opencart
export VERSION=`jq .[0].release SHOPVERSIONS`

docker-compose up -d

#wait for shop system to initialize
while ! $(curl --output /dev/null --silent --head --fail "${NGROK_URL}/admin"); do
    echo "Waiting for docker container to initialize"
    sleep 5
done

docker exec ${OPENCART_CONTAINER_NAME} ./plugin/.bin/install-extension.sh

#run installation from UI
# this requires codeception preinstalled and selenium ob browserstack running
#system/library/bin/codecept run acceptance -g installator --steps --debug

