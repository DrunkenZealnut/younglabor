# Board Templates 데이터베이스 설정 시스템

## 개요

Board Templates의 데이터베이스 관련 설정을 통합 관리할 수 있는 웹 인터페이스입니다. 하드코딩된 데이터베이스 정보를 동적으로 관리할 수 있도록 구현되었습니다.

## 주요 기능

### 1. 데이터베이스 연결 설정
- 호스트, 포트, 데이터베이스명, 사용자명, 비밀번호 관리
- 실시간 연결 테스트
- 연결 설정 검증

### 2. 테이블 설정
- 테이블명 커스터마이징
- 테이블 존재 여부 확인
- 테이블별 상태 확인

### 3. 고급 설정
- 파일 업로드 경로 설정
- 보안 설정
- 캐시 설정
- 로그 설정

### 4. 시스템 검증
- 데이터베이스 연결 상태
- 테이블 존재 확인
- 파일 권한 확인
- 시스템 상태 모니터링

### 5. 백업 및 복원
- 설정 백업 생성
- 백업 복원
- 설정 내보내기/가져오기
- 백업 관리

### 6. 로그 관리
- 액션 로그 조회
- 로그 필터링 (날짜, 레벨)
- 로그 삭제

## 파일 구조

```
board_templates/admin/
├── database_settings.php          # 메인 설정 페이지
├── includes/
│   └── DatabaseSettingsManager.php # 백엔드 로직
├── assets/
│   └── database_settings.js       # 프론트엔드 JavaScript
└── api/                           # API 엔드포인트
    ├── system_status.php          # 시스템 상태 조회
    ├── validate_tables.php        # 테이블 검증
    ├── export_config.php          # 설정 내보내기
    ├── import_config.php          # 설정 가져오기
    ├── backup_config.php          # 백업 관리
    └── log_viewer.php             # 로그 조회
```

## 설정 파일 위치

- **설정 파일**: `board_templates/config/database_settings.json`
- **백업 디렉토리**: `board_templates/config/backups/`
- **로그 디렉토리**: `board_templates/config/logs/`

## 사용법

### 1. 접속
```
http://udong.local:8012/board_templates/admin/database_settings.php
```

### 2. 기본 설정
1. **데이터베이스 연결** 탭에서 연결 정보 입력
2. **연결 테스트** 버튼으로 연결 확인
3. **설정 저장** 버튼으로 저장

### 3. 테이블 설정
1. **테이블 설정** 탭에서 테이블명 확인/수정
2. **테이블 검증** 버튼으로 존재 여부 확인

### 4. 백업 관리
1. **고급 설정** 탭의 백업 섹션에서 관리
2. 자동 백업 생성 (설정 변경시)
3. 수동 백업 생성 및 복원

### 5. 시스템 모니터링
1. **시스템 검증** 탭에서 전체 상태 확인
2. 실시간 상태 업데이트
3. 문제 발생시 알림

## API 사용법

### 시스템 상태 조회
```javascript
fetch('/board_templates/admin/api/system_status.php')
    .then(response => response.json())
    .then(data => console.log(data));
```

### 데이터베이스 연결 테스트
```javascript
fetch('/board_templates/admin/api/system_status.php', {
    method: 'POST',
    headers: {'Content-Type': 'application/json'},
    body: JSON.stringify({
        action: 'test_connection',
        config: {...}
    })
})
.then(response => response.json())
.then(data => console.log(data));
```

### 테이블 검증
```javascript
fetch('/board_templates/admin/api/validate_tables.php', {
    method: 'POST',
    headers: {'Content-Type': 'application/json'},
    body: JSON.stringify({action: 'validate_tables'})
})
.then(response => response.json())
.then(data => console.log(data));
```

## 보안 기능

### 1. 인증 확인
- 세션 기반 관리자 인증
- 개발 환경에서는 자동 로그인 허용

### 2. 입력 검증
- XSS 방지를 위한 입력 sanitization
- SQL Injection 방지를 위한 준비된 문장 사용
- 파일 업로드 보안 검증

### 3. 로그 기록
- 모든 설정 변경 액션 로그 기록
- 사용자 및 IP 주소 추적
- 시간별 액션 기록

### 4. 백업 보안
- 설정 변경시 자동 백업 생성
- 민감한 정보(비밀번호) 내보내기시 제외
- 백업 파일 접근 권한 제한

## 문제 해결

### 1. 연결 실패
- 데이터베이스 서버 상태 확인
- 호스트명, 포트 번호 확인
- 사용자 권한 확인

### 2. 테이블 존재하지 않음
- 데이터베이스에 필요한 테이블 생성
- 테이블명 설정 확인
- 스키마 권한 확인

### 3. 설정 저장 실패
- 파일 쓰기 권한 확인
- 디스크 공간 확인
- config 디렉토리 권한 확인

### 4. 백업 실패
- backups 디렉토리 쓰기 권한 확인
- 디스크 공간 확인
- 기존 설정 파일 존재 여부 확인

## 개발자 참고사항

### 1. 클래스 구조
- `DatabaseSettingsManager`: 메인 백엔드 로직
- 네임스페이스: `BoardTemplates\Admin`
- PSR-4 오토로딩 호환

### 2. 에러 처리
- 모든 메서드는 `['success' => bool, 'message' => string]` 형태 반환
- Exception 기반 에러 처리
- 로그 기록을 통한 디버깅 지원

### 3. 확장성
- 플러그인 방식으로 새로운 설정 추가 가능
- API 엔드포인트를 통한 외부 연동 지원
- 모듈화된 구조로 유지보수 용이

### 4. 성능 최적화
- 설정 캐싱
- 지연 로딩
- 트랜잭션 단위 처리

## 버전 정보

- **버전**: 1.0.0
- **최초 작성**: 2025-01-08
- **호환성**: PHP 8.0+, MySQL 5.7+
- **의존성**: PDO, JSON 확장

## 라이선스

이 소프트웨어는 우리동네노동권찾기 프로젝트의 일부입니다.