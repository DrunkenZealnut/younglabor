# 희망씨 웹사이트 로컬 서버 가이드

## 🚀 서버 시작 방법

### 1. 자동 시작 (권장)
```bash
./start_server.sh
```

### 2. 수동 시작
```bash
# MySQL 시작
brew services start mysql@8.0

# PHP 서버 시작
php -S localhost:8000
```

## 🌐 접속 주소

- **메인 사이트**: http://localhost:8000
- **데이터베이스 관리**: http://localhost:8000/db_manager.php
- **직접 백업**: http://localhost:8000/db_backup.php
- **직접 복원**: http://localhost:8000/db_restore.php

## 🔐 로그인 정보

- **관리자 비밀번호**: `hopec2024!`

## 📊 데이터베이스 정보

- **호스트**: 127.0.0.1:3306
- **데이터베이스명**: hopec
- **사용자명**: hopec
- **비밀번호**: hopec2024

## 🛠️ 설치된 구성 요소

- **PHP**: 8.4.7
- **MySQL**: 8.0.43
- **웹서버**: PHP Built-in Server

## 📁 주요 디렉토리

- `/data/backup/` - 백업 파일 저장소
- `/data/` - 데이터 디렉토리
- `start_server.sh` - 서버 시작 스크립트

## ⚠️ 주의사항

1. **포트 충돌**: 8000번 포트가 사용 중이면 다른 포트 사용
   ```bash
   php -S localhost:8080
   ```

2. **MySQL 연결 오류**: MySQL 서비스가 실행 중인지 확인
   ```bash
   brew services list | grep mysql
   ```

3. **권한 문제**: 백업 디렉토리 권한 확인
   ```bash
   chmod 707 data/backup
   ```

## 🔧 문제 해결

### MySQL 연결 실패
```bash
# MySQL 재시작
brew services restart mysql@8.0

# 상태 확인
brew services list | grep mysql
```

### PHP 에러
```bash
# PHP 버전 확인
php --version

# 에러 로그 확인
tail -f /opt/homebrew/var/log/php-error.log
```

## 🛑 서버 중지

1. **PHP 서버**: `Ctrl + C`
2. **MySQL 서버**: `brew services stop mysql@8.0`

## 📞 지원

문제가 발생하면 로그를 확인하고 필요시 관리자에게 문의하세요.