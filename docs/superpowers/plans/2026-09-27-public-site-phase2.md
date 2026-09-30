# 공개 사이트 2차 개편 — 바이브코딩동아리 전환 Implementation Plan

> **For agentic workers:** REQUIRED SUB-SKILL: Use superpowers:subagent-driven-development (recommended) or superpowers:executing-plans to implement this plan task-by-task. Steps use checkbox (`- [ ]`) syntax for tracking.

**Goal:** 옛 청소년노동안전동아리 신청을 닫고, 바이브코딩동아리 소개·모집 동선(`/club`, 홈 알림 띠)을 연다. 아울러 재단 크레딧·공유 이미지·검색 노출을 바로잡는다.

**Architecture:** 서버 렌더링 PHP 구조를 그대로 쓴다. 모집 정보(명칭·신청 URL·마감일)는 `includes/SiteContent.php`의 순수 함수 한 곳에서 관리하고, 모집 중 여부는 서버가 Asia/Seoul 기준 마감일로 판정한다. 운영 배포의 삭제 동기화가 옵트인이라, 옛 경로는 파일을 지우지 않고 301·410 응답으로 내용을 바꾼다. 신청은 외부 신청서가 받고, 사이트는 소개와 연결만 맡는다.

**Tech Stack:** Apache(mod_rewrite), PHP 8(운영 8.4, 로컬 CLI 8.4, XAMPP 8.2), HTML5, CSS, bash·curl, Node.js 22(접근성 테스트), BF 저장소 `content/tools/promo.py`(공유 이미지 렌더)

**Spec:** `docs/superpowers/specs/2026-09-27-public-site-phase2-design.md`

## 1단계 실행 기록 (2026-09-28)

Task 0~6을 작업마다 구현 → 스펙 리뷰 → 품질 리뷰 순서로 실행했다(`0467ae8..5ea44ee`, 커밋 14개). 1단계 전체 최종 리뷰는 "코드는 배포 준비 완료, 배포는 O1 완료 뒤"로 판정했다. 리뷰에서 나와 계획의 코드와 달라진 점은 다음과 같다. 최종 코드는 커밋을 따른다.

| Task | 달라진 점 | 이유 |
|---|---|---|
| 1 | Step 4의 grep에 `--exclude-dir=.worktrees --exclude-dir=.git` | 작업 트리 안 `.worktrees/`의 옛 사본이 오탐을 낸다 |
| 2 | 테스트에 `date_default_timezone_set('UTC')`와 인자 없는 `clubRecruitmentIsOpen()` 호출 추가, 마감일 설정에 설명 주석 | 서버 기본 시간대가 서울일 때 시간대 누락 회귀가 통과했다 |
| 3 | `.page-header .club-status`(명시도), `.notice-panel .tool-link { width: fit-content; grid-template-columns: auto 1fr }`, `.club-facts { max-width: 48rem }`, 상단 `← 함께하기` 링크(설계 §6), h1의 `<wbr>`, 마감 뒤 title·description, "넓혀 가려 합니다", `.sponsor-box`를 공통 주석 아래로, 테스트 needle 보강 | 상태 배지 대비 3.30:1(WCAG AA 미달), 신청 링크의 ↗가 패널 끝으로 벌어짐, 360px 넘침 |
| 4 | 테스트에 조건문·삼항식·히어로 뒤 위치 검사 추가, 띠 링크에 `sr-only` 설명, `justify-content: flex-start` | 조건 제거·반전 등 변이 5가지가 통과했다. 링크 목록에서 목적이 드러나지 않았다 |
| 5 | 301에 `Cache-Control: max-age=86400`, 종료 테스트가 `committee` 문자열 전체와 txt·xml·html·json·svg까지 검사 | 롤백 때 캐시된 301이 깨진 `/club`에 묶이는 것을 막는다 |
| 6 | 스모크: `expect()` 진단, 봇 UA(방문 통계 제외), 게시판은 `/activity/ /press/ /resources/`로 요청, 슬래시 없는 게시판 주소의 301 모양 확인, `/committee` 체인, 410 본문 확인. 접근성: 렌더된 모집 상태로 분기, `CLUB_EXPECT_STATE=open|closed`, `redirected === false`. `managed-content-http-smoke.sh`도 끝 슬래시로. 런북: 배포 확인 9번에 운영 명령, `## rollback` 첫머리에 "전환 이전으로 되돌리지 말고 수정 배포" 경고 | 실제 Apache와 운영에서는 슬래시 없는 디렉터리가 `.htaccess`보다 먼저 301 된다. 링크 존재 여부로 분기하면 링크가 사라져도 통과했다 |
| D8 변경 (2026-09-30) | `/club` 참가 정보의 "러버블 계정은 센터가 준비해 안내합니다" → "러버블 가입은 센터가 안내합니다" | D8이 "보호자 동의 후 본인 가입"으로 바뀌어 센터가 계정을 만들지 않는다 |
| 신청 페이지 (2026-09-30) | `/club` 신청 안내의 "신청서는 외부 설문 서비스에서 받으며" → "신청은 센터가 따로 만든 신청 페이지에서 받으며". Task 7 선행 조건을 링크 확인으로 바꿈 | 신청서는 센터가 직접 만든 서베이 페이지이고, 사이트는 링크만 한다 |

검증 환경: XAMPP가 꺼져 있을 때는 XAMPP의 httpd를 별도 설정으로 sudo 없이 띄워(127.0.0.1:8080, DocumentRoot `htdocs`, mod_rewrite·mod_dir·PHP 모듈) 운영과 같은 Apache 동작으로 HTTP 검사를 돌렸다. PHP 내장 서버는 mod_dir 동작이 달라 쓰지 않는다. `tests/apache-security-rules.test.sh`는 bash에 ripgrep이 없어 돌리지 못했다(`brew install ripgrep` 필요).

## 전제와 제약

- 작업 트리는 `/Applications/XAMPP/xamppfiles/htdocs/younglabor`이다. XAMPP가 이 경로를 `http://localhost:8080/younglabor`로 서비스한다. `.worktrees/` 아래는 루트 `.htaccess`가 403으로 막아 HTTP 테스트를 할 수 없다.
- 브랜치는 `origin/main`(`5bcd274`)에서 만든 `feature/vibecoding-club`이다.
- 작업 트리의 `CLAUDE.md`에는 커밋되지 않은 로컬 변경이 있다. 이 계획은 `CLAUDE.md`를 수정·스테이징하지 않는다. 커밋은 항상 파일을 지정해 `git add <파일>`로 한다(`git add -A`·`git add .` 금지).
- 명칭은 `바이브코딩동아리`(띄어쓰기 없음), 도구는 `러버블(Lovable)`이다. `Claude Code`·`클로드코드`는 공개 문구에 쓰지 않는다.
- 재단 크레딧은 `공익단체 인큐베이팅 지원사업`이다(연도 없음).
- 공개 파일의 폐기 문구: `청소년노동안전동아리`, `노동안전동아리`, `자격증준비`, `참견위원회`, `청소년 동아리 신청`.
- 신청 링크는 `https://zealot-survey.vercel.app/RJXag60aMfMT` 하나다. iframe과 `target="_blank"`는 쓰지 않는다(1차 외부 링크 규칙).
- 모집 마감은 `2026-10-15`이다. 23:59:59(Asia/Seoul)까지 모집 중으로 본다.
- 학교 실명, 학생 개인정보, 연락처는 코드·테스트·커밋·PR에 넣지 않는다.
- 푸시·PR·머지는 사용자 승인 뒤에 한다. `main` 머지는 곧 운영 자동 배포다.
- 운영 DB 작업(3단계)은 사람이 호스팅 DB 콘솔에서 한다. 웹 루트에 무인증 마이그레이션 스크립트를 두지 않는다.

## Review Focus

- 모집 판정 경계(10-15 23:59:59 / 10-16 00:00:00 KST)가 서버 시간대와 무관하게 맞는지
- 외부 신청 링크가 escape되고 도메인과 외부 이동이 텍스트로 드러나는지
- 옛 신청 API가 어떤 요청에도 DB와 메일을 건드리지 않는지
- 알림 띠가 히어로(단체 정체성) 뒤에 오고 마감 뒤 사라지는지
- sitemap에 draft·`/committee`가 없고 DB 장애 때도 200인지
- 관리자 동아리 코드를 지운 뒤(3단계) 대시보드·사이드바가 깨지지 않는지

## 파일 구조

| 파일 | 책임 | 단계·작업 |
|---|---|---|
| `includes/SiteContent.php` | 크레딧 문자열, 모집 설정·마감 판정·마감일 표기 | 1 · Task 1·2 |
| `club.php` (신설) | `/club` 소개·신청 안내 페이지 | 1 · Task 3, 2 · Task 8 |
| `index.php` | 홈 알림 띠, `함께하기` 버튼 | 1 · Task 4 |
| `assets/css/style.css` | `/club`·알림 띠·지원 표기 스타일 | 1 · Task 3·4 |
| `committee/index.php` | 옛 주소 → `/club` 301 (계속 유지) | 1 · Task 5 |
| `api/committee.php` | 옛 신청 API → 410 (3단계에서 삭제) | 1 · Task 5, 3 · Task 11 |
| `includes/Mailer.php` | 동아리 신청 메일 본문 함수 제거 | 1 · Task 5 |
| `tests/club-recruitment.test.php` (신설) | 모집 설정·마감 경계 | 1 · Task 2 |
| `tests/club-page.test.php` (신설) | `/club` 페이지 계약 | 1 · Task 3 |
| `tests/retired-club.test.php` (신설) | 옛 경로 응답·폐기 문구 검사 | 1 · Task 5, 3 · Task 11 |
| `tests/site-content.test.php`, `tests/public-copy.test.php`, `tests/public-layout.test.php` | 기존 계약 갱신 | Task 1·4·8 |
| `tests/public-http-smoke.sh`, `tests/public-accessibility.test.js` | HTTP·접근성 게이트 | Task 6·9·11 |
| `docs/runbooks/managed-content-deployment.md` | 배포 확인 항목 | Task 6·11 |
| `includes/header.php` | 기본 `og:image`와 페이지별 `$pageImage` | 2 · Task 8 |
| `assets/images/og-default.png`, `og-club.png` (신설) | 1200×630 공유 이미지 | 2 · Task 8 |
| `includes/Sitemap.php`, `sitemap.php`, `robots.txt` (신설), `.htaccess` | 검색 노출 | 2 · Task 9 |
| `tests/sitemap.test.php` (신설) | sitemap 항목·장애 격리·이스케이프 | 2 · Task 9 |
| `admin/helpers.php`, `admin/dashboard.php` | 동아리 메뉴·집계 제거 | 3 · Task 11 |
| `admin/committee.php`, `admin/api/committee-action.php` | 삭제 | 3 · Task 11 |
| `database/migrations/20261012_drop_committee_applications.sql` (신설) | 옛 신청 테이블 삭제 | 3 · Task 11 |
| `tests/admin-committee-removed.test.php` (신설) | 관리자 코드 제거 확인 | 3 · Task 11 |

## 일정과 순서

| 단계 | 기간 | 작업 | 선행 조건 |
|---|---|---|---|
| 준비 | 09-28 | Task 0, 운영 O1·O2 시작 | — |
| 1단계 | 09-28 ~ 10-02 | Task 1~7 | 신청 페이지 링크가 열려야 함 (O1 문구 수정은 서베이 쪽 작업) |
| 2단계 | 10-05 ~ 10-10 | Task 8~10, 운영 O4~O6 | 1단계 배포 |
| 모집·선발 | 10-05 ~ 10-23 | 운영 O3·O6 | — |
| 3단계 | 10-12 목표 | Task 11~12 | O4(옛 신청 데이터 파기) 완료 기록 |

코드 밖 운영 작업(O1~O12)은 문서 끝 "운영 작업"에 있다.

---

### Task 0: 작업 브랜치 준비

**Files:** 없음 (브랜치·환경)

