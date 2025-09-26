<?php
// /admin/boards/delete.php
require '../bootstrap.php';

// 한글 깨짐 방지를 위한 문자셋 설정
header('Content-Type: text/html; charset=utf-8');
mb_internal_encoding('UTF-8');

// ID 확인
if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
  echo "<script>alert('올바르지 않은 접근입니다.'); location.href='list.php';</script>";
  exit;
}

$id = (int)$_GET['id'];

// 확인 단계
if (!isset($_GET['confirm']) || $_GET['confirm'] !== 'yes') {
  // 게시판 정보 조회
  try {
    $tableName = get_table_name('boards');
    $stmt = $pdo->prepare("SELECT board_name FROM {$tableName} WHERE id = ?");
    $stmt->execute([$id]);
    $board = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$board) {
      echo "<script>alert('존재하지 않는 게시판입니다.'); location.href='list.php';</script>";
      exit;
    }
    
    // 확인 페이지 표시
    ?>
    <!DOCTYPE html>
    <html lang="ko">
    <head>
      <meta charset="UTF-8">
      <meta http-equiv="Content-Type" content="text/html; charset=utf-8">
      <title>게시판 삭제 확인</title>
      <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    </head>
    <body>
    <div class="container mt-5">
      <div class="row justify-content-center">
        <div class="col-md-6">
          <div class="card shadow">
            <div class="card-header bg-danger text-white">
              <h5 class="mb-0">게시판 삭제 확인</h5>
            </div>
            <div class="card-body">
              <div class="alert alert-warning">
                <h5 class="alert-heading">⚠️ 경고</h5>
                <p>게시판을 삭제하면 이 게시판에 등록된 모든 게시글도 함께 삭제되며, 이 작업은 되돌릴 수 없습니다.</p>
              </div>
              
              <p>정말로 <strong>"<?= htmlspecialchars($board['board_name']) ?>"</strong> 게시판을 삭제하시겠습니까?</p>
              
              <div class="d-flex justify-content-between mt-4">
                <a href="list.php" class="btn btn-secondary">취소</a>
                <a href="delete.php?id=<?= $id ?>&confirm=yes" class="btn btn-danger">삭제</a>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
    </body>
    </html>
    <?php
    exit;
  } catch (PDOException $e) {
    echo "<script>alert('게시판 정보를 불러오는데 실패했습니다.'); location.href='list.php';</script>";
    exit;
  }
}

// 삭제 처리
try {
  // 명시적으로 UTF-8 설정
  $pdo->exec("SET NAMES utf8mb4");
  
  // 트랜잭션 시작
  $pdo->beginTransaction();
  
  // 게시판 정보 조회 (삭제 메시지용)
  $tableName = get_table_name('boards');
  $stmt = $pdo->prepare("SELECT board_name FROM {$tableName} WHERE id = ?");
  $stmt->execute([$id]);
  $board = $stmt->fetch(PDO::FETCH_ASSOC);
  
  if (!$board) {
    $pdo->rollBack();
    echo "<script>alert('존재하지 않는 게시판입니다.'); location.href='list.php';</script>";
    exit;
  }
  
  // TODO: 게시글 삭제 로직 추가 (게시글 테이블이 구현되면)
  // $stmt = $pdo->prepare("DELETE FROM " . get_table_name('posts') . " WHERE board_id = ?");
  // $stmt->execute([$id]);
  
  // 게시판 삭제
  $stmt = $pdo->prepare("DELETE FROM {$tableName} WHERE id = ?");
  $result = $stmt->execute([$id]);
  
  if ($result) {
    $pdo->commit();
    echo "<script>alert('게시판이 삭제되었습니다.'); location.href='list.php?deleted=1';</script>";
  } else {
    $pdo->rollBack();
    echo "<script>alert('게시판 삭제 중 오류가 발생했습니다.'); location.href='list.php';</script>";
  }
} catch (PDOException $e) {
  $pdo->rollBack();
  echo "<script>alert('데이터베이스 오류: " . addslashes($e->getMessage()) . "'); location.href='list.php';</script>";
}
?> 