Netgen Tags Bundle installation instructions
============================================

Requirements
------------

* eZ Publish 5.3+ / eZ Publish Community Project 2014.05+
* eZ Publish Legacy Stack with legacy eZ Tags 1.3 installed and configured

### Note

Netgen Tags Bundle 1.0 can only be used with eZ Publish Enterprise 5.2 or eZ Publish Community Project 2013.07-2014.03 due to [changes in field type API](https://github.com/ezsystems/ezpublish-kernel/pull/429). If you have previous versions of eZ Publish, please use 0.9 version of Tags Bundle.

Netgen Tags Bundle 1.1 can only be used with eZ Publish Enterprise 5.3 or later or eZ Publish Community Project 2014.05 or later.

Installation steps
------------------

### Use Composer

Add the following to your composer.json and run `php composer.phar update` to refresh dependencies:

```json
"require": {
    "netgen/tagsbundle": "~1.1",
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

Put the following in your `ezpublish/config/routing.yml` file to be able to display tag view pages:

```yml
_eztagsRoutes:
    resource: "@NetgenTagsBundle/Resources/config/routing.yml"
```

### Clear the caches

Clear eZ Publish 5 caches.

```bash
php ezpublish/console cache:clear
```

### Edit Varnish configuration

Add the following block to the end of `if (req.request == "PURGE")` block in `ez_purge` method in your Varnish configuration file to be able to clear Varnish cache for tag view pages:

```varnish
if ( req.http.X-Tag-Id == "*" ) {
    # Purge all tags
    ban( "obj.http.X-Tag-Id ~ ^[0-9]+$" );
    error 200 "Purge all tags done.";
} elseif ( req.http.X-Tag-Id ) {
    # Purge tag by its ID
    ban( "obj.http.X-Tag-Id == " + req.http.X-Tag-Id );
    error 200 "Purge of tag with id " + req.http.X-Tag-Id + " done.";
}
```

### Use the bundle

1) You can now load and create content with `eztags` field type

2) Use `TagsService` in your controllers to work with tags. The service is accessible through Symfony2 DIC, with ID `ezpublish.api.service.tags`
