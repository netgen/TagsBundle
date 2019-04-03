Netgen Tags Bundle upgrade instructions
=======================================

Notes on upgrading to eZ Platform 2.2
-------------------------------------

Beginning with 2.2, eZ Platform introduced support for `utf8mb4` MySQL character set. When upgrading to eZ Platform 2.2, make
sure to update Netgen Tags table indexes and primary keys with the following commands to properly support `utf8mb4` charset.

```sql
ALTER TABLE `eztags` DROP KEY `idx_eztags_keyword`;
ALTER TABLE `eztags` ADD KEY `idx_eztags_keyword` (`keyword`(191));

ALTER TABLE `eztags` DROP KEY `idx_eztags_keyword_id`;
ALTER TABLE `eztags` ADD KEY `idx_eztags_keyword_id` (`keyword`(191), id);

ALTER TABLE `eztags_keyword` DROP PRIMARY KEY;
ALTER TABLE `eztags_keyword` ADD PRIMARY KEY (`keyword_id`, `locale`(191));
```

Upgrade from 3.3 to 3.4
-----------------------

* `tag_view.pagelayout` configuration for setting the pagelayout of the tag view page has been deprecated and its usages removed. Instead, it has been replaced with pagelayout config from eZ Platform.

  If you didn't override the tag view template and wish to continue using this parameter, override the `@NetgenTags/tag/view.html.twig` template and use the following `extends` instead of the original one:

  `{% extends ezpublish.configResolver.parameter( 'tag_view.pagelayout', 'eztags' ) %}`

* Minimum supported version of eZ Platform is now 1.13 (with Repository Forms 1.11 and Platform UI 1.13) and eZ Platform 7.5
* Minimum supported version of Solr Search Engine is now 1.5

Upgrade from 3.0 to 3.1
-----------------------

* `eztags.field_type.eztags.storage_gateway` service now points to the Doctrine version of storage gateway. If you want to keep using the legacy version in your code, it is available with `eztags.field_type.eztags.storage_gateway.legacy` service name
* Minimum supported version of eZ Platform is now 1.11 (with Repository Forms 1.9 and Platform UI 1.11)
* Minimum supported version of Solr Search Engine is now 1.4
* Support for eZ Core Extra Bundle 1.0 has been dropped
* Support for PHP 7.0 has been dropped

Upgrade from 2.2 to 3.0
-----------------------

Tags Bundle 3.0 is a major release, with a number of breaking changes:

* Minimum supported version of PHP is now PHP 5.6
* Minimum supported version of eZ Platform is 1.5 (with eZ Publish kernel 6.5.2 and Platform UI 1.5)
* Minimum supported version of Repository Forms is 1.4
* Minimum supported version of eZ Platform Solr Search Engine is 1.1.3
* All `*.class` parameters have been removed from Symfony DIC. Override the whole service if needed, as recommended by Symfony
* Support for eZ Publish Legacy is completely removed (meaning, tag object converter is removed)
* `enable_tag_router` config was removed, as it was used for legacy tags admin interface
* Bundle now requires [`EzCoreExtraBundle`](https://github.com/lolautruche/EzCoreExtraBundle) to be activated to work properly
* `subTreeLimit` and `maxTags` field settings are now part of `TagsValueValidator` validator schema. You will have to modify your code working with `subTreeLimit` and `maxTags` field settings to specify them as validators.
* Content and Location `TagId` and `TagKeyword` Solr criterion visitors are removed and replaced with universal criterion visitors, one for `TagId` and one for `TagKeyword` criterion, which are used both for Content and Location search
* `tags/id`, `tags/dashboard` and `tags/search` policies have been removed. They have been used for legacy admin interface and are unused in the new one
* `tags/view` policy is now required to use `tags/view` route (that is, full view of the tag). Be sure to add the policy to all users that need access to the route
* Tag full view page does not set `Last-Modified` header any more. If you relied on it, implement a listener which sets the header based on tag last modification time
* TagView object does not implement `CachableView` from eZ kernel any more, instead, it now implements own implementation `Netgen\TagsBundle\View\CachableView` which has the same signature as eZ one
* `TagsService::getRelatedContent` and matching Pagerfanta adapter now return `ContentInfo` objects by default instead of `Content`. You can change the configuration of the adapter to get the old behavior back
* Tag Value object constructor only allows receiving array of tags, thus `null` is not supported any more
* Number of Symfony services had their name changed to use TagsBundle (`eztags`) prefix. The following table lists the old names and the new names. Backwards compatibility for old names is kept only for the main `TagsService`

  | Old name | New name
  | -------- | --------
  | `ezpublish.api.persistence_handler.tags.factory` | `eztags.api.persistence_handler.tags.factory`
  | `ezpublish.api.persistence_handler.tags` | `eztags.api.persistence_handler.tags`
  | `ezpublish.api.service.tags` | `eztags.api.service.tags` (Old name is kept for BC)
  | `ezpublish.signalslot.service.tags` | `eztags.signalslot.service.tags`
  | `ezpublish.fieldType.eztags` | `eztags.field_type.eztags`
  | `ezpublish.fieldType.eztags.externalStorage` | `eztags.field_type.eztags.external_storage`
  | `ezpublish.fieldType.indexable.eztags` | `eztags.field_type.eztags.indexable`
  | `ezpublish.fieldType.eztags.formMapper` | `eztags.field_type.eztags.form_mapper`
  | `ezpublish.fieldType.eztags.converter` | `eztags.field_type.eztags.converter`
  | `ezpublish.fieldType.eztags.storage_gateway` | `eztags.field_type.eztags.storage_gateway`
  | `ezpublish.api.storage_engine.legacy.handler.tags.factory` | `eztags.api.storage_engine.legacy.handler.tags.factory`
  | `ezpublish.api.storage_engine.legacy.handler.tags` | `eztags.api.storage_engine.legacy.handler.tags`
  | `ezpublish.search.legacy.gateway.criterion_handler.common.tag_id` | `eztags.search.legacy.gateway.criterion_handler.common.tag_id`
  | `ezpublish.search.legacy.gateway.criterion_handler.common.tag_keyword` | `eztags.search.legacy.gateway.criterion_handler.common.tag_keyword`

Upgrade from 2.1 to 2.2
-----------------------

After installing Tags Bundle 2.2, run the SQL upgrade script by using the following command from your eZ Platform root folder:

    mysql -u "user" -p"password" -h"host" "database" < vendor/netgen/tagsbundle/Resources/sql/upgrade/mysql/2.2/dbupdate-2.1-to-2.2.sql
