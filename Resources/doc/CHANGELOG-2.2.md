Netgen Tags Bundle 2.2 changelog
================================

2.2.5 (09.05.2017)
------------------

* Fix tags API always loading untranslated tags, even when they are NOT always available (thanks @Mrkisha)
* Translate names of module and functions in Platform UI

2.2.4 (23.02.2017)
------------------

* Allow synonyms to be displayed in `/tags/view` page (thanks @supasteev0)

2.2.3 (26.01.2017)
------------------

* Removed obsolete Platform UI integration code since it was causing issues (thanks @chicgeek)

2.2.2 (05.12.2016)
------------------

* Only activate support for Solr and legacy search engine bundles if they are activated
* Fix deprecations in YML files

2.2.1 (07.04.2016)
------------------

* Made Solr criteria take into account all fields in content when target is not provided (thanks @whitefire)
* Fixed a bug with building Solr query for `TagKeyword` criterion and `EQ` operator

2.2 (24.03.2016)
----------------

* Do not use `PHP_INT_MAX` in `TagsService` as it is unfriendly to Solr
* Remove deprecated `TagViewController::viewTag` action
* Remove setting the deprecated `pager` variable into tag view template
* Remove injecting deprecated `tagId` variable into tag view template
* Use `UrlGeneratorInterface` constants in tag router
* Replaced "Show dropdown instead of autocomplete" field definition setting with "Edit view", to select from possible edit views when editing content
* Added REST API for loading and creating/updating tags
