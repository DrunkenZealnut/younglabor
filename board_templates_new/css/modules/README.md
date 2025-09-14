# CSS 모듈 시스템

## 구조 설계

```
css/modules/
├── README.md                    # 이 파일
├── base/                       # 기본 스타일
│   ├── variables.css           # CSS 변수 정의
│   ├── reset.css              # 에디터 리셋 스타일
│   └── typography.css         # 폰트 및 텍스트 기본 스타일
├── components/                # 컴포넌트별 스타일
│   ├── toolbar.css            # 툴바 스타일
│   ├── buttons.css            # 버튼 스타일
│   ├── dropdowns.css          # 드롭다운 스타일
│   └── modals.css             # 모달/팝업 스타일
├── plugins/                   # 플러그인별 스타일
│   ├── text-styles.css        # 텍스트 스타일 플러그인
│   ├── paragraph.css          # 문단 스타일 플러그인
│   ├── content.css           # 콘텐츠 삽입 플러그인
│   └── mobile.css            # 모바일 최적화
├── themes/                    # 테마별 스타일
│   ├── default.css           # 기본 테마
│   ├── dark.css              # 다크 테마
│   └── board-theme.css       # 게시판 테마 연동
└── editor-enhancements.css   # 통합 CSS (모든 모듈 import)
```

## CSS 네이밍 규칙

### BEM 방법론 적용
- **Block**: `.note-[component]`
- **Element**: `.note-[component]__[element]`  
- **Modifier**: `.note-[component]--[modifier]`

### 예시
```css
/* 툴바 컴포넌트 */
.note-toolbar {}                    /* Block */
.note-toolbar__group {}             /* Element */
.note-toolbar--mobile {}            /* Modifier */

/* 플러그인 버튼 */
.note-btn {}                        /* Block */
.note-btn__icon {}                  /* Element */
.note-btn--strikethrough {}         /* Modifier */
.note-btn--active {}                /* State */
```

## CSS 변수 시스템

기존 board-theme.css와 연동되도록 CSS 변수를 활용:

```css
:root {
  /* 기존 테마 변수 사용 */
  --editor-primary: var(--theme-primary, #FBBF24);
  --editor-secondary: var(--theme-secondary, #F97316);
  --editor-bg: var(--theme-bg-primary, #FFFBEB);
  --editor-text: var(--theme-text-primary, #111827);
  --editor-border: var(--theme-border-light, #FDE68A);
  
  /* 에디터 전용 변수 */
  --editor-font-size: 14px;
  --editor-line-height: 1.5;
  --editor-border-radius: 4px;
  --editor-shadow: 0 2px 8px rgba(0,0,0,0.1);
}
```

## 반응형 breakpoint

```css
/* 모바일 우선 설계 */
@media (min-width: 576px) { /* sm */ }
@media (min-width: 768px) { /* md */ }
@media (min-width: 992px) { /* lg */ }
@media (min-width: 1200px) { /* xl */ }
```

## 통합 로드 방식

`editor-enhancements.css`에서 모든 모듈을 import:

```css
@import 'base/variables.css';
@import 'base/reset.css';
@import 'components/toolbar.css';
@import 'plugins/text-styles.css';
/* ... 기타 모듈들 */
```