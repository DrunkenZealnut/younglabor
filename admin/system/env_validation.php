<?php
/**
 * Environment Variables Validation Tool
 * 
 * Validates that all environment variables are properly configured
 * and functioning as expected.
 */

require_once '../../bootstrap/app.php';

// Validation results
$validation_results = [];
$has_errors = false;

/**
 * Test environment variable
 */
function test_env_var($key, $description, $required = true, $test_callback = null) {
    global $validation_results, $has_errors;
    
    $value = env($key);
    $status = 'success';
    $message = '';
    
    if ($required && empty($value)) {
        $status = 'error';
        $message = 'Required environment variable is missing or empty';
        $has_errors = true;
    } elseif ($test_callback && is_callable($test_callback)) {
        $test_result = $test_callback($value);
        if (!$test_result['valid']) {
            $status = 'warning';
            $message = $test_result['message'];
        }
    }
    
    $validation_results[] = [
        'key' => $key,
        'description' => $description,
        'value' => $value,
        'status' => $status,
        'message' => $message
    ];
}

// Test core environment variables
test_env_var('APP_NAME', 'Application Name', true);
test_env_var('APP_ENV', 'Application Environment', true);
test_env_var('APP_DEBUG', 'Debug Mode', false);
test_env_var('APP_URL', 'Application URL', true, function($value) {
    return [
        'valid' => filter_var($value, FILTER_VALIDATE_URL) !== false,
        'message' => 'Invalid URL format'
    ];
});

// Test database configuration
test_env_var('DB_HOST', 'Database Host', true);
test_env_var('DB_DATABASE', 'Database Name', true);
test_env_var('DB_USERNAME', 'Database Username', true);
test_env_var('DB_PASSWORD', 'Database Password', false);
test_env_var('DB_CHARSET', 'Database Charset', false);

// Test organization information
test_env_var('ORG_NAME_SHORT', 'Organization Short Name', true);
test_env_var('ORG_NAME_FULL', 'Organization Full Name', true);
test_env_var('ORG_NAME_EN', 'Organization English Name', true);
test_env_var('ORG_DESCRIPTION', 'Organization Description', true);

// Test contact information
test_env_var('CONTACT_EMAIL', 'Contact Email', true, function($value) {
    return [
        'valid' => filter_var($value, FILTER_VALIDATE_EMAIL) !== false,
        'message' => 'Invalid email format'
    ];
});

// Test paths
test_env_var('UPLOAD_PATH', 'Upload Path', true, function($value) {
    $full_path = project_path($value);
    return [
        'valid' => is_dir($full_path) || mkdir($full_path, 0755, true),
        'message' => 'Upload directory does not exist and cannot be created'
    ];
});

test_env_var('LOG_PATH', 'Log Path', false, function($value) {
    if (empty($value)) return ['valid' => true, 'message' => ''];
    
    $full_path = project_path($value);
    return [
        'valid' => is_dir($full_path) || mkdir($full_path, 0755, true),
        'message' => 'Log directory does not exist and cannot be created'
    ];
});

// Test theme configuration
test_env_var('THEME_NAME', 'Theme Name', true, function($value) {
    $theme_path = project_path("theme/{$value}");
    return [
        'valid' => is_dir($theme_path),
        'message' => 'Theme directory does not exist'
    ];
});

// Test mail configuration
test_env_var('MAIL_SMTP_HOST', 'SMTP Host', false);
test_env_var('MAIL_SMTP_PORT', 'SMTP Port', false, function($value) {
    if (empty($value)) return ['valid' => true, 'message' => ''];
    
    return [
        'valid' => is_numeric($value) && $value > 0 && $value <= 65535,
        'message' => 'Invalid port number'
    ];
});

test_env_var('MAIL_FROM_EMAIL', 'From Email', false, function($value) {
    if (empty($value)) return ['valid' => true, 'message' => ''];
    
    return [
        'valid' => filter_var($value, FILTER_VALIDATE_EMAIL) !== false,
        'message' => 'Invalid email format'
    ];
});

// Test function availability
$function_tests = [
    'env' => 'Environment function',
    'org_name_short' => 'Organization helper functions',
    'get_bt_upload_path' => 'Path helper functions',
    'get_project_root' => 'Project root detection',
];

foreach ($function_tests as $function => $description) {
    $status = function_exists($function) ? 'success' : 'error';
    if ($status === 'error') {
        $has_errors = true;
    }
    
    $validation_results[] = [
        'key' => "function_{$function}",
        'description' => $description,
        'value' => $status === 'success' ? 'Available' : 'Missing',
        'status' => $status,
        'message' => $status === 'error' ? 'Function not available' : ''
    ];
}