- [ ] **Step 1: 저장소 상태 확인**

```bash
cd /Applications/XAMPP/xamppfiles/htdocs/younglabor
git status --short
git fetch origin
git log --oneline -1 origin/main
```

Expected: `M CLAUDE.md`와 추적되지 않은 파일 몇 개(`docs/superpowers/specs/2026-09-27-public-site-phase2-design.md`, `docs/superpowers/plans/2026-09-27-public-site-phase2.md` 포함). `origin/main`이 `5bcd274`이 아니면 `git diff --stat 5bcd274 origin/main`으로 이 계획의 파일(파일 구조 표)이 바뀌었는지 확인하고, 바뀌었으면 멈추고 사용자에게 알린다.

- [ ] **Step 2: 브랜치 만들기**

```bash
git switch -c feature/vibecoding-club origin/main
git status --short
```

Expected: 전환 성공. `M CLAUDE.md`가 그대로 남는다(두 커밋의 `CLAUDE.md`가 같아 충돌이 없다). 로컬 변경 때문에 전환이 거부되면 stash하지 말고 멈춘 뒤 사용자에게 알린다.

- [ ] **Step 3: PHP 테스트 기준선**

```bash
for t in tests/*.test.php; do php "$t" >/dev/null 2>&1 || echo "FAIL $t"; done; echo done
```

Expected: `done`만 출력된다.

- [ ] **Step 4: XAMPP 기동과 HTTP 기준선**

Apache·MySQL 기동에는 sudo가 필요하다. 에이전트라면 사용자에게 다음 실행을 요청한다: `! sudo /Applications/XAMPP/xamppfiles/bin/apachectl start && sudo /Applications/XAMPP/xamppfiles/bin/mysql.server start`

```bash
bash tests/public-http-smoke.sh && node tests/public-accessibility.test.js && echo HTTP-OK
bash -c 'command -v rg' || echo "NO-RG"
```

Expected: `HTTP-OK`와 `rg` 경로. `NO-RG`가 나오면 `tests/apache-security-rules.test.sh`가 실패한다(대화형 셸의 `rg` 함수는 bash 스크립트에서 보이지 않는다). 사용자에게 `! brew install ripgrep` 실행을 요청한다.

- [ ] **Step 5: 설계·계획 문서 커밋**

```bash
git add docs/superpowers/specs/2026-09-27-public-site-phase2-design.md docs/superpowers/plans/2026-09-27-public-site-phase2.md
git commit -m "docs: plan public site phase 2"
```

---

## 1단계 — 전환 배포 (09-28 ~ 10-02)

### Task 1: 재단 크레딧에서 연도 제거 (D4)

**Files:**

- Modify: `includes/SiteContent.php` (`siteSupportPartners()`)
- Modify: `tests/site-content.test.php:31`

**Interfaces:** `siteSupportPartners()[0]['program']` → `'공익단체 인큐베이팅 지원사업'`. 푸터·홈·소개 세 곳이 이 값을 출력한다.

- [ ] **Step 1: 기대값을 바꿔 테스트를 실패시킨다**

`tests/site-content.test.php`의 마지막 줄

```php
assertSameValue('2025 공익단체 인큐베이팅 지원사업', $support[0]['program'], '지원사업 크레딧이 다릅니다.');
```

을 다음 두 줄로 바꾼다.

```php
assertSameValue('공익단체 인큐베이팅 지원사업', $support[0]['program'], '지원사업 크레딧이 다릅니다.');
assertSameValue(0, preg_match('/\d{4}/', $support[0]['program']), '지원사업 크레딧에 연도를 넣지 않습니다.');
```

- [ ] **Step 2: 실패 확인**

Run: `php tests/site-content.test.php; echo "exit=$?"`

Expected: `지원사업 크레딧이 다릅니다.`와 `exit=1`

- [ ] **Step 3: 크레딧 수정**

`includes/SiteContent.php`의 `siteSupportPartners()`에서

```php
        'program' => '2025 공익단체 인큐베이팅 지원사업',
```

을 다음으로 바꾼다.

```php
        'program' => '공익단체 인큐베이팅 지원사업',
```

- [ ] **Step 4: 통과 확인**

```bash
php tests/site-content.test.php; echo "exit=$?"
php -l includes/SiteContent.php
grep -rn "2025 공익단체" --include='*.php' . | grep -v '^./docs/' ; echo "grep-exit=$?"
```

Expected: `exit=0`, `No syntax errors detected`, `grep-exit=1`(남은 곳 없음)

- [ ] **Step 5: 커밋**

```bash
git add includes/SiteContent.php tests/site-content.test.php
git commit -m "copy: drop year from foundation credit"
```

### Task 2: 모집 설정과 마감 판정

**Files:**

- Modify: `includes/SiteContent.php` (파일 끝에 함수 3개 추가)
- Create: `tests/club-recruitment.test.php`

**Interfaces:**

- `siteClubRecruitment(): array` — `name`, `apply_url`, `apply_domain`, `deadline`(`Y-m-d`)
- `clubRecruitmentIsOpen(?DateTimeImmutable $now = null): bool` — 마감일 다음 날 00:00(Asia/Seoul) 전이면 `true`
- `clubDeadlineLabel(): string` — `10월 15일`

- [ ] **Step 1: 실패하는 테스트 작성**

`tests/club-recruitment.test.php`:

```php
<?php
require_once __DIR__ . '/../includes/SiteContent.php';

function assertClub(bool $condition, string $message): void
{
    if (!$condition) {
        fwrite(STDERR, $message . "\n");
        exit(1);
    }
}

$club = siteClubRecruitment();
assertClub($club['name'] === '바이브코딩동아리', '동아리 명칭이 다릅니다.');
assertClub($club['apply_url'] === 'https://zealot-survey.vercel.app/RJXag60aMfMT', '신청서 URL이 다릅니다.');
assertClub(parse_url($club['apply_url'], PHP_URL_SCHEME) === 'https', '신청서 링크는 HTTPS여야 합니다.');
assertClub(parse_url($club['apply_url'], PHP_URL_HOST) === $club['apply_domain'], '표시 도메인이 링크 호스트와 다릅니다.');
assertClub($club['deadline'] === '2026-10-15', '모집 마감일이 다릅니다.');
assertClub(clubDeadlineLabel() === '10월 15일', '마감일 표기가 다릅니다.');

$seoul = new DateTimeZone('Asia/Seoul');
$utc = new DateTimeZone('UTC');
assertClub(clubRecruitmentIsOpen(new DateTimeImmutable('2026-09-28 09:00:00', $seoul)), '모집 기간에는 모집 중이어야 합니다.');
assertClub(clubRecruitmentIsOpen(new DateTimeImmutable('2026-10-15 23:59:59', $seoul)), '마감일 23:59:59에는 모집 중이어야 합니다.');
assertClub(!clubRecruitmentIsOpen(new DateTimeImmutable('2026-10-16 00:00:00', $seoul)), '마감 다음 날 0시에는 마감이어야 합니다.');
assertClub(clubRecruitmentIsOpen(new DateTimeImmutable('2026-10-15 14:59:59', $utc)), 'UTC로 주어져도 서울 기준으로 판정해야 합니다.');
assertClub(!clubRecruitmentIsOpen(new DateTimeImmutable('2026-10-15 15:00:00', $utc)), 'UTC 15시는 서울 16일 0시이므로 마감이어야 합니다.');
```

- [ ] **Step 2: 실패 확인**

Run: `php tests/club-recruitment.test.php; echo "exit=$?"`

Expected: `Call to undefined function siteClubRecruitment()` 치명적 오류와 `exit=255`

- [ ] **Step 3: 함수 구현**

`includes/SiteContent.php` 끝에 추가한다.

```php

function siteClubRecruitment(): array
{
    return [
        'name' => '바이브코딩동아리',
        'apply_url' => 'https://zealot-survey.vercel.app/RJXag60aMfMT',
        'apply_domain' => 'zealot-survey.vercel.app',
        'deadline' => '2026-10-15',
    ];
}

function clubRecruitmentIsOpen(?DateTimeImmutable $now = null): bool
{
    $zone = new DateTimeZone('Asia/Seoul');
    $now = ($now ?? new DateTimeImmutable('now', $zone))->setTimezone($zone);
    $closesAt = (new DateTimeImmutable(siteClubRecruitment()['deadline'], $zone))->modify('+1 day');
    return $now < $closesAt;
}

function clubDeadlineLabel(): string
{
    $deadline = new DateTimeImmutable(siteClubRecruitment()['deadline'], new DateTimeZone('Asia/Seoul'));
    return $deadline->format('n월 j일');
}
```

- [ ] **Step 4: 통과 확인**

```bash
php tests/club-recruitment.test.php; echo "exit=$?"
php tests/site-content.test.php; echo "exit=$?"
php -l includes/SiteContent.php
```

Expected: 두 테스트 `exit=0`, 구문 오류 없음

- [ ] **Step 5: 커밋**

```bash
git add includes/SiteContent.php tests/club-recruitment.test.php
git commit -m "feat: define vibe-coding club recruitment"
```

### Task 3: `/club` 페이지

**Files:**

- Create: `club.php`
- Modify: `assets/css/style.css` (파일 끝에 추가)
- Create: `tests/club-page.test.php`

**Interfaces:**

- Consumes: `siteClubRecruitment()`, `clubRecruitmentIsOpen()`, `clubDeadlineLabel()`
- Produces: `/club` — `.htaccess`의 확장자 없는 URL 규칙이 `club.php`로 연결한다(새 rewrite 규칙 없음)

- [ ] **Step 1: 실패하는 페이지 계약 테스트 작성**

`tests/club-page.test.php`:

```php
<?php
function failClubPage(string $message): void
{
    fwrite(STDERR, $message . "\n");
    exit(1);
}

$path = __DIR__ . '/../club.php';
if (!is_file($path)) failClubPage('club.php가 없습니다.');
if (is_dir(__DIR__ . '/../club')) failClubPage('club/ 디렉터리가 있으면 /club이 club.php로 가지 않습니다.');

$source = file_get_contents($path);
$required = [
    'siteClubRecruitment()', 'clubRecruitmentIsOpen()', 'clubDeadlineLabel()',
    '나도 개발자!', '러버블(Lovable)', '만 18세 미만 참가자는 보호자 동의가 필요합니다.',
    '외부 서비스로 이동', 'class="sponsor-box"', "url('activity')", '#contact',
];
foreach ($required as $needle) {
    if (strpos($source, $needle) === false) failClubPage("club.php에 없음: {$needle}");
}
foreach (['<iframe', 'target="_blank"', 'Claude Code', '클로드코드'] as $forbidden) {
    if (stripos($source, $forbidden) !== false) failClubPage("club.php에 금지 문구: {$forbidden}");
}

$css = file_get_contents(__DIR__ . '/../assets/css/style.css');
foreach (['.club-status', '.club-examples', '.club-facts', '.sponsor-box'] as $selector) {
    if (strpos($css, $selector) === false) failClubPage("style.css에 없음: {$selector}");
}
```

- [ ] **Step 2: 실패 확인**

Run: `php tests/club-page.test.php; echo "exit=$?"`

Expected: `club.php가 없습니다.`와 `exit=1`

- [ ] **Step 3: `club.php` 작성**

