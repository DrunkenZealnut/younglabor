<?php
/**
 * 옛 동아리 신청 API — 2026-09 전환으로 신청을 받지 않는다.
 * 요청 내용을 읽거나 저장·발송하지 않고 410만 반환한다.
 */
header('Content-Type: application/json; charset=utf-8');
http_response_code(410);
echo json_encode(['success' => false, 'message' => '이 신청은 종료되었습니다.'], JSON_UNESCAPED_UNICODE);
