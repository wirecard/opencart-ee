#!/bin/bash
export VERSION=`jq .[0].release SHOPVERSIONS`
export OPENCART_URL=http://127.0.0.1

# download and install BrowserStackLocal
wget https://www.browserstack.com/browserstack-local/BrowserStackLocal-linux-x64.zip
unzip BrowserStackLocal-linux-x64.zip
./BrowserStackLocal --key ${BROWSERSTACK_ACCESS_KEY} > /dev/null &
sleep 5

# start shop system
bash .bin/start-shopsystem.sh

# run acceptance test
system/library/bin/codecept run acceptance -g ui_test --steps --debug --html --xml