```php
<?php
require_once __DIR__ . '/config.php';
require_once __DIR__ . '/includes/Database.php';
require_once __DIR__ . '/includes/PageTracker.php';
require_once __DIR__ . '/includes/SiteContent.php';
PageTracker::track('바이브코딩동아리');

$club = siteClubRecruitment();
$clubOpen = clubRecruitmentIsOpen();
$clubName = htmlspecialchars($club['name'], ENT_QUOTES, 'UTF-8');
$deadlineLabel = htmlspecialchars(clubDeadlineLabel(), ENT_QUOTES, 'UTF-8');

$currentPage = 'club';
$pageTitle = $club['name'] . ' 모집 - ' . $site['name'];
$pageDescription = '반도체고등학교 청소년과 함께 AI로 나만의 앱을 만드는 3개월. 코딩을 몰라도 참가할 수 있습니다.';
$pageUrl = url('club');
require_once __DIR__ . '/includes/header.php';
?>
<section class="page-header">
    <div class="container">
        <p class="eyebrow">함께하기 · 청소년 동아리</p>
        <h1><?php echo $clubName; ?></h1>
        <p>나도 개발자! — 반도체고 청소년과 함께하는 3개월</p>
        <?php if ($clubOpen): ?>
            <p class="club-status">모집 중 · <?php echo $deadlineLabel; ?> 마감</p>
        <?php else: ?>
            <p class="club-status">모집 마감</p>
        <?php endif; ?>
    </div>
</section>

<section class="section">
    <div class="container split-intro">
        <h2 class="section-title">무엇을 하나요</h2>
        <div>
            <p>나에게 필요했지만 상상만 했던 앱을 직접 만들어요. 코딩을 몰라도 괜찮아요. AI에게 말로 설명하면서 만듭니다.</p>
            <ul class="club-examples">
                <li>나만의 운동관리</li>
                <li>해외축구 분석</li>
                <li>게임공략 챗봇</li>
                <li>자격증 대비 앱</li>
            </ul>
        </div>
    </div>
</section>

<section class="section section-alt">
    <div class="container split-intro">
        <h2 class="section-title">누가, 어떻게 참가하나요</h2>
        <dl class="club-facts">
            <div><dt>대상</dt><dd>반도체고등학교 재학생. 코딩 경험이 없어도 됩니다.</dd></div>
            <div><dt>기간</dt><dd>3개월</dd></div>
            <div><dt>지원</dt><dd>러버블(Lovable) 이용 지원, AI 전문가 자문</dd></div>
            <div><dt>계정</dt><dd>러버블 가입은 센터가 안내합니다. 만 18세 미만 참가자는 보호자 동의가 필요합니다.</dd></div>
        </dl>
    </div>
</section>

<section class="section">
    <div class="container split-intro">
        <h2 class="section-title">왜 센터가 하나요</h2>
        <div class="notice-panel">
            <p>AI 코딩은 학교가 따로 제공하기 어려운 경험입니다.</p>
            <p>필요한 도구를 스스로 만들다 보면 일하는 사람과 안전을 다시 보게 됩니다. 노동안전을 가르치는 대신, 학생이 만들며 익히는 동아리입니다.</p>
            <p>올해 첫 모델을 만들고, 내년에 더 많은 학교로 넓혀 갑니다.</p>
        </div>
    </div>
</section>

<section class="section section-alt" id="apply">
    <div class="container split-intro">
        <h2 class="section-title">신청하기</h2>
        <div class="notice-panel">
        <?php if ($clubOpen): ?>
            <p><strong><?php echo $deadlineLabel; ?>까지 신청을 받습니다.</strong> 신청자가 많으면 심사를 거쳐 선발하고, 결과는 신청서에 적은 휴대전화와 이메일로 알려드립니다.</p>
            <a class="tool-link" href="<?php echo htmlspecialchars($club['apply_url'], ENT_QUOTES, 'UTF-8'); ?>">
                신청서 작성하기
                <span aria-hidden="true">↗</span>
                <span class="tool-domain"><?php echo htmlspecialchars($club['apply_domain'], ENT_QUOTES, 'UTF-8'); ?></span>
                <span class="sr-only">외부 서비스로 이동</span>
            </a>
            <p>신청은 센터가 따로 만든 신청 페이지에서 받으며, 그 페이지에 안내된 개인정보 처리 기준이 적용됩니다.</p>
        <?php else: ?>
            <p><strong>이번 모집은 마감되었습니다.</strong> 동아리 활동 소식은 활동게시판에서 전합니다.</p>
            <a class="btn-cta btn-secondary" href="<?php echo url('activity'); ?>">활동게시판 보기</a>
        <?php endif; ?>
        </div>
    </div>
</section>

<section class="section">
    <div class="container split-intro">
        <h2 class="section-title">선생님께</h2>
        <div class="notice-panel">
            <p>동아리를 함께 지도해 주실 선생님을 찾습니다. 학교 일정에 맞춰 센터가 도구와 자문을 준비합니다.</p>
            <a class="btn-cta btn-secondary" href="<?php echo url('/'); ?>#contact">협력 문의하기</a>
        </div>
    </div>
    <div class="container">
        <div class="sponsor-box">
            <span>이 사업은</span>
            <img src="<?php echo url('assets/images/beautiful-foundation-ci.png'); ?>" width="524" height="126" loading="lazy" decoding="async" alt="아름다운재단">
            <span>지원으로 진행됩니다.</span>
        </div>
    </div>
</section>
<?php require_once __DIR__ . '/includes/footer.php'; ?>
```

- [ ] **Step 4: 스타일 추가**

`assets/css/style.css` 끝(`prefers-reduced-motion` 블록 다음)에 추가한다. `.sponsor-box`는 지금 `committee/index.php` 안에만 있어 Task 5에서 사라지므로 공통 스타일로 옮긴다.

```css

/* 바이브코딩동아리 (/club) */
.club-status { display: inline-block; margin-top: 1.5rem; padding: .25rem .6rem; background: var(--color-lime); color: var(--color-ink); font-size: .875rem; font-weight: 600; }
.club-examples { display: grid; grid-template-columns: repeat(2, minmax(0, 1fr)); gap: 0 1.5rem; margin-top: 1.5rem; padding: 0; list-style: none; }
.club-examples li { padding: 1rem 0; border-top: var(--hairline); font-weight: 600; }
.club-facts { margin: 0; }
.club-facts div { display: grid; grid-template-columns: 5rem minmax(0, 1fr); gap: 1rem; padding: 1rem 0; border-top: var(--hairline); }
.club-facts dt { font-weight: 600; }
.club-facts dd { margin: 0; color: var(--color-gray); }
.notice-panel .tool-link { margin: 1.5rem 0; }
.notice-panel .btn-cta { margin-top: 1.5rem; }
.sponsor-box { display: flex; flex-wrap: wrap; align-items: center; gap: .7rem; margin-top: clamp(2rem, 5vw, 4rem); padding: 1rem 0; border-top: var(--hairline); border-bottom: var(--hairline); }
.sponsor-box span { font-size: var(--fs-caption); color: var(--color-gray); }
.sponsor-box img { height: 28px; width: auto; }
@media (max-width: 600px) {
    .club-examples { grid-template-columns: 1fr; }
    .club-facts div { grid-template-columns: 1fr; gap: .25rem; }
}
```

- [ ] **Step 5: 테스트·구문·로컬 응답 확인**

```bash
php tests/club-page.test.php; echo "exit=$?"
php -l club.php
curl -sS -o /dev/null -w '%{http_code}\n' http://localhost:8080/younglabor/club
curl -sS http://localhost:8080/younglabor/club | grep -c 'https://zealot-survey.vercel.app/RJXag60aMfMT'
```

Expected: `exit=0`, 구문 오류 없음, `200`, `1`(모집 기간 중)

- [ ] **Step 6: 화면 확인**

브라우저에서 `http://localhost:8080/younglabor/club`을 데스크톱 폭과 390px 폭으로 연다. 제목·모집 상태·예시 4종·참가 정보·신청 링크(도메인 표시)·지원 표기가 겹치지 않고 가로 스크롤이 없어야 한다.

- [ ] **Step 7: 커밋**

```bash
git add club.php assets/css/style.css tests/club-page.test.php
git commit -m "feat: add vibe-coding club page"
```

### Task 4: 홈 알림 띠와 `함께하기` 버튼

**Files:**

- Modify: `index.php`
- Modify: `assets/css/style.css`
- Modify: `tests/public-copy.test.php` (끝에 추가)

**Interfaces:** 모집 중이면 히어로 바로 아래에 `aside.club-banner`를 그린다. `함께하기` 버튼은 `/club`으로 가고, 문구는 모집 중이면 `바이브코딩동아리 신청`, 마감 뒤면 `바이브코딩동아리 소식`이다.

- [ ] **Step 1: 실패하는 카피 테스트 추가**

`tests/public-copy.test.php` 끝에 추가한다.

```php
$heroAt = strpos($home, '제조업 청년노동자들의 안전과 노동권을 지킵니다.');
$bannerAt = strpos($home, 'class="club-banner"');
$fieldAt = strpos($home, '현장에서 무슨 일이 있었나');
if ($bannerAt === false || $bannerAt < $heroAt || $bannerAt > $fieldAt) exit(1);
if (strpos($home, '$clubOpen = clubRecruitmentIsOpen();') === false) exit(1);
if (substr_count($home, "url('club')") < 2) exit(1);
if (strpos($home, "url('committee')") !== false) exit(1);
if (strpos(file_get_contents(__DIR__ . '/../assets/css/style.css'), '.club-banner') === false) exit(1);
```

- [ ] **Step 2: 실패 확인**

Run: `php tests/public-copy.test.php; echo "exit=$?"`

Expected: `exit=1`

- [ ] **Step 3: 모집 상태 변수 추가**

`index.php`에서

```php
$homeContent = loadHomeContent(static function () {
    return new ContentRepository(Database::getInstance()->getConnection());
});
```

바로 아래에 두 줄을 넣는다.

```php
$club = siteClubRecruitment();
$clubOpen = clubRecruitmentIsOpen();
```

- [ ] **Step 4: 히어로 아래 알림 띠 추가**

`index.php`에서

```php
            <a href="<?php echo url('activity'); ?>" class="btn-cta btn-secondary">현장 활동 보기</a>
        </div>
    </div>
</section>
```

바로 아래에 넣는다.

```php

<?php if ($clubOpen): ?>
<aside class="club-banner" aria-label="<?php echo htmlspecialchars($club['name'], ENT_QUOTES, 'UTF-8'); ?> 모집 안내">
    <div class="container club-banner-inner">
        <p><strong><?php echo htmlspecialchars($club['name'], ENT_QUOTES, 'UTF-8'); ?> 모집 중</strong> · <?php echo htmlspecialchars(clubDeadlineLabel(), ENT_QUOTES, 'UTF-8'); ?> 마감</p>
        <a href="<?php echo url('club'); ?>">자세히 보기<span aria-hidden="true"> →</span></a>
    </div>
</aside>
<?php endif; ?>
```

- [ ] **Step 5: `함께하기` 버튼 교체**

`index.php`에서

```php
                <a class="btn-cta btn-secondary" href="<?php echo url('committee'); ?>">청소년 동아리 신청</a>
```

를 다음으로 바꾼다.

```php
                <a class="btn-cta btn-secondary" href="<?php echo url('club'); ?>"><?php echo htmlspecialchars($club['name'], ENT_QUOTES, 'UTF-8'); ?> <?php echo $clubOpen ? '신청' : '소식'; ?></a>
```

- [ ] **Step 6: 알림 띠 스타일 추가**

`assets/css/style.css`의 `/* 바이브코딩동아리 (/club) */` 주석 바로 위에 추가한다.

```css
/* 홈 모집 알림 띠 */
.club-banner { background: var(--color-lime); border-bottom: var(--hairline); }
.club-banner-inner { display: flex; flex-wrap: wrap; align-items: center; justify-content: space-between; gap: .5rem 1.5rem; padding-top: 1rem; padding-bottom: 1rem; }
.club-banner p { margin: 0; }
.club-banner a { color: var(--color-ink); font-weight: 600; text-underline-offset: 4px; }

```

- [ ] **Step 7: 테스트·구문·로컬 응답 확인**

```bash
php tests/public-copy.test.php; echo "exit=$?"
php -l index.php
curl -sS http://localhost:8080/younglabor/ | grep -c 'class="club-banner"'
curl -sS http://localhost:8080/younglabor/ | grep -o '바이브코딩동아리 신청'
```

Expected: `exit=0`, 구문 오류 없음, `1`, `바이브코딩동아리 신청`

