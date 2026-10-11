# 청쎈 전면 개편 Implementation Plan

> **For agentic workers:** REQUIRED SUB-SKILL: Use superpowers:subagent-driven-development or superpowers:executing-plans to implement this plan task-by-task. Steps use checkbox (`- [ ]`) syntax for tracking.

**Goal:** 승인된 기획에 따라 네 메뉴·네 사업축·교육 상세·지원사업 탭·상시모집 바이브코딩동아리와 관리자 운영을 연결한다.

**Architecture:** 기존 공통 PHP 레이아웃과 콘텐츠·파일 관리에 추가 스키마와 사업/프로그램 저장 계층을 연결한다. 모집과 회차 정보는 단일 원본에서 읽고 기존 글·파일과 이전 URL을 보존한다. 모든 구현 변경은 첨부 Obsidian 기획안에도 기록한다.

**Tech Stack:** PHP 8+, MySQL/MariaDB, Apache, HTML/CSS, vanilla JavaScript. 런타임 의존성·프레임워크를 추가하지 않는다.

**Spec:** `docs/superpowers/specs/2026-10-11-younglabor-renewal-design.md` (2026-10-11 사용자 승인, 이후 동아리 전환·상시모집 지시 반영).

## Global Constraints

- 핵심 대상은 중소·영세 제조현장 청년노동자와 직업계고 학생·졸업생이다.
- 단체소개 / 사업소개 / 청년과노동 / 게시판 네 메뉴를 사용한다.
- 브랜드는 청쎈과 정식 명칭을 함께 표기한다. 라임 `#CEE84F`·퍼플 `#430086`·블랙·화이트를 사용한다.
- 사진·후기·성과·정원·확인되지 않은 모집 상태를 생성하지 않는다.
- 바이브코딩동아리는 상시모집하며 모집 마감 날짜를 넣지 않는다. 지원 도구는 **바이브코딩도구**라고 표기한다.
- 동아리 신청 URL은 `https://zealot-survey.vercel.app/RJXag60aMfMT`, 강좌 신청 URL은 `https://zealot-survey.vercel.app/srcqw8uVJpFR`다. 링크 클릭은 접수 완료가 아니다.
- 기존 사용자 변경과 운영 데이터를 보존한다. 테스트 DB 이름은 `_test`로 끝나야 한다. main push·운영 배포·실제 외부 문의 발송은 이번 실행에 포함하지 않는다.
- 로컬 하위 경로를 지원하도록 내부 링크는 `url()`, 자산은 `assetUrl()`을 사용한다.
- 첨부 기획안 원본은 `/Users/zealnutkim/Library/Mobile Documents/iCloud~md~obsidian/Documents/SecondBrain/90-System/proposals/work-2026-10-11-1936-청년노동자인권센터-사이트-구축기획안.md`다. 구현·검증·미확정 운영 조건을 구분해 본문과 변경 기록에 반영한다.

## Review Focus

- 같은 slug의 기존 활동·언론 글이 합쳐진 게시판에서 다른 글로 연결되면 안 된다: Task 3·5.
- 마이그레이션 전 또는 DB 장애에서 SQL 오류·초안을 노출하지 않고 빈 목록과 장애를 구분한다: Task 1·5·7.
- 빈 날짜가 1970년·이미 마감으로 처리되면 안 된다. 상시모집은 미래 날짜에도 유지되고 관리자의 접수 중단은 존중한다: Task 2·6.
- 회차 하나가 잘못되어도 프로그램 본문만 저장되는 부분 성공을 허용하지 않는다: Task 2·4.
- 문의 저장 성공·메일 실패에서 재제출을 유도해 중복 문의를 만들면 안 된다: Task 6.

## 파일 역할

