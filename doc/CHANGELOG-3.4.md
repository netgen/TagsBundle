Netgen Tags Bundle 3.4 changelog
================================

3.4.2 (05.07.2019)
------------------

* `eztags.installer.listener.build_schema` event subscriber needs to be public on Symfony 2.8

3.4.1 (24.06.2019)
------------------

* Use siteaccess aware repository in tags service (thanks @RandyCupic)
* Renamed sequences in PostgreSQL to be inline with eZ Platform
* Added support for eZ Platform Doctrine schema builder
* Implement Nameable interface for the field type
* Added French translation file (thanks @peninonwilliam)
* Add support for advanced content filtering (thanks @RandyCupic)

3.4.0 (03.04.2019)
------------------

* Added possibility to browse for tags in field edit interface (thanks @JorgenSolli)
* Tag view page now uses the pagelayout configured in eZ Platform
* Fixed issues with eZ Platform Standard Design (thanks @amval)
* Performance enhancements to persistence cache (thanks @andrerom)
* Minimum supported versions of eZ Publish kernel are now 6.13 and 7.5
