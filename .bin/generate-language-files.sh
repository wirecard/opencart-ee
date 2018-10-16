#!/bin/bash

wget http://github.com/phrase/phraseapp-client/releases/download/1.11.1/phraseapp_linux_386.tar.gz
tar xvfz phraseapp_linux_386.tar.gz
cd phraseapp_linux_386
./configure
make
make install

phraseapp projects list
