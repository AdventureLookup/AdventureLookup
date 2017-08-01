#!/bin/bash

set -ev
php bin/console server:start 8003 --env test --pidfile /tmp/adl-test-server.pid
phantomjs --ssl-protocol=any --ignore-ssl-errors=true vendor/jcalderonzumba/gastonjs/src/Client/main.js 8510 1024 768 2>&1 >> /tmp/gastonjs.log &
