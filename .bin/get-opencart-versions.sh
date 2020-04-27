#!/bin/bash

curl -H "Authorization: token ${GITHUB_TOKEN}" https://api.github.com/repos/opencart/opencart/releases | jq -r '.[] | .tag_name' | egrep -v [a-zA-Z] | grep -v "3.0.3.0" | sed -e 's/.\([^.]*\)$/-\1/' | head -n3 > ${OPENCART_RELEASES_FILE}
git config --global user.name "Travis CI"
git config --global user.email "wirecard@travis-ci.org"

git add  ${OPENCART_RELEASES_FILE}
git commit -m "[skip ci] Update latest shop releases"
git push --quiet https://${GITHUB_TOKEN}@github.com/${TRAVIS_REPO_SLUG} HEAD:master