- [ ] **Step 8: 커밋**

```bash
git add index.php assets/css/style.css tests/public-copy.test.php
git commit -m "feat: announce club recruitment on home"
```

### Task 5: 옛 동아리 신청 종료

**Files:**

- Modify: `committee/index.php` (전체 교체)
- Modify: `api/committee.php` (전체 교체)
- Modify: `includes/Mailer.php` (`buildCommitteeEmailBody()` 삭제)
- Create: `tests/retired-club.test.php`

**Interfaces:** `/committee/` → 301 `url('club')`. `/api/committee.php` → 모든 요청에 410 JSON. 요청 본문을 읽거나 저장·발송하지 않는다.

- [ ] **Step 1: 실패하는 종료 테스트 작성**

`tests/retired-club.test.php`:

```php
<?php
$root = dirname(__DIR__);

function failRetired(string $message): void
{
    fwrite(STDERR, $message . "\n");
    exit(1);
}

$redirect = (string)@file_get_contents($root . '/committee/index.php');
if (strpos($redirect, "header('Location: ' . url('club'), true, 301);") === false) {
    failRetired('committee/index.php는 /club으로 301 이동해야 합니다.');
}
if (stripos($redirect, '<form') !== false) {
    failRetired('committee/index.php가 아직 신청 폼을 그립니다.');
}

$api = (string)@file_get_contents($root . '/api/committee.php');
if (strpos($api, 'http_response_code(410);') === false) {
    failRetired('api/committee.php는 410을 반환해야 합니다.');
}
foreach (['INSERT', 'committee_applications', 'Mailer', 'Database', 'php://input'] as $needle) {
    if (stripos($api, $needle) !== false) {
        failRetired("api/committee.php가 아직 {$needle}를 사용합니다.");
    }
}

$retired = ['청소년노동안전동아리', '노동안전동아리', '자격증준비', '참견위원회', '청소년 동아리 신청', 'buildCommitteeEmailBody', "url('committee')"];
$files = new RecursiveIteratorIterator(new RecursiveCallbackFilterIterator(
    new RecursiveDirectoryIterator($root, FilesystemIterator::SKIP_DOTS),
    static function (SplFileInfo $current) use ($root): bool {
        $relative = substr($current->getPathname(), strlen($root) + 1);
        if ($current->isDir()) {
            return preg_match('#^(?:\.|admin$|tests$|docs$|backup$|dbeditor$|data$|node_modules$)#', $relative) !== 1;
        }
        return preg_match('/\.(?:php|css|js)$/', $relative) === 1;
    }
));
foreach ($files as $file) {
    $relative = substr($file->getPathname(), strlen($root) + 1);
    $source = file_get_contents($file->getPathname());
    foreach ($retired as $needle) {
        if (strpos($source, $needle) !== false) {
            failRetired("{$relative}에 폐기 문구가 남아 있습니다: {$needle}");
        }
    }
}
```

`admin/`은 3단계(Task 11)에서 정리하므로 여기서는 검사하지 않는다.

- [ ] **Step 2: 실패 확인**

Run: `php tests/retired-club.test.php; echo "exit=$?"`

Expected: `committee/index.php는 /club으로 301 이동해야 합니다.`와 `exit=1`

- [ ] **Step 3: 옛 신청 주소를 301로 교체**

`committee/index.php` 전체를 다음으로 바꾼다. 인쇄물·QR로 퍼진 옛 주소를 살리기 위해 이 파일은 3단계 뒤에도 남긴다.

```php
<?php
/**
 * 옛 동아리 신청 주소 — 인쇄물·QR로 퍼진 주소를 살리기 위해 바이브코딩동아리 안내로 영구 이동한다.
 */
require_once __DIR__ . '/../config.php';
header('Location: ' . url('club'), true, 301);
exit;
```

- [ ] **Step 4: 옛 신청 API를 410으로 교체**

`api/committee.php` 전체를 다음으로 바꾼다.

```php
<?php
/**
 * 옛 동아리 신청 API — 2026-09 전환으로 신청을 받지 않는다.
 * 요청 내용을 읽거나 저장·발송하지 않고 410만 반환한다.
 */
header('Content-Type: application/json; charset=utf-8');
http_response_code(410);
echo json_encode(['success' => false, 'message' => '이 신청은 종료되었습니다.'], JSON_UNESCAPED_UNICODE);
```

- [ ] **Step 5: 메일 본문 함수 삭제**

`buildCommitteeEmailBody()`는 클래스의 마지막 메서드다(현재 246~326행: 빈 줄, 주석 3줄, 메서드). 줄 번호 대신 내용으로 지운다.

```bash
perl -0777 -pi -e 's/\n    \/\*\*\n     \* HTML 이메일 본문 생성 \(청소년노동안전동아리 신청용\)\n     \*\/\n    public static function buildCommitteeEmailBody\(.*?\n    \}\n(?=\}\n?\z)//s' includes/Mailer.php
grep -c 'buildCommitteeEmailBody' includes/Mailer.php
tail -4 includes/Mailer.php
php -r 'require "includes/Mailer.php"; var_dump(method_exists("Mailer", "buildContactEmailBody"), method_exists("Mailer", "buildCommitteeEmailBody"));'
```

Expected: `0`. 마지막 네 줄은 빈 줄, `        return base64_encode($html);`, `    }`, `}`이다. `bool(true)`와 `bool(false)`가 나온다. `grep` 결과가 `0`이 아니면 perl 패턴이 맞지 않은 것이므로 파일을 `git checkout includes/Mailer.php`로 되돌린 뒤 `grep -n 'buildCommitteeEmailBody\|^}' includes/Mailer.php`로 구조를 다시 확인한다.

- [ ] **Step 6: 테스트·구문·로컬 응답 확인**

```bash
php tests/retired-club.test.php; echo "exit=$?"
php -l committee/index.php && php -l api/committee.php && php -l includes/Mailer.php
curl -sS -o /dev/null -w '%{http_code} %{redirect_url}\n' http://localhost:8080/younglabor/committee/
curl -sS -o /dev/null -w '%{http_code}\n' -X POST -H 'Content-Type: application/json' -d '{}' http://localhost:8080/younglabor/api/committee.php
```

Expected: `exit=0`, 구문 오류 없음, `301 http://localhost:8080/younglabor/club`, `410`

- [ ] **Step 7: 커밋**

```bash
git add committee/index.php api/committee.php includes/Mailer.php tests/retired-club.test.php
git commit -m "feat: retire committee application"
```

### Task 6: HTTP·접근성 게이트와 런북

**Files:**

- Modify: `tests/public-http-smoke.sh` (전체 교체)
- Modify: `tests/public-accessibility.test.js` (전체 교체)
- Modify: `docs/runbooks/managed-content-deployment.md` (3·78·87행)

- [ ] **Step 1: 스모크 테스트 교체**

`tests/public-http-smoke.sh` 전체:

```bash
#!/usr/bin/env bash
set -euo pipefail
base_url="${SITE_BASE_URL:-http://localhost:8080/younglabor}"
body="$(mktemp /tmp/yl-public-smoke.XXXXXX)"
trap 'rm -f "$body"' EXIT
for path in / /about /activities /activity /press /resources /tools /club /admin/login.php; do
  code="$(curl -sS -o "$body" -w '%{http_code}' "$base_url$path")"
  test "$code" = 200
done
# 옛 동아리 신청 주소는 /club으로 영구 이동하고, 옛 신청 API는 아무것도 받지 않는다
test "$(curl -sS -o /dev/null -w '%{http_code} %{redirect_url}' "$base_url/committee/")" = "301 $base_url/club"
api_code="$(curl -sS -o "$body" -w '%{http_code}' -X POST -H 'Content-Type: application/json' -d '{}' "$base_url/api/committee.php")"
test "$api_code" = 410
home_bytes="$(curl -sS "$base_url/" | wc -c | tr -d ' ')"
css_bytes="$(curl -sS "$base_url/assets/css/style.css" | wc -c | tr -d ' ')"
test "$home_bytes" -lt 81920
test "$css_bytes" -lt 40960
```

- [ ] **Step 2: 접근성 테스트 교체**

`tests/public-accessibility.test.js` 전체:

```js
const assert = require('node:assert/strict');
const base = process.env.SITE_BASE_URL || 'http://localhost:8080/younglabor';

(async () => {
    for (const path of ['/', '/about', '/activities', '/activity', '/press', '/resources', '/tools', '/club']) {
        const response = await fetch(base + path);
        assert.equal(response.status, 200, `${path} must return 200`);
        const html = await response.text();
        assert.equal((html.match(/<main\b/g) || []).length, 1, `${path} must have one main`);
        assert.equal((html.match(/<h1\b/g) || []).length, 1, `${path} must have one h1`);
        assert.match(html, /href="#main-content"/, `${path} must have a skip link`);
        assert.match(html, /<link rel="canonical" href="https?:\/\//, `${path} must have canonical metadata`);
        assert.doesNotMatch(html, /pretendard|fonts\.googleapis\.com/i, `${path} must not load a remote font`);
        if (path === '/tools') {
            for (const value of ['https://safefactory.kr/', 'safefactory.kr', 'https://laborconsult.vercel.app/', 'laborconsult.vercel.app']) {
                assert.ok(html.includes(value), `tools page must include ${value}`);
            }
            assert.doesNotMatch(html, /<iframe\b/i, 'tools page must not embed services');
        }
        if (path === '/club') {
            assert.doesNotMatch(html, /<iframe\b/i, 'club page must not embed the application form');
            assert.ok(html.includes('지원으로 진행됩니다.'), 'club page must credit the foundation');
            if (html.includes('zealot-survey.vercel.app')) {
                assert.ok(html.includes('https://zealot-survey.vercel.app/RJXag60aMfMT'), 'club page must link the exact application form');
                assert.ok(html.includes('외부 서비스로 이동'), 'club page must announce the external link');
            }
        }
    }
})().catch((error) => { console.error(error); process.exit(1); });
```

- [ ] **Step 3: 런북 갱신**

`docs/runbooks/managed-content-deployment.md`에서 세 문장을 바꾼다.

3행

```markdown
대상 기능은 활동게시판, 언론보도, 자료실과 관리자 작성·수정·삭제다. 운영 데이터와 기존 관리자 로그인, 동아리 신청, 문의 API를 보존한다.
```

→

```markdown
대상 기능은 활동게시판, 언론보도, 자료실과 관리자 작성·수정·삭제다. 운영 데이터와 기존 관리자 로그인, 문의 API를 보존한다. 옛 동아리 신청은 2026-09 바이브코딩동아리 전환으로 종료됐다(`/committee` → `/club` 301, 옛 신청 API 410).
```

78행

```markdown
9. `/admin/login.php`, `/committee/`가 200인지 확인한다. 실제 데이터를 만들지 않는 범위에서 문의·동아리 API의 OPTIONS/검증 실패 응답도 확인한다.
```

→

```markdown
9. `/admin/login.php`, `/club`이 200이고 `/committee/`가 `/club`으로 301 이동하는지 확인한다. 실제 데이터를 만들지 않는 범위에서 문의 API의 OPTIONS/검증 실패 응답과 옛 동아리 신청 API의 410도 확인한다.
```

87행

```markdown
5. `/`, `/admin/login.php`, `/committee/`, 문의와 동아리 신청 기능을 확인한다.
```

→

```markdown
5. `/`, `/admin/login.php`, `/club`, `/committee/`(301)와 문의 기능을 확인한다.
```

- [ ] **Step 4: 전체 회귀 실행**

```bash
for t in tests/*.test.php; do php "$t" >/dev/null 2>&1 || echo "FAIL $t"; done; echo php-done
bash tests/apache-security-rules.test.sh && echo rules-ok
bash tests/public-http-smoke.sh && echo smoke-ok
node tests/public-accessibility.test.js && echo a11y-ok
node tests/admin-contact-xss.test.js && echo xss-ok
git ls-files -z '*.php' | xargs -0 -n1 php -l | grep -v '^No syntax errors' ; echo lint-done
```

