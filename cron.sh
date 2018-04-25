#!/usr/bin/env sh

set -e
php=`which php`
here="$( cd "$( dirname "${BASH_SOURCE[0]}" )" && pwd )"

cd ${here}
${php} run.php backup:backup

