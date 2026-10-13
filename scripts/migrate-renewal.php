<?php
if(PHP_SAPI!=='cli'){http_response_code(404);exit;}
require __DIR__.'/../config.php';require __DIR__.'/../includes/Database.php';require __DIR__.'/../includes/RenewalMigration.php';
migrateRenewal(Database::getInstance()->getConnection());echo "Renewal migration applied; existing data preserved.\n";
