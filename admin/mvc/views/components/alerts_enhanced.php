<?php
/**
 * Enhanced Alerts Component - Admin_templates 통합 버전
 * 
 * Admin_templates의 alerts 기능을 MVC 구조로 완전히 통합
 * 기존 templates_project/components/alerts.php 확장
 * 
 * 기능:
 * - 세션 기반 플래시 메시지 자동 표시
 * - URL 파라미터 기반 메시지 (success, error, info, warning)
 * - 커스텀 메시지 배열 지원
 * - Bootstrap 5 알림 스타일
 * - 자동 해제 기능
 * 
 * 사용법:
 * t_render_component('alerts_enhanced');
 * t_render_component('alerts_enhanced', ['custom_messages' => $messages]);
 * t_render_component('alerts_enhanced', ['show_dismissible' => false]);
 */

// 기본 설정
$show_dismissible = $show_dismissible ?? true;
$auto_hide_delay = $auto_hide_delay ?? 0; // 0 = 자동 숨김 비활성화, 밀리초 단위

// 메시지 수집
$all_messages = [];

// 1. 커스텀 메시지 (우선순위 높음)
if (isset($custom_messages) && is_array($custom_messages)) {
    $all_messages = array_merge($all_messages, $custom_messages);
}

// 2. 세션 기반 플래시 메시지
$session_message_types = ['success_message', 'error_message', 'warning_message', 'info_message'];
foreach ($session_message_types as $session_key) {
    if (isset($_SESSION[$session_key]) && !empty($_SESSION[$session_key])) {
        $type = str_replace('_message', '', $session_key);
        $all_messages[$type] = $_SESSION[$session_key];
        unset($_SESSION[$session_key]); // 메시지 표시 후 제거
    }
}

// 3. URL 파라미터 기반 메시지 (기존 코드 호환성)
$url_message_types = ['success', 'error', 'warning', 'info'];
foreach ($url_message_types as $type) {
    if (isset($_GET[$type]) && !empty($_GET[$type])) {
        $all_messages[$type] = $_GET[$type];
    }
}

// 4. functions.php의 get_flash_message() 지원 (기존 시스템 호환성)
if (function_exists('get_flash_message')) {
    $flash = get_flash_message();
    if ($flash && isset($flash['message']) && isset($flash['type'])) {
        $all_messages[$flash['type']] = $flash['message'];
    }
}

// 메시지가 없으면 아무것도 출력하지 않음
if (empty($all_messages)) {
    return;
}
?>

<div class="alerts-container mb-3" id="alerts-container">
    <?php foreach ($all_messages as $type => $message): ?>
        <?php
        // 메시지가 배열인 경우 (여러 메시지)
        $messages_array = is_array($message) ? $message : [$message];
        
        foreach ($messages_array as $single_message):
            // Bootstrap 알림 클래스 매핑
            $alert_class = match($type) {
                'success' => 'alert-success',
                'error', 'danger' => 'alert-danger', 
                'warning' => 'alert-warning',
                'info' => 'alert-info',
                default => 'alert-secondary'
            };
            
            // 아이콘 매핑
            $icon = match($type) {
                'success' => 'bi-check-circle-fill',
                'error', 'danger' => 'bi-exclamation-triangle-fill',
                'warning' => 'bi-exclamation-circle-fill', 
                'info' => 'bi-info-circle-fill',
                default => 'bi-info-circle-fill'
            };
            
            // 고유 ID 생성
            $alert_id = 'alert-' . $type . '-' . uniqid();
        ?>
        
        <div class="alert <?= $alert_class ?> <?= $show_dismissible ? 'alert-dismissible' : '' ?> fade show" 
             role="alert" 
             id="<?= $alert_id ?>"
             <?= $auto_hide_delay > 0 ? 'data-auto-hide="' . $auto_hide_delay . '"' : '' ?>>
            
            <div class="d-flex align-items-center">
                <i class="bi <?= $icon ?> me-2 flex-shrink-0"></i>
                <div class="flex-grow-1">
                    <?php if (filter_var($single_message, FILTER_VALIDATE_URL) === false): ?>
                        <?= nl2br(t_escape($single_message)) ?>
                    <?php else: ?>
                        <!-- URL인 경우 링크로 처리 -->
                        <a href="<?= t_escape($single_message) ?>" target="_blank" class="alert-link">
                            <?= t_escape($single_message) ?>
                        </a>
                    <?php endif; ?>
                </div>
                
                <?php if ($show_dismissible): ?>
                    <button type="button" class="btn-close ms-2" data-bs-dismiss="alert" aria-label="닫기"></button>
                <?php endif; ?>
            </div>
        </div>
        
        <?php endforeach; ?>
    <?php endforeach; ?>
</div>

<?php if ($auto_hide_delay > 0): ?>
<!-- 자동 숨김 스크립트 -->
<script>
document.addEventListener('DOMContentLoaded', function() {
    const alertsWithAutoHide = document.querySelectorAll('[data-auto-hide]');
    
    alertsWithAutoHide.forEach(alert => {
        const delay = parseInt(alert.dataset.autoHide);
        if (delay > 0) {
            setTimeout(() => {
                const bsAlert = new bootstrap.Alert(alert);
                bsAlert.close();
            }, delay);
        }
    });
});
</script>
<?php endif; ?>

<style>
.alerts-container .alert {
    border: none;
    border-radius: 8px;
    box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
    margin-bottom: 0.75rem;
}

.alert-success {
    background-color: #d4edda;
    border-left: 4px solid #28a745;
}

.alert-danger {
    background-color: #f8d7da;
    border-left: 4px solid #dc3545;
}

.alert-warning {
    background-color: #fff3cd;
    border-left: 4px solid #ffc107;
}

.alert-info {
    background-color: #d1ecf1;
    border-left: 4px solid #17a2b8;
}

.alert-secondary {
    background-color: #e2e3e5;
    border-left: 4px solid #6c757d;
}

.alert .bi {
    font-size: 1.1em;
}

/* 애니메이션 효과 */
.alerts-container .alert {
    animation: slideInDown 0.3s ease-out;
}

@keyframes slideInDown {
    from {
        opacity: 0;
        transform: translateY(-10px);
    }
    to {
        opacity: 1;
        transform: translateY(0);
    }
}

/* 반응형 처리 */
@media (max-width: 576px) {
    .alerts-container .alert {
        font-size: 0.9rem;
        padding: 0.75rem;
    }
    
    .alert .bi {
        font-size: 1em;
    }
}
</style>