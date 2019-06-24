Netgen Tags Bundle installation instructions
============================================

Requirements
------------

* eZ Platform 3.0+

Installation steps
------------------

### Use Composer

Run the following from your website root folder to install Netgen Tags Bundle:

```
$ composer require netgen/tagsbundle
```

### Activate the bundle

Activate the bundle in `config/bundles.php` file by adding it to the array, together with other required bundles:

```php
return [
    ...

    Lolautruche\EzCoreExtraBundle\EzCoreExtraBundle::class => ['all' => true],
    Netgen\TagsBundle\NetgenTagsBundle::class => ['all' => true],
];
```

### Add routing configuration

Add the file `config/routes/netgen_tags.yaml` with the following content to activate Netgen Tags routes:

```yml
netgen_tags:
    resource: "@NetgenTagsBundle/Resources/config/routing.yml"
```

### Import database tables

Netgen Tags Bundle uses custom database tables to store the tags. Use the following command to add the tables to your eZ Platform database:

```
$ mysql -u<user> -p<password> -h<host> <db_name> < vendor/netgen/tagsbundle/bundle/Resources/sql/mysql/schema.sql
```

PostgreSQL variant of the above schema file is also available at `vendor/netgen/tagsbundle/bundle/Resources/sql/postgresql/schema.sql`

Note: Netgen Tags supports eZ Platform schema builder, making it possible to automatically install its database tables when installing
clean/demo data of eZ Platform. In that case, there's no need to install the tables manually.

### Update Anonymous Role permissions

Give 'Read' permissions to the 'Tags' module for the `Anonymous` role.

### Clear the caches

Clear the eZ Platform caches with the following command:

```bash
$ php bin/console cache:clear
```

### Install assets

Run the following to correctly install assets for eZ Platform Admin UI:

```bash
$ php bin/console assets:install --symlink --relative
```

### Edit Varnish 4+ configuration (optional but recommended)

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

2) Use `TagsService` in your controllers to work with tags. The service is accessible through Symfony2 DIC, with ID `eztags.api.service.tags`
