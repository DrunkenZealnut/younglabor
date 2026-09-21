<?php
$rules = file_get_contents(__DIR__ . '/../.htaccess');
foreach (['^activity/([a-z0-9-]+)/?$', '^resources/([a-z0-9-]+)/?$'] as $rule) if (strpos($rules, $rule) === false) exit(1);
$files = ['activity/index.php','activity/view.php','press/index.php','resources/index.php','resources/view.php'];
foreach ($files as $file) if (!is_file(__DIR__ . '/../' . $file)) exit(1);
if (strpos(file_get_contents(__DIR__ . '/../activity/view.php'), "findPublishedBySlug('activity'") === false) exit(1);
if (strpos(file_get_contents(__DIR__ . '/../resources/view.php'), "findPublishedBySlug('resource'") === false) exit(1);
if (strpos(file_get_contents(__DIR__ . '/../press/index.php'), "listPublished('press'") === false) exit(1);
