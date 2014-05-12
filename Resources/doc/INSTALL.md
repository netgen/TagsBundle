Netgen Tags Bundle installation instructions
============================================

Requirements
------------

* eZ Publish 5.2+ / eZ Publish Community Project 2013.07+
* eZ Publish Legacy Stack with legacy eZ Tags 1.2.2 installed and configured

### Note

Netgen Tags Bundle 1.0 can only be used with eZ Publish Enterprise 5.2 or later or eZ Publish Community Project 2013.07 or later due to [changes in field type API](https://github.com/ezsystems/ezpublish-kernel/pull/429). If you have previous versions of eZ Publish, please use 0.9 version of Tags Bundle.

Installation steps
------------------

### Use Composer

Add the following to your composer.json and run `php composer.phar update` to refresh dependencies:

```json
"require": {
    "netgen/tagsbundle": "~1.0",
    "ezsystems/eztags-ls": "~1.3"
}
```

### Activate the bundle

Activate the bundle in `ezpublish\EzPublishKernel.php` file.

```php
use Netgen\TagsBundle\NetgenTagsBundle;

...

public function registerBundles()
{
   $bundles = array(
       new FrameworkBundle(),
       ...
       new NetgenTagsBundle()
   );

   ...
}
```

### Edit configuration

Put the following config in your `ezpublish/config/config.yml` file to be able to load `eztags` content field template.

```yml
parameters:
   ezsettings.YOUR_SITEACCESS_NAME.field_templates:
       - {template: EzPublishCoreBundle::content_fields.html.twig, priority: 0}
       - {template: NetgenTagsBundle::eztags_content_field.html.twig, priority: 0}
```

Be sure to replace `YOUR_SITEACCESS_NAME` text with the name of your frontend siteaccess.

### Clear the caches

Clear eZ Publish 5 caches.

```bash
php ezpublish/console cache:clear
```

### Use the bundle

1) You can now load and create content with `eztags` field type

2) Use `TagsService` in your controllers to work with tags. The service is accessible through Symfony2 DIC, with ID `ezpublish.api.service.tags`
