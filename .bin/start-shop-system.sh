#!/bin/bash
cd /plugin
# should be done in travis
composer install --no-dev

rsync -ah admin /opt/bitnami/opencart/
rsync -ah image /opt/bitnami/opencart/
rsync -ah system /opt/bitnami/opencart/
rsync -ah catalog /opt/bitnami/opencart/


##!/bin/bash
#
#git clone https://github.com/wirecard/opencart-ee.git
#cd opencart-ee
#git checkout TPWDCEE-3602
#
#
#curl -s https://bin.equinox.io/c/4VmDzA7iaHb/ngrok-stable-linux-386.zip > ngrok.zip
#unzip ngrok.zip
#chmod +x $PWD/ngrok
#
#
#$PWD/ngrok authtoken
#$PWD/ngrok http 80 -subdomain=c596a2a9 > /dev/null &
#
#docker-compose up -d
#
#docker exec -it opencart-ee_opencart_1 /bin/bash
#
#in container
#
#cd /plugin
#chmod +x .bin/generate-release-package.sh
#
#.bin/generate-release-package.sh
#
#cp opencart-ee.ocmod.zip .bin/
#cd .bin
#unzip opencart-ee.ocmod.zip
#php install-extension.php opencart-ee.ocmod.zip
#rm install.xml
#
#
#
#git config --global user.email "tatjana.starcenko@wirecard.com"
#git config --global user.name "Tatjana Starcenko"