Expected: `php-done`, `rules-ok`, `smoke-ok`, `a11y-ok`, `xss-ok`, `lint-done`만 출력(FAIL·구문 오류 없음). `apache-security-rules.test.sh`는 `rg`(ripgrep)가 필요하다.

- [ ] **Step 5: 커밋**

```bash
git add tests/public-http-smoke.sh tests/public-accessibility.test.js docs/runbooks/managed-content-deployment.md
git commit -m "test: cover club page and retired committee routes"
```

### Task 7: 1단계 배포 (사용자 승인 필요)

**Files:** 없음

신청서는 센터가 직접 만든 별도 서베이 페이지다(2026-09-30 확인). 사이트는 신청서를 붙이지 않고 링크만 하므로, 신청서 문구 수정(O1)은 서베이 쪽 작업이며 사이트 배포 조건이 아니다.

- [ ] **Step 0: 신청 페이지 링크 확인**

```bash
curl -sS -o /dev/null -w '%{http_code}\n' https://zealot-survey.vercel.app/RJXag60aMfMT
```

Expected: `200`. 신청 페이지 문구(마감일·러버블·재단 표기)가 사이트와 맞는지는 서베이 쪽에서 O1로 맞춘다.

- [ ] **Step 1: 푸시와 PR (승인 후)**

```bash
git push -u origin feature/vibecoding-club
gh pr create --base main --title "feat: 바이브코딩동아리 모집 전환 (2차 개편 1단계)" --body "$(cat <<'EOF'
## 변경
- `/club` 바이브코딩동아리 소개·모집 페이지, 홈 알림 띠(10-15 마감 뒤 자동 종료)
- `/committee/` → `/club` 301, 옛 신청 API 410, 동아리 신청 메일 함수 제거
- 재단 크레딧 `공익단체 인큐베이팅 지원사업` (연도 제거)

## 검증
- PHP 테스트 전체, Apache 규칙, HTTP 스모크, 접근성, XSS 회귀, PHP 구문 검사

설계: docs/superpowers/specs/2026-09-27-public-site-phase2-design.md

🤖 Generated with [Claude Code](https://claude.com/claude-code)
EOF
)"
```

- [ ] **Step 2: 리뷰 반영**

CodeRabbit 리뷰가 달리면 지적을 코드와 대조해 유효한 것만 고치고, Task 6 Step 4를 다시 실행한 뒤 커밋·푸시한다.

- [ ] **Step 3: 머지 (승인 후)**

```bash
gh pr merge --merge
gh run list --workflow deploy.yml --limit 1
```

Expected: 배포 워크플로 `completed success`. 워크플로 로그의 배포 응답에 copy error가 없어야 한다.

- [ ] **Step 4: 운영 확인**

```bash
SITE_BASE_URL=https://younglabor.kr bash tests/public-http-smoke.sh && echo PROD-SMOKE-OK
SITE_BASE_URL=https://younglabor.kr CLUB_EXPECT_STATE=open node tests/public-accessibility.test.js && echo PROD-A11Y-OK
curl -sS -A check-bot https://younglabor.kr/ | grep -c '2025 공익단체'
curl -sS -A check-bot https://younglabor.kr/ | grep -c 'class="club-banner"'
curl -sS -A check-bot https://younglabor.kr/ | grep -o '바이브코딩동아리 신청</a>'
curl -sSI -A check-bot https://younglabor.kr/club | grep -i '^cf-cache-status'
```

Expected: `PROD-SMOKE-OK`, `PROD-A11Y-OK`, `0`, `1`, `바이브코딩동아리 신청</a>`, `cf-cache-status: DYNAMIC`(HTML이 엣지에 캐시되면 10-16 0시 전환이 늦어진다).

사람이 확인할 것:
- 실제 휴대전화로 홈 띠 → `/club` → 신청서가 같은 탭에서 열리는지 한 번 확인한다.
- iPhone VoiceOver에서 띠의 링크가 "바이브코딩동아리 모집 자세히 보기"로 읽히는지 확인한다.

실패했을 때:
- smoke가 `/club` 404로 실패하면 되돌리지 말고 배포 워크플로를 다시 실행한다(`gh run rerun <run-id>`). 배포는 같은 결과를 다시 만든다.
- 그 밖의 실패는 수정 배포로 대응한다. 전환 이전 revision으로 되돌리지 않는다(런북 `## rollback` 첫 문단).

- [ ] **Step 5: 마감 시점 확인 (2026-10-16 00:00 KST 직후)**

- zealot-survey 신청서를 직접 닫는다. 신청서에는 자동 마감이 없다.
- 운영 확인:

```bash
SITE_BASE_URL=https://younglabor.kr CLUB_EXPECT_STATE=closed node tests/public-accessibility.test.js && echo CLOSED-OK
curl -sS -A check-bot https://younglabor.kr/ | grep -c 'class="club-banner"'
curl -sS -A check-bot https://younglabor.kr/ | grep -o '바이브코딩동아리 소식</a>'
```

Expected: `CLOSED-OK`, `0`, `바이브코딩동아리 소식</a>`

---

## 2단계 — 공유 이미지·검색 노출 (10-05 ~ 10-10)

### Task 8: 공유 이미지 (`og:image`)

**Files:**

- Create (BF 저장소, git-ignored 산출물): `content/out/2026-10-05-사이트-공유이미지/{brief.md, visual/og-banner-01.yaml, visual/og-banner-02.yaml}`
- Create: `assets/images/og-default.png`, `assets/images/og-club.png`
- Modify: `includes/header.php`, `club.php`
- Modify: `tests/public-layout.test.php` (끝에 추가)

**Interfaces:** 모든 페이지가 `og:image`를 낸다. 기본값은 `og-default.png`이고, 페이지가 `$pageImage`를 정하면 그 값을 쓴다. `/club`은 `og-club.png`를 쓴다.

- [ ] **Step 1: 실패하는 테스트 추가**

`tests/public-layout.test.php` 끝에 추가한다.

```php
foreach (['property="og:image"', 'og:image:width', 'og:image:height', 'twitter:card', '$pageImage'] as $needle) {
    if (strpos($header, $needle) === false) exit(1);
}
foreach (['og-default.png', 'og-club.png'] as $image) {
    $size = @getimagesize(__DIR__ . '/../assets/images/' . $image);
    if ($size === false || $size[0] !== 1200 || $size[1] !== 630) exit(1);
}
if (strpos(file_get_contents(__DIR__ . '/../club.php'), "\$pageImage = url('assets/images/og-club.png');") === false) exit(1);
```

- [ ] **Step 2: 실패 확인**

Run: `php tests/public-layout.test.php; echo "exit=$?"`

Expected: `exit=1`

- [ ] **Step 3: BF 저장소에서 이미지 원본 작성**

```bash
BF=/Users/zealnutkim/DEV/BeautifulFundProjectBoard-fresh
OUT="$BF/content/out/2026-10-05-사이트-공유이미지"
mkdir -p "$OUT/visual"
cat > "$OUT/brief.md" <<'EOF'
---
id: 2026-10-05-사이트-공유이미지
type: visual
audience: 청소년·시민
channel:
- 누리집 공유 미리보기
unit: ai-club
templates:
- og-banner
purpose: younglabor.kr 공유 미리보기 이미지 (사이트 기본, 바이브코딩동아리)
key_messages:
- 제조업 청년노동자의 안전과 노동권
- 바이브코딩동아리 — AI로 나만의 앱을 만드는 3개월
must_include:
- credit_sentences.material
must_exclude:
- 대표 연락처
- 모집 마감일 (공유 이미지는 마감 뒤에도 남는다)
sources:
- facts: units.ai-club
---
EOF
cat > "$OUT/visual/og-banner-01.yaml" <<'EOF'
template: og-banner
slots:
  eyebrow: "younglabor.kr"
  title: "제조업 청년노동자의 안전과 노동권"
  subtitle: "학교에서 일터로 향하는 청년 곁에서 안전을 만듭니다"
  org: "청년노동자인권센터"
  credit: "이 홍보물은 아름다운재단 지원으로 제작되었습니다."
EOF
cat > "$OUT/visual/og-banner-02.yaml" <<'EOF'
template: og-banner
slots:
  eyebrow: "반도체고 청소년 동아리"
  title: "바이브코딩동아리"
  subtitle: "나도 개발자! AI로 나만의 앱을 만드는 3개월"
  org: "청년노동자인권센터"
  credit: "이 홍보물은 아름다운재단 지원으로 제작되었습니다."
EOF
```

- [ ] **Step 4: 렌더·검사·복사**

```bash
cd "$BF"
content/.venv/bin/python3 content/tools/promo.py fill "$OUT/visual/og-banner-01.yaml" "$OUT/visual/og-banner-02.yaml"
content/.venv/bin/python3 content/tools/promo.py render --scale 1 --out "$OUT/final" "$OUT/visual/og-banner-01.html" "$OUT/visual/og-banner-02.html"
content/.venv/bin/python3 content/tools/promo.py check "$OUT" | tail -2
cd /Applications/XAMPP/xamppfiles/htdocs/younglabor
cp "$OUT/final/og-banner-01.png" assets/images/og-default.png
cp "$OUT/final/og-banner-02.png" assets/images/og-club.png
sips -g pixelWidth -g pixelHeight assets/images/og-default.png assets/images/og-club.png | grep pixel
```

Expected: `render … (1200x630 @1.0x)` 두 줄, check `결과: **PASS**`, 두 파일 모두 `pixelWidth: 1200`·`pixelHeight: 630`. 두 PNG를 열어 글자 잘림이 없는지 눈으로 확인한다. og-banner는 kb 브랜드 색(하늘·노랑)이라 사이트의 라임 색과 다르다(설계 §9.2에서 수용).

- [ ] **Step 5: 헤더에 `og:image` 추가**

`includes/header.php`에서

```php
if (!isset($pageUrl)) $pageUrl = url($currentPage === 'home' ? '' : $currentPage);
```

바로 아래에 추가한다.

```php
if (!isset($pageImage)) $pageImage = url('assets/images/og-default.png');
```

그리고

```php
    <meta property="og:url" content="<?php echo htmlspecialchars($pageUrl, ENT_QUOTES, 'UTF-8'); ?>">
```

바로 아래에 추가한다.

```php
    <meta property="og:image" content="<?php echo htmlspecialchars($pageImage, ENT_QUOTES, 'UTF-8'); ?>">
    <meta property="og:image:width" content="1200">
    <meta property="og:image:height" content="630">
    <meta name="twitter:card" content="summary_large_image">
```

- [ ] **Step 6: `/club` 전용 이미지 지정**

`club.php`에서

```php
$pageUrl = url('club');
```

바로 아래에 추가한다.

```php
$pageImage = url('assets/images/og-club.png');
```

- [ ] **Step 7: 테스트·구문·로컬 확인**

```bash
php tests/public-layout.test.php; echo "exit=$?"
php tests/club-page.test.php; echo "exit=$?"
php -l includes/header.php && php -l club.php
curl -sS http://localhost:8080/younglabor/club | grep -o '<meta property="og:image" content="[^"]*"'
curl -sS http://localhost:8080/younglabor/ | grep -o '<meta property="og:image" content="[^"]*"'
```

Expected: 두 테스트 `exit=0`, 구문 오류 없음, `/club`은 `…/assets/images/og-club.png`, 홈은 `…/assets/images/og-default.png`

- [ ] **Step 8: 커밋**

```bash
git add includes/header.php club.php tests/public-layout.test.php assets/images/og-default.png assets/images/og-club.png
git commit -m "feat: add social share images"
```

공유 이미지는 1년 캐시(`.htaccess` `immutable`)이므로, 나중에 그림을 바꾸면 파일 이름을 바꾸고 참조를 고친다.

### Task 9: `robots.txt`와 `sitemap.xml`

**Files:**

