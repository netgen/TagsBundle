eZ Tags Bundle installation instructions
========================================

Requirements
------------

* eZ Publish 5.0+
* eZ Publish Legacy Stack with legacy eZ Tags extension installed and configured

Installation steps
------------------

1. This bundle is not yet available through Composer/Packagist, so you will have to install it manually for now.
   Checkout the repo into `src/EzSystems/TagsBundle` folder of your eZ Publish 5 installation.

   ```bash
   mkdir src/EzSystems
   cd src/EzSystems
   git clone http://path.to.repo/... TagsBundle
   ```

2. Activate the bundle in `ezpublish\EzPublishKernel.php` file, by appending `new EzSystems\TagsBundle\EzSystemsTagsBundle()`
   to `$bundles` array in `registerBundles()` method.

   ```php
   public function registerBundles()
   {
       $bundles = array(
           new FrameworkBundle(),
           ...
           new EzSystems\TagsBundle\EzSystemsTagsBundle()
       );

       ...
   }
   ```
3. Put the following in your `ezpublish/config.yml` file to be able to load `eztags` content field template. Be sure to
   replace `YOUR_SITEACCESS_NAME` text with the name of your frontend siteaccess.

   ```yml
   parameters:
       ezsettings.YOUR_SITEACCESS_NAME.field_templates:
           - {template: EzPublishCoreBundle::content_fields.html.twig, priority: 0}
           - {template: eZTagsBundle::eztags_content_field.html.twig, priority: 0}
   ```
