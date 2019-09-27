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
    resource: "@NetgenTagsBundle/Resources/config/routing.yaml"
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

### Use the bundle

1) You can now load and create content with `eztags` field type

2) Use `TagsService` in your controllers to work with tags. The service is accessible through Symfony2 DIC, with ID `eztags.api.service.tags`
