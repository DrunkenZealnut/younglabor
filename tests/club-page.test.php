<?php
function failClubPage(string $message): void
{
    fwrite(STDERR, $message . "\n");
    exit(1);
}

$path = __DIR__ . '/../club.php';
if (!is_file($path)) failClubPage('club.php가 없습니다.');
if (is_dir(__DIR__ . '/../club')) failClubPage('club/ 디렉터리가 있으면 /club이 club.php로 가지 않습니다.');

$source = file_get_contents($path);
$required = [
    'siteClubRecruitment()', 'clubRecruitmentIsOpen()', 'clubDeadlineLabel()',
    '나도 개발자!', '러버블(Lovable)', '만 18세 미만 참가자는 보호자 동의가 필요합니다.',
    '외부 서비스로 이동', 'class="sponsor-box"', "url('activity')", '#contact',
    'class="tool-domain"', "htmlspecialchars(\$club['apply_url']", "htmlspecialchars(\$club['apply_domain']", 'class="content-back"',
];
foreach ($required as $needle) {
    if (strpos($source, $needle) === false) failClubPage("club.php에 없음: {$needle}");
}
foreach (['<iframe', 'target="_blank"', 'Claude Code', '클로드코드'] as $forbidden) {
    if (stripos($source, $forbidden) !== false) failClubPage("club.php에 금지 문구: {$forbidden}");
}

$css = file_get_contents(__DIR__ . '/../assets/css/style.css');
foreach (['.club-status', '.club-examples', '.club-facts', '.sponsor-box', '.page-header .club-status', 'width: fit-content'] as $selector) {
    if (strpos($css, $selector) === false) failClubPage("style.css에 없음: {$selector}");
}