- Create: `includes/Sitemap.php`, `sitemap.php`, `robots.txt`, `tests/sitemap.test.php`
- Modify: `.htaccess` (관리형 콘텐츠 경로 규칙 끝)
- Modify: `tests/public-http-smoke.sh` (전체 교체)

**Interfaces:**

- `sitemapStaticPaths(): array` — 정적 공개 경로. 게시판 디렉터리(`activity/`·`press/`·`resources/`)는 끝 슬래시를 붙인다. 슬래시 없는 주소는 Apache(mod_dir)가 301로 보내므로 sitemap에 넣지 않는다
- `sitemapEntries(callable $repositoryFactory, callable $urlFor): array` — `[['loc' => string, 'lastmod' => ?string], …]`. 게시물은 `listPublished()`로만 읽는다(draft 제외). 조회에 실패하면 정적 경로만 돌려준다
- `renderSitemap(array $entries): string` — sitemaps.org XML

- [ ] **Step 1: 실패하는 테스트 작성**

`tests/sitemap.test.php`:

```php
<?php
require_once __DIR__ . '/../includes/Sitemap.php';

function assertSitemap(bool $condition, string $message): void
{
    if (!$condition) {
        fwrite(STDERR, $message . "\n");
        exit(1);
    }
}

$urlFor = static function (string $path): string {
    return 'https://younglabor.kr' . ($path === '' ? '' : '/' . $path);
};

$repository = new class {
    public array $calls = [];
    public function listPublished(string $type, int $limit, int $offset): array
    {
        $this->calls[] = [$type, $limit, $offset];
        return $type === 'activity'
            ? [['slug' => 'school-visits', 'updated_at' => '2026-10-06 10:00:00']]
            : [['slug' => 'teacher-leaflet', 'updated_at' => '2026-10-07 09:30:00']];
    }
};
$entries = sitemapEntries(static fn () => $repository, $urlFor);
$locs = array_column($entries, 'loc');
assertSitemap(in_array('https://younglabor.kr/club', $locs, true), 'club 페이지가 sitemap에 없습니다.');
assertSitemap(in_array('https://younglabor.kr/activity/school-visits', $locs, true), '공개 활동 글이 없습니다.');
assertSitemap(in_array('https://younglabor.kr/resources/teacher-leaflet', $locs, true), '공개 자료가 없습니다.');
foreach ($locs as $loc) {
    assertSitemap(strpos($loc, 'committee') === false, 'committee 경로가 sitemap에 있습니다.');
}
assertSitemap($repository->calls === [['activity', 100, 0], ['resource', 100, 0]], '공개 목록 조회 방식이 예상과 다릅니다.');

$fallback = sitemapEntries(static function () {
    throw new RuntimeException('database unavailable');
}, $urlFor);
assertSitemap(array_column($fallback, 'loc') === array_map($urlFor, sitemapStaticPaths()), 'DB 장애 때는 정적 페이지만 있어야 합니다.');

$xml = renderSitemap([
    ['loc' => 'https://younglabor.kr/a?x=1&y=2', 'lastmod' => '2026-10-06'],
    ['loc' => 'https://younglabor.kr/b', 'lastmod' => 'not-a-date'],
]);
assertSitemap(strpos($xml, '<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">') !== false, 'urlset 네임스페이스가 없습니다.');
assertSitemap(strpos($xml, '<loc>https://younglabor.kr/a?x=1&amp;y=2</loc><lastmod>2026-10-06</lastmod>') !== false, 'loc 이스케이프나 lastmod가 틀립니다.');
assertSitemap(strpos($xml, 'not-a-date') === false, '형식이 틀린 lastmod는 빼야 합니다.');

$rules = file_get_contents(__DIR__ . '/../.htaccess');
assertSitemap(strpos($rules, 'RewriteRule ^sitemap\.xml$ sitemap.php [L]') !== false, 'sitemap.xml 경로 규칙이 없습니다.');
$robots = (string)@file_get_contents(__DIR__ . '/../robots.txt');
foreach (['User-agent: *', 'Disallow: /admin/', 'Sitemap: https://younglabor.kr/sitemap.xml'] as $line) {
    assertSitemap(strpos($robots, $line) !== false, "robots.txt에 없음: {$line}");
}
```

- [ ] **Step 2: 실패 확인**

Run: `php tests/sitemap.test.php; echo "exit=$?"`

Expected: `includes/Sitemap.php`를 찾지 못하는 치명적 오류와 `exit=255`

- [ ] **Step 3: `includes/Sitemap.php` 작성**

```php
<?php
/**
 * 공개 sitemap — 정적 페이지는 항상, 게시물은 공개 목록 조회에 성공했을 때만 넣는다.
 */

function sitemapStaticPaths(): array
{
    return ['', 'about', 'activities', 'activity/', 'press/', 'resources/', 'tools', 'club'];
}

function sitemapEntries(callable $repositoryFactory, callable $urlFor): array
{
    $entries = [];
    foreach (sitemapStaticPaths() as $path) {
        $entries[] = ['loc' => $urlFor($path), 'lastmod' => null];
    }
    try {
        $repository = $repositoryFactory();
        foreach (['activity' => 'activity/', 'resource' => 'resources/'] as $type => $prefix) {
            for ($offset = 0; ; $offset += 100) {
                $rows = $repository->listPublished($type, 100, $offset);
                foreach ($rows as $row) {
                    $entries[] = [
                        'loc' => $urlFor($prefix . $row['slug']),
                        'lastmod' => isset($row['updated_at']) ? substr((string)$row['updated_at'], 0, 10) : null,
                    ];
                }
                if (count($rows) < 100) {
                    break;
                }
            }
        }
    } catch (Throwable $error) {
        error_log('Sitemap content unavailable');
    }
    return $entries;
}

function renderSitemap(array $entries): string
{
    $xml = '<?xml version="1.0" encoding="UTF-8"?>' . "\n"
        . '<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">' . "\n";
    foreach ($entries as $entry) {
        $xml .= '  <url><loc>' . htmlspecialchars($entry['loc'], ENT_XML1 | ENT_QUOTES, 'UTF-8') . '</loc>';
        if ($entry['lastmod'] !== null && preg_match('/^\d{4}-\d{2}-\d{2}$/', $entry['lastmod'])) {
            $xml .= '<lastmod>' . $entry['lastmod'] . '</lastmod>';
        }
        $xml .= "</url>\n";
    }
    return $xml . "</urlset>\n";
}
```

- [ ] **Step 4: `sitemap.php`, `robots.txt`, `.htaccess` 작성**

`sitemap.php`:

```php
<?php
require_once __DIR__ . '/config.php';
require_once __DIR__ . '/includes/Database.php';
require_once __DIR__ . '/includes/ContentRepository.php';
require_once __DIR__ . '/includes/Sitemap.php';

$entries = sitemapEntries(
    static function () {
        return new ContentRepository(Database::getInstance()->getConnection());
    },
    static function (string $path): string {
        return url($path);
    }
);
header('Content-Type: application/xml; charset=utf-8');
echo renderSitemap($entries);
```

`robots.txt`:

```text
User-agent: *
Disallow: /admin/
Sitemap: https://younglabor.kr/sitemap.xml
```

`.htaccess`에서

```apache
RewriteRule ^downloads/([0-9]+)/?$ download.php?id=$1 [L,QSA]
```

바로 아래에 추가한다.

```apache
RewriteRule ^sitemap\.xml$ sitemap.php [L]
```

- [ ] **Step 5: 스모크 테스트에 검색 노출 검사 추가**

1단계에서 `tests/public-http-smoke.sh`는 `expect()` 도우미와 봇 UA를 쓰는 형태로 바뀌었다(1단계 실행 기록 참조). 전체를 교체하지 말고, `grep -q '"success":false' "$body" || …` 줄 바로 아래에 다음을 넣는다.

```bash
# 검색 노출
expect "GET /robots.txt" "$(curl -sS -A "$ua" -o /dev/null -w '%{http_code}' "$base_url/robots.txt")" 200
expect "GET /sitemap.xml" "$(curl -sS -A "$ua" -o "$body" -w '%{http_code}' "$base_url/sitemap.xml")" 200
grep -q '<urlset' "$body" || { echo 'FAIL sitemap.xml: no urlset' >&2; exit 1; }
grep -q "<loc>$base_url/club</loc>" "$body" || { echo 'FAIL sitemap.xml: /club missing' >&2; exit 1; }
if grep -q 'committee' "$body"; then echo 'FAIL sitemap.xml: lists a retired committee path' >&2; exit 1; fi
```

`robots.txt`·`sitemap.php`·`includes/Sitemap.php`에는 주석으로도 `committee`를 쓰지 않는다. `tests/retired-club.test.php`가 공개 파일의 이 문자열을 막는다.

- [ ] **Step 6: 테스트·구문·로컬 확인**

XAMPP가 꺼져 있으면 1단계와 같이 실제 Apache 도우미(`yl-http.sh`, 1단계 실행 기록 참조)로 HTTP 검사를 돌린다.

```bash
php tests/sitemap.test.php; echo "exit=$?"
php tests/public-content-pages.test.php; echo "exit=$?"
php tests/retired-club.test.php; echo "exit=$?"
bash -c 'command -v rg' >/dev/null && bash tests/apache-security-rules.test.sh && echo rules-ok
php -l includes/Sitemap.php && php -l sitemap.php
bash tests/public-http-smoke.sh && echo smoke-ok
curl -sS http://localhost:8080/younglabor/sitemap.xml | head -4
```

Expected: 세 테스트 `exit=0`, `rules-ok`(ripgrep이 설치된 경우), 구문 오류 없음, `smoke-ok`, XML 머리와 첫 `<url>` 줄

- [ ] **Step 7: 커밋**

```bash
git add includes/Sitemap.php sitemap.php robots.txt .htaccess tests/sitemap.test.php tests/public-http-smoke.sh
git commit -m "feat: publish robots and sitemap"
```

### Task 10: 2단계 배포 (사용자 승인 필요)

**Files:** 없음

- [ ] **Step 1: 전체 회귀**

Task 6 Step 4의 명령을 그대로 다시 실행한다.

```bash
for t in tests/*.test.php; do php "$t" >/dev/null 2>&1 || echo "FAIL $t"; done; echo php-done
bash tests/apache-security-rules.test.sh && echo rules-ok
bash tests/public-http-smoke.sh && echo smoke-ok
node tests/public-accessibility.test.js && echo a11y-ok
node tests/admin-contact-xss.test.js && echo xss-ok
git ls-files -z '*.php' | xargs -0 -n1 php -l | grep -v '^No syntax errors' ; echo lint-done
```

Expected: FAIL과 구문 오류 없이 `php-done`, `rules-ok`, `smoke-ok`, `a11y-ok`, `xss-ok`, `lint-done`

- [ ] **Step 2: 푸시·PR·머지 (승인 후)**

```bash
git push
gh pr create --base main --title "feat: 공유 이미지·robots·sitemap (2차 개편 2단계)" --body "$(cat <<'EOF'
## 변경
- 모든 페이지 og:image(기본)와 /club 전용 공유 이미지(1200×630)
- robots.txt, sitemap.xml (공개 글만, DB 장애 시 정적 페이지만)

## 검증
- PHP 테스트 전체, Apache 규칙, HTTP 스모크(robots·sitemap 포함), 접근성, 구문 검사

🤖 Generated with [Claude Code](https://claude.com/claude-code)
EOF
)"
gh pr merge --merge
gh run list --workflow deploy.yml --limit 1
```

1단계 머지 때 원격 브랜치가 자동 삭제됐으면 `git push` 대신 `git push -u origin feature/vibecoding-club`로 다시 올린다. 새 PR의 diff에는 Task 8·9 커밋만 나타난다.

- [ ] **Step 3: 운영 확인**

```bash
SITE_BASE_URL=https://younglabor.kr bash tests/public-http-smoke.sh && echo PROD-SMOKE-OK
SITE_BASE_URL=https://younglabor.kr node tests/public-accessibility.test.js && echo PROD-A11Y-OK
curl -sS https://younglabor.kr/club | grep -o '<meta property="og:image" content="[^"]*"'
```

