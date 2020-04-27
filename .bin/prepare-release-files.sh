#!/bin/bash

TARGET_DIRECTORY="upload"

mkdir ${TARGET_DIRECTORY}
cp -r admin catalog image system composer.json ${TARGET_DIRECTORY}/
rm -rf admin catalog image system
