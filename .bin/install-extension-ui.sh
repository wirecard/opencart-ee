#!/bin/bash

set -a # automatically export all variables from .env file
source .env
set +a

# run installation from UI (missing part that cannot be done via command line)
sleep 5
printenv
system/library/bin/codecept run acceptance -g installator --steps