Expected: 두 OK와 `https://younglabor.kr/assets/images/og-club.png`. 카카오톡으로 `https://younglabor.kr/club`을 보내 미리보기 이미지를 눈으로 확인한다.

---

## 3단계 — 옛 동아리 관리 기능 제거 (O4 파기 완료 뒤, 목표 10-12)

### Task 11: 관리자 동아리 코드와 옛 API 제거

**Files:**

- Modify: `admin/helpers.php`, `admin/dashboard.php`
- Delete: `admin/committee.php`, `admin/api/committee-action.php`, `api/committee.php`
- Create: `database/migrations/20261012_drop_committee_applications.sql`
- Create: `tests/admin-committee-removed.test.php`
- Modify: `tests/retired-club.test.php` (API 검사), `tests/public-http-smoke.sh` (API 기대값), `docs/runbooks/managed-content-deployment.md` (3·54·78행)

선행 조건: 운영 작업 O4의 파기 기록이 있고, 운영에서 `SELECT COUNT(*) FROM committee_applications;`가 `0`이다. 파일 이름의 날짜는 실제 실행일과 다르면 그 날짜로 바꾸고, 테스트의 파일 이름도 같이 바꾼다.

- [ ] **Step 1: 실패하는 제거 테스트 작성**

`tests/admin-committee-removed.test.php`:

```php
<?php
$root = dirname(__DIR__);

function assertRemoved(bool $condition, string $message): void
{
    if (!$condition) {
        fwrite(STDERR, $message . "\n");
        exit(1);
    }
}

foreach (['admin/committee.php', 'admin/api/committee-action.php', 'api/committee.php'] as $file) {
    assertRemoved(!file_exists($root . '/' . $file), "{$file}가 남아 있습니다.");
}
assertRemoved(is_file($root . '/committee/index.php'), '옛 주소 301용 committee/index.php는 남겨야 합니다.');
foreach (array_merge(glob($root . '/admin/*.php'), glob($root . '/admin/api/*.php')) as $file) {
    $source = file_get_contents($file);
    foreach (['committee_applications', 'getPendingCommitteeCount', '동아리 신청', '$recentApps', '$totalApps', '$pendingApps'] as $needle) {
        assertRemoved(strpos($source, $needle) === false, basename($file) . "에 {$needle}가 남아 있습니다.");
    }
}
$migration = $root . '/database/migrations/20261012_drop_committee_applications.sql';
assertRemoved(is_file($migration), '테이블 삭제 마이그레이션이 없습니다.');
assertRemoved(strpos(file_get_contents($migration), 'DROP TABLE IF EXISTS committee_applications;') !== false, '마이그레이션 내용이 다릅니다.');
```

- [ ] **Step 2: 실패 확인**

Run: `php tests/admin-committee-removed.test.php; echo "exit=$?"`

Expected: `admin/committee.php가 남아 있습니다.`와 `exit=1`

- [ ] **Step 3: 파일 삭제와 마이그레이션 작성**

```bash
git rm admin/committee.php admin/api/committee-action.php api/committee.php
```

`database/migrations/20261012_drop_committee_applications.sql`:

```sql
-- 옛 동아리 신청 테이블 삭제 (2026-09 바이브코딩동아리 전환, 신청 데이터 파기 완료 후)
-- 실행 전 확인: SELECT COUNT(*) FROM committee_applications; 결과가 0이어야 한다.
-- 되돌리기: 실행 직전에 보관한 SHOW CREATE TABLE committee_applications 출력(스키마만)으로 빈 테이블을 다시 만든다.
DROP TABLE IF EXISTS committee_applications;
```

- [ ] **Step 4: 관리자 헬퍼 정리**

`admin/helpers.php`에서 다음 함수와 뒤따르는 빈 줄을 지운다.

```php
function getPendingCommitteeCount(): int {
    try {
        $db = Database::getInstance()->getConnection();
        $stmt = $db->query("SELECT COUNT(*) FROM committee_applications WHERE status = 'pending'");
        return (int)$stmt->fetchColumn();
    } catch (\Throwable $e) {
        return 0;
    }
}

```

`adminHeader()`에서 다음 줄을 지운다.

```php
    $pendingCount = getPendingCommitteeCount();
```

사이드바에서 다음 링크 블록을 지운다.

```php
            <a href="<?php echo $baseUrl; ?>/committee" class="<?php echo $currentPage === 'committee' ? 'active' : ''; ?>">
                <span class="icon">&#128101;</span> 동아리 신청
                <?php if ($pendingCount > 0): ?>
                    <span class="nav-badge"><?php echo $pendingCount; ?></span>
                <?php endif; ?>
            </a>
```

- [ ] **Step 5: 대시보드 정리**

`admin/dashboard.php`에서 다음 줄들을 지운다.

```php
$pendingApps = $db->query("SELECT COUNT(*) FROM committee_applications WHERE status = 'pending'")->fetchColumn() ?: 0;
```

```php
$totalApps = $db->query("SELECT COUNT(*) FROM committee_applications")->fetchColumn() ?: 0;
```

```php
// 최근 신청 5건
$recentApps = $db->query("SELECT id, name, school, grade, status, created_at FROM committee_applications ORDER BY created_at DESC LIMIT 5")->fetchAll();

```

통계 카드 두 개를 지운다.

```php
    <div class="stat-card">
        <span class="stat-icon">&#128221;</span>
        <div class="stat-label">대기 중 신청</div>
        <div class="stat-value" style="color:<?php echo $pendingApps > 0 ? '#f59e0b' : '#22c55e'; ?>"><?php echo number_format($pendingApps); ?></div>
    </div>
```

```php
    <div class="stat-card">
        <span class="stat-icon">&#128101;</span>
        <div class="stat-label">전체 신청</div>
        <div class="stat-value"><?php echo number_format($totalApps); ?></div>
    </div>
```

최근 항목 영역의 여는 줄과 첫 카드(최근 동아리 신청)를 지우고 한 열로 바꾼다. 다음 블록을

```php
<div style="display:grid;grid-template-columns:1fr 1fr;gap:20px">
    <div class="card">
        <div class="card-title" style="display:flex;justify-content:space-between;align-items:center">
            최근 동아리 신청
            <a href="<?php echo url('admin/committee'); ?>" style="font-size:13px;color:var(--color-primary-dark);text-decoration:none">전체보기 &rarr;</a>
        </div>
        <?php if (empty($recentApps)): ?>
            <p style="color:#94a3b8;font-size:14px;padding:20px 0;text-align:center">아직 신청 내역이 없습니다.</p>
        <?php else: ?>
            <table class="data-table">
                <thead><tr><th>이름</th><th>학교</th><th>상태</th><th>날짜</th></tr></thead>
                <tbody>
                <?php foreach ($recentApps as $app): ?>
                    <tr>
                        <td><?php echo e($app['name']); ?></td>
                        <td><?php echo e($app['school']); ?></td>
                        <td><?php echo statusBadge($app['status']); ?></td>
                        <td style="font-size:13px;color:#64748b"><?php echo date('m/d', strtotime($app['created_at'])); ?></td>
                    </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
        <?php endif; ?>
    </div>

    <div class="card">
```

다음으로 바꾼다.

```php
<div style="display:grid;grid-template-columns:1fr;gap:20px">
    <div class="card">
```

- [ ] **Step 6: 종료 테스트·스모크·런북 갱신**

`tests/retired-club.test.php`에서 `$api = …`부터 `foreach (['INSERT', …` 블록 끝까지를 다음으로 바꾼다.

```php
if (file_exists($root . '/api/committee.php')) {
    failRetired('api/committee.php는 3단계에서 삭제했습니다.');
}
```

`tests/public-http-smoke.sh`에서 다음 두 줄을

```bash
expect "POST /api/committee.php" "$(curl -sS -A "$ua" -o "$body" -w '%{http_code}' -X POST -H 'Content-Type: application/json' -d '{}' "$base_url/api/committee.php")" 410
grep -q '"success":false' "$body" || { echo 'FAIL POST /api/committee.php: body is not the retired stub' >&2; exit 1; }
```

다음으로 바꾼다. 운영 prune이 꺼져 있으면 수동 삭제(Task 12) 전까지 410이 남는다.

```bash
api_code="$(curl -sS -A "$ua" -o /dev/null -w '%{http_code}' -X POST -H 'Content-Type: application/json' -d '{}' "$base_url/api/committee.php")"
case "$api_code" in 404|410) ;; *) echo "FAIL POST /api/committee.php: expected 404 or 410 got [$api_code]" >&2; exit 1 ;; esac
```

`docs/runbooks/managed-content-deployment.md`에서 네 곳을 바꾼다.

- 3행: `(`/committee` → `/club` 301, 옛 신청 API 410)`를 `(`/committee` → `/club` 301, 옛 신청 API·관리 화면·테이블은 2026-10 삭제)`로 바꾼다.
- 54행: `` 기존 `admin_user`, `committee_applications`, `inquiries`, `younglabor_visitor_log`는 변경하지 않는다. ``를 `` 기존 `admin_user`, `inquiries`, `younglabor_visitor_log`는 변경하지 않는다(`committee_applications`는 2026-10 파기 후 삭제). ``로 바꾼다.
- 배포 확인 9번: `옛 동아리 신청 API가 410인지`를 `옛 동아리 신청 API가 404(삭제됨)인지`로 바꾼다.
- `## rollback` 첫 문단: `전환 파일 5개(`club.php`, `includes/SiteContent.php`, `committee/index.php`, `api/committee.php`, `assets/css/style.css`)를 전환 이후 버전으로 다시 올리고`를 `전환 파일 4개(`club.php`, `includes/SiteContent.php`, `committee/index.php`, `assets/css/style.css`)를 전환 이후 버전으로 다시 올리고 되살아난 `api/committee.php`를 지운 뒤`로 바꾼다.

- [ ] **Step 7: 테스트·구문 확인**

```bash
php tests/admin-committee-removed.test.php; echo "exit=$?"
php tests/retired-club.test.php; echo "exit=$?"
php -l admin/helpers.php && php -l admin/dashboard.php
for t in tests/*.test.php; do php "$t" >/dev/null 2>&1 || echo "FAIL $t"; done; echo php-done
bash tests/public-http-smoke.sh && echo smoke-ok
```

Expected: 두 테스트 `exit=0`, 구문 오류 없음, `php-done`(FAIL 없음), `smoke-ok`. 로컬에서 `/younglabor/admin/`에 로그인해 대시보드와 사이드바가 오류 없이 뜨는지 확인한다.

- [ ] **Step 8: 커밋**

```bash
git add admin/helpers.php admin/dashboard.php database/migrations/20261012_drop_committee_applications.sql tests/admin-committee-removed.test.php tests/retired-club.test.php tests/public-http-smoke.sh docs/runbooks/managed-content-deployment.md
git commit -m "refactor: remove retired committee administration"
```

(`git rm`으로 지운 세 파일은 이미 스테이징돼 있다.)

### Task 12: 3단계 운영 반영 (사람이 수행, 사용자 승인 필요)

**Files:** 없음 (운영 서버·DB)

- [ ] **Step 1: 스키마 보관**

호스팅 DB 콘솔에서 실행하고, 출력(스키마만, 행 없음)을 저장소 밖 운영 기록에 저장한다.

```sql
SELECT COUNT(*) FROM committee_applications;
SHOW CREATE TABLE committee_applications;
```

Expected: 첫 결과 `0`. 0이 아니면 멈추고 O4를 다시 확인한다.

- [ ] **Step 2: 코드 배포**

