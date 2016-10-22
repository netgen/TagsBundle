Netgen Tags Bundle installation instructions
============================================

Requirements
------------

* eZ Publish 5.4.5+ / eZ Publish Community Project 2014.11+
* eZ Publish Legacy Stack with legacy eZ Tags 2.0.x installed and configured

### Note

Netgen Tags Bundle 1.0 can only be used with eZ Publish Enterprise 5.2 or eZ Publish Community Project 2013.07-2014.03 due to [changes in field type API](https://github.com/ezsystems/ezpublish-kernel/pull/429). If you have previous versions of eZ Publish, please use 0.9 version of Tags Bundle.

Netgen Tags Bundle 1.1 can only be used with eZ Publish Enterprise 5.3 or later or eZ Publish Community Project 2014.05 or later.

Installation steps
------------------

### Use Composer

Run the following from your website root folder to install Netgen Tags Bundle:

```
$ composer require netgen/tagsbundle:~2.0.0
```

### Activate the bundle

Activate the bundle in `ezpublish/EzPublishKernel.php` file by adding it to the `$bundles` array in `registerBundles` method:

```php
public function registerBundles()
{
    ...

    $bundles[] = new Netgen\TagsBundle\NetgenTagsBundle();

    return $bundles;
}
```

*NB:* make sure that the NetgenTagsBundle bundle is loaded *after* the eZPublish Kernel bundles, otherwise you might get an error related to bad services configuration.

### Edit configuration

Put the following in your `ezpublish/config/routing.yml` file to be able to display tag view pages:

```yml
_eztagsRoutes:
    resource: "@NetgenTagsBundle/Resources/config/routing.yml"
```

### Import database tables

Netgen Tags Bundle uses custom database tables to store the tags. Use the following command to add the tables to your eZ Publish database:

```
$ mysql -u<user> -p<password> -h<host> <db_name> < vendor/netgen/tagsbundle/Netgen/TagsBundle/Resources/sql/mysql/schema.sql
```

PostgreSQL variant of the above schema file is also available at `vendor/netgen/tagsbundle/Netgen/TagsBundle/Resources/sql/postgresql/schema.sql`

### Clear the caches

Clear eZ Publish 5 caches.

```bash
$ php ezpublish/console cache:clear
```

### Edit Varnish configuration (optional but recommended)

#### Varnish 3

Add the following block to the end of `if (req.request == "BAN")` block in `ez_purge` method in your Varnish configuration file to be able to clear Varnish cache for tag view pages:

```varnish
if ( req.http.X-Tag-Id == "*" ) {
    # Ban all tags
    ban( "obj.http.X-Tag-Id ~ ^[0-9]+$" );

    if (client.ip ~ debuggers) {
        set req.http.X-Debug = "Ban done for all tags";
    }

    error 200 "Banned";
} elseif ( req.http.X-Tag-Id ) {
    # Ban tag by its ID
    ban( "obj.http.X-Tag-Id == " + req.http.X-Tag-Id );

    if (client.ip ~ debuggers) {
        set req.http.X-Debug = "Ban done for tag with ID " + req.http.X-Tag-Id;
    }

    error 200 "Banned";
}
```

#### Varnish 4

Add the following block to the end of `if (req.method == "BAN")` block in `ez_purge` method in your Varnish configuration file to be able to clear Varnish cache for tag view pages:

```varnish
if ( req.http.X-Tag-Id == "*" ) {
    # Ban all tags
    ban( "obj.http.X-Tag-Id ~ ^[0-9]+$" );

    if (client.ip ~ debuggers) {
        set req.http.X-Debug = "Ban done for all tags";
    }

    return (synth(200, "Banned"));
} elseif ( req.http.X-Tag-Id ) {
    # Ban tag by its ID
    ban( "obj.http.X-Tag-Id == " + req.http.X-Tag-Id );

    if (client.ip ~ debuggers) {
        set req.http.X-Debug = "Ban done for tag with ID " + req.http.X-Tag-Id;
    }

    return (synth(200, "Banned"));
}
```

#### Clearing tag view pages caches

After you restart Varnish, you will be able to clear the caches for a specific tag with the following example shell command:

```bash
$ curl -v -X BAN -H "X-Tag-Id: 1" http://varnish.local:81/
```

This will clear Varnish cache for tag view pages for tag with ID of 1.

### Use the bundle

1) You can now load and create content with `eztags` field type

2) Use `TagsService` in your controllers to work with tags. The service is accessible through Symfony2 DIC, with ID `ezpublish.api.service.tags`