- `includes/RenewalMigration.php`, `scripts/migrate-renewal.php`: 추가 스키마와 실행 기록, CLI 전용 이관.
- `includes/RenewalSeed.php`, `scripts/seed-renewal.php`: 기획의 초기 사업·지원사업·프로그램 데이터, 재실행 시 편집값 보존.
- `includes/WorkRepository.php`: 사업축·단위사업·지원사업·프로그램·회차 공개/관리자 조회와 revision 저장.
- `includes/WorkValidation.php`, `includes/WorkManager.php`: 입력·관련 ID·회차 검증과 트랜잭션 저장.
- `includes/WorkPresenter.php`: 표시 상태·행동·날짜·안전한 링크 판정.
- 기존 `ContentRepository.php`, `ContentValidation.php`, `ContentManager.php`, `ContentStorage.php`, `ContentPresenter.php`: 글 유형·분류·작성 주체·홈 노출·관련 사업/프로그램 확장.
- `includes/PublicContent.php`, `includes/PublicCards.php`: 공개 목록·장애 결과와 공통 카드 출력.
- `work/`, `youth-labor/`, `board/`: 공통 레이아웃을 사용하는 공개 페이지.
- `admin/work.php`, `admin/work-edit.php`, `admin/program-edit.php`, `admin/funding-edit.php`: 사업·단위사업·프로그램/회차·지원사업 편집. 기존 콘텐츠 관리 화면은 글 편집 확장.
- `includes/ContactService.php`, `includes/ContactValidation.php`, `assets/js/contact.js`: 문의 검증·접수·메일 결과 구분과 폼 피드백.
- `docs/runbooks/renewal-deployment.md`, `tests/renewal-*`, `tests/integration/renewal-*`: 실행·복구·검증 기록.

## Task 1: 기존 데이터를 보존하는 스키마와 초기 콘텐츠

**Files:** Create migration/seed 파일과 `tests/integration/renewal-migration.test.php`, `tests/renewal-seed.test.php`. 기존 DB 통합 테스트의 fixture 생성 절차를 갱신한다.

**Interfaces:** `migrateRenewal(PDO $pdo): void`, `renewalSeedData(): array`, `seedRenewal(PDO $pdo): void`. 이관 이력 테이블로 완료 단계를 기록하며 CLI는 웹 요청을 거부한다.

- [ ] 기존 activity/press/resource와 파일 fixture를 넣은 테스트 DB에 이관을 두 번 실행하는 실패 테스트를 작성한다. 글 ID·공개 상태·파일 관계가 유지되고 초기 데이터 재입력이 관리자 편집값을 덮어쓰지 않는지 검증한다.
- [ ] `TEST_DB_NAME=younglabor_renewal_test php tests/integration/renewal-migration.test.php`와 `php tests/renewal-seed.test.php`를 실행해 미구현 실패를 확인한다.
- [ ] 기존 `content_posts` enum에 article/notice를 추가하고 `public_author`, `editorial_category`, `work_area_id`, `program_id`, `home_featured`를 추가한다. `inquiries`에는 `inquiry_type`을 추가하고 이름은 선택 입력을 빈 문자열로 저장해 기존 name 열과 호환한다.
- [ ] `work_areas`, `work_units`, `work_funding`, `work_unit_areas`, `work_programs`, `work_sessions`를 만든다. 모든 편집 대상에는 draft/published, revision, 생성·수정 시각을 둔다. 단위사업은 지원사업과 사업축에 연결하고 회차는 프로그램에 연결한다. 관련 ID는 FK 또는 저장 시 실존 검증으로 보호한다.
- [ ] 프로그램은 `kind`(course/club), `recruitment_status`(pending/open/closed), `recruitment_mode`(dated/rolling), nullable `deadline_at`을 가진다. 회차 일시는 Asia/Seoul 기준으로 저장한다. 회차 번호는 프로그램 안에서 유일하다.
- [ ] 초기 공개 설명으로 네 사업축, 2026년·1년차 지원사업과 네 단위사업, 교육 프로그램·네 회차, 바이브코딩동아리를 넣는다. 강좌 접수는 pending, 동아리는 open/rolling/null 마감이다. 새 초안 글·성과·자료는 만들어 넣지 않는다.
- [ ] 두 테스트가 종료 코드 0인지 확인하고 작업 파일만 커밋한다. 로컬 미이관 DB를 웹에서 읽을 때의 표시 확인은 Task 5에서 수행한다.

