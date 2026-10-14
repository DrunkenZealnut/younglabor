# TODOS

## Deferred Work

### news.php: DB 기반 CMS 확장
- **What:** news.php를 하드코딩 PHP 배열에서 DB 기반 콘텐츠 관리로 전환
- **Why:** 3년 운영 중 캠페인 후기, 배경 설명, 공지사항이 쌓이면 PHP 파일 직접 편집으로는 한계
- **Pros:** 관리자 패널에서 글 작성/수정/삭제 가능. 비개발자도 콘텐츠 관리 가능
- **Cons:** DB 스키마 추가, admin 패널 확장 개발 필요
- **Context:** 디자인 문서 Approach C에서 보류. 캠페인(3월 시작) 이후 콘텐츠 양을 보고 필요성 판단. admin 패널 프레임워크가 이미 있으므로 확장은 비교적 간단
- **Depends on:** 사이트 재편 (Phase 1-5) 완료 후. 실제 콘텐츠 운영 경험 후 판단

## DB 이관 (RenewalMigration / RenewalSeed)

Migrate Cafe24 DB workflow로 apply를 쉽게 재실행할 수 있게 되면서 드러난 기존 이관 코드의 약점. 첫 apply 한 번에는 영향 없음 (런북에 "apply는 한 번만" 명시).

### seed 재실행 시 지운 초기 콘텐츠가 되살아남
- **Priority:** P2
- **What:** `RenewalSeed.php`는 slug만 보고 넣으므로, 관리자가 지우거나 slug를 바꾼 초기 글·사업이 apply 재실행 때 `published`로 다시 생긴다. funding slug를 바꾸면 `year UNIQUE` 충돌로 seed가 매번 실패한다.
- **Fix:** seed 완료 마커(`20261011-renewal-seed`)를 seed 트랜잭션 안에서 `renewal_migrations`에 남기고, 있으면 seed를 건너뛴다. 이 마커를 `deployMigrationStatus()`의 pending 조건에도 넣는다. 지금은 이관 마커가 seed보다 먼저 기록되어, seed가 실패하거나 그 사이에 요청이 끊기면 status가 "up to date"로 나온다 (counts로만 알 수 있음).

### 중간에 실패한 기본 스키마가 재실행으로 복구되지 않음
- **Priority:** P2
- **What:** `migrateRenewal()`은 `content_posts` 존재만 보고 `20260921_create_managed_content.sql`을 건너뛴다. 이 SQL의 CREATE TABLE에는 `IF NOT EXISTS`가 없어, `content_posts`만 생기고 `content_files`가 실패하면 이후 apply가 계속 건너뛰고 status는 영원히 pending이다.
- **Fix:** SQL에 `IF NOT EXISTS`를 넣거나 테이블별로 판단한다.

### 매 apply마다 content_posts.type ENUM을 덮어씀
- **Priority:** P3
- **What:** `ALTER TABLE content_posts MODIFY type ENUM(...)`이 조건 없이 실행된다. 나중에 ENUM 값을 추가하면 재실행 시 strict 모드에선 오류, non-strict에선 값이 `''`로 사라진다.
- **Fix:** 현재 컬럼 정의를 조회해 다를 때만 실행한다.

### prune 시 서버의 database/ 파일 삭제 가능
- **Priority:** P3
- **What:** `database`가 배포 제외 목록에서 빠져, `DEPLOY_PRUNE_REMOVED=true`이면 서버에만 있는 database/ 파일(수동 덤프 등)이 삭제 대상이 된다. 기본값은 false.
- **Fix:** `DeploySafety.php`의 보호 경로에 `database`를 추가한다 (복사에는 영향 없음).

### 이관 workflow를 main 외 브랜치에서 실행 가능
- **Priority:** P3
- **What:** `if: github.ref == 'refs/heads/main'`은 보안 경계가 아니다. 쓰기 권한이 있으면 다른 브랜치에서 workflow를 고쳐 같은 secret으로 실행할 수 있다.
- **Fix:** GitHub Environment(main 브랜치 제한, 승인자 지정)를 만들고 `DEPLOY_SECRET`을 그 Environment로 옮긴다.

### deploy.yml 서명 payload가 고정되어 재사용 가능
- **Priority:** P3
- **What:** `deploy.yml`의 push payload가 고정 문자열이라 한 번 노출된 서명을 영구히 재사용할 수 있다. secret을 `${{ }}`로 스크립트 본문에 직접 넣어 `'`가 들어가면 깨지고, `permissions:`도 없다.
- **Fix:** migrate.yml처럼 secret을 env로 넘기고 `requested_at`을 넣어 push 경로에도 신선도 검사를 적용한다. `permissions: {}`를 추가한다.

### 카페24 OPcache 설정 미확인
- **Priority:** P3
- **What:** `opcache.validate_timestamps=0`이거나 `revalidate_freq`가 길면, 배포 직후에도 옛 deploy.php가 migrate를 "Ignored event"로 응답할 수 있다. 이 경우 "Deploy를 다시 실행" 안내로 해결되지 않는다.
- **Fix:** 카페24 phpinfo에서 opcache 설정을 확인하고, 필요하면 배포 끝에 복사한 .php 파일마다 `opcache_invalidate()`를 호출한다.
