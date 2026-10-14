<?php
$header = file_get_contents(__DIR__ . '/../includes/header.php');
$footer = file_get_contents(__DIR__ . '/../includes/footer.php');
foreach (['단체소개','사업소개','청년과노동','게시판'] as $label) {
    if (strpos($header, $label) === false) exit(1);
}
if (substr_count($header, '<main') !== 1) exit(1);
if (strpos($header, '</main>') !== false) exit(1);
if (strpos($footer, '</main>') === false) exit(1);
if (strpos($footer, 'siteSupportPartners()') === false) exit(1);
if (strpos($header, 'aria-expanded="false"') === false) exit(1);
if (strpos($header, "document.documentElement.classList.add('js')") === false) exit(1);
if (strpos($footer, "setAttribute('aria-expanded'") === false) exit(1);
if (strpos($header, 'https://cdn.jsdelivr.net/gh/orioncactus/pretendard') !== false) exit(1);

// Value: protects=모든 페이지 헤더 로고가 렌더링되어 홈(url('/'))으로 가고, 보조기술이 읽는 이름에 정식 명칭 '청년노동자인권센터'(한 덩어리)와 약칭 '청쎈'이 모두 들어감; fails_when=로고 마크업 수정으로 정식 명칭이 빠지거나 <br>로 다시 쪼개지거나 aria-hidden 처리되거나, 링크가 홈이 아니게 되거나, header.php 렌더링이 fatal; why_new=위 검사는 메뉴 라벨만 문자열로 보고, public-http-smoke.sh는 실행 중인 서버가 있어야 로고 URL만 확인; seam=none; no_claim=CSS 줄바꿈 위치(flex-wrap·keep-all·<wbr>)·보라색 대비·모바일 폭 넘침은 브라우저 확인 영역
require_once __DIR__ . '/../config.php';
$currentPage = 'about';
ob_start();
require __DIR__ . '/../includes/header.php';
$renderedHeader = ob_get_clean();
if (!preg_match('/<a\b[^>]*\bclass="logo"[^>]*>(.*?)<\/a>/s', $renderedHeader, $logo)) { fwrite(STDERR, "헤더에 로고 링크가 없습니다.\n"); exit(1); }
if (!preg_match('/\bhref="([^"]*)"/', $logo[0], $logoHref) || html_entity_decode($logoHref[1], ENT_QUOTES, 'UTF-8') !== url('/')) { fwrite(STDERR, "로고 링크가 홈으로 가지 않습니다.\n"); exit(1); }
$logoName = preg_replace('/<([a-z]+)\b[^>]*aria-hidden="true"[^>]*>.*?<\/\1>/s', '', $logo[1]);
$logoName = preg_replace('/<br\s*\/?>/i', ' ', $logoName);
$logoName = trim(preg_replace('/\s+/u', ' ', html_entity_decode(strip_tags($logoName), ENT_QUOTES, 'UTF-8')));
foreach (['청년노동자인권센터', '청쎈'] as $name) {
    if (!str_contains($logoName, $name)) { fwrite(STDERR, "로고 이름에 '{$name}'이 없습니다: {$logoName}\n"); exit(1); }
}
if (!preg_match('/청년노동자인권센터\s+청쎈/u', $logoName)) { fwrite(STDERR, "로고의 두 이름이 붙어서 읽힙니다: {$logoName}\n"); exit(1); }

// Value: protects=홈 히어로의 '청쎈이 하는 일 →' 링크가 빠진 뒤 홈 첫 화면에서 단체소개로 가는 주 경로인 헤더 메뉴 링크(푸터에도 같은 링크가 있음)(url('about'), '단체소개'); fails_when=메뉴 배열 키/경로가 바뀌어 단체소개 링크가 다른 곳을 가리키거나 사라짐; why_new=위 라벨 검사는 문자열 존재만 보고 렌더링된 href를 확인하지 않음; seam=none; no_claim=모바일 메뉴 열기(JS) 동작
if (!preg_match('/<a\b[^>]*\bhref="' . preg_quote(htmlspecialchars(url('about'), ENT_QUOTES, 'UTF-8'), '/') . '"[^>]*>\s*단체소개\s*<\/a>/u', $renderedHeader)) { fwrite(STDERR, "헤더 메뉴에 단체소개 링크가 없습니다.\n"); exit(1); }
