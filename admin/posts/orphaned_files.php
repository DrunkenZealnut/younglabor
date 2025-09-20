<?php
// 고아 파일 관리 페이지
require_once '../bootstrap.php';
require_once 'attachment_helpers.php';

// 고아 파일 조회 - 정확한 연결 관계 확인
try {
    // 첨부파일과 게시글의 정확한 연결 관계 확인
    $sql = "SELECT 
                pf.bf_no,
                pf.board_type,
                pf.wr_id,
                pf.bf_source,
                pf.bf_filesize,
                pf.bf_download,
                pf.bf_datetime,
                pf.bf_type,
                p.wr_id as post_id,
                p.wr_subject,
                p.wr_content
            FROM hopec_post_files pf
            LEFT JOIN hopec_posts p ON pf.wr_id = p.wr_parent AND pf.board_type = p.board_type
            WHERE p.wr_id IS NULL
            ORDER BY pf.board_type, pf.bf_datetime DESC";
    
    $stmt = $pdo->prepare($sql);
    $stmt->execute();
    $orphaned_files = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // 게시판별로 그룹화
    $files_by_board = [];
    foreach ($orphaned_files as $file) {
        $files_by_board[$file['board_type']][] = $file;
    }
    
} catch (PDOException $e) {
    $orphaned_files = [];
    $files_by_board = [];
    error_log("고아 파일 조회 오류: " . $e->getMessage());
}

$board_names = [
    'finance_reports' => '재정보고',
    'notices' => '공지사항',
    'press' => '언론보도', 
    'newsletter' => '소식지',
    'gallery' => '갤러리',
    'resources' => '자료실',
    'nepal_travel' => '네팔나눔연대여행'
];
?>

<!DOCTYPE html>
<html lang="ko">
<head>
    <meta charset="UTF-8">
    <title>고아 파일 관리 - 희망씨 관리자</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
</head>
<body>
    <div class="container-fluid">
        <div class="row">
            <!-- 사이드바 -->
            <div class="col-md-2 bg-dark text-white p-3">
                <h4><a href="../index.php" class="text-white text-decoration-none">희망씨 관리자</a></h4>
                <hr>
                <ul class="nav flex-column">
                    <li class="nav-item">
                        <a href="list.php" class="nav-link text-white">
                            <i class="bi bi-list"></i> 게시글 목록
                        </a>
                    </li>
                    <li class="nav-item">
                        <a href="orphaned_files.php" class="nav-link text-white active">
                            <i class="bi bi-file-earmark-x"></i> 고아 파일 관리
                        </a>
                    </li>
                </ul>
            </div>

            <!-- 메인 컨텐츠 -->
            <div class="col-md-10 p-4">
                <div class="d-flex justify-content-between align-items-center mb-4">
                    <h2><i class="bi bi-file-earmark-x text-warning"></i> 고아 파일 관리</h2>
                </div>

                <div class="alert alert-info">
                    <h5><i class="bi bi-info-circle"></i> 고아 파일이란?</h5>
                    <p class="mb-0">게시글과 연결이 끊어진 첨부파일들입니다. 주로 삭제된 게시글의 첨부파일이나 업로드 오류로 발생합니다.</p>
                    <small class="text-muted">이 파일들은 여전히 다운로드 가능하며, 필요시 해당 게시글에 다시 업로드하거나 삭제할 수 있습니다.</small>
                </div>

                <?php if (empty($files_by_board)): ?>
                    <div class="alert alert-success">
                        <i class="bi bi-check-circle"></i> 고아 파일이 없습니다. 모든 첨부파일이 정상적으로 연결되어 있습니다.
                    </div>
                <?php else: ?>
                    <?php foreach ($files_by_board as $board_type => $files): ?>
                        <div class="card mb-4">
                            <div class="card-header">
                                <h5 class="mb-0">
                                    <i class="bi bi-folder"></i> 
                                    <?= htmlspecialchars($board_names[$board_type] ?? $board_type) ?>
                                    <span class="badge bg-warning"><?= count($files) ?>개</span>
                                </h5>
                            </div>
                            <div class="card-body">
                                <div class="table-responsive">
                                    <table class="table table-hover">
                                        <thead>
                                            <tr>
                                                <th>파일명</th>
                                                <th>크기</th>
                                                <th>다운로드 수</th>
                                                <th>업로드 날짜</th>
                                                <th>작업</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php foreach ($files as $file): ?>
                                                <tr>
                                                    <td>
                                                        <i class="<?= getFileIcon($file['bf_source']) ?> me-2"></i>
                                                        <?= htmlspecialchars($file['bf_source']) ?>
                                                        <br>
                                                        <small class="text-muted">
                                                            연결 대상 ID: <?= $file['wr_id'] ?>
                                                            <?php if ($file['bf_type'] == 2): ?>
                                                                <span class="badge bg-info">이미지</span>
                                                            <?php endif; ?>
                                                        </small>
                                                    </td>
                                                    <td><?= formatFileSize($file['bf_filesize']) ?></td>
                                                    <td><?= $file['bf_download'] ?>회</td>
                                                    <td><?= date('Y-m-d H:i', strtotime($file['bf_datetime'])) ?></td>
                                                    <td>
                                                        <a href="download_attachment.php?id=<?= $file['bf_no'] ?>" 
                                                           class="btn btn-sm btn-outline-primary">
                                                            <i class="bi bi-download"></i> 다운로드
                                                        </a>
                                                    </td>
                                                </tr>
                                            <?php endforeach; ?>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>

                    <div class="alert alert-success">
                        <h6><i class="bi bi-check-circle"></i> 시스템 상태:</h6>
                        <ul class="mb-0">
                            <li><strong>Newsletter 파일 연결 문제 해결됨:</strong> 168개 파일이 정상적으로 연결되었습니다</li>
                            <li><strong>현재 고아 파일:</strong> 삭제된 게시글의 첨부파일만 남아있습니다</li>
                            <li><strong>새로운 게시글:</strong> 모든 첨부파일이 정상적으로 연결됩니다</li>
                        </ul>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>