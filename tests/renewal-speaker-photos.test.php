<?php
require __DIR__.'/support/renewal.php';require __DIR__.'/../includes/WorkPresenter.php';
check(function_exists('sessionSpeakerPhoto'),'speaker photo presenter required');
foreach(['신수연','연혜원','천현우','박동욱'] as $name){
 $photo=sessionSpeakerPhoto('2026-youth-labor',$name);check($photo!==null,'verified speaker photo: '.$name);
 $file=__DIR__.'/../'.$photo['path'];check(is_file($file),'photo asset exists');
 $size=getimagesize($file);check($size!==false && $size[0]===$photo['width'] && $size[1]===$photo['height'],'correct portrait dimensions');
}
check(sessionSpeakerPhoto('other-course','신수연')===null,'unrelated courses do not inherit portraits');
check(sessionSpeakerPhoto('2026-youth-labor','다른 강사')===null,'changed speaker does not inherit portrait');
echo "PASS verified speaker photos and identity matching\n";
