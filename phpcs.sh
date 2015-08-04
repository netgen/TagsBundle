#!/bin/bash

phpcs --standard=PSR2 -s -p -v --extensions=php --encoding=utf-8 --ignore=_fixtures,vendor/,var/cache,extension/ .
