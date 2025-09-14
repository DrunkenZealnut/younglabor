<?php
/**
 * 간단한 결과 페이지 (테스트용)
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
    echo "<p>토큰: " . htmlspecialchars($token) . "</p>";
    echo "<p>경로: " . htmlspecialchars($tempDir) . "</p>";
    echo "<a href='index.php'>돌아가기</a>";
    exit;
}

$metadata = json_decode(file_get_contents($metadataPath), true);
?>
<!DOCTYPE html>
<html lang="ko">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>생성 완료 - 간단 테스트</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
    <div class="container mt-5">
        <div class="row">
            <div class="col-md-8 mx-auto">
                <div class="card">
                    <div class="card-header bg-success text-white">
                        <h4 class="mb-0">✅ 생성 완료!</h4>
                    </div>
                    <div class="card-body">
                        <h5>프로젝트 정보</h5>
                        <table class="table table-sm">
                            <tr>
                                <th>프로젝트명:</th>
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
                                <th>생성 시간:</th>
                                <td><?= $metadata['generated_at'] ?></td>
                            </tr>
                            <tr>
                                <th>토큰:</th>
                                <td><code><?= htmlspecialchars($token) ?></code></td>
                            </tr>
                        </table>

                        <h5 class="mt-4">발견된 테이블</h5>
                        <ul class="list-group">
                            <?php foreach ($metadata['tables'] as $table): ?>
                                <li class="list-group-item"><?= htmlspecialchars($table) ?></li>
                            <?php endforeach; ?>
                        </ul>

                        <h5 class="mt-4">생성된 파일</h5>
                        <ul class="list-group">
                            <?php foreach ($metadata['generated_files'] as $file): ?>
                                <?php 
                                $filePath = $tempDir . '/' . $file;
                                $exists = file_exists($filePath);
                                $size = $exists ? filesize($filePath) : 0;
                                ?>
                                <li class="list-group-item d-flex justify-content-between align-items-center">
                                    <?= htmlspecialchars($file) ?>
                                    <span class="badge <?= $exists ? 'bg-success' : 'bg-danger' ?>">
                                        <?= $exists ? "✓ ({$size} bytes)" : "✗ 없음" ?>
                                    </span>
                                </li>
                            <?php endforeach; ?>
                        </ul>

                        <h5 class="mt-4">디렉토리 정보</h5>
                        <div class="alert alert-info">
                            <strong>임시 디렉토리:</strong><br>
                            <code><?= htmlspecialchars($tempDir) ?></code><br>
                            <small>
                                존재: <?= is_dir($tempDir) ? '✓' : '✗' ?> | 
                                쓰기 가능: <?= is_writable($tempDir) ? '✓' : '✗' ?>
                            </small>
                        </div>

                        <?php if (isset($metadata['sql_content'])): ?>
                        <h5 class="mt-4">SQL 내용 미리보기</h5>
                        <pre class="bg-light p-3" style="max-height: 200px; overflow-y: auto;"><?= htmlspecialchars(substr($metadata['sql_content'], 0, 1000)) ?><?= strlen($metadata['sql_content']) > 1000 ? '...' : '' ?></pre>
                        <?php endif; ?>

                        <div class="mt-4 text-center">
                            <a href="index.php" class="btn btn-primary">새 테스트 하기</a>
                            <a href="index.php?simple=1" class="btn btn-success">간단 모드로 돌아가기</a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</body>
</html>