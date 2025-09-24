<?php
// /admin/menu/delete.php
require '../auth.php';
require '../db.php';

// 한글 깨짐 방지를 위한 문자셋 설정
header('Content-Type: text/html; charset=utf-8');
mb_internal_encoding('UTF-8');

// ID 확인
if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
  echo "<script>alert('올바르지 않은 접근입니다.'); location.href='list.php';</script>";
  exit;
}

$id = (int)$_GET['id'];

try {
  // 1. 메뉴가 존재하는지 확인
  $stmt = $pdo->prepare("SELECT id, title FROM hopec_menu WHERE id = ?");
  $stmt->execute([$id]);
  $menu = $stmt->fetch(PDO::FETCH_ASSOC);
  
  if (!$menu) {
    echo "<script>alert('존재하지 않는 메뉴입니다.'); location.href='list.php';</script>";
    exit;
  }
  
  // 2. 하위 메뉴가 있는지 확인 (외래 키 제약으로 자동 삭제되지만, 사용자에게 알려주기 위해)
  $stmt = $pdo->prepare("SELECT COUNT(*) FROM hopec_menu WHERE parent_id = ?");
  $stmt->execute([$id]);
  $childCount = $stmt->fetchColumn();
  
  // 3. 메뉴 삭제 (하위 메뉴도 외래키 제약 조건의 CASCADE 옵션으로 자동 삭제됨)
  $stmt = $pdo->prepare("DELETE FROM hopec_menu WHERE id = ?");
  $result = $stmt->execute([$id]);
  
  if ($result) {
    $message = '메뉴가 삭제되었습니다.';
    if ($childCount > 0) {
      $message .= ' 하위 메뉴 ' . $childCount . '개도 함께 삭제되었습니다.';
    }
    echo "<script>alert('$message'); location.href='list.php';</script>";
  } else {
    echo "<script>alert('메뉴 삭제 중 오류가 발생했습니다.'); location.href='list.php';</script>";
  }
  
} catch (PDOException $e) {
  // 외래 키 제약 조건 등의 오류가 발생할 경우
  echo "<script>alert('데이터베이스 오류: " . addslashes($e->getMessage()) . "'); location.href='list.php';</script>";
}
?> 