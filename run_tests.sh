#/usr/bin/env/sh

set -e

phpunit -d memory_limit=-1 --colors -c phpunit.xml
phpunit -d memory_limit=-1 --colors -c phpunit-integration-legacy.xml
