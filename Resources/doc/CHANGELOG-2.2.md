Netgen Tags Bundle 2.2 changelog
================================

2.2 (xx.xx.xxxx)
----------------

* Do not use `PHP_INT_MAX` in `TagsService` as it is unfriendly to Solr
* Remove deprecated `TagViewController::viewTag` action
* Remove setting the deprecated `pager` variable into tag view template
* Remove injecting deprecated `tagId` variable into tag view template
* Use `UrlGeneratorInterface` constants in tag router
