# 청쎈 사이트 개편 적용과 검증

## 구현 범위

PHP·MySQL 사이트를 네 메뉴(단체소개·사업소개·청년과노동·게시판)로 개편했다. 사업축·지원사업·단위사업·프로그램·회차와 글 분류를 관리자에서 편집한다. 바이브코딩동아리는 상시모집이고 특정 제품명과 모집 기한을 표시하지 않는다. 문의는 비공개 저장 이후 메일을 알리며 알림 실패가 접수 실패로 안내되지 않는다.

## 적용 절차

1. 적용 대상 환경과 DB 이름을 확인한다. 운영 작업 전 기존 DB 전체와 콘텐츠 저장 디렉터리, 현재 코드·환경 파일을 백업한다. 백업은 공개 웹 디렉터리 밖에 둔다.
2. PHP PDO MySQL·mbstring·fileinfo·GD, Apache rewrite와 외부 웹 루트의 쓰기 가능한 `CONTENT_STORAGE_PATH`를 확인한다. 기존 `.env.local` 설정을 유지하며 비밀번호를 커밋하지 않는다. `BASE_URL_LOCAL` 또는 운영 BASE URL은 실제 제공 주소와 일치해야 한다.
3. 대상 환경을 확인한 CLI에서 `php scripts/migrate-renewal.php`, `php scripts/seed-renewal.php`를 차례로 실행한다. 추가 스키마를 생성하고 기존 글·파일·신청 데이터를 보존한다. Seed는 기존 관리자 편집값을 덮어쓰지 않는다.
4. 관리자에서 사업 상태, 프로그램·회차, 공개 글과 연결 사업을 확인한다. 교육 강좌는 초기값이 **신청 안내 확인 중**이다. 운영자가 실제 접수 조건을 확인한 뒤 모집 상태를 바꾼다. 동아리는 공개/open/rolling 상태이며 운영자가 closed로 바꾸면 신청 링크가 사라진다.
5. 아래 HTTP·접수 검사를 대상 환경에서 실행한다. 운영 SMTP 확인은 담당자와 실제 수신을 확인하는 별도 단계다. 이번 개발 검수에서는 운영 이메일을 발송하지 않았다.
6. 개인정보 열람 담당자·보관/삭제 기준과 외부 신청 페이지 안내, 재단 표기를 확인한 뒤 운영에 공개한다. 공개 페이지에 미확정 보관 기간을 임의로 적지 않았다.

## 되돌리기

현재 DB와 새 접수·게시물·파일을 먼저 백업하고 코드를 이전 버전으로 복구한다. 새 테이블/열은 보존한다. DB 삭제나 이전 백업 덮어쓰기로 신규 접수를 유실하지 않는다. 복구 후 이전 콘텐츠·파일·문의 열람과 인증을 확인한다.

## 개발 검수 환경

공유 XAMPP 서비스가 중지되어 별도 로컬 MariaDB(`127.0.0.1:33317`, `younglabor_renewal_test`)와 Apache(`127.0.0.1:18764`)를 사용했다. 운영 DB 이관·운영 배포는 수행하지 않았다. 로컬 미리보기: `http://127.0.0.1:18764/younglabor/`.

테스트 DB 전용 `tests/support/bootstrap-db.php`는 관리자·문의·접속 로그 fixture를 만든다. 운영에서 실행하지 않는다. 기존 콘텐츠 통합 테스트는 `_test` DB의 콘텐츠 테이블을 교체/정리하므로 먼저 실행하고 bootstrap 후 개편 테스트를 실행한다.

```sh
for test in tests/*.test.php; do php "$test" || exit; done
TEST_DB_NAME=younglabor_renewal_test TEST_DB_PORT=33317 php tests/integration/content-repository.test.php
TEST_DB_NAME=younglabor_renewal_test TEST_DB_PORT=33317 php tests/integration/content-manager.test.php
php tests/support/bootstrap-db.php
for test in tests/integration/renewal-*.test.php; do php "$test" || exit; done
SITE_BASE_URL=http://127.0.0.1:18764/younglabor bash tests/public-http-smoke.sh
SITE_BASE_URL=http://127.0.0.1:18764/younglabor node tests/public-accessibility.test.js
SITE_BASE_URL=http://127.0.0.1:18764/younglabor bash tests/renewal-http-smoke.sh
```

PHP 문법·단위/DB 테스트, Apache 신규 주소/옛 주소 이동·404·폐기 API 410·비정상 문의 400·canonical·홈/CSS 용량을 확인했다. 파일 smoke는 공개/초안 이미지와 PDF fixture의 GET/HEAD/Range(206, 10bytes), ETag·캐시·초안 404를 확인한다. fixture ID와 slug는 `tests/managed-content-http-smoke.sh` 환경변수로 제공한다.

브라우저 검수는 Aside snapshot과 로컬 Chrome/Puppeteer를 사용했다(Aside의 viewport 변경 API 미지원). 390px·1440px에서 홈·사업 두 탭·강좌·동아리·글 목록의 가로 넘침과 화면 배치를 확인한다. 키보드 메뉴 Enter/Escape와 초점 복귀, 문의 필수값·서버 오류/접수 완료·입력 초기화, 관리자 회차 표시를 확인한다. 개발 검수는 실제 당사자 사용성 조사와 운영 담당자의 독립 운영 검증을 대신하지 않는다.

## 구현 중 결정

기존의 옛 마감일·메뉴·소스 문자열 검사는 승인된 새 상태·공개 경로에 맞춰 수정했다. 저장소 테스트의 환경변수 우선순위를 격리했고, Apache의 디렉터리 슬래시 이동을 끄고 명시적 라우트를 사용한다. 기존 정적 `HomeContent` 도우미 대신 공통 `PublicContent` 로더를 새 화면에서 사용한다. 기존 비공개 파일·CSRF·충돌·rollback 검증은 유지했다.

