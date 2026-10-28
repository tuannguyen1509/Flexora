#!/bin/sh
# Run against canonical theme files, without bootstrapping real WordPress in fixtures.
set -eu
cd /workspace
find flexora flexora-child-starter tests -name '*.php' -print0 | xargs -0 -n 1 php -n -l >/tmp/flx-lint.log
printf 'PASS PHP lint: %s files\n' "$(find flexora flexora-child-starter tests -name '*.php' | wc -l)"
for scenario in native reentry; do
  php -n tests/foundation.php "$scenario"
  php -n tests/component-preflight.php "$scenario"
done
for test in registration assets compatibility head-enqueue; do php -n "tests/$test.php"; done
for scope in admin ajax rest cron cli; do php -n tests/scope.php "$scope"; done
for mode in enabled disabled admin component; do php -n tests/accessibility.php "$mode"; done
php -n tests/naming-css.php
php -n tests/fields.php
