#!/bin/bash

set -ev
php bin/console server:start -p 8001 --env test --no-debug --force
phantomjs --ssl-protocol=any --ignore-ssl-errors=true vendor/jcalderonzumba/gastonjs/src/Client/main.js 8510 1024 768 2>&1 >> /tmp/gastonjs.log &
