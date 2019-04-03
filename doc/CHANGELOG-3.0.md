Netgen Tags Bundle 3.0 changelog
================================

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
