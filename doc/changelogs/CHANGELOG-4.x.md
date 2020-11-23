Netgen Tags Bundle 4.x changelog
================================

4.0.6 (23.11.2020)
------------------

* Fix filtering when using the target argument (thanks @Plopix)

4.0.5 (09.11.2020)
------------------

* Make sure siteaccess exists in request before using it in `SetPageLayoutListener`

4.0.4 (06.11.2020)
------------------

* Fixed missing browser validation for required field (thanks @hgiesenow)

4.0.3 (10.07.2020)
------------------

* Support `cmf_routing_object` as the route name for tag objects
* Fix move/copy/delete routes in admin

4.0.2 (10.07.2020)
------------------

* General fixes and cleanup

4.0.1 (21.04.2020)
------------------

* Support using Tag object with `ez_url` / `ez_path` Twig functions

4.0.0 (14.04.2020)
------------------

Since 4.0 is a new major release, there were a number of breaking changes, so be sure to read [upgrade instructions](UPGRADE.md#upgrade-from-34-to-40).

The following lists only the most important changes from version 3.4 to version 4.0:

* Minimum supported version of PHP is now PHP 7.3
* Minimum supported version of eZ Platform is now 3.0
* The codebase has been modernized across the board:
    - The entire code base is migrated to PHP 7 style code (scalar and return typehints, strict type checking)
    - Most of the classes are now `final`. Use composition to extend the functionality of the bundle
