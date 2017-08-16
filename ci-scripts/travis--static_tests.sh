#!/bin/sh
set -e

# ---------------------------------------------------------------------------- #
#
# Run the coder review.
#
# ---------------------------------------------------------------------------- #

# Check the current build.
if [ -z "${CODE_REVIEW+x}" ] || [ "$CODE_REVIEW" -ne 1 ]; then
 exit 0;
fi

cd "${TRAVIS_BUILD_DIR}/docker"

# Workaround for docker-compose not supporting adding host variables to the container directly.
# @todo: Fix catching the error codes to avoid false positive builds.
if [ -n "${REVIEW_STANDARD}" ]; then
  if [ "${REVIEW_STANDARD}" = "Drupal" ]; then
    (docker-compose exec --user 82 php sh -c "sh docker--phpcs-standard.sh; exit $?"); RESCODE=$?; echo "Exiting with code ${RESCODE} \n"; exit $RESCODE;
  fi
  if [ "${REVIEW_STANDARD}" = "DrupalPractice" ]; then
    (docker-compose exec --user 82 php sh -c "sh docker--phpcs-practice.sh; exit $?"); RESCODE=$?; echo "Exiting with code ${RESCODE} \n"; exit $RESCODE;
  fi
fi
