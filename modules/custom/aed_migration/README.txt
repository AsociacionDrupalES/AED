1 Install the module and the dependencies.
2 add the database conection with the key upgrade to your settings.php
3 remove the current roles on your configuration (them will be reimported)
4 drush migrate-import --group=aed

Rollback
drush migrate-rollback --group=aed

