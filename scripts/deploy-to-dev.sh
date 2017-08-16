#!/bin/bash
# Can be called from the application's root directory to deploy the latest changes from GitHub to the dev server.
# Assumes the SSH_PORT is set in an environment variable.

ssh deploy@dev.adventurelookup.com -p ${SSH_PORT} 'set -ev; cd /var/www/dev.adventurelookup.com/html/; bash -s "origin/dev";' < ./scripts/update.sh
