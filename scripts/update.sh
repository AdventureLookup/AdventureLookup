#!/bin/bash
# Can be called from the application's root directory to pull the latest changes from GitHub and apply them.
# An optional argument specifies the branch name, defaults to origin/master.
# Example for origin/dev branch:
#   ./scripts/update.sh origin/dev

set -e
if [ -z "$1" ]; then
    BRANCH=origin/master
else
    BRANCH=$1
fi
echo "Updating using branch $BRANCH."
set -ev
git fetch --all
git reset --hard $BRANCH
rm -rf var/cache/prod/*
export SYMFONY_ENV=prod
composer install --no-dev --optimize-autoloader -n
npm install
npm run prod
php bin/console doctrine:migrations:migrate -n
php bin/console app:elasticsearch:reindex -n
rm -fR var/sessions/prod/*
rm -f web/config.php web/app_dev.php web/app_test.php
exit 0