```bash
git push -u origin feature/vibecoding-club
gh pr create --base main --title "refactor: 옛 동아리 관리 기능 제거 (2차 개편 3단계)" --body "$(cat <<'EOF'
## 변경
- 관리자 동아리 신청 메뉴·대시보드 집계·관리 화면 삭제
- 옛 신청 API 삭제 (`/committee/` → `/club` 301은 유지)
- `committee_applications` 삭제 마이그레이션 (신청 데이터 파기 완료 후)

## 운영 반영 순서
스키마 보관 → 이 PR 배포 → 마이그레이션 실행 → prune이 꺼져 있으면 운영 옛 파일 수동 삭제

🤖 Generated with [Claude Code](https://claude.com/claude-code)
EOF
)"
gh pr merge --merge
gh run list --workflow deploy.yml --limit 1
```

Expected: 배포 워크플로 `completed success`. 배포 뒤 운영 관리자에 로그인해 대시보드와 사이드바가 오류 없이 뜨는지 확인한다.

- [ ] **Step 3: 테이블 삭제**

DB 콘솔에서 `database/migrations/20261012_drop_committee_applications.sql`을 실행한다. 이어서 `SHOW TABLES LIKE 'committee_applications';` 결과가 비어 있는지 확인한다.

- [ ] **Step 4: 운영의 옛 파일 삭제**

운영 `.env`의 `DEPLOY_PRUNE_REMOVED`가 `true`가 아니면, 원본 서버 SSH에서 문서 루트 기준으로 지운다.

```sh
rm -f admin/committee.php admin/api/committee-action.php api/committee.php
```

`committee/index.php`(301)는 지우지 않는다.

- [ ] **Step 5: 운영 확인**

```bash
SITE_BASE_URL=https://younglabor.kr bash tests/public-http-smoke.sh && echo PROD-SMOKE-OK
curl -sS -o /dev/null -w '%{http_code}\n' -X POST -d '{}' https://younglabor.kr/api/committee.php
```

Expected: `PROD-SMOKE-OK`, `404`

---

## 운영 작업 (코드 밖)

| # | 할 일 | 기한 | 담당 |
|---|---|---|---|
| O1 | 신청서 수정 (S1·S2·S4~S8) | Task 7 배포 전 (09-30) | 대표 |
| O2 | 러버블 문의 — 보호자 동의 후 본인 가입 확인과 학생 요금 (D8) | 09-30 발송 | 대표 |
| O3 | 활동 동의서·보호자 동의서 (S9) | 10-23 선발 안내 전 | 대표 |
| O4 | 옛 신청자 안내·파기 (D3) | 10-05 ~ 10-10 | 대표 |
| O5 | 첫 게시 전 런북 확인과 초기 게시물 | 10-05 ~ | 대표 |
| O6 | 모집 홍보물(QR 카드·카드뉴스) | 10-05 | 대표 (BF `/promo`) |
| O7 | kb 반영 (`/promo kb-sync`) | 10-10 | 대표 (BF) |
| O8 | 2027년 1월분 비용 처리 (D6 a) | 12월 | 대표 (관리시스템) |
| O9 | 메시지 확인 — 반도체고 재학생 연령대 3명 이상에게 `/club`을 모바일로 보여 주고 "무엇을 하는 동아리이고 어떻게 신청하는가"를 묻는다. 모두 "AI로 앱을 만든다"와 "신청서 링크"를 답하면 통과 (설계 §11) | 1단계 배포 직후 | 대표 |
| O10 | 소개 페이지 협력기관 3곳의 이름·관계(지원·협력·자문)를 kb `facts.partners`와 대조해 확인. 바꿀 내용이 정해지면 `about.php` 한 곳만 고치는 별도 작은 변경으로 처리 (설계 §9.3) | 10월 중 | 대표 |
| O11 | Cloudflare "Always Use HTTPS"(와 HSTS) 검토. 지금은 `/committee`·`/activity` 같은 슬래시 없는 주소가 Apache 301로 `http://`를 거친다 (1단계 리뷰, 기존 문제) | 10월 중 | 대표 |
| O12 | 운영 백로그(코드): 헤더·푸터·canonical의 게시판 링크를 슬래시 경로로 바꾸거나 슬래시 없는 주소를 직접 처리, `/club/`(끝 슬래시)가 403인 것을 `/club`으로 301, 옛 신청 API 410 문구에 `/club` 안내 추가 검토 | 3단계 이후 | 개발 |

### O1 신청서 수정

zealot-survey 신청서에서 다음을 바꾼다.

| 위치 | 바꿀 내용 |
|---|---|
| 제목 | `반도체고등학교 청소년들과 함께하는 바이브코딩동아리 신청서` |
| 참가자에게 | `3개월 동안 러버블(Lovable) 이용 지원 · AI 전문가 자문. 러버블 가입은 센터가 안내합니다. 만 18세 미만 참가자는 보호자 동의 후 본인이 가입합니다.` |
| 신청 마감 | `2026년 10월 15일 · 신청자가 많을 경우 소정의 심사가 진행될 수 있습니다. 결과는 10월 23일(금)까지 개별 안내합니다.` |
| 설명 끝 | `이 사업은 아름다운재단 지원으로 진행됩니다.` |
| 새 문항 (필수, 이름 다음) | `학교` (단답), `학과` (단답) |
| 이메일 설명 | `러버블 이용 안내에 사용합니다. 자주 확인하는 주소를 정확히 적어주세요.` |
| 동의 — 수집 항목 | `이름, 학교·학과·학년, 휴대전화, 이메일, 신청서에 적어주신 내용` |
| 동의 — 이용 목적 | `참가자 선정(심사), 참가 안내(일정·장소 공지), 러버블 이용 안내, AI 전문가 자문 연결` |
| 동의 — 보유 기간 | `선발되지 않은 분: 선발 결과 안내 후 바로 파기 / 참가자: 프로그램 종료 후 3개월 이내 파기` |
| 동의 — 덧붙임 | `선발 후 러버블 가입과 이용은 보호자 동의서로 따로 안내하고 동의를 받습니다.` |
| 공유 이미지 (가능하면) | `og-club.png`와 같은 1200×630 이미지 (Task 8에서 생성) |

### O2 러버블 문의 메일

받는 곳: privacy@lovable.dev (학생 할인·교육 창구를 안내받으면 그쪽으로 이어 간다)

```text
Subject: Under-18 students signing up with parental permission — nonprofit coding club in Korea

Hello Lovable team,

We are 청년노동자인권센터 (Young Labor Workers' Rights Center), a nonprofit in South Korea (younglabor.kr). From late October 2026 we will run a three-month vibe-coding club for students at semiconductor vocational high schools. Most participants will be 15 to 17 years old.

Your Terms say users under 18 may use Lovable with a parent's or legal guardian's permission, while your Privacy Policy says people must be 18 or older to create their own account and that accounts created by under-18s outside an organization program will be closed. We plan to have each student sign up for their own account after we collect written guardian permission. Could you confirm:
1. that this is acceptable, or tell us what you require instead (for example a written agreement or Lovable for Classrooms);
2. pricing, student discounts, or nonprofit terms for about 5 students (possibly up to 10) for three months;
3. in which countries account and project data is processed and stored, so we can inform students and guardians.

Thank you,
Kim Changsu, Representative
청년노동자인권센터 (Young Labor Workers' Rights Center)
```

답이 오면 요금으로 설계 §10 C3(예산 편성)을 확인한다. 러버블이 본인 가입을 허용하지 않는다고 답하면, 학생에게 가입을 안내하기 전에 Lovable for Classrooms(담당교사 운영)나 서면 계약으로 경로를 바꾼다. 답이 없더라도 학생 프로젝트는 GitHub 연동으로 백업하도록 안내한다(설계 §10 C5).

### O3 보호자 동의서

선발 안내(10-23)와 함께 보낸다. 들어갈 항목은 다음과 같다.

- 참가자: 이름, 학교, 학년, 만 18세 미만 여부(예/아니오 — 생년월일은 받지 않는다)
- 러버블 가입 허락: 학생이 러버블에 본인 계정을 만드는 것에 동의(러버블 이용약관이 18세 미만에게 보호자 허락을 요구한다)
- 해외 서비스 이용 안내: 운영사 Lovable Labs Inc., 처리 국가(O2 답변 또는 러버블 개인정보처리방침에서 확인한 값), 입력하는 정보(이름·이메일·작업 내용), 러버블의 약관·개인정보처리방침이 적용된다는 점
- AI 이용 안내: 입력한 내용은 AI 서비스로 전송·처리된다. 자기·다른 사람의 개인정보를 입력하지 않는다. AI 결과는 틀릴 수 있다
- 선택 동의: 활동 사진 게시(얼굴이 나오지 않는 사진만/얼굴 포함), 산출물 링크 소개
- 보호자 동의(만 18세 미만): 보호자 이름, 관계, 동의 서명, 연락처

### O4 옛 신청자 안내·파기

1. 관리자 → 동아리 신청 화면에서 **건수만** 확인한다. 0건이면 5번으로 간다.
2. 신청 때 받은 휴대전화로 1회 문자를 보낸다(휴대전화가 없으면 이메일).
   > [청년노동자인권센터] 신청해 주신 청소년노동안전동아리는 운영하지 않게 되어 알려드립니다. 대신 AI로 나만의 앱을 만드는 바이브코딩동아리를 10월 15일까지 모집합니다. younglabor.kr/club 에서 확인해 주세요. 보내주신 신청 정보는 이 안내 뒤 모두 삭제합니다.
3. 재발송·추가 홍보는 하지 않는다.
4. 발송 직후 관리자 화면에서 전체 선택 → 삭제로 모든 행을 지운다. 관리자 메일함의 신청 알림 메일과, CSV로 내보낸 파일이 있으면 그것도 영구 삭제한다.
5. 운영 기록(저장소 밖)에 남긴다: 안내 발송 일시·건수(문자/이메일), 파기 일시·방법(관리자 화면 삭제, 메일 영구 삭제, CSV 삭제 여부), 백업 처리 방침(`~/.yl-backups` 2026-09-21 DB 백업과 `backup/` 아카이브 — 새 기준 백업을 만든 뒤 폐기하거나 보존 기한을 정함), 처리자. 신청자 이름·연락처는 기록에 쓰지 않는다.

### O5 첫 게시와 초기 콘텐츠

런북 "배포와 기능 확인" 3~8(업로드 저장소 쓰기, 이미지 WebP, range 다운로드 등)을 운영에서 마친 기록이 없으면 그 절차부터 한다. 그다음 설계 §7의 초기 게시물을 순서대로 올린다. 문안은 BF 저장소 `/promo draft`(type: text)로 만들고 `promo.py check`를 통과시킨다. 행사 글의 크레딧은 `이 행사는 아름다운재단 지원으로 진행됩니다.`를 쓴다.

### O6 모집 홍보물

BF 저장소 `/promo brief` → `/promo visual`(card-portrait, 카드뉴스)로 만든다. QR 링크는 `https://younglabor.kr/club`이다. 학교 방문·커피차·교사 리플릿에 넣는다(설계 §5.6).

### O7 kb 반영

BF 저장소 `/promo kb-sync`로 설계 §10의 kb 후속을 반영한다.

- `facts.units[id=ai-club].name` → `바이브코딩동아리`, 등록명 `AI 학습동아리`는 `registered_name`으로 보존
- `facts.deprecated`에 `청소년노동안전동아리`·`노동안전동아리` 추가
- 도구 `러버블(Lovable)`과 미성년자 이용 조건을 `02-단위사업/AI학습동아리.md`에 기록
- 모집 마감 `2026-10-15`·결과 안내 `2026-10-23`을 `05-일정.md §3`에 반영
- 사이트 크레딧 예외(연도 없음)를 `08-재단규정.md` 적용 메모에 기록

### O8 2027년 1월분 비용 (D6 a)

관리시스템(예산 관리)에서 2026년 12월까지의 러버블 이용료·자문비만 지원금으로 집행 등록한다. 2027년 1월분은 센터 자체 재원으로 결제하고 지원금 집행으로 등록하지 않는다. 12월 연속지원 결과가 나오면 2차년도 계획에 동아리 확산 항목을 편성한다.
