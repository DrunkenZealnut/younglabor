<?php
/**
 * 테마 시스템 테스트 페이지
 * Global Theme Loader의 동작을 테스트하고 확인
 */

$pageTitle = '테마 시스템 테스트';
$pageDescription = 'HOPEC 글로벌 테마 시스템 테스트 페이지';

// 헤더 포함 (Global Theme Loader 포함)
include __DIR__ . '/includes/header.php';
?>

<body class="bg-background text-foreground min-h-screen">
    <!-- 테마 선택기 포함 -->
    <?php include __DIR__ . '/theme/globals/components/theme-selector.php'; ?>
    
    <div class="container mx-auto px-4 py-8">
        <!-- 헤더 섹션 -->
        <header class="text-center mb-12">
            <h1 class="text-4xl font-bold mb-4 gradient-text-primary">
                🎨 테마 시스템 테스트
            </h1>
            <p class="text-muted-foreground text-lg mb-6">
                HOPEC Global Theme Loader 시스템을 테스트해보세요
            </p>
            <div class="bg-card p-4 rounded-lg border border-border inline-block">
                <p class="text-sm">
                    <strong>현재 활성 테마:</strong> 
                    <span class="text-primary font-semibold"><?= htmlspecialchars($activeTheme) ?></span>
                </p>
            </div>
        </header>

        <!-- 테마별 색상 프리뷰 -->
        <section class="grid md:grid-cols-2 lg:grid-cols-3 gap-6 mb-12">
            <div class="bg-card p-6 rounded-lg border border-border">
                <h3 class="text-lg font-semibold mb-4 text-primary">Primary Colors</h3>
                <div class="space-y-3">
                    <div class="flex items-center gap-3">
                        <div class="w-6 h-6 rounded border border-border" style="background: var(--primary)"></div>
                        <span class="text-sm">Primary</span>
                    </div>
                    <div class="flex items-center gap-3">
                        <div class="w-6 h-6 rounded border border-border" style="background: var(--secondary)"></div>
                        <span class="text-sm">Secondary</span>
                    </div>
                    <div class="flex items-center gap-3">
                        <div class="w-6 h-6 rounded border border-border" style="background: var(--accent)"></div>
                        <span class="text-sm">Accent</span>
                    </div>
                </div>
            </div>

            <div class="bg-card p-6 rounded-lg border border-border">
                <h3 class="text-lg font-semibold mb-4 text-primary">Background Colors</h3>
                <div class="space-y-3">
                    <div class="flex items-center gap-3">
                        <div class="w-6 h-6 rounded border border-border" style="background: var(--background)"></div>
                        <span class="text-sm">Background</span>
                    </div>
                    <div class="flex items-center gap-3">
                        <div class="w-6 h-6 rounded border border-border" style="background: var(--muted)"></div>
                        <span class="text-sm">Muted</span>
                    </div>
                    <div class="flex items-center gap-3">
                        <div class="w-6 h-6 rounded border border-border" style="background: var(--card)"></div>
                        <span class="text-sm">Card</span>
                    </div>
                </div>
            </div>

            <div class="bg-card p-6 rounded-lg border border-border">
                <h3 class="text-lg font-semibold mb-4 text-primary">Gradient Examples</h3>
                <div class="space-y-3">
                    <div class="gradient-primary h-8 rounded border border-border"></div>
                    <div class="gradient-secondary h-8 rounded border border-border"></div>
                    <div class="gradient-natural h-8 rounded border border-border"></div>
                </div>
            </div>
        </section>

        <!-- 인터랙티브 요소들 -->
        <section class="mb-12">
            <h2 class="text-2xl font-bold mb-6 text-center">인터랙티브 요소 테스트</h2>
            
            <div class="grid md:grid-cols-2 gap-8">
                <!-- 버튼 테스트 -->
                <div class="bg-card p-6 rounded-lg border border-border">
                    <h3 class="text-lg font-semibold mb-4">버튼 스타일</h3>
                    <div class="space-y-3">
                        <button class="bg-primary text-primary-foreground px-4 py-2 rounded hover:opacity-90 transition-opacity">
                            Primary Button
                        </button>
                        <button class="bg-secondary text-secondary-foreground px-4 py-2 rounded hover:opacity-90 transition-opacity">
                            Secondary Button
                        </button>
                        <button class="bg-accent text-accent-foreground px-4 py-2 rounded hover:opacity-90 transition-opacity">
                            Accent Button
                        </button>
                    </div>
                </div>

                <!-- 카드 효과 테스트 -->
                <div class="bg-card p-6 rounded-lg border border-border">
                    <h3 class="text-lg font-semibold mb-4">카드 효과</h3>
                    <div class="space-y-3">
                        <div class="glass-card p-4 rounded hover-lift">
                            <p class="text-sm">Glass Card with Hover Effect</p>
                        </div>
                        <div class="card-glow p-4 rounded bg-muted">
                            <p class="text-sm">Card with Glow Animation</p>
                        </div>
                        <div class="floating-animation p-4 rounded bg-accent">
                            <p class="text-sm">Floating Animation Card</p>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <!-- 사용 가능한 테마 목록 -->
        <section class="bg-card p-6 rounded-lg border border-border">
            <h2 class="text-xl font-bold mb-4">사용 가능한 테마</h2>
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                <?php 
                $availableThemes = $globalThemeLoader->getAvailableThemes();
                foreach ($availableThemes as $theme): 
                    $isActive = $theme['name'] === $activeTheme;
                    $cardClass = $isActive ? 'border-primary bg-primary/5' : 'border-border';
                ?>
                    <div class="p-4 rounded-lg border <?= $cardClass ?> hover:shadow-md transition-shadow">
                        <h3 class="font-semibold <?= $isActive ? 'text-primary' : 'text-foreground' ?>">
                            <?= htmlspecialchars($theme['display_name']) ?>
                            <?= $isActive ? ' (현재 활성)' : '' ?>
                        </h3>
                        <p class="text-sm text-muted-foreground mb-3">
                            파일: <?= htmlspecialchars($theme['file']) ?>
                        </p>
                        <?php if (!$isActive): ?>
                            <a href="?theme=<?= urlencode($theme['name']) ?>" 
                               class="inline-block px-3 py-1 bg-primary text-primary-foreground text-xs rounded hover:opacity-90 transition-opacity">
                                적용하기
                            </a>
                        <?php else: ?>
                            <span class="inline-block px-3 py-1 bg-muted text-muted-foreground text-xs rounded">
                                ✓ 적용됨
                            </span>
                        <?php endif; ?>
                    </div>
                <?php endforeach; ?>
            </div>
        </section>

        <!-- 시스템 정보 -->
        <section class="mt-12 bg-muted p-6 rounded-lg">
            <h2 class="text-xl font-bold mb-4">시스템 정보</h2>
            <div class="grid md:grid-cols-2 gap-6 text-sm">
                <div>
                    <h3 class="font-semibold mb-2">테마 경로</h3>
                    <ul class="space-y-1 font-mono text-xs">
                        <li>기본 테마: <code>/theme/natural-green/</code></li>
                        <li>글로벌 테마: <code>/theme/globals/styles/</code></li>
                        <li>테마 로더: <code>/theme/globals/config/theme-loader.php</code></li>
                    </ul>
                </div>
                <div>
                    <h3 class="font-semibold mb-2">사용법</h3>
                    <ul class="space-y-1 text-xs">
                        <li>• URL에 <code>?theme=테마명</code> 추가</li>
                        <li>• 우측 상단 테마 선택기 사용</li>
                        <li>• 세션에 선택한 테마 저장됨</li>
                        <li>• 새로운 테마는 <code>global_이름.css</code>로 추가</li>
                    </ul>
                </div>
            </div>
        </section>
    </div>

    <script>
    // 테마 변경 시 부드러운 전환 효과
    document.addEventListener('DOMContentLoaded', function() {
        console.log('🎨 Global Theme System Loaded');
        console.log('Current Theme:', '<?= $activeTheme ?>');
        console.log('Available Themes:', <?= json_encode(array_keys($availableThemes)) ?>);
        
        // 테마별 CSS 변수값 출력
        const rootStyles = getComputedStyle(document.documentElement);
        console.log('Theme CSS Variables:', {
            primary: rootStyles.getPropertyValue('--primary').trim(),
            background: rootStyles.getPropertyValue('--background').trim(),
            foreground: rootStyles.getPropertyValue('--foreground').trim()
        });
    });
    </script>
</body>
</html>