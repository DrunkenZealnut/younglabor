<?php
require_once __DIR__.'/auth.php';
$entityKind=is_string($_GET['kind']??null) && in_array($_GET['kind'],['area','unit'],true)?$_GET['kind']:'area';
require __DIR__.'/work-form.php';
