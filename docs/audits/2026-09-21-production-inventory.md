# Production Inventory

점검일: 2026-09-21 (Asia/Seoul)

## Access and runtime

- Access mode: unavailable. 이 실행 환경에는 승인된 `YL_SSH_HOST`, `YL_DOCUMENT_ROOT`, FTP 접속 설정 또는 호스팅 제어판 세션이 없다.
- Production PHP version: 확인 불가.
- Required production PHP modules (`pdo_mysql`, `fileinfo`, `gd`, `mbstring`, `zip`) and `gd_info()['WebP Support']`: 확인 불가.
- Database engine/version: 확인 불가.
- Apache modules relevant to rewrite, headers, expires, and compression: 확인 불가.
- Local reference only: PHP 8.4.7, Apache 2.4.56; `pdo_mysql`, `fileinfo`, `gd`, `mbstring`, `zip`, GD WebP encoding은 로컬에서 활성화돼 있다. 이 값은 운영 환경의 증거로 사용하지 않는다.

## Preserved production capabilities

| 경로 | 공개 HTTP 결과 | 서버 파일·해시 | 필요한 테이블 |
| --- | --- | --- | --- |
| `/admin/login.php` | 200 | 확인 불가 | 저장소 코드상 `admin_user`; 운영 스키마 미확인 |
| `/committee/` | 200 | 확인 불가 | 저장소 코드상 `committee_applications`; 운영 스키마 미확인 |
| `/board.php` | 404 | 소스 존재 여부 확인 불가 | 확인 불가 |
| `/community/` | 404 | 소스 존재 여부 확인 불가 | 확인 불가 |

공개 HTTP 확인은 폼 제출 없이 수행했다. 저장소 코드에서는 `admin_user`, `committee_applications`, `inquiries`, `younglabor_visitor_log`를 참조하지만, 운영 테이블의 존재와 구조는 확인하지 못했다.

## Repository differences

- Production-only paths: 확인 불가.
- Repository-only paths: 확인 불가.
- Different paths: 확인 불가.
- 관리자, 동아리 신청, 게시판, 업로드, 배포 파일의 운영 해시를 수집하지 못했으므로 저장소와 운영 서버가 같다고 판단할 수 없다.

## Database schema

- `SHOW TABLES`와 `SHOW CREATE TABLE`을 실행할 승인된 운영 DB 세션이 없어 구조를 확인하지 못했다.
- 현재 저장소가 요구하는 테이블 이름은 `admin_user`, `committee_applications`, `inquiries`, `younglabor_visitor_log`다.
- 새 `content_posts`와 `content_files` 마이그레이션을 운영 DB에 적용해도 되는지는 기존 테이블, 문자셋, 권한, 백업을 확인하기 전까지 판단할 수 없다.

## Uploads and backups

- 저장소에는 `data/file/notices/` 업로드 경로의 흔적이 있으나 운영 경로, 소유권, 보존 정책은 확인하지 못했다.
- 운영 문서 루트 밖에 `CONTENT_STORAGE_PATH`를 만들 수 있는지 확인하지 못했다.
- 운영 파일 백업과 데이터베이스 백업의 존재, 시점, 복구 가능 여부를 확인하지 못했다.
- 운영 접근이 제공되면 업로드 파일명이나 사용자 데이터는 보고서에 기록하지 않고 경로와 보존 규칙만 확인한다.

## Errors and unknowns

- SSH/FTP/호스팅 제어판 접근 정보가 없어 수집기를 운영 서버에서 실행하지 못했다.
- 운영 PHP 및 Apache 모듈을 확인하지 못했다.
- 운영 데이터베이스 버전과 스키마를 확인하지 못했다.
- 운영 파일과 저장소의 해시 차이를 확인하지 못했다.
- 운영 업로드와 백업의 보존·복구 가능성을 확인하지 못했다.

## Baseline decision

- 결정: 보류.
- 현재 저장소, 운영 서버, 조정된 사본 중 어느 것을 구현 기준으로 삼을지 결정할 증거가 부족하다.
- 필요한 다음 증거: 운영 문서 루트의 읽기 전용 파일·해시 목록, 운영 PHP/Apache 모듈, 스키마 전용 DB 출력, 업로드 경로와 복구 가능한 백업 확인.
