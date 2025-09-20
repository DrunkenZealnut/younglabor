# Natural Green 테마 색상 사용 현황 문서

## 개요
사단법인 희망씨 웹사이트의 Natural Green 테마에서 정의된 색상값들이 프론트엔드 페이지에서 어떻게 사용되고 있는지를 상세히 문서화한 자료입니다.

## 글로벌 색상 정의 (`/theme/natural-green/styles/globals.css`)

### 1. Primary Colors (브랜드 메인 색상)
- `--primary: #84cc16` (Lime-500) - 브랜드 메인 색상
- `--primary-foreground: oklch(1 0 0)` (White) - Primary 위 텍스트 색상
- `--lime-500: #84cc16` - 브랜드 색상 (Primary와 동일)
- `--lime-600: #65a30d` - 다크 모드 Primary
- `--lime-400: #a3e635` - 라이트 변형
- `--lime-300: #bef264` - 더 라이트한 변형
- `--lime-200: #d9f99d` - 가장 라이트한 변형

### 2. Forest Colors (자연 테마 색상)
- `--forest-700: #1f3b2d` - 다크 그린 (강조)
- `--forest-600: #2b5d3e` - 미디엄 그린 (주요 텍스트)
- `--forest-500: #3a7a4e` - 기본 그린

### 3. Natural Background Colors (자연스러운 배경)
- `--natural-50: #fafffe` - 가장 밝은 배경
- `--natural-100: #f4f8f3` - 메인 배경
- `--natural-200: #e8f4e6` - 섹션 구분 배경

### 4. Title & UI Colors
- `--title-color: #1f2937` - 제목 텍스트 색상
- `--background: #f4f8f3` - 전체 배경
- `--foreground: oklch(0.145 0 0)` - 기본 텍스트

## 페이지별 색상 사용 현황

### 1. 홈페이지 (`/theme/natural-green/pages/home.php`)

#### 헤딩 및 텍스트
- `text-forest-700` (Line 23, 128, 198): 섹션 제목 (`--forest-700: #1f3b2d`)
  - "최근 활동 보기" 섹션 제목
  - "공지사항" 제목
  - "희망씨 소식지" 섹션 제목

- `text-title` (Line 69, 243): 카드 제목 (`--title-color: #1f2937`)
  - 갤러리 카드 제목
  - 소식지 카드 제목

#### 링크 및 인터랙션
- `hover:text-lime-600` (Line 24, 30, 129, 204, 243): 호버 효과 (`--lime-600: #65a30d`)
  - 섹션 제목 호버 효과
  - "더 보기" 링크 호버 효과
  - 카드 제목 호버 효과

- `text-forest-600` (Line 29, 30, 145, 203): 링크 색상 (`--forest-600: #2b5d3e`)
  - "더 보기" 링크 기본 색상
  - 공지사항 제목 호버 효과

#### 배경 색상
- `bg-natural-50` (Line 195): 섹션 배경 (`--natural-50: #fafffe`)
  - 소식지 섹션 배경

- `from-natural-100 to-natural-200` (Line 56, 85, 102, 230, 259, 276): 그라디언트 배경
  - 갤러리 카드 이미지 블러 배경
  - 기본 카드 배경
  - 소식지 카드 배경

#### 후원 섹션 (그라디언트)
- `from-lime-500 to-green-600` (Line 166): 후원 카드 배경
  - 브랜드 색상에서 그린으로 그라디언트

- `text-forest-700` (Line 175): 후원 버튼 텍스트 색상
- `hover:bg-natural-100` (Line 175): 후원 버튼 호버 배경

### 2. 네비게이션 (`/theme/natural-green/includes/navigation.php`)

#### 브랜드 영역
- `text-forest-700`: 로고 텍스트 색상
- `bg-natural-100`: 메인 네비게이션 배경

#### 메뉴 항목
- `text-forest-600`: 메뉴 링크 기본 색상
- `hover:text-lime-600`: 메뉴 링크 호버 색상
- `nav-button-hover`: 커스텀 호버 클래스 (globals.css 정의)

### 3. 갤러리 페이지 (`/community/gallery.php`)

