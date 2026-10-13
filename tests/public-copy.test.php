<?php
$home=file_get_contents(__DIR__.'/../index.php');
$last=-1;
foreach(['일하는 청년이,','청쎈이 만나는 청년','네 가지 방식,','지금, 함께할 활동','청년과노동','새 소식','질문도, 제안도.'] as $copy){$at=strpos($home,$copy);if($at===false || $at<=$last)exit(1);$last=$at;}
if(str_contains($home,'숫자로 보는 활동'))exit(1);
if(!str_contains($home,"array_filter(\$programs,'programAcceptsApplications')"))exit(1);
