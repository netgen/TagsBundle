Netgen Tags Bundle 3.1 changelog
================================

3.1.4 (21.11.2017)
------------------

* Make the Pagerfanta view lazy to prevent out of memory exceptions

3.1.3 (21.11.2017)
------------------

* Allow installing eZ kernel 7.0

3.1.2 (10.11.2017)
------------------

* Field controller needs to be public (thanks @SylvainGuittard)

3.1.1 (30.10.2017)
------------------

* Twig global variable and tag router services need to be public

3.1.0 (27.10.2017)
------------------

* Use Twig paths to reference to Twig templates
* Replaced the `eztags` field type legacy storage gateway with Doctrine storage gateway
* Removed usage of deprecated gateway based storage API
* Removed usage of deprecated base field criterion visitor from eZ Solr bundle
* Bumped minimum eZ version version to 6.11
* Bumped minimum eZ Solr Search Engine Bundle version to 1.4
* Bumped minimum eZ Core Extra Bundle version to 2.0
* Dropped support for PHP 7.0
* New project structure
* Separate PSR-4 autoloading for bundle and tests
* All services are marked as public/private, for compatibility with Symfony 3.4
