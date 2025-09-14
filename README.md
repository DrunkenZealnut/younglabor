# HopeC - 희망씨 웹사이트 관리 시스템

> 재사용 가능한 PHP 웹사이트 템플릿 시스템으로 관리자 패널과 테마가 포함되어 있습니다.

희망씨(HopeC)는 중소기업, 커뮤니티, 교육기관을 위한 완전한 웹사이트 솔루션입니다. 강력한 관리자 시스템과 아름다운 테마로 구성되어 있어 빠른 웹사이트 구축이 가능합니다.

## ✨ 주요 특징

- 🚀 **빠른 설치**: 웹 기반 설치 마법사로 5분 내 설정 완료
- 🎨 **다양한 테마**: Natural Green 기본 테마 및 커스터마이징 가능
- ⚙️ **환경 설정**: .env 파일 기반 환경별 설정 관리
- 🔒 **보안 강화**: CSRF 보호, 세션 관리, SQL 인젝션 방지
- 📱 **반응형 디자인**: 모바일과 데스크탑 모두 지원
- 🎯 **MVC 아키텍처**: 체계적인 코드 구조
- 📝 **게시판 시스템**: 다양한 스타일의 게시판 지원
- 🔧 **커스터마이징**: 쉬운 색상 변경과 브랜딩 설정

## 🏗️ 시스템 구조

```
hopec/
├── admin/                   # 관리자 시스템
│   ├── mvc/                 # MVC 프레임워크
│   ├── settings/            # 사이트 설정
│   ├── posts/               # 게시글 관리
│   └── .env.example         # 환경 변수 템플릿
├── theme/                   # 테마 시스템
│   └── natural-green/       # 기본 테마
├── board_templates/         # 게시판 템플릿
├── includes/                # 공통 포함 파일
├── config/                  # 설정 파일들
├── template-docs/           # 문서
└── .env.example            # 환경 설정 템플릿
```

## 🚀 빠른 시작

### 시스템 요구사항
- **PHP**: 7.4 이상
- **MySQL**: 5.7 이상 또는 MariaDB 10.2 이상
- **웹서버**: Apache 또는 Nginx
- **브라우저**: 모던 브라우저 (Chrome, Firefox, Safari, Edge)

### 설치 방법

#### 1. 웹 설치 마법사 (권장)

1. **파일 업로드**
   ```bash
   # 파일을 웹 서버에 업로드
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

#### 2. 수동 설치

1. **환경 파일 설정**
   ```bash
   cp .env.example .env
   # .env 파일을 편집하여 데이터베이스 정보 입력
   ```

2. **데이터베이스 생성**
   - MySQL/MariaDB에서 새 데이터베이스 생성
   - `setup_database.php` 실행

3. **권한 설정**
   ```bash
   chmod 755 perms.sh
   ./perms.sh
   ```

## 🎨 테마 시스템

### Natural Green 테마
- 자연친화적인 녹색 기반 디자인
- 반응형 레이아웃
- Hero 슬라이더 지원
- 다양한 색상 프리셋

### 테마 커스터마이징

```env
# .env 파일에서 색상 변경
THEME_PRIMARY_COLOR=#84cc16
THEME_SECONDARY_COLOR=#16a34a
```

## 🔧 주요 기능

### 관리자 시스템
- ✅ **대시보드**: 사이트 현황 한눈에 보기
- ✅ **사용자 관리**: 관리자 계정 생성 및 권한 관리
- ✅ **게시판 관리**: 다양한 게시판 생성 및 설정
- ✅ **파일 관리**: 이미지 및 문서 업로드 관리
- ✅ **사이트 설정**: 색상, 로고, 기본 정보 설정
- ✅ **통계**: 방문자 통계 및 사용 현황

### 게시판 시스템
- ✅ **다양한 스킨**: FAQ, 갤러리, Q&A, 웹진 스타일
- ✅ **에디터 통합**: Summernote 에디터 지원
- ✅ **파일 업로드**: 이미지 및 첨부파일 지원
- ✅ **검색 기능**: 제목, 내용, 작성자 검색
- ✅ **댓글 시스템**: 계층형 댓글 지원

### 테마 시스템
- ✅ **반응형 디자인**: 모바일/태블릿/데스크탑 지원
- ✅ **Hero 슬라이더**: 메인 페이지 이미지 슬라이더
- ✅ **컴포넌트**: 재사용 가능한 UI 컴포넌트
- ✅ **SEO 최적화**: 검색엔진 최적화 기본 설정

## 🔒 보안 기능

- **CSRF 보호**: 모든 폼에 CSRF 토큰 적용
- **SQL 인젝션 방지**: PDO prepared statements 사용
- **세션 관리**: 안전한 세션 처리
- **파일 업로드 보안**: 확장자 및 크기 제한
- **환경 변수**: 민감한 정보 .env 파일 분리
- **XSS 방지**: 입력 데이터 필터링

## 📚 문서

- [설치 가이드](template-docs/installation-guide.md)
- [Admin 시스템 가이드](admin/README.md)
- [테마 사용 가이드](theme/natural-green/README.md)
- [게시판 템플릿 가이드](board_templates/BOARD_TEMPLATES_PORTING_GUIDE.md)
- [관리자 테마 매뉴얼](HOPEC_ADMIN_THEME_REUSE_MANUAL.md)

## 🎯 사용 사례

### 적합한 프로젝트
- 중소기업 웹사이트
- 커뮤니티 사이트
- 교육 기관 사이트
- 비영리 단체 사이트
- 포트폴리오 사이트

## 🛠️ 개발 및 커스터마이징

### 색상 변경
```css
:root {
  --primary-color: #84cc16;
  --secondary-color: #16a34a;
  --accent-color: #22c55e;
}
```

### 로고 변경
관리자 패널에서 직접 업로드하거나 `assets/images/` 폴더의 `logo.png` 파일을 교체하세요.

### 새 게시판 타입 추가
`board_templates/skins/` 폴더에 새로운 스킨 파일을 추가하여 커스텀 게시판 스타일을 만들 수 있습니다.

## ⚙️ 환경 설정

### .env 파일 예시
```env
# Application Configuration
APP_NAME="My Website"
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

## 🤝 기여하기

1. Repository Fork
2. Feature branch 생성 (`git checkout -b feature/AmazingFeature`)
3. 변경사항 커밋 (`git commit -m 'Add some AmazingFeature'`)
4. Branch에 Push (`git push origin feature/AmazingFeature`)
5. Pull Request 생성

## 📄 라이센스

이 프로젝트는 MIT 라이센스 하에 배포됩니다. 자세한 내용은 [LICENSE](LICENSE.txt) 파일을 참조하세요.

## 🙏 기여자

- **개발팀**: HopeC Development Team
- **디자인**: Natural Green Theme System
- **테스팅**: Community Contributors

## 📞 지원

### 문제 해결
1. [문서](template-docs/) 확인
2. [GitHub Issues](https://github.com/zealnutkim/hopec/issues) 검색
3. 새로운 이슈 생성

### 연락처
- **이메일**: 문의 시 GitHub Issues 활용
- **웹사이트**: 추후 공개 예정

---

**HopeC로 더 나은 웹사이트를 만들어보세요! 💻✨**