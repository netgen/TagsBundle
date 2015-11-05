Netgen Tags Bundle changelog
============================

1.2.5 (05.11.2015)
------------------

* Parametrize pagelayout used in tag view page

1.2.4 (05.11.2015)
------------------

* Parametrize template used for tag view pages

1.2.3 (16.07.2015)
------------------

* Require `ezsystems/eztags-ls` 1.x

1.2.2 (13.05.2015)
------------------

* Support new namespaces for search implementation in eZ Publish kernel

1.2.1 (07.05.2015)
------------------

* Mark `eztags` field type as unindexable for now

1.2 (20.04.2015)
----------------

* Add support for specifying field definition settings in process of creating it
* Add support for setting the priority of tags stored in field, load tags from field by priority
* Switch loading field type view template to PrependExtensionInterface method
* Fixed a bug with removing a tag from content through Public API
* Fixed support for pgsql databases
* Fixed creating tags with parent ID 0


1.1.3 (19.08.2014)
------------------

* Fix recursion in signal slot methods `loadTagSynonyms` & `getTagSynonymCount`
* Fix signal slot methods `loadTagsByKeyword` and `getTagsByKeywordCount` not returning value
* Add unit tests for signal slot service


1.1.2 (27.06.2014)
------------------

* Enable reverse proxy caching of tag view page and clearing the cache via `X-Tag-Id` header
* Switched usage of `eztags.tag_view.related_content_list.limit` parameter in tag view controller to config resolver


1.1.1 (20.06.2014)
------------------

* Add loading tags and tags count by keyword in `TagsService`
* Add implementation of `Tag` limitation for use in `tags/add` policy


1.1 (27.05.2014)
----------------

* Add `TagId` content & location search criterion
* Add `TagKeyword` content & location search criterion
* Allow loading tags and tag count from root level (by making `$tag` parameter in `TagsService::loadTagChildren` and `TagsService::getTagChildrenCount` optional)
* Implement loading a tag by its URL (for example `ez+publish/extensions/eztags`)
* Add a controller to render `/tag/{tagId}` and `/tag/{tagUrl}` pages (includes pagination)
* Add `eztags_tag_url` Twig function to be able to link to `/tag/{tagUrl}` page properly
* Add links to `/tag/{tagUrl}` page for each tag in `eztags` content field template
* Reconfigure unit tests to allow running from repo root instead of eZ Publish 5 root


1.0 (08.07.2013)
----------------

* Update Tags field type to new version of field type API
* Fix bug with not handling -1 as limit in `TagsService::getRelatedContent`


0.9 (19.06.2013)
----------------

* Initial release
