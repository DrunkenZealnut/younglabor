<?php
require_once __DIR__ . '/config.php';
require_once __DIR__ . '/includes/Database.php';
require_once __DIR__ . '/includes/PageTracker.php';
PageTracker::track('소식');

$currentPage = 'news';
$pageTitle = '소식 - ' . $site['name'];
$pageDescription = $site['name'] . '의 활동 소식과 공지사항';
require_once __DIR__ . '/includes/header.php';

// 소식 데이터 (새 항목은 배열 상단에 추가)
$newsItems = [
    // [
    //     'date' => '2026-04-15',
    //     'category' => '활동기록',
    //     'title' => '첫 번째 학교 캠페인 후기',
    //     'summary' => '경주 반도체고에서 첫 번째 캠페인을 진행했습니다.',
    // ],
];
?>

    <div class="page-header">
        <div class="container">
            <h1>소식</h1>
            <p>활동 기록, 공지사항, 배경 이야기</p>
        </div>
    </div>

    <section class="section">
        <div class="container">
            <?php if (empty($newsItems)): ?>
                <div class="empty-state fade-in">
                    <p>아직 등록된 소식이 없습니다.</p>
                    <p style="font-size: 0.95rem; color: #aaa;">학교 캠페인이 시작되면 활동 소식을 전해드리겠습니다.<br>먼저 <a href="<?php echo url('about'); ?>" style="color: var(--color-primary-dark);">단체 소개</a>를 확인해보세요.</p>
                </div>
            <?php else: ?>
                <div class="news-list">
                    <?php foreach ($newsItems as $item): ?>
                        <article class="news-item fade-in">
                            <span class="news-category"><?php echo htmlspecialchars($item['category']); ?></span>
                            <div class="news-date"><?php echo htmlspecialchars($item['date']); ?></div>
                            <h3 class="news-title"><?php echo htmlspecialchars($item['title']); ?></h3>
                            <p class="news-summary"><?php echo htmlspecialchars($item['summary']); ?></p>
                        </article>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>
    </section>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
