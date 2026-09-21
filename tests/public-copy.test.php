<?php
$home = file_get_contents(__DIR__ . '/../index.php');
$about = file_get_contents(__DIR__ . '/../about.php');
$activities = file_get_contents(__DIR__ . '/../activities.php');
$required = [
    '중소영세 제조업 청년노동자와 함께, 처음 일하는 몸을 지킵니다.',
    '현장에서 무슨 일이 있었나', '왜 이 일을 하는가', '우리가 하는 일',
    '현장에서 쓰는 안전 도구', '언론이 본 현장', '숫자로 보는 활동', '함께하기', '함께하는 곳들',
];
$last = -1;
foreach ($required as $copy) {
    $position = strpos($home, $copy);
    if ($position === false || $position <= $last) exit(1);
    $last = $position;
}
foreach (['왜 제조업 청년노동자인가', '왜 반도체고에서 시작하는가'] as $copy) {
    if (strpos($about, $copy) === false) exit(1);
}
foreach (['현장 조직', '교육', '안전 도구', '연구'] as $copy) {
    if (strpos($activities, $copy) === false) exit(1);
}
