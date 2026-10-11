<?php
require_once __DIR__.'/includes/PublicCards.php';
$r=loadPublicWork(static fn($repo)=>$repo->findProgram('vibecoding-club'));publicNotFoundStatus($r);$club=$r['data'];
startPublicPage('work',$club['title']??'바이브코딩동아리',$club['summary']??'바이브코딩동아리 안내','club');
if(!$club){renderUnavailableDetail($r);endPublicPage();exit;}
$clubOpen=programAcceptsApplications($club);renderPageIntro('청쎈과 함께 만드는 경험',$club['title'],$club['summary']);
?>
<section class="section"><div class="container club-intro"><div><span class="club-status"><?php echo publicEscape(programStateLabel($club)); ?></span><h2>나에게 필요한 것,<br>내가 직접 만듭니다.</h2><div class="plain-content"><?php echo renderPlainText($club['body']??''); ?></div></div><div class="club-art" aria-hidden="true"><span>&lt; idea /&gt;</span><strong>말하고.<br>만들고.<br>바꾸고.</strong><span>&lt; your app /&gt;</span></div></div></section>
<section class="section section-alt"><div class="container"><div class="section-heading"><h2>함께 만들 수 있는 것</h2><p>불편했던 일상에서 시작해 보세요.</p></div><div class="club-example-grid"><article><span>01</span><h3>과제 자료 도우미</h3><p>필요한 자료를 모으고 정리하는 나만의 도구.</p></article><article><span>02</span><h3>공부 도구</h3><p>내가 배우는 내용을 찾고 정리하는 앱.</p></article><article><span>03</span><h3>게임 공략 챗봇</h3><p>모아 둔 공략에서 필요한 답을 찾는 경험.</p></article></div></div></section>
<section class="section"><div class="container split-intro"><h2 class="section-title">누가, 어떻게<br>참여하나요</h2><dl class="club-facts"><div><dt>대상</dt><dd><?php echo publicEscape($club['audience']); ?></dd></div><div><dt>모집</dt><dd><?php echo publicEscape(programStateLabel($club)); ?></dd></div><div><dt>참가비</dt><dd><?php echo publicEscape($club['fee']); ?></dd></div><div><dt>지원</dt><dd><?php echo publicEscape($club['support_details']); ?></dd></div><div><dt>문의</dt><dd><a href="mailto:<?php echo publicEscape($club['contact_email']); ?>"><?php echo publicEscape($club['contact_email']); ?></a></dd></div></dl></div></section>
<section class="section club-apply" id="apply"><div class="container split-intro"><h2>함께 만들 준비,<br>여기서 시작해요.</h2><div>
<?php if($clubOpen): ?><p>바이브코딩동아리는 상시모집합니다. 신청서에 관심 있는 활동과 연락처를 남겨 주세요.</p><a class="tool-link" href="<?php echo publicEscape($club['application_url']); ?>">신청서 작성하기 <span aria-hidden="true">↗</span><span class="tool-domain"><?php echo publicEscape(externalLinkHost($club['application_url'])); ?></span><span class="sr-only">외부 서비스로 이동</span></a><p>신청은 센터가 따로 만든 신청 페이지에서 받습니다. 실제 접수 확인과 개인정보 안내는 해당 페이지에서 확인해 주세요.</p>
<?php else: ?><p>현재 접수를 받지 않습니다. 활동 소식과 다음 안내는 게시판에서 확인해 주세요.</p><a class="btn-cta" href="<?php echo publicEscape(url('board')); ?>">게시판 보기 ↗</a><?php endif; ?>
</div></div></section>
<section class="section"><div class="container split-intro"><h2>학교에서 함께</h2><div><p>학생의 제작과 학습을 함께할 학교와 선생님의 협력 문의를 기다립니다.</p><a class="btn-cta btn-secondary" href="<?php echo publicEscape(url('').'#contact'); ?>">학교 협력 문의하기</a></div></div><div class="container"><div class="sponsor-box"><span>이 사업은</span><img src="<?php echo publicEscape(assetUrl('assets/images/beautiful-foundation-ci.png')); ?>" width="524" height="126" alt="아름다운재단" loading="lazy"><span>지원으로 진행됩니다.</span></div></div></section>
<?php endPublicPage(); ?>
