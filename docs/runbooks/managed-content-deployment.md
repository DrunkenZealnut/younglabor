# 관리형 콘텐츠 배포 런북

대상 기능은 활동게시판, 언론보도, 자료실과 관리자 작성·수정·삭제다. 운영 데이터와 기존 관리자 로그인, 동아리 신청, 문의 API를 보존한다.

## 배포 전 확인

1. `docs/audits/2026-09-21-production-inventory.md`의 `Gate: PASS`와 기준선 결정을 확인한다.
2. 현재 운영 DB와 코드를 문서 루트 밖에 백업한다. DB gzip을 읽을 수 있고 코드 ZIP 무결성, `index.php`, `admin/login.php` 포함 여부가 확인돼야 한다.
3. 웹 PHP에서 `pdo_mysql`, `fileinfo`, `gd`, `mbstring`, `zip`과 GD WebP가 활성화됐는지 확인한다.
4. 운영 배포 revision과 직전 revision을 기록한다.
5. 별도 테스트 DB에서 두 migration과 전체 PHP 테스트를 실행한다. 테스트 DB 이름은 반드시 `_test`로 끝나야 한다.

## 외부 저장소 준비

SSH 계정의 홈처럼 문서 루트 밖 위치에 전용 디렉터리를 만든다.

```bash
install -d -m 700 "$HOME/.younglabor-content"
cd "$HOME/.younglabor-content" && pwd -P
```

출력된 절대경로를 운영 환경 파일에 다음 키로 기록한다. 환경 파일과 실제 경로는 저장소에 커밋하지 않는다.

```dotenv
CONTENT_STORAGE_PATH=/absolute/path/outside/document-root
```

웹 PHP 프로세스가 디렉터리를 읽고 쓸 수 있고, HTTP로 같은 경로에 접근할 수 없는지 확인한다. 파일은 0600, 디렉터리는 0700을 유지한다.

## DB migration

유지보수 시간을 시작한 뒤 호스팅 DB 콘솔이나 승인된 MySQL 세션에서 다음 순서로 실행한다.

1. `database/migrations/20260921_create_managed_content.sql`
2. `SHOW CREATE TABLE content_posts`
3. `SHOW CREATE TABLE content_files`

기존 `admin_user`, `committee_applications`, `inquiries`, `younglabor_visitor_log`는 변경하지 않는다. 운영 서버에는 PHP CLI가 없으므로 웹 루트에 무인증 migration 스크립트를 두지 않는다.

## 배포와 기능 확인

1. 새 revision을 배포하되 `.env*`, 기존 업로드, 문서 루트 밖 저장소와 백업을 제외한다.
2. `/admin/login.php`에서 기존 계정으로 로그인한다.
3. 활동게시물 하나를 임시저장하고 공개한다. 이미지가 WebP로 표시되고 대체 텍스트가 출력되는지 확인한다.
4. 언론보도 하나를 임시저장하고 공개한다. HTTPS 원문 링크와 실제 호스트가 표시되는지 확인한다.
5. 자료 하나를 첨부파일 방식으로 만들고 range 다운로드를 확인한다. 외부 URL 방식도 별도로 확인한다.
6. 세 유형에서 수정 충돌 안내, 임시저장 404, 공개 전환, 삭제를 확인한다.
7. 관리자 콘텐츠 목록의 `파일 정리 재시도`를 실행해 대기 항목을 처리한다.
8. 다음 환경변수에 검증 fixture 값을 넣어 HTTP smoke test를 실행한다.

```bash
SITE_BASE_URL=https://younglabor.kr \
CONTENT_PUBLISHED_ACTIVITY_SLUG=published-slug \
CONTENT_DRAFT_ACTIVITY_SLUG=draft-slug \
CONTENT_PUBLIC_MEDIA_ID=1 \
CONTENT_DRAFT_MEDIA_ID=2 \
CONTENT_PUBLIC_FILE_ID=3 \
CONTENT_DRAFT_FILE_ID=4 \
bash tests/managed-content-http-smoke.sh
```

9. `/admin/login.php`, `/committee/`가 200인지 확인한다. 실제 데이터를 만들지 않는 범위에서 문의·동아리 API의 OPTIONS/검증 실패 응답도 확인한다.
10. `backup/`, `dbeditor/`, `.env`, 외부 저장소가 계속 HTTP 차단되는지 확인한다.

## rollback

1. 유지보수 상태로 전환한다.
2. `content_posts`, `content_files`와 `CONTENT_STORAGE_PATH` 디렉터리를 먼저 별도 보관한다.
3. 직전 revision을 배포한다.
4. 보존할 관리형 콘텐츠가 있으면 배포 전 DB 백업을 복원한다. 보존할 콘텐츠가 전혀 없을 때만 `database/migrations/20260921_drop_managed_content.sql`을 실행한다.
5. `/`, `/admin/login.php`, `/committee/`, 문의와 동아리 신청 기능을 확인한다.
6. 원래 상태가 확인된 뒤 유지보수 상태를 해제한다.

DB 메타데이터를 보관하기 전에 외부 저장소를 삭제하지 않는다. 백업·환경 파일·업로드 파일을 배포 prune 대상으로 포함하지 않는다.
