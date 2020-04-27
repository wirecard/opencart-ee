#!/bin/bash

set -a # automatically export all variables from .env file
source .env
set +a

# download and install ngrok
curl -s https://bin.equinox.io/c/4VmDzA7iaHb/ngrok-stable-linux-amd64.zip > ngrok.zip
unzip ngrok.zip
chmod +x $PWD/ngrok
# Download json parser for determining ngrok tunnel
curl -sO http://stedolan.github.io/jq/download/linux64/jq
chmod +x $PWD/jq

# Open ngrok tunnel
$PWD/ngrok authtoken $NGROK_TOKEN
TIMESTAMP=$(date +%s)
$PWD/ngrok http 80 -subdomain="${OPENCART_PREFIX}-${GATEWAY}-${OPENCART_RELEASE_VERSION}" > /dev/null &

NGROK_URL=$(curl -s localhost:4040/api/tunnels/command_line | jq --raw-output .public_url)

# allow ngrok to initialize
while [ ! ${NGROK_URL} ] || [ ${NGROK_URL} = 'null' ];  do
    echo "Waiting for ngrok to initialize"
    export NGROK_URL=$(curl -s localhost:4040/api/tunnels/command_line | jq --raw-output .public_url)
    sleep 1
done
