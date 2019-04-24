#!/bin/bash

# download and install BrowserStackLocal
wget -q https://www.browserstack.com/browserstack-local/BrowserStackLocal-linux-x64.zip
unzip -q BrowserStackLocal-linux-x64.zip
./BrowserStackLocal --key ${BROWSERSTACK_ACCESS_KEY} > /dev/null &
sleep 5
