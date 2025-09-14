# Board Templates Theme Configuration

게시판 템플릿의 UI 스타일(테두리색상, 버튼 스타일 등)을 외부에서 제어하기 위한 테마 시스템입니다.

## 테마 시스템 구조

### 1. 자동 테마 연동 시스템

**🎨 완전 자동화**: 게시판 템플릿은 현재 ATTI 프로젝트의 테마 설정을 자동으로 감지하고 적용합니다.

#### 현재 적용된 테마: **Yellow Bright 테마**
- **Primary Color**: #FBBF24 (밝은 노란색)
- **Secondary Color**: #F97316 (주황색)
- **Background**: #FFFBEB (크림색 배경)
- **Border**: #FDE68A (황금색 테두리)

#### 자동 변수 매핑:
```css
:root {
    /* ATTI 프로젝트 현재 설정에서 자동 로드 */
    --primary-color: #FBBF24;
    --secondary-color: #F97316;
    --accent-color: #FED7AA;
    --text-primary: #111827;
    --text-secondary: #4B5563;
    --background: #FFFBEB;
    --border-color: #FDE68A;
    --border-radius: 16px;
}
```

### 2. 외부 테마 제어 방법

#### 방법 1: PHP 설정을 통한 제어

`$config` 배열을 통해 테마 설정을 제어할 수 있습니다:

```php
$config = [
    'include_board_theme' => true,  // 테마 CSS 포함 여부
    'board_theme_css_path' => 'assets/board-theme.css',  // 테마 CSS 파일 경로
    // 기존 메인 테마 시스템과 연동
    'theme' => 'warm',  // 'default', 'warm', 'calm', 'golden'
];
```

#### 방법 2: CSS 변수 오버라이드

외부 CSS에서 CSS 변수를 오버라이드하여 테마를 변경:

```css
:root {
    /* 메인 테마 변수 (기존 시스템과 호환) */
    --primary-color: #FF9800;
    --bg-primary: #ffffff;
    --border-light: #e2e8f0;
    --text-primary: #1e293b;
}
```

#### 방법 3: 기존 테마 시스템과의 연동

`includes/functions.php`의 `get_predefined_themes()` 함수와 연동:

```php
// 기존 테마 설정이 게시판에도 자동 적용됩니다
$themes = get_predefined_themes();
$current_theme = $themes['warm']; // 따뜻한 톤 테마 적용
```

### 3. 지원하는 스타일 요소들

#### 색상 시스템
- **배경색**: 기본 배경, 보조 배경, 강조 배경
- **테두리색**: 연한 테두리, 중간 테두리, 진한 테두리
- **텍스트색**: 기본 텍스트, 보조 텍스트, 흐린 텍스트
- **상태색**: 성공, 경고, 오류, 정보

#### UI 컴포넌트
- **버튼**: Primary, Secondary, Outline 버튼 스타일
- **배지**: 공지사항, 추천글 배지
- **폼 요소**: 입력 필드, 드롭다운, 텍스트 영역
- **댓글**: 댓글 아이템, 답글 인덴테이션
- **테이블**: 헤더, 행, 호버 효과

#### 게시판 구조별 요소
- **게시판 헤더**: `.board-header`, `.board-title`, `.board-description`
- **게시글**: `.board-post-header`, `.board-post-title`, `.board-post-meta`
- **댓글**: `.board-comments-heading`
- **폼**: `.board-write-form`, `.board-edit-form`

### 4. 사용 예시

#### 기본 사용법 (완전 자동)
```php
// 게시판 템플릿 사용 시 자동으로 현재 시스템 테마가 적용됩니다
// 추가 설정 불필요!
include 'board_templates/board_list.php';
```

#### 테마 테스트
브라우저에서 `test-board-theme.php`를 방문하여 현재 테마 적용 상태를 확인할 수 있습니다.

#### 실시간 테마 변경
1. `admin/design-settings.php`에서 테마를 변경
2. 게시판 페이지를 새로고침하면 자동으로 새 테마가 적용됨

#### 커스텀 테마 적용 (선택사항)
```php
$config = [
    'include_board_theme' => true,
    'board_theme_css_path' => 'custom/my-board-theme.css',
    'generate_dynamic_css' => false, // 동적 CSS 생성 비활성화
];
include 'board_templates/board_list.php';
```

### 5. 파일 구조

```
board_templates/
├── assets/
│   └── board-theme.css          # 메인 테마 CSS 파일 (자동 업데이트됨)
├── theme_integration.php        # 🆕 ATTI 테마 연동 로직
├── board_list.php               # 게시판 목록 (자동 테마 적용)
├── post_detail.php              # 게시글 상세 (자동 테마 적용)
├── comments_widget.php          # 댓글 위젯 (자동 테마 적용)
├── write_form.php               # 글쓰기 폼 (자동 테마 적용)
├── edit_form.php                # 수정 폼 (자동 테마 적용)
├── THEME_CONFIGURATION.md       # 이 문서
└── test-board-theme.php         # 🆕 테마 테스트 파일
```

**새로 추가된 파일들:**
- `theme_integration.php`: ATTI 프로젝트의 테마 설정을 자동으로 읽어와서 게시판에 적용
- `test-board-theme.php`: 현재 적용된 테마를 시각적으로 확인할 수 있는 테스트 페이지

### 6. 호환성

- **기존 시스템과의 호환성**: 기존 ATTI 프로젝트의 테마 시스템과 완전 호환
- **브라우저 지원**: CSS Custom Properties를 지원하는 모든 모던 브라우저
- **반응형**: 모바일, 태블릿, 데스크톱 모든 해상도 지원
- **다크모드**: `@media (prefers-color-scheme: dark)` 지원

### 7. 확장 방법

새로운 테마 변수나 컴포넌트를 추가하려면:

1. `board-theme.css`에 새로운 CSS 변수 정의
2. 해당 변수를 사용하는 스타일 규칙 추가
3. 필요한 경우 `includes/functions.php`의 테마 설정에 새로운 색상 값 추가

이렇게 구성된 테마 시스템을 통해 게시판의 모든 UI 요소를 외부에서 유연하게 제어할 수 있습니다.