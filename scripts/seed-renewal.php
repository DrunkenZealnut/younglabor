<?php
if(PHP_SAPI!=='cli'){http_response_code(404);exit;}
require __DIR__.'/../config.php';require __DIR__.'/../includes/Database.php';require __DIR__.'/../includes/RenewalSeed.php';
seedRenewal(Database::getInstance()->getConnection());echo "Initial renewal content added without overwriting edits.\n";
