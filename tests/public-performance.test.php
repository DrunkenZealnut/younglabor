<?php
require_once __DIR__ . '/../config.php';
$url = assetUrl('assets/css/style.css');
if (!preg_match('/\/assets\/css\/style\.css\?v=\d+$/', $url)) exit(1);
$header = file_get_contents(__DIR__ . '/../includes/header.php');
if (strpos($header, "assetUrl('assets/css/style.css')") === false) exit(1);
$rules = file_get_contents(__DIR__ . '/../.htaccess');
foreach (['<IfModule mod_expires.c>', '<IfModule mod_headers.c>', '<IfModule mod_deflate.c>'] as $directive) {
    if (strpos($rules, $directive) === false) exit(1);
}
if (file_exists(__DIR__ . '/../assets/images/hero.jpg')) exit(1);
