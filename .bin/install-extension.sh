#!/bin/bash

cd /plugin
# should be done in travis
composer install --no-dev

rsync -ah admin /opt/bitnami/opencart/
rsync -ah image /opt/bitnami/opencart/
rsync -ah system /opt/bitnami/opencart/
rsync -ah catalog /opt/bitnami/opencart/

php .bin/install-payment.php creditcard