## Task 2: 사업·프로그램 저장, 상태와 모집 판단

**Files:** Create `WorkRepository.php`, `WorkValidation.php`, `WorkManager.php`, `WorkPresenter.php`, `tests/renewal-work-validation.test.php`, `tests/renewal-recruitment.test.php`, `tests/integration/renewal-work.test.php`.

**Interfaces:** `WorkRepository(PDO $pdo)`의 `listAreas(bool $publishedOnly = true): array`, `findArea(string $slug, bool $publishedOnly = true): ?array`, `findFunding(int $year, bool $publishedOnly = true): ?array`, `fundingUnits(int $fundingId, bool $publishedOnly = true): array`, `findProgram(string $slug, bool $publishedOnly = true): ?array`, `sessions(int $programId): array`, `listPrograms(bool $publishedOnly = true): array`, `findAdmin(string $kind, int $id): ?array`, `saveEntity(string $kind, array $values, ?int $expectedRevision): int`.

`WorkManager::save(string $kind, array $input, ?int $expectedRevision): int`이 area/unit/funding/program 입력과 program의 `sessions` 배열을 검증·트랜잭션 저장한다. `validateWorkInput(string $kind, array $input): array`는 values/errors를 반환한다. `programAcceptsApplications(array $program, ?DateTimeImmutable $now = null): bool`, `programAction(array $program, ?DateTimeImmutable $now = null): ?array`는 label/href를 반환한다.

- [ ] 실패 테스트에서 rolling/open/null 마감이 2026년과 2030년 모두 신청 가능이고, rolling/closed는 불가인지 검증한다. dated/open은 명시 마감 이후 신청 불가, pending은 항상 불가다. 미래 dated 강좌는 마감 미정이라도 관리자가 open으로 확인했으면 신청 가능하다.
- [ ] 제목·slug·공개 여부·상태·HTTPS URL·메일·일시와 회차 번호 중복·잘못된 방식·중첩 배열 입력·잘못된 관계 ID를 거부하는 실패 테스트를 작성한다.
- [ ] 해당 PHP 테스트 세 개를 실행해 실패를 확인한다.
- [ ] 정확한 필드 whitelist와 prepared statement로 저장하고 revision 불일치는 `ContentConflictException`을 반환한다. 회차 한 건 검증 또는 저장 실패 시 프로그램 변경까지 rollback한다. unpublished 프로그램의 회차도 공개 조회에 노출하지 않는다.
- [ ] 신청 URL은 기존 HTTPS 검증을 활용하고 내부 행동 경로는 허용 경로만 저장한다. rolling에서는 마감을 null로 정규화한다. 시간 경과를 개최 완료·영상 공개로 해석하지 않는다.
- [ ] 테스트 종료 코드 0을 확인하고 커밋한다.

## Task 3: 청년과노동·게시판 데이터와 관리자 글 편집

**Files:** Modify 기존 콘텐츠 저장·검증·표시·파일 계층, `admin/content.php`, `admin/content-edit.php`, `admin/helpers.php`. Create `tests/renewal-content.test.php`, `tests/integration/renewal-content.test.php`.

**Interfaces:** `ContentRepository::listPublicSection(string $section, array $filters, int $limit, int $offset): array`, `countPublicSection(string $section, array $filters): int`, `findPublicBoard(string $routeSlug): ?array`. section은 youth-labor/board, filters는 category/area/program/home_featured다. `publicPostPath(array $post): string`은 내부 상대 경로를 반환한다. 기존 CRUD 함수 signature는 유지한다.

