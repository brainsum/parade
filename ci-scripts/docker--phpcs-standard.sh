#!/bin/sh

HAS_ERRORS=0
REVIEW_STANDARD="Drupal"

# Add binaries to PATH.
export PATH="/var/www/html/vendor/bin:${PATH}"

##
# Function to run the actual code review
#
# This function takes 2 params:
# @param string $1
#   The file path to the directory or file to check.
# @param string $2
#   The ignore pattern(s).
##
code_review () {
  echo "${LWHITE}$1${RESTORE}"

  if ! phpcs --standard="$REVIEW_STANDARD" -p --colors --extensions=php,module,inc,install,test,profile "$1"; then
    HAS_ERRORS=1
  fi
}

# Review custom modules, run each folder separately to avoid memory limits.
echo
echo "${LBLUE}> Sniffing Modules following '${REVIEW_STANDARD}' standard. ${RESTORE}"

for dir in /var/www/html/web/modules/contrib/parade/*/ ; do
  code_review "$dir"
done

echo "Check for ${REVIEW_STANDARD} finished, exiting with ${HAS_ERRORS}"

exit ${HAS_ERRORS}
