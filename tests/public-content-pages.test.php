<?php
require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/../includes/ContentPresenter.php';
$rules = file_get_contents(__DIR__ . '/../.htaccess');
foreach (['^activity/([a-z0-9-]+)/?$', '^resources/([a-z0-9-]+)/?$'] as $rule) if (strpos($rules, $rule) === false) exit(1);
$files = ['activity/index.php','activity/view.php','press/index.php','resources/index.php','resources/view.php'];
foreach ($files as $file) if (!is_file(__DIR__ . '/../' . $file)) exit(1);
if (strpos(file_get_contents(__DIR__ . '/../activity/view.php'), "findPublishedBySlug('activity'") === false) exit(1);
if (strpos(file_get_contents(__DIR__ . '/../resources/view.php'), "findPublishedBySlug('resource'") === false) exit(1);
if (strpos(file_get_contents(__DIR__ . '/../press/index.php'), "listPublished('press'") === false) exit(1);
$activityIndex = file_get_contents(__DIR__ . '/../activity/index.php');
$activityView = file_get_contents(__DIR__ . '/../activity/view.php');
foreach ([$activityIndex, $activityView] as $source) {
    if (strpos($source, "['file_width']") === false || strpos($source, "['file_height']") === false) exit(1);
    if (strpos($source, 'srcset=') === false || strpos($source, 'sizes=') === false) exit(1);
}
if (strpos(file_get_contents(__DIR__ . '/../media.php'), 'ensureVariant') === false) exit(1);
$mediaSource = file_get_contents(__DIR__ . '/../media.php');
if (strpos($mediaSource, 'hash_file(') !== false || strpos($mediaSource, 'filesize(') !== false) exit(1);
$srcset = contentImageSrcset(['file_id'=>7, 'file_width'=>1600]);
foreach (['media/7?w=480 480w', 'media/7?w=960 960w', 'media/7 1600w'] as $candidate) {
    if (strpos($srcset, $candidate) === false) exit(1);
}
