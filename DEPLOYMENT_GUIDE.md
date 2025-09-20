# HOPEC 사이트 배포 가이드

## 🚀 환경별 .htaccess 설정

### 개발환경 (현재)
현재 활성 파일: `.htaccess`
- `/hopec/` 경로 기반 라우팅
- 개발친화적 짧은 캐시 설정
- 디버깅을 위한 최소 보안 헤더

### 프로덕션 환경
백업 파일: `.htaccess.production`
- 루트 경로 기반 라우팅  
- 강력한 보안 헤더
- 최적화된 캐시 설정
- GZIP 압축 최적화

## 📋 서버 업로드 시 체크리스트

### 1단계: 파일 업로드 전 확인
```bash
# 로컬에서 테스트
curl -s -w "%{http_code}" http://localhost:8080/hopec/ 
curl -s -w "%{http_code}" http://localhost:8080/hopec/about
curl -s -w "%{http_code}" http://localhost:8080/hopec/board/list/1
```

### 2단계: 프로덕션 서버 업로드
1. 전체 파일 업로드 (FTP/SFTP)
2. `.htaccess.production` → `.htaccess`로 이름 변경
   ```bash
   mv .htaccess.production .htaccess
   ```

### 3단계: 서버 설정 확인
서버에서 다음 Apache 설정이 필요합니다:
- `mod_rewrite` 모듈 활성화
- `AllowOverride All` 설정
- 해당 디렉터리에 대한 읽기/실행 권한

### 4단계: 라이브 테스트
```bash
# 메인 사이트
curl -s -w "%{http_code}" https://yourdomain.com/

# 섹션 페이지  
curl -s -w "%{http_code}" https://yourdomain.com/about
curl -s -w "%{http_code}" https://yourdomain.com/programs
curl -s -w "%{http_code}" https://yourdomain.com/community
curl -s -w "%{http_code}" https://yourdomain.com/donate

# 게시판 (게시물이 있는 경우)
curl -s -w "%{http_code}" https://yourdomain.com/board/list/1
```

## 🔧 문제 해결

### 404 에러 발생 시
1. Apache `mod_rewrite` 모듈 확인
   ```bash
   apache2ctl -M | grep rewrite
   ```

2. `.htaccess` 파일 권한 확인
   ```bash
   ls -la .htaccess
   # 644 권한 필요 (-rw-r--r--)
   ```

3. Apache 설정에서 `AllowOverride All` 확인

### 성능 문제 시
1. GZIP 압축 활성화 확인
2. 브라우저 캐시 헤더 확인
3. 정적 파일 CDN 사용 고려

## 📁 파일 구조
```
hopec/
├── .htaccess (개발환경)
├── .htaccess.production (프로덕션용)
├── index.php (메인 라우터)
├── board.php (게시판)
├── about/about.php
├── programs/domestic.php  
├── community/notice_view.php
└── donate/one-time.php
```

## ⚡ URL 라우팅 규칙

### 메인 섹션
- `/about` → `about/about.php`
- `/programs` → `programs/domestic.php`
- `/community` → `community/notice_view.php`
- `/donate` → `donate/one-time.php`

### 게시판
- `/board/list/1` → `board.php?id=1`
- `/board/list/999` → `board.php?id=999`

### Fallback
- 기타 모든 요청 → `index.php` (메인 라우터가 처리)

## 🛡️ 보안 기능

### 개발환경
- 기본 XSS/Clickjacking 보호
- 디렉터리 브라우징 비활성화

### 프로덕션환경
- 강화된 보안 헤더
- Permissions Policy 적용
- 최적화된 캐시 정책
- ETag 최적화

## 📞 지원
문제 발생 시 다음 정보와 함께 문의:
1. 서버 환경 (Apache 버전, PHP 버전)
2. 에러 메시지 및 에러 로그
3. 접근하려던 URL
4. Apache error_log 내용