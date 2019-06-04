#!/bin/bash

export OPENCART_CONTAINER_NAME=opencart

#export NGROK_POSTFIX=.ngrok.io
#export OPENCART_DOMAIN=${OPENCART_PREFIX}-${GATEWAY}${NGROK_POSTFIX}
#export OPENCART_URL=https://${OPENCART_DOMAIN}

echo OPENCART_DOMAIN=${OPENCART_DOMAIN}
echo OPENCART_URL=${OPENCART_URL}
echo NGROK_URL=${NGROK_URL}

#Replace Opencart domain value and create docker-compose file from template
sed -e "s/\${i}/1/" -e 's/\${OPENCART_DOMAIN}/'"${OPENCART_DOMAIN}"'/' docker-compose.tmpl > docker-compose.yml

docker-compose up -d

# wait for shop system to initialize
while ! $(curl --silent --output /dev/null --head --fail "${OPENCART_URL}/admin"); do
    echo "Waiting for docker container to initialize"
    sleep 5
done

sleep 5
docker exec --env GATEWAY=${GATEWAY} ${OPENCART_CONTAINER_NAME} ./plugin/.bin/install-extension.sh
