#!/bin/bash

export OPENCART_CONTAINER_NAME=opencart
export NGROK_URL=http://c596a2a9.ngrok.io

#git clone https://github.com/wirecard/opencart-ee.git
#cd opencart-ee
#git checkout TPWDCEE-3602
#chmod +x .bin/*

# in future we should use browserstack tunnel instead of ngrok
curl -s https://bin.equinox.io/c/4VmDzA7iaHb/ngrok-stable-linux-386.zip > ngrok.zip
unzip ngrok.zip
chmod +x $PWD/ngrok
$PWD/ngrok authtoken ${NGROK_AUTHTOKEN}
$PWD/ngrok http 80 -subdomain=c596a2a9 > /dev/null &



docker-compose up -d

#wait for shop system to initialize
while ! $(curl --output /dev/null --silent --head --fail "${NGROK_URL}/admin"); do
    echo "Waiting for docker container to initialize"
    sleep 5
done

docker exec ${OPENCART_CONTAINER_NAME} ./plugin/.bin/install-extension.sh