- [ ] article은 공개 작성 주체·본문·story/column/policy 분류가 필요하고 notice는 notice/recruitment 분류가 필요하다는 실패 테스트를 작성한다. 기존 글은 새 필드 기본값으로 저장 가능해야 한다. 같은 slug인 activity/press/notice 세 글이 서로 다른 정확한 경로로 조회되는지 통합 검증한다.
- [ ] 기존 slug 규칙은 단일 하이픈만 허용한다. 활동 경로는 `/board/{slug}`, 언론은 `/board/press--{slug}`, 공지·모집은 `/board/notice--{slug}`로 표현해 기존 activity slug와 충돌하지 않게 한다. article은 `/youth-labor/{slug}`다.
- [ ] 두 테스트를 실행해 실패를 확인한다.
- [ ] article/notice에 activity와 같은 안전한 본문·cover 정책을 적용한다. 초안·비공개 파일을 공개하지 않고 관련 프로그램이 비공개면 링크를 숨긴다. 게시판 모든 유형을 날짜/id 순으로 합쳐 페이지네이션한다.
- [ ] 관리자 입력·분류·작성 주체·관련 사업/프로그램·홈 노출을 추가한다. 기존 CSRF·동시 수정 충돌·파일 삭제/rollback 정책을 유지한다. 관련 프로그램 선택은 일정·신청값을 글에 복사하지 않고 상세 링크를 표시한다.
- [ ] 기존 `content-*`, `admin-content`, 파일 검증과 새 테스트를 실행하고 커밋한다.

## Task 4: 사업·지원사업·프로그램 관리자 운영

**Files:** Create 네 관리자 사업 편집 파일, `tests/renewal-admin.test.php`, `tests/integration/renewal-admin.test.php`; Modify `admin/helpers.php`, `admin/dashboard.php`, `admin/committee.php`, `admin/api/committee-action.php`.

**Interfaces:** Task 2의 `findAdmin`과 `WorkManager::save`만 저장에 사용한다. GET의 kind/id는 whitelist와 양의 정수로 검사한다. POST는 CSRF와 expected_revision을 요구한다. 프로그램 폼의 `sessions`는 회차 번호·날짜·시각·강사·제목·설명·방식·패널 필드를 가진 배열이다.

- [ ] 인증 없는 요청은 로그인 이동, 잘못된 CSRF는 저장 불가, 다른 관리자의 수정 뒤 재저장은 충돌, 회차 오류는 입력 보존이라는 실패 테스트를 작성한다. 임시 관리자와 테스트 DB에서 사업 등록→공개→프로그램 신청 상태 변경→종료를 검증한다.
- [ ] 관리자 옛 동아리 메뉴·통계가 `committee_applications`를 조회하지 않고 옛 변경 API는 인증된 요청에도 410으로 변경을 거부하는 테스트를 추가한다. 테이블 내용은 보존한다.
- [ ] 새 테스트를 실행해 실패를 확인한다.
- [ ] 사업/단위사업·지원사업·프로그램/회차 폼과 목록을 구현하고 입력 오류를 해당 필드에 표시한다. 지원사업 편집에서 등록명 AI 학습동아리와 공개명 바이브코딩동아리를 구분한다.
- [ ] 옛 동아리 관리 화면은 종료 안내로 교체한다. 관리자 통계는 문의·공개 글·모집 중 프로그램 중심으로 변경한다. 외부 설문 응답을 내부 신청 건수로 추정하지 않는다.
- [ ] 저장·발행·종료와 인증·CSRF·충돌·원자성 테스트를 통과시키고 커밋한다.

## Task 5: 공통 디자인과 모든 공개 페이지 연결

**Files:** Modify header/footer/CSS, `index.php`, `about.php`, `.htaccess`, `activities.php`, `news.php`, `activity/index.php`, `activity/view.php`, `press/index.php`, HomeContent/SiteContent. Create PublicContent/PublicCards, `work/index.php`, `work/area.php`, `work/incubating.php`, `work/program.php`, `youth-labor/index.php`, `youth-labor/view.php`, `board/index.php`, `board/view.php`, `tests/renewal-public.test.php`, `tests/renewal-http-smoke.sh`.

**Interfaces:** `loadPublicSection(callable $repositoryFactory, string $section, array $filters, int $page, int $perPage = 12): array`는 rows/total/page/pages/unavailable을 반환한다. `renderPublicCard(array $post): void`는 Task 3의 `publicPostPath`를 사용한다. 프로그램·사업 화면은 Task 2 공개 조회를 사용한다. 공유 bootstrapping에서 schema 준비 상태와 조회 장애를 구분한다.

