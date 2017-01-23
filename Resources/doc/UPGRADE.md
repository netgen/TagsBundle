Netgen Tags Bundle upgrade instructions
=======================================

Upgrade from 2.2 to 3.0
-----------------------

Tags Bundle 3.0 is a major release, with a number of breaking changes:

* Minimum supported version of PHP is now PHP 5.6
* Minimum supported version of eZ Platform is 1.4 (with eZ Publish kernel 6.4 and Platform UI 1.4)
* Minimum supported version of Repository Forms is 1.3
* Bundle now requires [`EzCoreExtraBundle`](https://github.com/lolautruche/EzCoreExtraBundle) to be activated to work properly

Upgrade from 2.1 to 2.2
-----------------------

After installing Tags Bundle 2.2, run the SQL upgrade script by using the following command from your eZ Platform root folder:

    mysql -u "user" -p"password" -h"host" "database" < vendor/netgen/tagsbundle/Resources/sql/upgrade/mysql/2.2/dbupdate-2.1-to-2.2.sql
