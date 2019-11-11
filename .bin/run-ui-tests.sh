set -a # automatically export all variables from .env file
source .env
set +a

export GIT_BRANCH=${TRAVIS_BRANCH}

# if tests triggered by PR, use different Travis variable to get branch name
if [ ${TRAVIS_PULL_REQUEST} != "false" ]; then
    export $GIT_BRANCH="${TRAVIS_PULL_REQUEST_BRANCH}"
fi

# find out test group to be run
if [[ $GIT_BRANCH =~ "${PATCH_RELEASE}" ]]; then
   TEST_GROUP="${PATCH_RELEASE}"
elif [[ $GIT_BRANCH =~ "${MINOR_RELEASE}" ]]; then
   TEST_GROUP="${MINOR_RELEASE}"
# run all tests if nothing else specified
else
   TEST_GROUP="${MAJOR_RELEASE}"
fi

system/library/bin/codecept run acceptance -g ${TEST_GROUP} --html --xml