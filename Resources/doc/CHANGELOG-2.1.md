Netgen Tags Bundle 2.1 changelog
================================

2.1.2 (04.03.2016)
------------------

* Do not use `PHP_INT_MAX` in `TagsService` as it is unfriendly to Solr

2.1.1 (05.02.2016)
------------------

* Allow installing eZ Publish kernel > 6.0.x

2.1 (13.01.2016)
----------------

* Implemented Solr indexing handler
* Legacy search criterion handlers now support specifying target (i.e. field definition identifier)
* Refactored view controller to use eZ Platform view API
* Added support for exposing tags policies
* Add legacy template converter for `Tag` value object
* Various bug fixes
