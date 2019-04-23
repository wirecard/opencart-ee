#!/bin/bash
export VERSION=`jq .[0].release SHOPVERSIONS`

system/library/bin/codecept run acceptance -g ui_test --steps --debug --html --xml
