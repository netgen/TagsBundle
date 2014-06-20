Netgen Tags Bundle changelog
============================

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
