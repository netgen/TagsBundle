Netgen Tags Bundle 3.x changelog
================================

3.4.8 (09.11.2020)
------------------

* Make sure siteaccess exists in request before using it in `SetPageLayoutListener`

3.4.7 (06.11.2020)
------------------

* Fixed missing browser validation for required field (thanks @hgiesenow)

3.4.6 (16.03.2020)
------------------

* Fix translations for roles in eZ Platform Admin UI

3.4.5 (19.12.2019)
------------------

* Field type nameable services need to be public (thanks @RandyCupic)

3.4.4 (22.10.2019)
------------------

* Update logo and colors to new Netgen branding, vol2

3.4.3 (18.10.2019)
------------------

* Updated logo and colors to new Netgen branding

3.4.2 (05.07.2019)
------------------

* `eztags.installer.listener.build_schema` event subscriber needs to be public on Symfony 2.8

3.4.1 (24.06.2019)
------------------

* Use siteaccess aware repository in tags service (thanks @RandyCupic)
* Renamed sequences in PostgreSQL to be inline with eZ Platform
* Added support for eZ Platform Doctrine schema builder
* Implement Nameable interface for the field type
* Added French translation file (thanks @peninonwilliam)
* Add support for advanced content filtering (thanks @RandyCupic)

3.4.0 (03.04.2019)
------------------

* Added possibility to browse for tags in field edit interface (thanks @JorgenSolli)
* Tag view page now uses the pagelayout configured in eZ Platform
* Fixed issues with eZ Platform Standard Design (thanks @amval)
* Performance enhancements to persistence cache (thanks @andrerom)
* Minimum supported versions of eZ Publish kernel are now 6.13 and 7.5

3.3.5 (23.05.2019)
------------------

* Use siteaccess aware repository in tags service (thanks @RandyCupic)

3.3.4 (02.04.2019)
------------------

* Added styles for tags input in eZ Platform Admin UI (thanks @JorgenSolli)
* `TagsService::sudo()` is no2 part of the public API

3.3.3 (14.11.2018)
------------------

* Removed the not implemented tree view from field edit interface

3.3.2 (06.10.2018)
------------------

* Fix duplicate YAML key
* Fixes to tests

3.3.1 (05.10.2018)
------------------

* Added display of tag remote ID in the admin interface
* Renamed the title of the tab in eZ admin interface
* Various install instructions improvements

3.3.0 (28.03.2018)
------------------

* Persistence cache is introduced. It will automatically use any persistence cache configured in eZ Platform v1 or v2.

3.2.1 (17.01.2018)
------------------

* Fix issue with disappearing "Add" button in field edit interface in eZ Platform UI (thanks @konradoboza)

3.2.0 (22.12.2017)
------------------

* Add support for eZ Platform Admin UI v2
* Usage of Twig classes switched to namespaces
* Separated Twig extension definition from its runtime

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

3.0.8 (16.10.2017)
------------------

* Fix query error when fetching children after 3.0.6

3.0.7 (05.10.2017)
------------------

* Fix ordering of tags by keyword when fetching children after 3.0.6

3.0.6 (04.10.2017)
------------------

* Fixed an issue with wrong usage of offset and limit in storage gateway. `getChildren`, `getSynonyms`
  and `getTagsByKeywords` methods would return `$limit` ROWS instead of `$limit` tags, which meant that
  not all tag translations would be loaded.

3.0.5 (21.08.2017)
------------------

* Fixed storing tags with special characters (e.g. `&`) when using Platform UI admin interface

3.0.4 (19.06.2017)
------------------

* Allow installation of version 2.x of `lolautruche/ez-core-extra-bundle` (thanks @wizhippo)

3.0.3 (09.05.2017)
------------------

* Fix tags API always loading untranslated tags, even when they are NOT always available (thanks @Mrkisha)
* Translate names of module and functions in Platform UI

3.0.2 (08.07.2017)
------------------

* Require ^1.0 version of `lolautruche/ez-core-extra-bundle`

3.0.1 (28.03.2017)
------------------

* Fixed admin interface buttons not working in Firefox or Safari

3.0.0 (09.03.2017)
------------------

Since 3.0 is a new major release, there were a number of breaking changes, so be sure to read [upgrade instructions](UPGRADE.md#upgrade-from-22-to-30).

This release was backed in [crowdfunding campaign](https://www.indiegogo.com/projects/netgen-tags-bundle-support-for-ez-platform-ui--3) by [Netmaking AS](https://netmaking.no), [Greater Stavanger](http://www.greaterstavanger.com) and others.

The following lists only the most important changes from version 2.2 to version 3.0:

* Admin UI is added, which can work standalone at `/tags/admin/`, or integrated into Platform UI
* Tags field edit interface and field definition interfaces are implemented for Platform UI
* You can now move and copy tags to the root of the tree
* You can now search for tags starting with a provided string
* You can now use semantic configuration to configure the bundle
* Content related to a tag is now sorted by modified date, descending
* Allow synonyms to be displayed in `/tags/view` page (thanks @supasteev0)
* Removed support for eZ Publish Legacy
* Fixed user policies without limitations not working with roles applied to subtrees
