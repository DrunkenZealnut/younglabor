<?php
require __DIR__.'/../support/renewal.php';require __DIR__.'/../../includes/ContentRepository.php';
$p=renewalTestPdo();$r=new ContentRepository($p);$p->exec("DELETE FROM content_posts WHERE slug='same-test'");
foreach(['activity','press','notice','article'] as $type){$r->save(['type'=>$type,'title'=>$type,'slug'=>'same-test','summary'=>'소개','body'=>'본문','content_date'=>'2026-10-11','status'=>'published','outlet'=>'언론','external_url'=>'https://example.org','resource_category'=>null,'author_id'=>1,'public_author'=>'청쎈','editorial_category'=>$type==='article'?'column':($type==='notice'?'notice':null),'home_featured'=>1],null);}
check($r->findPublicBoard('same-test')['type']==='activity','activity identified');
check($r->findPublicBoard('press--same-test')['type']==='press','press identified');
check($r->findPublicBoard('notice--same-test')['type']==='notice','notice identified');
check(count($r->listPublicSection('board',[],12,0))===3,'all board types combined');
check($r->countPublicSection('youth-labor',['category'=>'column'])===1,'article category');
$p->exec("UPDATE content_posts SET status='draft' WHERE type='notice' AND slug='same-test'");check($r->findPublicBoard('notice--same-test')===null,'draft hidden');
$p->exec("DELETE FROM content_posts WHERE slug='same-test'");
echo "PASS combined sections, draft privacy, slug namespace\n";