?>
<!DOCTYPE html>
<html lang="ko">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>환경변수 검증 - <?php echo org_name_short(); ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css" rel="stylesheet">
</head>
<body class="bg-light">
    <div class="container mt-4">
        <div class="row">
            <div class="col-12">
                <div class="d-flex justify-content-between align-items-center mb-4">
                    <h1 class="h3">환경변수 통합 검증</h1>
                    <a href="../" class="btn btn-secondary">
                        <i class="bi bi-arrow-left"></i> 관리자 홈으로
                    </a>
                </div>

                <?php if ($has_errors): ?>
                <div class="alert alert-danger" role="alert">
                    <h4 class="alert-heading"><i class="bi bi-exclamation-triangle"></i> 오류 발견</h4>
                    <p>환경변수 구성에 문제가 있습니다. 아래 오류를 확인하고 수정해주세요.</p>
                </div>
                <?php else: ?>
                <div class="alert alert-success" role="alert">
                    <h4 class="alert-heading"><i class="bi bi-check-circle"></i> 검증 완료</h4>
                    <p>모든 환경변수가 올바르게 구성되어 있습니다.</p>
                </div>
                <?php endif; ?>

                <div class="card">
                    <div class="card-header">
                        <h5 class="card-title mb-0">검증 결과</h5>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-striped">
                                <thead>
                                    <tr>
                                        <th>변수명</th>
                                        <th>설명</th>
                                        <th>현재값</th>
                                        <th>상태</th>
                                        <th>메시지</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($validation_results as $result): ?>
                                    <tr>
                                        <td><code><?php echo htmlspecialchars($result['key']); ?></code></td>
                                        <td><?php echo htmlspecialchars($result['description']); ?></td>
                                        <td>
                                            <?php if (strlen($result['value']) > 50): ?>
                                                <span title="<?php echo htmlspecialchars($result['value']); ?>">
                                                    <?php echo htmlspecialchars(substr($result['value'], 0, 50)) . '...'; ?>
                                                </span>
                                            <?php else: ?>
                                                <?php echo htmlspecialchars($result['value']); ?>
                                            <?php endif; ?>
                                        </td>
                                        <td>
                                            <?php if ($result['status'] === 'success'): ?>
                                                <span class="badge bg-success">
                                                    <i class="bi bi-check-circle"></i> 정상
                                                </span>
                                            <?php elseif ($result['status'] === 'warning'): ?>
                                                <span class="badge bg-warning">
                                                    <i class="bi bi-exclamation-triangle"></i> 경고
                                                </span>
                                            <?php else: ?>
                                                <span class="badge bg-danger">
                                                    <i class="bi bi-x-circle"></i> 오류
                                                </span>
                                            <?php endif; ?>
                                        </td>
                                        <td>
                                            <?php if (!empty($result['message'])): ?>
                                                <span class="text-<?php echo $result['status'] === 'error' ? 'danger' : 'warning'; ?>">
                                                    <?php echo htmlspecialchars($result['message']); ?>
                                                </span>
                                            <?php endif; ?>
                                        </td>
                                    </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>

                <div class="row mt-4">
                    <div class="col-md-6">
                        <div class="card">
                            <div class="card-header">
                                <h6 class="card-title mb-0">프로젝트 정보</h6>
                            </div>
                            <div class="card-body">
                                <table class="table table-sm">
                                    <tr>
                                        <th>프로젝트 루트:</th>
                                        <td><code><?php echo htmlspecialchars(get_project_root()); ?></code></td>
                                    </tr>
                                    <tr>
                                        <th>업로드 경로:</th>
                                        <td><code><?php echo htmlspecialchars(get_bt_upload_path()); ?></code></td>
                                    </tr>
                                    <tr>
                                        <th>조직명 (짧은):</th>
                                        <td><?php echo htmlspecialchars(org_name_short()); ?></td>
                                    </tr>
                                    <tr>
                                        <th>조직명 (전체):</th>
                                        <td><?php echo htmlspecialchars(org_name_full()); ?></td>
                                    </tr>
                                </table>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="card">
                            <div class="card-header">
                                <h6 class="card-title mb-0">권장사항</h6>
                            </div>
                            <div class="card-body">
                                <ul class="list-unstyled">
                                    <li><i class="bi bi-check text-success"></i> 환경변수는 .env 파일에서 관리</li>
                                    <li><i class="bi bi-check text-success"></i> 민감한 정보는 환경변수로 분리</li>
                                    <li><i class="bi bi-check text-success"></i> 경로는 상대경로 사용 권장</li>
                                    <li><i class="bi bi-check text-success"></i> 프로덕션 환경에서는 DEBUG=false</li>
                                </ul>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>