- [ ] 네 메뉴·두 URL 탭 선택·하나의 h1/main·canonical·초안 비노출·404·이전 주소 이동·로컬 하위 경로를 검증하는 실패 테스트를 작성한다. board slug 충돌 fixture를 HTTP로도 확인한다. 조회 실패와 빈 목록 메시지가 달라야 한다.
- [ ] tests를 실행해 신규 경로 미구현 실패를 확인한다.
- [ ] 공통 헤더/푸터를 네 메뉴로 개편하고 브랜드·색·타이포·모바일 메뉴·키보드 초점을 구현한다. CSS 40KiB와 홈 HTML 80KiB의 기존 예산을 유지하며 오래된 중복 스타일을 정리한다.
- [ ] 홈은 설계의 여덟 구역으로 구현한다. 공개 글·소식 최대 세 편, 실제 상태별 CTA를 출력한다. 기본 소개는 DB 장애에서도 읽을 수 있게 하되 관리자 데이터와 다른 일정·상태를 정적 복제하지 않는다.
- [ ] 네 사업 상세, 전체 사업/지원사업 화면, 강좌 상세를 구현한다. 강좌 네 회차·강사·방식·현장 장소·무료·주최·지원은 seed 원본을 읽는다. 강좌 pending이면 신청 버튼을 숨기고 회차 안내·문의만 제공한다.
- [ ] youth-labor/board 목록·상세·분류·페이지네이션을 구현한다. 공지는 연결 프로그램 상세 하나로 이동하고 언론 상세는 출처와 외부 원문을 보여준다. 빈 파일·영상·기록 버튼은 출력하지 않는다.
- [ ] 구 activities→work, news/activity→board, press→board의 언론 필터, activity/{slug}→board/{slug}의 301을 PHP에서 `url()`로 구성한다. `/resources`, `/tools`, media/downloads 경로는 유지한다. .php 형태의 옛 직접 요청에도 같은 대응을 적용한다.
- [ ] 미이관 DB·장애 DB·빈 DB·seed DB를 로컬 검증에서 각각 확인한다. 새 경로 200/404/301과 초안 파일 404가 맞는지 검증하고 커밋한다.

## Task 6: 상시모집 동아리와 최소 문의 흐름

**Files:** Modify `club.php`, SiteContent/HomeContent, `api/contact.php`, Mailer, `admin/contacts.php`, `admin/api/contact-action.php`, Privacy 안내. Create ContactValidation/ContactService/contact.js, `privacy.php`, `tests/renewal-contact.test.php`, `tests/renewal-club.test.php`, `tests/integration/renewal-contact.test.php`. Update 기존 동아리 마감 관련 tests.

**Interfaces:** `validateContactInput(array $input): array`는 values/errors를 반환한다. `ContactService(callable $saveInquiry, callable $notify)::submit(array $input): array`는 success/message/errors와 내부 notification 상태를 반환한다. 개인정보·메일 예외 원문은 공개하지 않는다. 동아리 설정은 WorkRepository의 `findProgram('vibecoding-club')`에서 읽고 기존 helper는 같은 레코드를 참조하는 어댑터로 전환한다.

- [ ] 동아리 테스트는 2026-10-16과 2030년에도 상시모집이고 마감일·특정 제품명이 공개 HTML에 없으며 신청 URL이 정확한지 검증한다. 운영자가 closed로 바꾸면 홈 신청 CTA와 상세 신청 버튼이 모두 사라져야 한다.
- [ ] 문의는 유형 general/education/cooperation/participation, 유효한 이메일(254자 이하), 내용 1~3000자, 선택 이름 100자 이하를 검증한다. 배열 입력·길이 초과·잘못된 이메일/유형은 저장·메일 호출 없이 거부하는 테스트를 작성한다.
- [ ] 저장 실패는 success=false·메일 호출 없음, 저장 성공+메일 실패는 success=true·중복 재시도 안내 없음, 둘 다 성공은 success=true인 실패 테스트를 작성한다. SMTP는 spy/double을 사용한다.
- [ ] 테스트를 실행해 실패를 확인한다.
- [ ] `/club`을 바이브코딩도구 일반명·앱/웹 제작·상시모집으로 수정한다. 옛 마감 날짜·마감 카운트·특정 서비스 계정 안내를 제거한다. `/committee` 301과 공개 옛 API 410은 유지한다. 구 전단 이미지는 새 모집 이미지로 그대로 사용하지 않는다.
- [ ] 문의 저장→알림 순서를 구현한다. 관리자 문의함에 유형·선택 이름을 안전하게 표시한다. 공개 폼은 라벨·필수 안내·오류·aria-live 완료 메시지를 표시하고 실패 시 입력을 보존한다. 개인정보 수집 목적·필수/선택값·비공개 접수 안내를 제공하되 보관 기한 등 미확정값은 창작하지 않는다.
- [ ] 단위/DB 테스트와 HTTP invalid POST를 검증하고 커밋한다. 실제 운영 이메일로 시험 발송하지 않는다.

