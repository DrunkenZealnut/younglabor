<?php
$source = file_get_contents(__DIR__ . '/../tools.php');
foreach (['siteTools()', '현장에서 생긴 질문을, 누구나 사용할 수 있는 도구로 만듭니다.', 'AI 답변과 계산 결과는 참고용이며 법적 효력이 없습니다.'] as $needle) {
    if (strpos($source, $needle) === false) exit(1);
}
if (stripos($source, '<iframe') !== false) exit(1);
if (strpos($source, 'target="_blank"') !== false) exit(1);
