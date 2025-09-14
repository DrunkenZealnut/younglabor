# HopeC Template System

> 재사용 가능한 admin과 theme 템플릿 시스템

새로운 프로젝트에서 쉽게 사용할 수 있는 포터블한 관리자 시스템과 테마 템플릿입니다.

## ✨ 특징

- 🚀 **빠른 설치**: 웹 기반 설치 마법사로 5분 내 설정 완료
- 🎨 **다양한 테마**: 4가지 색상 테마 제공 (Natural Green, Ocean Blue, Sunset Orange, Royal Purple)
- ⚙️ **환경 설정**: .env 파일 기반 환경별 설정 관리
- 🔒 **보안 강화**: CSRF 보호, 세션 관리, SQL 인젝션 방지
- 📱 **반응형 디자인**: 모바일과 데스크탑 모두 지원
- 🔧 **커스터마이징**: 쉬운 색상 변경과 브랜딩 설정

## 🏗️ 구조

```
hopec-template/
├── admin/                  # 관리자 시스템
│   ├── config/            # 설정 파일
│   ├── mvc/               # MVC 프레임워크
│   ├── posts/             # 게시글 관리
│   ├── settings/          # 사이트 설정
│   └── .env.example       # 환경 변수 템플릿
├── theme/                  # 테마 시스템
│   ├── natural-green/     # 기본 테마
│   └── template-themes/   # 추가 테마 설정
├── includes/              # 공통 포함 파일
├── template-sql/          # 데이터베이스 스키마
├── template-docs/         # 문서
├── template-setup.php     # 설치 마법사
└── .env.example          # 환경 설정 템플릿
```

## 🚀 빠른 시작

### 1. 웹 설치 마법사 (권장)

1. **파일 복사**
   ```bash
   cp -r hopec-template /your-new-project/
   cd /your-new-project/
   ```

2. **웹 브라우저에서 설치**
   ```
   http://your-domain.com/template-setup.php
   ```

3. **설치 마법사 단계**
   - 프로젝트 정보 입력
   - 데이터베이스 설정
   - 관리자 계정 생성
   - 테마 선택
   - 자동 설치 완료

### 2. 수동 설치

자세한 수동 설치 방법은 [설치 가이드](template-docs/installation-guide.md)를 참조하세요.

## 🎨 테마 시스템

### 사용 가능한 테마

| 테마 | 설명 | 주요 색상 |
|------|------|-----------|
| **Natural Green** | 자연친화적인 녹색 테마 | `#84cc16`, `#16a34a` |
| **Ocean Blue** | 시원한 바다색 테마 | `#0ea5e9`, `#0284c7` |
| **Sunset Orange** | 따뜻한 노을색 테마 | `#f97316`, `#ea580c` |
| **Royal Purple** | 고급스러운 보라색 테마 | `#8b5cf6`, `#7c3aed` |

### 테마 변경

```env
# .env 파일에서 색상 변경
THEME_PRIMARY_COLOR=#84cc16
THEME_SECONDARY_COLOR=#16a34a
```

## ⚙️ 환경 설정

### .env 파일 예시

```env
# Application Configuration
APP_NAME="My Project"
APP_ENV=local
APP_DEBUG=true
APP_URL=http://localhost

# Database Configuration
DB_HOST=localhost
DB_DATABASE=my_database
DB_USERNAME=root
DB_PASSWORD=""

# Theme Settings
THEME_PRIMARY_COLOR=#84cc16
THEME_SECONDARY_COLOR=#16a34a
```

## 🔧 주요 기능

### 관리자 시스템
- ✅ **사용자 관리**: 관리자 계정 생성 및 권한 관리
- ✅ **게시판 관리**: 다양한 게시판 생성 및 설정
- ✅ **파일 관리**: 이미지 및 문서 업로드 관리
- ✅ **사이트 설정**: 색상, 로고, 기본 정보 설정
- ✅ **통계**: 방문자 통계 및 사용 현황

### 테마 시스템
- ✅ **반응형 디자인**: 모바일/태블릿/데스크탑 지원
- ✅ **Hero 슬라이더**: 메인 페이지 이미지 슬라이더
- ✅ **컴포넌트**: 재사용 가능한 UI 컴포넌트
- ✅ **SEO 최적화**: 검색엔진 최적화 기본 설정

## 📚 문서

- [설치 가이드](template-docs/installation-guide.md)
- [Admin 시스템 가이드](admin/README.md)
- [테마 사용 가이드](theme/natural-green/README.md)

## 🔒 보안

- **CSRF 보호**: 모든 폼에 CSRF 토큰 적용
- **SQL 인젝션 방지**: PDO prepared statements 사용
- **세션 관리**: 안전한 세션 처리
- **파일 업로드 보안**: 확장자 및 크기 제한
- **환경 변수**: 민감한 정보 .env 파일 분리

## 🎯 사용 사례

### 적합한 프로젝트
- 중소기업 웹사이트
- 커뮤니티 사이트
- 포트폴리오 사이트
- 블로그 사이트
- 교육 기관 사이트

### 기술 요구사항
- **PHP**: 7.4 이상
- **MySQL**: 5.7 이상 또는 MariaDB 10.2 이상
- **웹서버**: Apache 또는 Nginx
- **브라우저**: 모던 브라우저 (Chrome, Firefox, Safari, Edge)

## 🛠️ 커스터마이징

### 색상 변경
```css
:root {
  --primary-color: #your-color;
  --secondary-color: #your-secondary;
}
```

### 로고 변경
```php
// theme/natural-green/config/theme.php
'site_name' => 'Your Company',
'title' => 'Your Company',
'content' => 'Your Mission',
```

### 추가 기능
- 새로운 게시판 타입 추가
- 커스텀 컴포넌트 개발
- API 엔드포인트 확장

## 📞 지원

### 문제 해결
1. [문서](template-docs/) 확인
2. [GitHub Issues](https://github.com/your-repo/issues) 검색
3. 커뮤니티 포럼 질문

### 기여하기
1. Fork the repository
2. Create feature branch
3. Commit changes
4. Push to branch
5. Create Pull Request

## 📄 라이센스

이 프로젝트는 MIT 라이센스 하에 배포됩니다. 자세한 내용은 `LICENSE` 파일을 참조하세요.

## 🏆 Credits

- **개발팀**: HopeC Development Team
- **디자인**: Natural Green Theme System
- **기여자**: 모든 기여자들에게 감사드립니다

---

## 🎉 시작하기

지금 바로 새로운 프로젝트를 시작해보세요!

```bash
# 1. 템플릿 복사
cp -r hopec-template my-new-project

# 2. 웹 브라우저에서 설치
open http://localhost/my-new-project/template-setup.php

# 3. 5분 후 완성! 🚀
```

**Happy Coding! 💻✨**