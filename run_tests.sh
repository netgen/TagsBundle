#/usr/bin/env/sh

set -e

./vendor/bin/phpunit -d memory_limit=-1 --colors -c phpunit.xml
./vendor/bin/phpunit -d memory_limit=-1 --colors -c phpunit-integration-legacy.xml
