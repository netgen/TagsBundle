#!/bin/bash

phpcs --standard=ezcs -s -p -v --extensions=php --encoding=utf-8 --ignore=_fixtures,vendor/,var/cache .
