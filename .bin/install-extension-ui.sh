#!/bin/bash

export OPENCART_USERNAME=user
export OPENCART_PASSWORD=bitnami1

# run installation from UI (missing part that cannot be done via command line)
sleep 5
system/library/bin/codecept run acceptance -g installator --steps
