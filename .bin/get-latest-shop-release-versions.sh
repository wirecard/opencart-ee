#!/bin/bash

curl -H "Authorization: token ${GITHUB_TOKEN}" https://api.github.com/repos/opencart/opencart/releases | jq -r '.[] | .tag_name' | egrep -v [a-zA-Z] | grep -v "3.0.3.0" | sed -e 's/.\([^.]*\)$/-\1/' | head -n3 > tmp.txt
git config --global user.name "Travis CI"
git config --global user.email "wirecard@travis-ci.org"

sort -nr tmp.txt > ${OPENCART_COMPATIBILITY_FILE}

if [[ $(git diff HEAD ${OPENCART_COMPATIBILITY_FILE}) != '' ]]; then
  git add  ${OPENCART_COMPATIBILITY_FILE}
  git commit -m "[skip ci] Update latest shop releases"
  git push --quiet https://${GITHUB_TOKEN}@github.com/${TRAVIS_REPO_SLUG} HEAD:master
fi
