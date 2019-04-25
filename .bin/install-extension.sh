#!/bin/bash

cd /plugin
rsync -ah admin /opt/bitnami/opencart/
rsync -ah image /opt/bitnami/opencart/
rsync -ah system /opt/bitnami/opencart/
rsync -ah catalog /opt/bitnami/opencart/
# configure credit card payment method
cd .bin
php install-payment.php creditcard
