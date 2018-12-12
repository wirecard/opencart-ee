#!/bin/bash

TARGET_DIRECTORY="upload"

composer install --no-dev --prefer-dist
rm -rf $TARGET_DIRECTORY
echo "copying files to target directory ${TARGET_DIRECTORY}"
mkdir $TARGET_DIRECTORY
cp -r admin catalog image system ${TARGET_DIRECTORY}/

zip -r opencart-ee.ocmod.zip ${TARGET_DIRECTORY} install.xml
