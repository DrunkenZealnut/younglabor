<?php
require_once __DIR__ . '/../config.php';
header('Location: ' . url('board/'.(is_string($_GET['slug']??null)?$_GET['slug']:'')), true, 301);
exit;
