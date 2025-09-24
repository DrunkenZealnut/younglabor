<?php
/**
 * Default Configuration Form
 * 기본 설정 폼 (정의되지 않은 탭용)
 */

// 현재 탭에 해당하는 환경 변수 찾기
$tab_settings = [];
foreach ($current_env as $key => $value) {
    // 탭 이름과 관련된 설정 찾기
    if (stripos($key, $current_tab) !== false) {
        $tab_settings[$key] = $value;
    }
}

if (empty($tab_settings)): ?>
    <div class="alert alert-info">
        <i class="fas fa-info-circle"></i> 이 섹션에 대한 설정이 아직 정의되지 않았습니다.
    </div>
<?php else: ?>
    <div class="row">
        <?php 
        $count = 0;
        foreach ($tab_settings as $key => $value): 
            if ($count % 2 === 0 && $count > 0) echo '</div><div class="row">';
        ?>
        <div class="col-md-6">
            <div class="mb-3">
                <label for="<?php echo strtolower($key); ?>" class="form-label">
                    <?php echo str_replace('_', ' ', ucfirst(strtolower($key))); ?>
                </label>
                <input type="text" class="form-control" id="<?php echo strtolower($key); ?>" 
                       name="settings[<?php echo $key; ?>]" 
                       value="<?php echo htmlspecialchars($value); ?>">
                <small class="form-text text-muted">환경 변수: <?php echo $key; ?></small>
            </div>
        </div>
        <?php 
        $count++;
        endforeach; 
        ?>
    </div>
<?php endif; ?>

<div class="alert alert-warning mt-4">
    <i class="fas fa-exclamation-triangle"></i> 
    이 페이지는 자동 생성된 기본 폼입니다. 
    더 나은 사용자 경험을 위해 전용 폼 파일을 생성하는 것을 권장합니다.
</div>