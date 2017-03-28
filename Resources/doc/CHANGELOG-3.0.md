Netgen Tags Bundle 3.0 changelog
================================

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
