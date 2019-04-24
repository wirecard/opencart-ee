#!/bin/bash
#export VERSION=`jq .[0].release SHOPVERSIONS`
#export OPENCART_URL=http://127.0.0.1

## download and install BrowserStackLocal
#wget -q https://www.browserstack.com/browserstack-local/BrowserStackLocal-linux-x64.zip
#unzip -q BrowserStackLocal-linux-x64.zip
#./BrowserStackLocal --key ${BROWSERSTACK_ACCESS_KEY} > /dev/null &
#sleep 5



# run acceptance test
system/library/bin/codecept run acceptance -g ui_test --steps --html --xml
