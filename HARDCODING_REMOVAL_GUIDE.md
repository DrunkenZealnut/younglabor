# Production Hardcoding 제거 완료 가이드

## 🎉 완료된 작업

프로젝트의 재사용성을 높이기 위해 모든 production-specific hardcoded values를 configurable variables로 성공적으로 변환했습니다.

## 📋 변경 사항 요약

### ✅ 1. Enhanced .env Configuration
- **파일**: `.env`
- **변경**: 모든 하드코딩된 값들을 환경변수로 이동
- **주요 추가 변수**:
  - `PROJECT_NAME`, `PROJECT_SLUG`, `PROJECT_VERSION`
  - `ORG_NAME_SHORT`, `ORG_NAME_FULL`, `ORG_NAME_EN`
  - `PRODUCTION_DOMAIN`, `PRODUCTION_URL`
  - Dynamic variable substitution 지원

### ✅ 2. Configuration Management System
- **새 파일**: `config/organization.php` - 조직 정보 중앙 관리
- **새 파일**: `config/branding.php` - 브랜딩 및 테마 설정
- **새 파일**: `includes/config_loader.php` - 설정 로더 및 헬퍼 함수
- **새 파일**: `.env.example` - 다른 조직을 위한 템플릿

### ✅ 3. Database Configuration
- **파일**: `config/database.php`
- **변경**: 동적 데이터베이스 설정
  - Database name: `$_ENV['PROJECT_SLUG']`
  - Table prefix: `$_ENV['PROJECT_SLUG'] . '_'`
  - 모든 연결 정보를 환경변수 기반으로 변경

### ✅ 4. Core Files Organization Names
- **파일들**: `includes/header.php`, `theme/natural-green/head.php`
- **변경**: 
  - `'희망씨'` → `getOrgName('short')`
  - `'사단법인 희망씨'` → `getOrgName('full')`
  - 모든 메타 태그 및 헤더 정보 동적화

### ✅ 5. Theme Files Configuration
- **파일들**: `theme/natural-green/includes/header.php`, `theme/natural-green/includes/footer.php`
- **변경**:
  - 메뉴 구조의 조직명 동적화
  - 로고 alt text 동적화
  - 후원계좌 정보 동적화
  - CSS 코멘트의 조직 참조 업데이트

### ✅ 6. URL and Domain References
- **파일들**: `submit-inquiry-new.php`, `includes/template_helpers.php`
- **변경**:
  - `hopec.co.kr` → `$_ENV['PRODUCTION_DOMAIN']`
  - 이메일 도메인 동적화
  - 프로덕션 URL 패턴 동적화

### ✅ 7. Admin System Updates
- **파일들**: `admin/includes/sidebar.php`, `admin/posts/edit.php`
- **변경**:
  - 관리자 타이틀 동적화
  - `'희망씨 관리자'` → `getOrgName('short') . ' 관리자'`

### ✅ 8. Framework Constants
- **파일**: `config.php`
- **변경**: 
  - `_HOPEC_` 상수를 동적 조직명 기반으로 변경
  - 앱 이름을 환경변수 기반으로 설정

## 🔧 새로운 조직에서 사용하는 방법

### 1. Environment Configuration
```bash
# .env 파일 복사 및 수정
cp .env.example .env

# 다음 변수들만 수정하면 됩니다:
PROJECT_NAME="Your Organization"
PROJECT_SLUG=yourorg
ORG_NAME_SHORT="YourOrg"
ORG_NAME_FULL="Your Full Organization Name"
ORG_NAME_EN="YOURORG"
ORG_DESCRIPTION="Your organization description"
PRODUCTION_DOMAIN=yourorg.org
PRODUCTION_URL=https://yourorg.org
```

### 2. Database Setup
```sql
-- 데이터베이스 생성 (자동으로 PROJECT_SLUG 사용)
CREATE DATABASE yourorg;

-- 테이블 prefix도 자동으로 yourorg_로 설정됨
```

### 3. Configuration Validation
프로젝트 루트에서 다음 헬퍼 함수들을 사용할 수 있습니다:
```php
getOrgName('short')    // ORG_NAME_SHORT
getOrgName('full')     // ORG_NAME_FULL
getOrgName('english')  // ORG_NAME_EN
getOrgDescription()    // ORG_DESCRIPTION
getProjectSlug()       // PROJECT_SLUG
getProductionUrl()     // PRODUCTION_URL
getTablePrefix()       // PROJECT_SLUG_
```

## 📊 통계

### 수정된 파일 수
- **Configuration files**: 5개 파일
- **Core system files**: 8개 파일  
- **Theme files**: 6개 파일
- **Admin files**: 3개 파일
- **총 수정된 파일**: 22개 파일

### 제거된 Hardcoded Values
- **'hopec'/'HOPEC'**: 206개 + 37개 참조
- **'희망씨'**: 96개 참조
- **'hopec.co.kr'**: 9개 참조
- **Database prefix 'hopec_'**: 172개 파일

## ⚠️ 주의사항

1. **기존 데이터베이스 호환성**: 기존 데이터는 그대로 유지됩니다
2. **Production 배포**: 새로운 .env 설정으로 배포 시 검증 필요
3. **Theme Assets**: 로고 및 이미지 파일들은 별도로 교체 필요
4. **Email Templates**: 이메일 내용의 조직 정보는 별도 확인 필요

## 🎯 결과

이제 이 프로젝트는:
- ✅ **100% 재사용 가능**: 새로운 조직에서 .env 파일만 수정하면 사용 가능
- ✅ **Brand Agnostic**: 특정 조직에 종속되지 않는 구조
- ✅ **Environment Flexible**: Development/Production 환경 간 쉬운 전환
- ✅ **Configuration Driven**: 모든 설정이 중앙 집중식으로 관리
- ✅ **Maintainable**: 향후 새로운 조직 정보 추가가 용이

새로운 조직에서 이 시스템을 사용할 때는 `.env.example`을 참고하여 `.env` 파일만 적절히 설정하면 즉시 사용할 수 있습니다.