<?php
require __DIR__.'/renewal-club.test.php';
check(!is_dir(__DIR__.'/../club'),'club route has no conflicting directory');
check(!str_contains($html,'<iframe'),'application stays on external service');
check(str_contains($html,'외부 서비스로 이동'),'external application labelled');
check(str_contains($html,'학교 협력 문의하기'),'school cooperation path available');
