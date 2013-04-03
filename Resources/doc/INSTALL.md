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
        "url": "https://github.com/netgen/EzSystemsTagsBundle.git"
    }
],
"require": {
    "ezsystems/tagsbundle": "dev-master"
}
```

### Activate the bundle

Activate the bundle in `ezpublish\EzPublishKernel.php` file.

```php
use EzSystems\TagsBundle\EzSystemsTagsBundle;

...

public function registerBundles()
{
   $bundles = array(
       new FrameworkBundle(),
       ...
       new EzSystemsTagsBundle()
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
       - {template: eZTagsBundle::eztags_content_field.html.twig, priority: 0}
```

Be sure to replace `YOUR_SITEACCESS_NAME` text with the name of your frontend siteaccess.

### Clear the caches

Clear eZ Publish 5 caches.

```bash
php ezpublish/console cache:clear
```
