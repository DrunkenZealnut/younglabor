<?php
class Database {
    public static function getInstance() { throw new RuntimeException('database unavailable'); }
}
require_once __DIR__ . '/../includes/PageTracker.php';
$_SERVER['HTTP_USER_AGENT'] = 'Public smoke browser';
$_SERVER['REQUEST_URI'] = '/';
PageTracker::track('홈');
echo "tracker failure isolated\n";
