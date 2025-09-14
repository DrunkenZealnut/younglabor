<?php
define('_INDEX_', true);
include_once('./_common.php');

// 초기화면 파일 경로 지정 : 이 코드는 가능한 삭제하지 마십시오.
if ($config['cf_include_index']) {
    if (!@include_once($config['cf_include_index'])) {
        die('기본환경 설정에서 초기화면 파일 경로가 잘못 설정되어 있습니다.');
    }
    return; // 이 코드의 아래는 실행을 하지 않습니다.
}


include_once('./head.sub.php');


$sql = "select * from g5_content where co_id = '".$co_id."'";
$row = sql_fetch($sql);
?>


<div style="margin:10px; border-top:3px solid #000; padding:30px; background:#f2f2f2;">
	<h1 style="font-size:28px; font-weight:bold; margin:0px 0 20px; color:#009FFF; font-family:맑은 고딕;"><?=$row['co_subject'];?></h1>
	<div class="con" style="background:#fff; border:1px solid #777; padding:20px;line-height:180%">
		<?=$row['co_content'];?>
	</div>
	<div style="text-align:center; margin-top:30px;">
		<a href="javascript:window.close();" style="background:#333; color:#fff; padding:10px 20px;">닫기</a>
	</div>
</div>


<?php
include_once('./tail.sub.php');
?>