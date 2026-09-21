<?php
require_once __DIR__ . '/../includes/Privacy.php';

$cases = [
    '203.0.113.42' => '203.0.113.0',
    '2001:db8:1234:5678:90ab:cdef:1234:5678' => '2001:db8:1234:5678:90ab:cdef:1234:0',
    '2001:db8::1' => '2001:db8::',
    '2001:db8:1234::' => '2001:db8:1234::',
    '' => '',
    'not-an-ip' => 'not-an-ip',
];

foreach ($cases as $input => $expected) {
    $actual = maskIpAddress($input);
    if ($actual !== $expected) {
        fwrite(STDERR, "maskIpAddress($input) returned $actual; expected $expected\n");
        exit(1);
    }
}

$contact = file_get_contents(__DIR__ . '/../api/contact.php');
$tracker = file_get_contents(__DIR__ . '/../includes/PageTracker.php');
foreach ([$contact, $tracker] as $source) {
    if (strpos($source, 'maskIpAddress(') === false) exit(1);
}
