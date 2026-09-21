<?php
$list = file_get_contents(__DIR__ . '/../admin/content.php');
$edit = file_get_contents(__DIR__ . '/../admin/content-edit.php');
foreach ([$list, $edit] as $source) {
    if (strpos($source, "require_once __DIR__ . '/auth.php'") === false) exit(1);
    foreach (['verifyCsrfToken()', "\$_SERVER['REQUEST_METHOD'] === 'POST'", 'ContentRepository'] as $needle) {
        if (strpos($source, $needle) === false) exit(1);
    }
}
if (strpos($edit, '$adminUser[\'id\']') === false || strpos($edit, 'expected_revision') === false) exit(1);
if (strpos($edit, '$formRevision') === false || strpos($edit, '$conflicted') === false) exit(1);
if (strpos($edit, 'value="<?php echo (int)$formRevision; ?>"') === false) exit(1);
if (strpos($edit, "<?php echo \$conflicted ? ' disabled' : ''; ?>") === false) exit(1);
if (strpos($edit, 'catch (ContentSlugConflictException $error)') === false) exit(1);
if (strpos($edit, 'catch (ContentConflictException $error)') === false) exit(1);
if (strpos($edit, "\$errors['slug'] = '이미 사용 중인 주소 이름입니다.';") === false) exit(1);
if (stripos($edit, 'contenteditable') !== false || stripos($edit, 'wysiwyg') !== false) exit(1);
