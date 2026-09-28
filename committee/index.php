<?php
/**
 * 옛 동아리 신청 주소 — 인쇄물·QR로 퍼진 주소를 살리기 위해 바이브코딩동아리 안내로 영구 이동한다.
 */
require_once __DIR__ . '/../config.php';
header('Location: ' . url('club'), true, 301);
exit;