## Task 7: 전체 검증·운영 절차·기획안 동기화

**Files:** Update 테스트의 변경된 메뉴·카피·경로 기대값, `tests/public-accessibility.test.js`, `tests/public-http-smoke.sh`, `tests/managed-content-http-smoke.sh`. Create `docs/runbooks/renewal-deployment.md`. Update 승인 설계와 첨부 Obsidian 기획안.

**Interfaces:** 검증 결과에는 명령·종료 코드·실행 환경·검증하지 못한 항목을 기록한다. 이관·seed는 CLI 실행 전 로컬/운영 구분·DB 백업 절차를 런북에 설명하고 운영 실행은 이번 작업에서 하지 않는다.

- [ ] 이전 마감·메뉴를 요구하는 테스트만 새 기획으로 갱신한다. 인증·CSRF·다운로드·초안 비노출·파일 rollback·폐기 API 계약은 약화하지 않는다.
- [ ] 수정 PHP 전체의 `php -l`, 관련 PHP 테스트 전체, `_test` MySQL 통합 테스트를 실행한다. schema 오류·잘못된 ID·마감 없는 상시모집·분류별 slug 충돌 테스트 결과를 확인한다.
- [ ] Apache에서 신규·구 URL, sitemap/canonical을 제공하는 경우 새 주소 정합성, 문의 오류 응답, 파일 HEAD/Range/권한, CSS/홈 크기를 검증한다. robots/sitemap 추가는 기존 운영 여부를 확인하고 필요하면 새 공개 경로만 포함한다.
- [ ] 브라우저 스킬을 사용해 390px·1440px 화면에서 홈·탭·강좌·동아리·목록·문의 폼·관리자 회차 편집을 확인한다. 키보드 메뉴 열기/닫기·Escape·초점·폼 라벨·오류·완료 메시지와 가로 넘침을 검사한다. 결과를 런북에 기록한다.
- [ ] 각 task 완료 시의 구현 변경을 첨부 기획안 본문/변경 기록에도 반영한다. 마지막에는 구현 완료 범위, 실제 실행한 개발 검증과 미실행 사용자 조사, 운영 공개 전 접수 상태·개인정보 운영 기준·재단 표기 확인을 구분해 기록한다.
- [ ] 이관→seed→로컬 확인→배포 검토의 운영 절차, 기존 데이터 백업, 되돌릴 때 새 데이터 보존·코드 복구 순서를 작성한다. destructive rollback SQL은 제공하지 않는다.
- [ ] `git diff --check`와 변경 파일 목록을 확인하고 작업 파일만 커밋한다. 로컬 검토 주소와 검증 결과를 사용자에게 전달한다.

## 실행 방식과 승인

일곱 작업은 스키마·관계·공개 표시·관리자·모집·문의가 서로 의존한다. **직접 실행(Native)을 추천한다.** 같은 세션에서 내가 각 작업을 구현·검증하면 인터페이스와 기획안 동기화를 일관되게 유지할 수 있다. 다른 선택은 작업별 구현/검토 에이전트를 사용하는 Subagent-driven이다. 구현은 사용자가 이 실행 계획을 검토하고 실행 방식을 선택한 뒤 시작한다.
