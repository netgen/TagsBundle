eZ Tags Bundle installation instructions
========================================

Requirements
------------

* eZ Publish 5.0+
* eZ Publish Legacy Stack with legacy eZ Tags 1.2.2 installed and configured

Installation steps
------------------

### Use Composer

Add the following to your composer.json and run `php composer.phar update` to refresh dependencies:

```json
"repositories": [
    {
        "type": "vcs",
        "url": "https://github.com/netgen/NetgenTagsBundle.git"
    }
],
"require": {
    "netgen/tagsbundle": "dev-master"
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

Put the following config in your `ezpublish/config.yml` file to be able to load `eztags` content field template.

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
