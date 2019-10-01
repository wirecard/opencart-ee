set -a # automatically export all variables from .env file
source .env
set +a

printenv
system/library/bin/codecept run acceptance -g ui_test --steps -vvv