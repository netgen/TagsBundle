Netgen Tags Bundle 5.x changelog
================================

5.0.0 (06.05.2022)
------------------

Since 5.0 is a new major release, there were a number of breaking changes, so be sure to read [upgrade instructions](UPGRADE.md#upgrade-from-40-to-50).

The following lists only the most important changes from version 4.0 to version 5.0:

* Minimum supported version of PHP is now PHP 7.4 or 8.0+
* Minimum supported version of Ibexa Platform is now 4.0
* Service name for Tags service has been renamed to `netgen_tags.api.service.tags` (old name is kept for BC)
* The codebase has been modernized across the board:
    - Property typehints have been added to all code

5.1.0 (06.09.2023)
------------------

* Implemented TagId and TagKeyword visitors for elasticsearch (Thanks @petarjakopec)
* Bumped all code to PHP 8.1

5.2.0 (29.03.2024)
------------------

* Bumped jQuery to 3.7.1 (Thanks @ljacmi)
