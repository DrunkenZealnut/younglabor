<?php
require_once __DIR__.'/../auth.php';header('Content-Type: application/json; charset=utf-8');http_response_code(410);echo json_encode(['success'=>false,'message'=>'이 신청 관리 기능은 종료되었습니다.'],JSON_UNESCAPED_UNICODE);
