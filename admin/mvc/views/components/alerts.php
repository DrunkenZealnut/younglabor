<?php
// 플래시 메시지 표시 컴포넌트
if (session_status() === PHP_SESSION_NONE) session_start();

// 세션에서 메시지 가져오기
$messages = [];
$message_types = ['success', 'error', 'warning', 'info'];

foreach ($message_types as $type) {
    $key = $type . '_message';
    if (isset($_SESSION[$key]) && !empty($_SESSION[$key])) {
        $messages[] = [
            'type' => $type,
            'message' => $_SESSION[$key]
        ];
        unset($_SESSION[$key]);
    }
}

// 메시지 출력
if (!empty($messages)): ?>
    <div class="alerts-container mb-3">
        <?php foreach ($messages as $msg): ?>
            <div class="alert alert-<?= $msg['type'] === 'error' ? 'danger' : $msg['type'] ?> alert-dismissible fade show" role="alert">
                <?= htmlspecialchars($msg['message']) ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        <?php endforeach; ?>
    </div>
<?php endif; ?>