<?php
require_once __DIR__ . '/../includes/SiteContent.php';

function assertSameValue($expected, $actual, string $message): void
{
    if ($expected !== $actual) {
        fwrite(STDERR, $message . "\n");
        exit(1);
    }
}

$areas = siteWorkAreas();
assertSameValue(['education', 'tools', 'research'], array_column($areas, 'key'), '활동 갈래 순서가 달라졌습니다.');

$tools = siteTools();
assertSameValue(2, count($tools), '안전 도구는 두 개여야 합니다.');
assertSameValue('https://safefactory.kr/', $tools[0]['url'], 'SafeFactory URL이 다릅니다.');
assertSameValue('https://laborconsult.vercel.app/', $tools[1]['url'], '노동상담 URL이 다릅니다.');
assertSameValue(['제작 중', '제작 중'], array_column($tools, 'status'), '두 도구 모두 제작 중이어야 합니다.');
foreach ($tools as $tool) {
    $parts = parse_url($tool['url']);
    assertSameValue('https', $parts['scheme'] ?? '', '도구 링크는 HTTPS여야 합니다.');
    if (!in_array($parts['host'] ?? '', ['safefactory.kr', 'laborconsult.vercel.app'], true)) {
        exit(1);
    }
}

if (function_exists('siteImpactStats')) exit(1);
$support = siteSupportPartners();
assertSameValue('아름다운재단', $support[0]['name'], '지원기관 이름이 다릅니다.');
assertSameValue('2025 공익단체 인큐베이팅 지원사업', $support[0]['program'], '지원사업 크레딧이 다릅니다.');
