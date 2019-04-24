#!/bin/bash

export OPENCART_USERNAME=user
export OPENCART_PASSWORD=bitnami1

#run installation from UI
sleep 5
system/library/bin/codecept run acceptance -g installator --steps



