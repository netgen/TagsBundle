Netgen Tags Bundle [![Build status](https://travis-ci.org/netgen/TagsBundle.png)](https://travis-ci.org/netgen/TagsBundle)
==================

Netgen Tags Bundle is an eZ Publish 5 bundle for taxonomy management and easier classification of content, providing more functionality for tagging content than ezkeyword field type included in eZ Publish kernel.

This repository represents eZ Publish 5 rewrite of the original eZ Publish 4 extension located at [http://github.com/ezsystems/eztags](/ezsystems/eztags).

Implemented features
--------------------

* `eztags` field type
* Tags service and legacy SPI handler
* SignalSlot Tags service

License and installation instructions
-------------------------------------

[License](LICENSE)

[Installation instructions](Resources/doc/INSTALL.md)

Unit tests
----------

There are two sets of tests available, unit tests and legacy integration tests. Both sets of tests are ran from root folder of eZ Publish 5 install.

Before running the tests, copy (or symlink) `config.php-DEVELOPMENT` file to `config.php` in eZ Publish kernel:

    $ cp vendor/ezsystems/ezpublish-kernel/config.php-DEVELOPMENT vendor/ezsystems/ezpublish-kernel/config.php

### Running unit tests

    $ phpunit -c vendor/netgen/tagsbundle/Netgen/TagsBundle/phpunit.xml

### Running legacy integration tests

    $ phpunit -c vendor/netgen/tagsbundle/Netgen/TagsBundle/phpunit-integration-legacy.xml
