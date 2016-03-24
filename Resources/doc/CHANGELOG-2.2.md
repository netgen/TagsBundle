Netgen Tags Bundle 2.2 changelog
================================

2.2 (xx.xx.xxxx)
----------------

* Do not use `PHP_INT_MAX` in `TagsService` as it is unfriendly to Solr
* Remove deprecated `TagViewController::viewTag` action
* Remove setting the deprecated `pager` variable into tag view template
* Remove injecting deprecated `tagId` variable into tag view template
* Use `UrlGeneratorInterface` constants in tag router
* Replaced "Show dropdown instead of autocomplete" field definition setting with "Edit view", to select from possible edit views when editing content
* Added implementation of tree view in field edit interface
* Added REST API for loading tags
