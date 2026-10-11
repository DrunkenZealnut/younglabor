<?php
require_once __DIR__.'/auth.php';http_response_code(410);adminHeader();
?>
<div class="main-header"><h1>종료된 신청 관리</h1></div><div class="card"><p>이 신청 기능은 종료되었습니다. 바이브코딩동아리는 프로그램 관리에서 안내를 수정합니다.</p><a href="<?php echo e(url('admin/work.php')); ?>">프로그램 관리</a></div>
<?php adminFooter(); ?>
