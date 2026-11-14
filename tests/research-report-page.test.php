<?php
// 반도체고 노동안전교과서 기초연구 소개 페이지: 보고서 PDF가 저장소에 있고, 페이지·홈·자료실이 그 경로로 연결되는지 확인
$root = __DIR__ . '/..';
$pdf = $root . '/assets/files/semiconductor-textbook-basic-research-2026.pdf';
if (!is_file($pdf) || filesize($pdf) < 100000 || file_get_contents($pdf, false, null, 0, 5) !== '%PDF-') { fwrite(STDERR, "보고서 PDF가 없거나 손상되었습니다.\n"); exit(1); }
$page = file_get_contents($root . '/research/semiconductor-textbook.php');
foreach (["'assets/files/semiconductor-textbook-basic-research-2026.pdf'", 'assetUrl($reportPdfPath)', '15장 목차(안)', "url('research/semiconductor-textbook')"] as $needle) {
    if (strpos($page, $needle) === false) { fwrite(STDERR, "소개 페이지에 '$needle'이 없습니다.\n"); exit(1); }
}
foreach (['index.php', 'resources/index.php'] as $entry) {
    if (strpos(file_get_contents($root . '/' . $entry), "url('research/semiconductor-textbook')") === false) { fwrite(STDERR, "$entry에 보고서 링크가 없습니다.\n"); exit(1); }
}
exec('git -C ' . escapeshellarg($root) . ' check-ignore -q assets/files/semiconductor-textbook-basic-research-2026.pdf', $out, $ignored);
if ($ignored === 0) { fwrite(STDERR, "보고서 PDF가 .gitignore에 걸려 배포되지 않습니다.\n"); exit(1); }
echo "research report page ok\n";