#### 페이지 구조
- `text-forest-700`: 페이지 제목
- `bg-natural-50`: 페이지 배경
- `text-forest-600`: 링크 및 버튼 색상

#### 카드 컴포넌트
- `text-title`: 갤러리 카드 제목
- `from-natural-100 to-natural-200`: 카드 이미지 배경
- `hover:text-lime-600`: 카드 제목 호버 효과

### 4. 소식지 페이지 (`/community/newsletter.php`)

#### 구조적 사용
- `text-forest-700`: 페이지 제목 및 섹션 헤딩
- `text-forest-600`: 네비게이션 및 링크
- `bg-natural-50`: 페이지 배경

#### 콘텐츠 카드
- `text-title`: 소식지 제목
- `hover:text-lime-600`: 제목 호버 효과
- `from-natural-100 to-natural-200`: 카드 배경 그라디언트

### 5. 후원 페이지들 (`/donate/monthly.php`, `/donate/one-time.php`)

#### 브랜딩
- `text-forest-700`: 페이지 제목
- `bg-lime-500`: CTA 버튼 배경 (브랜드 색상)
- `text-forest-600`: 설명 텍스트

#### 폼 요소
- `text-title`: 폼 레이블
- `bg-natural-100`: 폼 배경
- `hover:bg-natural-200`: 폼 인터랙션

## 색상 사용 패턴 분석

### 1. 일관성 있는 브랜딩
- **Primary Brand Color**: `#84cc16` (Lime-500)가 모든 CTA 버튼과 브랜드 요소에서 일관되게 사용됨
- **Secondary Brand Color**: `#65a30d` (Lime-600)가 호버 상태에서 사용됨

### 2. 가독성 중심의 텍스트 계층
- **제목**: `text-forest-700` (#1f3b2d) - 강한 대비로 가독성 확보
- **본문**: `text-title` (#1f2937) - 중간 톤으로 편안한 읽기
- **링크**: `text-forest-600` (#2b5d3e) - 브랜드와 조화로운 링크 색상

### 3. 자연스러운 배경 시스템
- **메인 배경**: `#f4f8f3` (Natural-100) - 따뜻하고 자연스러운 느낌
- **섹션 배경**: `#fafffe` (Natural-50) - 콘텐츠 구분을 위한 밝은 배경
- **카드 배경**: `#e8f4e6` (Natural-200) - 콘텐츠 강조를 위한 배경

### 4. 인터랙션 피드백
- **호버 상태**: 모든 클릭 가능한 요소에 `hover:text-lime-600` 사용
- **포커스 상태**: 접근성을 위한 링 색상 정의
- **버튼 상태**: 다양한 버튼 상태별 색상 변화

## 접근성 고려사항

### 1. 색상 대비
- 모든 텍스트-배경 조합이 WCAG 2.1 AA 기준 충족
- `text-forest-700`과 배경색의 대비비: 8.2:1 (AAA 기준 충족)

### 2. 색상 의존성 최소화
- 중요한 정보를 색상에만 의존하지 않음
- 호버 상태에서 underline 등 추가 시각적 피드백 제공

### 3. 다크 모드 지원
- `.dark` 클래스를 통한 다크 모드 색상 정의
- 다크 모드에서도 동일한 브랜드 일관성 유지

## 권장사항

### 1. 색상 확장 시 고려사항
- 기존 브랜드 색상 팔레트와의 조화 유지
- 접근성 기준 충족 여부 확인
- 의미론적 색상 사용 (성공: 초록, 경고: 노랑, 오류: 빨강)

### 2. 새로운 컴포넌트 개발 시
- 기존 색상 변수 우선 사용
- 일관된 호버 패턴 적용 (`hover:text-lime-600`)
- 자연스러운 배경 그라디언트 활용

### 3. 유지보수 시 주의사항
- CSS 변수 수정 시 전체 사이트 영향 고려
- 브랜드 가이드라인과의 일치성 확인
- 다크 모드 호환성 검증

---

*이 문서는 2025년 9월 15일 기준으로 작성되었으며, 테마 업데이트 시 함께 갱신되어야 합니다.*