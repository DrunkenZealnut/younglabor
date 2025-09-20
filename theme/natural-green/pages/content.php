<?php
// 단일 콘텐츠 페이지 플레이스홀더
$title = isset($_GET['page']) ? $_GET['page'] : '';
?>
<section class="py-16 bg-white">
  <div class="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8">
    <h1 class="text-3xl md:text-4xl text-forest-700 mb-6"><?php echo htmlspecialchars($title ?: '콘텐츠'); ?></h1>
    <p class="text-gray-600">해당 메뉴의 콘텐츠는 추후 게시판/페이지 데이터와 연동하여 출력합니다.</p>
  </div>
</section>