## 최종 코드 리뷰 후 보완

생성되는 홈/문의 URL에 끝 슬래시를 붙여 Apache 하위 경로에서도 200 응답을 보장했다. 기본 동아리의 slug/종류는 보호하고 추가 동아리는 개별 상세와 신청서로 연결한다. 홈 사업축/프로그램의 노출 체크를 적용하고 네 초기 사업축은 홈 노출로 seed한다. 지원사업의 동아리 모집 상태는 공통 공개 프로그램에서 읽는다. 한글 본문은 TEXT 저장 용량도 입력 단계에서 검사한다. 각 문제의 재현 실패 후 수정 성공을 확인했다.

2026-10-12 개선 완료: 강좌 카드와 상세 화면의 공통 소개 표제를 `교육·강좌`로 일반화했다. 추가 강좌에도 동일하게 적용하며 개별 강좌 제목·일정은 관리 데이터에서 표시한다.

최종 검수: PHP 파일 116개 문법 검사와 PHP 단위/DB 테스트 35개 모두 통과(종료 코드 0). 로컬 DB·Apache 기준이며 생산 환경 실행은 포함하지 않는다.

## 실행 중 판단 기록

- Ruling: Use a private temporary MariaDB instance and PHP dev router when XAMPP is stopped — avoid changing shared server state — cost if wrong: Apache-specific rules need a separate smoke check.
- Task 3: Ruling: update legacy activity label expectation to 활동 소식 — new board classifies activity as a category — cost if wrong: label-only regression.
- Task 5: Ruling: legacy menu/copy source checks follow new approved four-menu layout and shared templates — obsolete source locations no longer implement those routes — cost if wrong: public route tests guard behavior.
- Task 5: Ruling: storage test clears file-loaded environment aliases before putenv overrides — private preview env revealed existing test isolation assumption — cost if wrong: test-only environment cleanup.
- Task 7: Ruling: use Aside for snapshots and local Puppeteer for actual viewport QA — Aside exposes no setViewportSize — cost if wrong: two local browser engines instead of one.
- Task 7: Ruling: turn off DirectorySlash with explicit committee route — Apache otherwise redirects extensionless new directories and leaves retired no-slash path unhandled — cost if wrong: explicit route smoke protects paths.
- Final: Ruling: SMTP, production migration/deployment, live DB, actual participant usability and final privacy/foundation approvals remain operational follow-up — authorized scope is local implementation — cost if wrong: production still needs those checks.
- Final: Ruling: external application form remains user-approved destination, no external submission or mutation — website describes its own entry route — cost if wrong: external form conditions need operator confirmation.
- Final: Ruling: locally fast-forward original feature/vibecoding-club after verification — user authorized redesign of this shared workspace, reversible integration is within scope under developer instructions — cost if wrong: local branch moves; no remote publication.

원래 작업 브랜치 `feature/vibecoding-club`에 로컬 반영하고 문법·PHP 단위 테스트와 Apache 검사를 다시 확인했다. 사용자가 작업하던 파일은 그대로 유지했다. 미리보기 서버가 이 격리 작업공간을 사용하므로 작업공간은 보존한다.

2026-10-13: 기존 웹자보 원본의 강사 네 명 사진을 회차별 일정에 추가했다. 파일 출처는 `assets/images/speakers/README.md`에 기록했다. 프로그램/강사명 일치로 사진을 연결하며 관리자에서 강사명이 변경되면 이전 사진을 숨긴다. 새로운 강좌 사진은 확인된 자료로 별도 연결한다.

2026-10-13 진행 방식 변경: 사용자 확정에 따라 1~3강은 온라인, 4강은 현장+온라인으로 안내한다. 초기 데이터와 로컬 미리보기의 회차 정보를 수정했다. 이미 seed를 적용한 다른 DB는 기존 관리자 편집을 보존하므로 관리자 프로그램/회차 편집에서 1·2강의 방식을 온라인으로 변경한다. 현장 장소는 4강에만 적용한다.

사용자 화면 수정 요청: 공통 푸터의 기하학 장식 아이콘 두 개를 모두 제거하고 연락처·지원기관 정보를 데스크톱 두 열/모바일 한 열로 배치한다. 푸터 청쎈 워드마크를 데스크톱 최대 96px·모바일 최대 64px로 줄였다.

푸터 배치 추가 변경: 메뉴·청쎈·저작권 묶음을 상단 왼쪽으로 이동하고 연락처와 SUPPORT를 그 옆에 나란히 배치한다. 좁은 화면에서는 브랜드 묶음을 먼저 한 행으로 표시하고 연락처·지원기관을 다음 행에 나란히 표시한다. 줄인 청쎈 크기는 유지한다.

푸터 정렬 보완: 메뉴를 별도 윗줄로 분리하고 청쎈·CONTACT·SUPPORT를 같은 행의 시작 높이에 맞춘다. 모바일에서는 기존의 브랜드 다음 연락처·지원기관 배치를 유지한다.

1230px 화면 피드백 반영: 푸터의 세 열 배치를 800px 이상 화면까지 유지해 청쎈·CONTACT·SUPPORT가 같은 행에 표시되도록 했다. 799px 이하에서만 브랜드 다음 행으로 연락처·지원기관을 배치한다.

최종 표시 기준(주석 이미지 반영): CONTACT·SUPPORT 시작 높이를 개인정보 안내를 포함한 푸터 메뉴 줄과 맞춘다. 메뉴는 왼쪽 브랜드 묶음 안에 두고 청쎈·저작권은 메뉴 폭의 중앙에 정렬한다. 800px 이상 세 열, 모바일의 브랜드/연락처·지원기관 배치는 유지한다.
