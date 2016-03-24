Netgen Tags Bundle upgrade instructions
=======================================

Upgrade from 2.1 to 2.2
-----------------------

After installing Tags Bundle 2.2, run the SQL upgrade script by using the following command from your eZ Platform root folder:

    mysql -u "user" -p"password" -h"host" "database" < vendor/netgen/tagsbundle/Resources/sql/upgrade/mysql/2.2/dbupdate-2.1-to-2.2.sql
