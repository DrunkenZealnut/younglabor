<?php
require_once __DIR__.'/../config.php';require_once __DIR__.'/Database.php';require_once __DIR__.'/WorkRepository.php';require_once __DIR__.'/ContentPresenter.php';
function publicRepository(): ContentRepository {return new ContentRepository(Database::getInstance()->getConnection());}
function publicWorkRepository(): WorkRepository {return new WorkRepository(Database::getInstance()->getConnection());}
function loadPublicSection(callable $repositoryFactory,string $section,array $filters,int $page,int $perPage=12): array {
 $rows=[];$total=0;$pages=1;$unavailable=false;$page=max(1,$page);$perPage=max(1,min(100,$perPage));
 try{$r=$repositoryFactory();$total=$r->countPublicSection($section,$filters);$pages=max(1,(int)ceil($total/$perPage));$page=min($page,$pages);$rows=$r->listPublicSection($section,$filters,$perPage,($page-1)*$perPage);}catch(Throwable $e){$unavailable=true;$page=1;}
 return compact('rows','total','page','pages','unavailable');
}
function loadPublicWork(callable $read): array {
 try{return ['data'=>$read(publicWorkRepository()),'state'=>'ready'];}
 catch(PDOException $e){return ['data'=>null,'state'=>in_array((int)($e->errorInfo[1]??0),[1146,1054],true)?'preparing':'unavailable'];}
 catch(Throwable $e){return ['data'=>null,'state'=>'unavailable'];}
}
function workUnavailableMessage(string $state): string {return $state==='preparing'?'사업 안내를 준비하고 있습니다. 문의는 이메일로 보내 주세요.':'안내를 불러오지 못했습니다. 잠시 후 다시 확인하거나 이메일로 문의해 주세요.';}
function publicNotFoundStatus(array $result): void {if($result['state']!=='ready')http_response_code(503);elseif($result['data']===null)http_response_code(404);}
function startPublicPage(string $menu,string $title,string $description,string $path): void {
 global $site,$theme,$currentPage,$pageTitle,$pageDescription,$pageUrl;
 $currentPage=$menu;$pageTitle=$title.' - '.$site['name'];$pageDescription=$description;$pageUrl=url($path);require __DIR__.'/header.php';
}
function endPublicPage(): void {global $site;require __DIR__.'/footer.php';}
function renderPageIntro(string $eyebrow,string $title,string $description): void { ?>
<section class="page-header"><div class="container"><?php if(trim($eyebrow)!==''): ?><p class="eyebrow"><?php echo publicEscape($eyebrow); ?></p><?php endif; ?><h1><?php echo publicEscape($title); ?></h1><?php if($description!==''): ?><p><?php echo publicEscape($description); ?></p><?php endif; ?></div></section>
<?php }
function renderWorkTabs(string $active): void { ?>
<nav class="work-tabs" aria-label="사업소개 분류"><a href="<?php echo publicEscape(url('work')); ?>"<?php echo $active==='all'?' aria-current="page"':''; ?>>전체 사업</a><a href="<?php echo publicEscape(url('work/incubating/2026')); ?>"<?php echo $active==='funding'?' aria-current="page"':''; ?>>아름다운재단 인큐베이팅 지원사업</a></nav>
<?php }
function renderUnavailableDetail(array $result): void {renderPageIntro('안내',$result['state']==='ready'?'페이지를 찾을 수 없습니다.':'안내를 준비하고 있습니다.',$result['state']==='ready'?'주소를 확인하거나 메뉴에서 필요한 내용을 찾아 주세요.':workUnavailableMessage($result['state'])); ?>
<section class="section"><div class="container"><a class="btn-cta" href="<?php echo publicEscape(url('work')); ?>">사업소개 보기</a> <a class="content-back" href="mailto:admin@younglabor.kr">admin@younglabor.kr</a></div></section>
<?php }
function requestText(string $name,string $default=''): string {return isset($_GET[$name]) && is_string($_GET[$name])?trim($_GET[$name]):$default;}
