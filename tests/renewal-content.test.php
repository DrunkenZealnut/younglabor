<?php
require __DIR__.'/support/renewal.php';require __DIR__.'/../includes/ContentValidation.php';require __DIR__.'/../includes/ContentPresenter.php';
$i=['type'=>'article','title'=>'글','slug'=>'column','summary'=>'소개','body'=>'본문','content_date'=>'2026-10-11','status'=>'published','public_author'=>'청쎈','editorial_category'=>'column'];
check(validateContentInput($i)['errors']===[],'article can publish with author');
check(isset(validateContentInput(array_replace($i,['public_author'=>'']))['errors']['public_author']),'missing public author rejected');
check(publicPostPath(['type'=>'activity','slug'=>'same'])==='board/same','activity path');
check(publicPostPath(['type'=>'press','slug'=>'same'])==='board/press--same','press collision safe');
check(publicPostPath(['type'=>'notice','slug'=>'same'])==='board/notice--same','notice collision safe');
echo "PASS article validation and collision-safe paths\n";
