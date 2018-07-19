
echo "Create tag for branch master";

if [[ $TRAVIS_BRANCH == 'master' ]]; then
    echo "if it is no pull request";
    if [[ $TRAVIS_PULL_REQUEST == 'false' ]]; then
        echo "cat Version for tag";
        VERSION=`cat VERSION`
        STATUS=`curl -s -o /dev/null -w "%{http_code}" -H "Authorization: token ${GITHUB_TOKEN}" https://api.github.com/repos/wirecard/opencart-ee/git/refs/tags/${VERSION}`

        if [[ ${STATUS} == "200" ]] ; then
            echo "Tag is up to date with version."
            exit 0
        elif [[ ${STATUS} != "404" ]] ; then
            echo "Got status ${STATUS} from GitHub. Exiting."
            exit 0
        else
            echo "Version is updated, creating tag ${VERSION}"
        fi

        git config --global user.name "Travis CI"
        git config --global user.email "wirecard@travis-ci.org"

        git tag -a ${VERSION} -m "Pre-release version"
        git push origin --tags
        git fetch --tags
    fi
fi
