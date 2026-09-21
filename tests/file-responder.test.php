<?php
require __DIR__ . '/../includes/FileResponder.php';
if (parseSingleByteRange('bytes=10-19', 100) !== [10,19]) exit(1);
if (parseSingleByteRange('bytes=-10', 100) !== [90,99]) exit(1);
if (parseSingleByteRange('bytes=90-110', 100) !== [90,99]) exit(1);
if (parseSingleByteRange('bytes=10-', 100) !== [10,99]) exit(1);
if (parseSingleByteRange('bytes=-1000', 100) !== [0,99]) exit(1);
foreach (['bytes=100-101','bytes=0-1,4-5','items=0-1'] as $bad) {
    try { parseSingleByteRange($bad, 100); exit(1); } catch (InvalidRangeException $expected) {}
}
if (safeDownloadName("../보고서\r\nX-Test: yes.pdf") !== '보고서 X-Test yes.pdf') exit(1);
if (!fileResponderEtagMatches('W/"abc", "other"', '"abc"')) exit(1);
$missing = sys_get_temp_dir() . '/missing-managed-file-' . bin2hex(random_bytes(4));
ob_start();
streamContentFile(['sha256'=>str_repeat('a', 64), 'mime'=>'image/webp', 'original_name'=>'missing.webp'], $missing, false);
$missingOutput = ob_get_clean();
if ($missingOutput !== '' || http_response_code() !== 404) exit(1);
