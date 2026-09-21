# Production Inventory

점검일: 2026-09-21 (Asia/Seoul)

Gate: PASS

판정 근거: 운영 원본 서버에 직접 접속해 웹 PHP/Apache 환경, 문서 루트 파일과 SHA-256, 필수 DB 스키마, 업로드 경로, 새 DB·코드 백업을 확인했다. 구현 기준은 저장소 코드를 사용하되 운영 DB와 업로드를 보존하는 조정 기준선으로 확정한다.

## Access and runtime

- Access mode: 원본 서버 직접 SSH 접속과 HTTPS 진단. Cloudflare 프록시 주소는 SSH에 사용하지 않는다.
- Web runtime: PHP 8.4.21p1, `apache2handler`.
- Required PHP modules: `pdo_mysql`, `fileinfo`, `gd`, `mbstring`, `zip` 모두 활성화.
- GD WebP encoding: 활성화.
- Apache modules: `mod_rewrite`, `mod_headers`, `mod_expires` 활성화. `mod_deflate`는 활성 상태를 확인하지 못했으므로 성능 구현에서 전제하지 않는다.
- Database: MariaDB 10.6.17.
- 공유호스팅 SSH에는 PHP CLI가 없고 Bash 실행이 불안정하다. 운영 점검 스크립트는 POSIX `sh`와 Python SHA-256 폴백을 사용한다.

## Preserved production capabilities

| 경로 | 공개 HTTP 결과 | 운영 파일 | 필요한 테이블 |
| --- | --- | --- | --- |
| `/admin/login.php` | 200 | 존재 | `admin_user` 존재 |
| `/committee/` | 200 | 존재 | `committee_applications` 존재 |
| `/board.php` | 404 | 없음 | 해당 없음 |
| `/community/` | 404 | 없음 | 해당 없음 |

- `inquiries`, `younglabor_visitor_log`도 운영 DB에 존재한다.
- 공개 HTTP 확인은 폼 제출과 관리자 로그인 없이 수행했다.
- 보안 규칙 배포 후 홈, 관리자 로그인, 동아리 신청 화면은 원본 서버에서 모두 200을 유지한다.
- 환경 파일은 406, 업로드 디렉터리는 403, `backup/`과 `dbeditor/`는 원본 서버와 Cloudflare 공개 경로에서 403으로 확인했다.

## Repository differences

- 운영 문서 루트 파일: 11,243개. PHP/CSS/JS/`.htaccess` SHA-256 대상: 6,566개.
- 저장소 비교 대상: 33개. 공통 25개 중 동일 18개, 상이 7개, 운영 전용 6,541개, 저장소 전용 8개.
- 상이한 공통 파일: `admin/contacts.php`, `api/contact.php`, `api/deploy.php`, `committee/index.php`, `config.php`, `includes/PageTracker.php`, `index.php`.
- 차이는 저장소의 최신 저장형 XSS 수정, CORS 제한, IP 마스킹, 안전한 배포 동기화, 공통 레이아웃과 디자인 개편이다. 운영 고유 기능으로 보존할 변경은 발견하지 못했다.
- 저장소 전용 공개 코드: `about.php`, `activities.php`, `assets/css/style.css`, `includes/footer.php`, `includes/header.php`, `includes/symbols.php`, `news.php`.
- 운영 전용 파일 대부분은 문서 루트의 `backup/`과 `dbeditor/`에 있다. 두 경로는 HTTP 차단 상태로 보존하며 이번 기능 구현의 기준 코드로 사용하지 않는다.
- 루트와 관리자 `.htaccess`는 감사 중 운영에 적용했고 저장소 해시와 일치한다.

## Database schema

- 운영 DB에는 총 31개 테이블이 있다. 행 데이터와 자격 증명은 수집하지 않았다.
- `admin_user`: `id`, `username`, `password_hash`, `email`, `name`, `role`, `status`, `is_active`, 로그인 잠금·시각 필드와 생성·수정 시각 필드를 포함한다.
- `committee_applications`: 신청자·학교·학년·전공·연락처·동기, 상태·관리자 메모·검토 시각과 생성·수정 시각 필드를 포함한다.
- `inquiries`: 분류·문의자·연락처·제목·본문·첨부 경로, 상태·답변, 접속정보와 생성·수정 시각 필드를 포함한다.
- `younglabor_visitor_log`: 접속 IP, 사용자 에이전트, 방문 일시, 페이지 URL과 리퍼러 필드를 포함한다.
- 새 `content_posts`와 `content_files`는 기존 테이블과 충돌하지 않는 별도 마이그레이션으로 추가할 수 있다.

## Uploads and backups

- 현재 업로드 흔적은 문서 루트 내부 `data/file/`에 있고 웹 프로세스가 쓸 수 있다. 파일 내용과 파일명은 수집하지 않았다.
- `CONTENT_STORAGE_PATH`는 아직 설정되지 않았다. 문서 루트의 상위 디렉터리는 쓰기 가능하므로 새 콘텐츠 저장소를 외부에 만들 수 있다.
- 기존 문서 루트 `backup/`에는 기본 무결성을 통과한 아카이브 11개가 있으나 최신 시점은 2025-10-24였다. 이 경로는 HTTP 403으로 차단했다.
- 감사 시점에 문서 루트 밖 `~/.yl-backups`에 현재 운영 기준 백업을 새로 생성했다.
  - DB: 31개 기본 테이블, gzip SQL 228,046바이트, gzip 읽기와 덤프 머리말 검증 통과.
  - 코드: 105개 파일, ZIP 20,148,960바이트, ZIP 무결성과 `index.php`, `admin/login.php` 포함 검증 통과.
  - 권한: 백업 디렉터리 0700, DB·코드 파일 0600.
- 실제 별도 DB로의 전체 복원 리허설은 운영 마이그레이션 직전 런북 단계에서 수행한다.

## Errors and unknowns

- `mod_deflate` 활성 여부는 확인되지 않았다. 압축은 Cloudflare 응답 또는 확인된 서버 기능만 사용한다.
- SSH 세션의 `/tmp`는 세션별로 분리된다. 운영 점검 임시 파일은 홈 디렉터리에 두고 작업 종료 후 삭제했다.
- 기존 `backup/`과 `dbeditor/`의 제거 여부는 이번 범위에서 결정하지 않는다. 현재 HTTP 차단 상태를 유지한다.
- 전체 복원 리허설과 `CONTENT_STORAGE_PATH` 생성은 관리형 콘텐츠 운영 배포 전에 수행한다.

## Baseline decision

- 결정: 저장소 코드를 구현 기준으로 사용하고 운영 DB, 업로드, 환경 설정을 보존하는 조정 기준선.
- 운영의 오래된 홈·신청 화면과 보안 수정 전 코드를 저장소로 역병합하지 않는다.
- `admin_user`, `committee_applications`, `inquiries`, `younglabor_visitor_log`, 기존 `data/file/`, 환경 파일, 문서 루트 밖 새 백업을 보존한다.
- 관리형 콘텐츠 구현을 시작해도 된다. 운영 배포는 외부 저장소 생성, 복원 리허설, 마이그레이션과 기능별 연기 테스트를 모두 통과한 뒤 진행한다.
