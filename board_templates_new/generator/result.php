<?php
/**
 * Board Templates Generator - 결과 페이지
 * 
 * 생성 완료 후 결과를 표시하고 다운로드 링크를 제공합니다.
 */

$token = $_GET['token'] ?? '';
if (empty($token)) {
    header('Location: index.php');
    exit;
}

$tempDir = __DIR__ . '/temp/' . $token;
$metadataPath = $tempDir . '/metadata.json';

if (!file_exists($metadataPath)) {
    echo "<h1>오류</h1>";
    echo "<p>토큰이 유효하지 않거나 파일을 찾을 수 없습니다.</p>";
    echo "<a href='index.php'>돌아가기</a>";
    exit;
}

$metadata = json_decode(file_get_contents($metadataPath), true);

/**
 * 파일 크기를 사람이 읽기 쉬운 형태로 변환
 */
function format_file_size(int $bytes): string 
{
    if ($bytes === 0) {
        return '0 B';
    }
    
    $units = ['B', 'KB', 'MB', 'GB'];
    $base = 1024;
    $index = floor(log($bytes) / log($base));
    $index = min($index, count($units) - 1);
    
    $size = round($bytes / pow($base, $index), 2);
    
    return $size . ' ' . $units[$index];
}
?>
<!DOCTYPE html>
<html lang="ko">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>생성 완료 - Board Templates Generator</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css" rel="stylesheet">
    <style>
        .success-section {
            background: linear-gradient(135deg, #28a745 0%, #20c997 100%);
            color: white;
            padding: 3rem 0;
        }
        .info-card {
            border: none;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
            margin-bottom: 1.5rem;
        }
        .file-item {
            display: flex;
            justify-content: between;
            align-items: center;
            padding: 0.5rem 1rem;
            border: 1px solid #dee2e6;
            border-radius: 0.375rem;
            margin-bottom: 0.5rem;
            background: #f8f9fa;
        }
        .file-item:last-child {
            margin-bottom: 0;
        }
        .table-badge {
            background: #e3f2fd;
            color: #1976d2;
            padding: 0.25rem 0.75rem;
            border-radius: 1rem;
            font-size: 0.875rem;
            margin-right: 0.5rem;
        }
    </style>
</head>
<body>
    <!-- 성공 섹션 -->
    <div class="success-section">
        <div class="container">
            <div class="text-center">
                <div class="mb-4">
                    <i class="bi bi-check-circle display-1"></i>
                </div>
                <h1 class="display-4 fw-bold mb-3">생성 완료!</h1>
                <p class="lead mb-4">
                    게시판 시스템이 성공적으로 생성되었습니다.<br>
                    아래에서 생성 결과를 확인하고 파일을 다운로드하세요.
                </p>
                <div class="d-flex gap-2 justify-content-center">
                    <span class="badge bg-light text-dark px-3 py-2">
                        <i class="bi bi-table"></i> <?= count($metadata['tables']) ?>개 테이블 분석
                    </span>
                    <span class="badge bg-light text-dark px-3 py-2">
                        <i class="bi bi-file-earmark-code"></i> <?= count($metadata['generated_files']) ?>개 파일 생성
                    </span>
                    <span class="badge bg-light text-dark px-3 py-2">
                        <i class="bi bi-clock"></i> <?= $metadata['generated_at'] ?>
                    </span>
                </div>
            </div>
        </div>
    </div>

    <div class="container my-5">
        <div class="row">
            <!-- 프로젝트 정보 -->
            <div class="col-lg-4 mb-4">
                <div class="card info-card h-100">
                    <div class="card-header bg-primary text-white">
                        <h5 class="mb-0">
                            <i class="bi bi-info-circle"></i>
                            프로젝트 정보
                        </h5>
                    </div>
                    <div class="card-body">
                        <table class="table table-borderless">
                            <tr>
                                <th width="40%">프로젝트명:</th>
                                <td><?= htmlspecialchars($metadata['project_name']) ?></td>
                            </tr>
                            <tr>
                                <th>원본 파일:</th>
                                <td><?= htmlspecialchars($metadata['original_filename']) ?></td>
                            </tr>
                            <tr>
                                <th>테이블 접두사:</th>
                                <td><?= htmlspecialchars($metadata['table_prefix'] ?: '없음') ?></td>
                            </tr>
                            <tr>
                                <th>테마:</th>
                                <td>
                                    <span class="badge bg-info">
                                        <?= htmlspecialchars(ucfirst($metadata['theme'])) ?>
                                    </span>
                                </td>
                            </tr>
                            <tr>
                                <th>언어:</th>
                                <td>
                                    <span class="badge bg-success">
                                        <?= $metadata['language'] === 'ko' ? '한국어' : strtoupper($metadata['language']) ?>
                                    </span>
                                </td>
                            </tr>
                        </table>
                    </div>
                </div>
            </div>

            <!-- 발견된 테이블 -->
            <div class="col-lg-4 mb-4">
                <div class="card info-card h-100">
                    <div class="card-header bg-success text-white">
                        <h5 class="mb-0">
                            <i class="bi bi-table"></i>
                            발견된 테이블 (<?= count($metadata['tables']) ?>개)
                        </h5>
                    </div>
                    <div class="card-body">
                        <?php if (!empty($metadata['tables'])): ?>
                            <div class="d-flex flex-wrap gap-2">
                                <?php foreach ($metadata['tables'] as $tableKey => $tableName): ?>
                                    <span class="table-badge" title="테이블 키: <?= htmlspecialchars($tableKey) ?>">
                                        <?= htmlspecialchars($tableName) ?>
                                    </span>
                                <?php endforeach; ?>
                            </div>
                        <?php else: ?>
                            <p class="text-muted">발견된 테이블이 없습니다.</p>
                        <?php endif; ?>
                    </div>
                </div>
            </div>

            <!-- 다운로드 -->
            <div class="col-lg-4 mb-4">
                <div class="card info-card h-100">
                    <div class="card-header bg-warning text-dark">
                        <h5 class="mb-0">
                            <i class="bi bi-download"></i>
                            다운로드
                        </h5>
                    </div>
                    <div class="card-body text-center">
                        <?php if ($metadata['download_ready'] && file_exists($tempDir . '/board_templates.zip')): ?>
                            <div class="mb-3">
                                <i class="bi bi-file-zip display-4 text-primary"></i>
                            </div>
                            <p class="text-muted mb-3">
                                생성된 모든 파일이 ZIP 아카이브로 준비되었습니다.
                            </p>
                            <a href="<?= htmlspecialchars($metadata['download_url'] ?? 'download.php?token=' . urlencode($token)) ?>" 
                               class="btn btn-primary btn-lg">
                                <i class="bi bi-download"></i>
                                파일 다운로드
                            </a>
                            <div class="mt-2">
                                <small class="text-muted">
                                    파일 크기: <?= format_file_size(filesize($tempDir . '/board_templates.zip')) ?>
                                </small>
                            </div>
                        <?php else: ?>
                            <div class="alert alert-danger">
                                <i class="bi bi-exclamation-triangle"></i>
                                다운로드 파일을 찾을 수 없습니다.
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>

        <!-- 생성된 파일 목록 -->
        <div class="row">
            <div class="col-12">
                <div class="card info-card">
                    <div class="card-header bg-info text-white">
                        <h5 class="mb-0">
                            <i class="bi bi-file-earmark-code"></i>
                            생성된 파일 목록 (<?= count($metadata['generated_files']) ?>개)
                        </h5>
                    </div>
                    <div class="card-body">
                        <?php if (!empty($metadata['generated_files'])): ?>
                            <div class="row">
                                <?php 
                                $fileCategories = [
                                    'index.php' => ['name' => '메인 페이지', 'icon' => 'house-door', 'color' => 'primary'],
                                    'config.php' => ['name' => '설정 파일', 'icon' => 'gear', 'color' => 'secondary'],
                                    'style.css' => ['name' => '스타일시트', 'icon' => 'palette', 'color' => 'info'],
                                    'script.js' => ['name' => '자바스크립트', 'icon' => 'code-square', 'color' => 'warning']
                                ];
                                
                                foreach ($metadata['generated_files'] as $file): 
                                    $filePath = $tempDir . '/' . $file;
                                    $fileExists = file_exists($filePath);
                                    $fileSize = $fileExists ? filesize($filePath) : 0;
                                    
                                    // 파일 유형별 정보
                                    $fileInfo = $fileCategories[$file] ?? null;
                                    if (!$fileInfo) {
                                        if (strpos($file, '_list.php') !== false) {
                                            $fileInfo = ['name' => '목록 페이지', 'icon' => 'list-ul', 'color' => 'success'];
                                        } elseif (strpos($file, '_write.php') !== false) {
                                            $fileInfo = ['name' => '작성 폼', 'icon' => 'pencil-square', 'color' => 'success'];
                                        } elseif (strpos($file, '_detail.php') !== false) {
                                            $fileInfo = ['name' => '상세 페이지', 'icon' => 'eye', 'color' => 'success'];
                                        } else {
                                            $fileInfo = ['name' => '기타 파일', 'icon' => 'file-earmark', 'color' => 'secondary'];
                                        }
                                    }
                                ?>
                                <div class="col-md-6 col-lg-4 mb-2">
                                    <div class="file-item">
                                        <div class="d-flex align-items-center flex-grow-1">
                                            <i class="bi bi-<?= $fileInfo['icon'] ?> text-<?= $fileInfo['color'] ?> me-2"></i>
                                            <div>
                                                <strong><?= htmlspecialchars($file) ?></strong>
                                                <br>
                                                <small class="text-muted"><?= $fileInfo['name'] ?></small>
                                            </div>
                                        </div>
                                        <div class="text-end">
                                            <?php if ($fileExists): ?>
                                                <span class="badge bg-success">
                                                    <i class="bi bi-check"></i>
                                                    <?= format_file_size($fileSize) ?>
                                                </span>
                                            <?php else: ?>
                                                <span class="badge bg-danger">
                                                    <i class="bi bi-x"></i>
                                                    없음
                                                </span>
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                </div>
                                <?php endforeach; ?>
                            </div>
                        <?php else: ?>
                            <p class="text-muted">생성된 파일이 없습니다.</p>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>

        <!-- 사용 안내 -->
        <div class="row mt-4">
            <div class="col-12">
                <div class="card info-card">
                    <div class="card-header bg-dark text-white">
                        <h5 class="mb-0">
                            <i class="bi bi-lightbulb"></i>
                            사용 안내
                        </h5>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-4">
                                <h6><i class="bi bi-1-circle text-primary"></i> 파일 다운로드</h6>
                                <p class="small text-muted">
                                    위의 다운로드 버튼을 클릭하여 생성된 모든 파일을 ZIP으로 다운로드하세요.
                                </p>
                            </div>
                            <div class="col-md-4">
                                <h6><i class="bi bi-2-circle text-primary"></i> 프로젝트 설정</h6>
                                <p class="small text-muted">
                                    다운로드한 파일을 프로젝트 디렉토리에 압축 해제하고 config.php에서 데이터베이스 연결 정보를 수정하세요.
                                </p>
                            </div>
                            <div class="col-md-4">
                                <h6><i class="bi bi-3-circle text-primary"></i> 사용 시작</h6>
                                <p class="small text-muted">
                                    index.php를 웹 브라우저에서 열어 생성된 게시판 시스템을 확인하고 사용을 시작하세요.
                                </p>
                            </div>
                        </div>
                        
                        <hr>
                        
                        <div class="alert alert-info">
                            <i class="bi bi-info-circle"></i>
                            <strong>주의사항:</strong> 
                            생성된 파일은 임시로 저장되며, 24시간 후 자동으로 삭제됩니다. 
                            필요한 파일은 미리 다운로드하여 보관하세요.
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- 하단 버튼 -->
        <div class="text-center mt-4">
            <a href="index.php" class="btn btn-outline-primary">
                <i class="bi bi-arrow-left"></i>
                새로운 프로젝트 생성
            </a>
            <?php if ($metadata['download_ready']): ?>
                <a href="<?= htmlspecialchars($metadata['download_url'] ?? 'download.php?token=' . urlencode($token)) ?>" 
                   class="btn btn-primary">
                    <i class="bi bi-download"></i>
                    다시 다운로드
                </a>
            <?php endif; ?>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>