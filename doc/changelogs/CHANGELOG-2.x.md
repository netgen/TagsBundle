Netgen Tags Bundle 2.x changelog
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

2.2.0 (24.03.2016)
------------------

* Do not use `PHP_INT_MAX` in `TagsService` as it is unfriendly to Solr
* Remove deprecated `TagViewController::viewTag` action
* Remove setting the deprecated `pager` variable into tag view template
* Remove injecting deprecated `tagId` variable into tag view template
* Use `UrlGeneratorInterface` constants in tag router
* Replaced "Show dropdown instead of autocomplete" field definition setting with "Edit view", to select from possible edit views when editing content
* Added REST API for loading and creating/updating tags

2.1.3 (07.04.2016)
------------------

* Made Solr criteria take into account all fields in content when target is not provided (thanks @whitefire)
* Fixed a bug with building Solr query for `TagKeyword` criterion and `EQ` operator

2.1.2 (04.03.2016)
------------------

* Do not use `PHP_INT_MAX` in `TagsService` as it is unfriendly to Solr

2.1.1 (05.02.2016)
------------------

* Allow installing eZ Publish kernel > 6.0.x

2.1.0 (13.01.2016)
------------------

* Implemented Solr indexing handler
* Legacy search criterion handlers now support specifying target (i.e. field definition identifier)
* Refactored view controller to use eZ Platform view API
* Added support for exposing tags policies
* Add legacy template converter for `Tag` value object
* Various bug fixes

2.0.10 (04.03.2015)
-------------------

* Do not use `PHP_INT_MAX` in `TagsService` as it is unfriendly to Solr

2.0.9 (30.11.2015)
------------------

* Related content Pagerfanta adapter is now a service. Makes it possible to override it and implement custom related content logic (thanks @zsusac)

2.0.8 (05.11.2015)
------------------

* Parametrize pagelayout used in tag view page

2.0.7 (28.10.2015)
------------------

* Add `sudo` method to Tags service, used by tags router to match the tag since at that point user is still anonymous
* Add setting to enable or disable tag router, useful for legacy siteaccesses

2.0.6 (08.10.2015)
------------------

* Fix tag router causing config resolver to be created too early (thanks @wizhippo)

2.0.5 (25.09.2015)
------------------

* Also support `UserReference` argument in `TagLimitationType` methods

2.0.4 (10.09.2015)
------------------

* Replace `ez_trans_prop` usage with `netgen_tags_tag_keyword` function, to be able to get keyword by tag ID also

2.0.3 (10.09.2015)
------------------

* Do not use removed criterion property on Query object

2.0.2 (08.09.2015)
------------------

* Use `ez_trans_prop` to render tag keywords in most prioritized languages in field template and tag view template
* Fix generating tag URLs when fallback to internal route occurs
* Switch coding standards to PSR2

2.0.1 (16.07.2015)
------------------

* Fix calling `empty()` with expressions

2.0.0 (16.07.2015)
------------------

* Support multilanguage tags!
* Use Symfony router and generator to match and generate tag view URLs
* Path to tag view page changed from `/tag/{tagUrl}` to `/tags/view/{tagUrl}` to be compatible with legacy
* You can now use a container parameter to change the path prefix used to generate tag URLs
* You can now use a container parameter to select which template will `/tags/view` controller use
* Various bug fixes and optimizations
