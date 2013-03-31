#!/bin/bash

phpcs --standard=ezcs -p -v --extensions=php --encoding=utf-8 --ignore=_fixtures .
