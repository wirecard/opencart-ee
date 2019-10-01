#!/bin/bash

set -a # automatically export all variables from .env file
source .env
set +a

# run installation from UI (missing part that cannot be done via command line)
sleep 5

docker exec --env GATEWAY=${GATEWAY} ${OPENCART_CONTAINER_NAME} cd plugin
docker exec --env GATEWAY=${GATEWAY} ${OPENCART_CONTAINER_NAME} ./plugin/system/library/bin/codecept run acceptance -g installator --steps