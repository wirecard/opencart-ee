#!/bin/bash
PREVIEW_LINK='https://raw.githack.com/wirecard/reports'
REPORT_FILE='report.html'
# choose slack channel depending on the gateway
if [[ ${GATEWAY} = "NOVA" ]]; then
  CHANNEL='shs-ui-nova'
elif [[ ${GATEWAY} = "API-WDCEE-TEST" ]]; then
  CHANNEL='shs-ui-api-wdcee-test'
elif [[  ${GATEWAY} = "API-TEST" ]]; then
   CHANNEL='shs-ui-api-test'
elif [[  ${GATEWAY} = "TEST-SG" ]]; then
   CHANNEL='shs-ui-test-sg'
elif [[  ${GATEWAY} = "SECURE-TEST-SG" ]]; then
   CHANNEL='shs-ui-secure-test-sg'
fi

# send information about the build
curl -X POST -H 'Content-type: application/json' \
    --data "{'text': 'Build Failed. OpenCart version: ${OPENCART_VERSION}\n
    Build URL : ${TRAVIS_JOB_WEB_URL}\n
    Build Number: ${TRAVIS_BUILD_NUMBER}\n
    Branch: ${TRAVIS_BRANCH}', 'channel': '${CHANNEL}'}" ${SLACK_ROOMS}

FAILED_TESTS=$(ls -1q tests/_output/*.fail.png | wc -l)

# send link to the report into slack chat room
curl -X POST -H 'Content-type: application/json' --data "{
    'attachments': [
        {
            'fallback': 'Failed test data',
            'text': 'There are failed tests.
             Test report: ${PREVIEW_LINK}/${SCREENSHOT_COMMIT_HASH}/${RELATIVE_REPORTS_LOCATION}/${REPORT_FILE} .
             All screenshots can be found  ${REPO_LINK}/tree/${SCREENSHOT_COMMIT_HASH}/${RELATIVE_REPORTS_LOCATION} .',
            'color': '#764FA5'
        }
    ], 'channel': '${CHANNEL}'
}"  ${SLACK_ROOMS};
