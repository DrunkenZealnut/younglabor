# figma-style-sync Design Document

> **Summary**: Frame & Form 디자인 시스템의 토큰·컴포넌트·페이지별 구현 명세 (전면 채택)
>
> **Project**: 청년노동자인권센터 (younglabor.kr)
> **Version**: N/A (vanilla PHP)
> **Author**: 김창수 (with Claude Code)
> **Date**: 2026-07-21
> **Status**: Draft
> **Planning Doc**: [figma-style-sync.plan.md](../../01-plan/features/figma-style-sync.plan.md)

---

## 1. Overview

### 1.1 Design Goals

- Figma 'Frame & Form' 템플릿의 시각 언어를 픽셀 근거 기반으로 이식 (색상은 스크린샷 실측값 사용)
- PHP 마크업 변경 최소화: 공통 레이아웃(header/footer) + 페이지 섹션 마크업 조정 + `style.css` 전면 재작성
- 기능 무손상: 폼 제출 JS·API·PageTracker 로직은 diff 0

### 1.2 Design Principles

- **헤어라인 우선**: 그림자·라운딩·배경색 블록 대신 1px 블랙 라인으로 구획
- **타이포가 곧 그래픽**: 초대형 헤드라인·숫자·워드마크가 시각 주인공
- **색은 블록에만**: 텍스트는 블랙/화이트, 라임·퍼플은 면(블록 배경)으로만 사용
- **토큰 단일 소스**: 모든 색상은 `.env` → `getThemeCSSVariables()` → CSS 변수 경유

---

## 2. Design Tokens (확정값)

### 2.1 색상 — 스크린샷 픽셀 실측 (2026-07-21, PNG 디코더 추출)

| 토큰 | 값 | 근거 (샘플 좌표) | 용도 |
|------|-----|----------------|------|
| `--color-bg` | `#FFFFFF` | s2(600,520) | 페이지·섹션 배경 (alt 배경 없음) |
| `--color-ink` | `#111111` | 워드마크 실측 `#000000`, 본문 가독성 위해 +7% 밝기 | 텍스트·헤어라인·버튼 배경 |
| `--color-ink-pure` | `#000000` | s7(540,455) 외 3점 | 워드마크·초대형 디스플레이 전용 |
| `--color-lime` | `#CEE84F` | s3(300,718) | 대표 액센트 (카드 블록·포인트) |
| `--color-lime-soft` | `#F8FF9F` | s4(450,100) | 라임 그라디언트 종점 |
| `--color-lime-deep` | `#AECE2B` | s3(260,700) | 라임 그라디언트 시점 |
| `--color-purple` | `#430086` | s4 4점 전부 일치 | 대비 블록 배경 |
| `--color-green` | `#18B201` | s5(60,60)·(120,100)·(880,100) | 보조 블록 (텍스트 없는 장식 전용) |
| `--color-muted` | `#F4F3EE` | 근사 (저줌 관찰) | 빈 블록·placeholder 배경 |
| `--color-gray` | `#767676` | 관례값 | 캡션·보조 텍스트 (AA 충족) |

**그라디언트 정의**: `--grad-lime: linear-gradient(160deg, #AECE2B 0%, #CEE84F 45%, #F8FF9F 100%)` (radial 변형 허용)

### 2.2 접근성 대비 규칙 (필수 준수)

| 배경 | 허용 텍스트 색 | 대비 |
|------|--------------|------|
| `#FFFFFF` | `#111111`, `#767676`(보조만) | 18.9:1 / 4.5:1 ✅ |
| `#CEE84F` (라임) | **블랙만** `#111111` | 14.2:1 ✅ |
| `#430086` (퍼플) | **화이트만** `#FFFFFF` | 12.4:1 ✅ |
| `#18B201` (그린) | **블랙만** `#111111` (화이트는 2.5:1 미달로 금지) | 8.0:1 ✅ |

### 2.3 타이포그래피 (65% 스크린샷 역산)

폰트: **Pretendard 유지** (기존 CDN). 그로테스크 스타일 부합, 한국어 필수.

| 토큰 | 값 | 근거/용도 |
|------|-----|----------|
| `--fs-display` | `clamp(2.75rem, 6.5vw, 5rem)` | 히어로 헤드라인 (실측 ~64-70px@1440) |
| `--fs-stat` | `clamp(3.5rem, 9vw, 7rem)` | 통계 숫자 (실측 ~108px@1440) |
| `--fs-wordmark` | `clamp(2.5rem, 10.5vw, 9.5rem)` | 푸터 워드마크 (풀블리드, 실측 ~138px@1440) |
| `--fs-h2` | `clamp(1.5rem, 3vw, 2rem)` | 섹션 인트로 문장 |
| `--fs-body` | `1rem` (16px) | 본문 |
| `--fs-caption` | `0.9375rem` (15px) | 내비·캡션·라벨 |
| 행간 | 디스플레이 `1.08`, 본문 `1.6` | Figma 타이트 행간 재현 |
| 자간 | 디스플레이 `-0.03em`, 워드마크 `-0.04em` | 그로테스크 타이트 자간 (워드마크는 초대형이라 추가 타이트) |
| 웨이트 | 디스플레이 500-600, 본문 400, 라벨 500 | Figma는 미디엄급 (800 금지 — 현 hero 800에서 하향) |

### 2.4 레이아웃 상수

| 토큰 | 값 |
|------|-----|
| `--container-max` | `1376px` (Figma 1440 - 여백 32×2) |
| `--space-gutter` | `2rem` (32px, 모바일 1.25rem) |
| `--space-section` | `clamp(4rem, 8vw, 7rem)` |
| `--hairline` | `1px solid var(--color-ink)` |
| 라운딩 | **0** (전면 제거) |
| 그림자 | **없음** (전면 제거) |
| 카드 그리드 gap | `0.75rem` (12px — Figma 타이트 간격) |

### 2.5 브레이크포인트 (Figma 프레임 기준)

| 이름 | 범위 | 미디어쿼리 |
|------|------|-----------|
| Desktop | 1280px+ | 기본 |
| Tablet | 800–1279px | `@media (max-width: 1279px)` |
| Mobile | <800px | `@media (max-width: 799px)` |

> 기존 768px 단일 브레이크포인트를 위 2개로 교체.

---

## 3. `.env` / config.php 변경 명세

### 3.1 THEME_* 값 교체 (.env / .env.local / .env.production 3종 동일)

```bash
# 변경 전 → 변경 후
THEME_PRIMARY=#5BC0DE        →  THEME_PRIMARY=#111111        # 잉크(액션·버튼)
THEME_PRIMARY_DARK=#3498DB   →  THEME_PRIMARY_DARK=#000000   # 순검정(디스플레이)
THEME_SECONDARY=#87CEEB      →  THEME_SECONDARY=#430086      # 퍼플
THEME_ACCENT=#F0A500         →  THEME_ACCENT=#CEE84F         # 라임
THEME_TEXT_DARK=#333333      →  THEME_TEXT_DARK=#111111
THEME_TEXT_LIGHT=#FFFFFF     →  (유지)
THEME_BACKGROUND=#E8F4F8     →  THEME_BACKGROUND=#FFFFFF     # alt 배경 폐지
THEME_BACKGROUND_ALT=#FFFFFF →  (유지)
# 신규 3키
THEME_ACCENT_SOFT=#F8FF9F
THEME_ACCENT_DEEP=#AECE2B
THEME_ACCENT_GREEN=#18B201
```

### 3.2 config.php

- `$theme` 배열에 `accent_soft`, `accent_deep`, `accent_green` 키 추가 (기본값 위 표)
- `getThemeCSSVariables()`에 `--color-accent-soft`, `--color-accent-deep`, `--color-accent-green` 3줄 추가
- 기타 로직 변경 없음

> style.css 내부에서는 의미론적 별칭 사용: `--color-ink: var(--color-primary)` 식 재선언으로 기존 변수명과 신규 별칭 공존.

---

## 4. 공통 컴포넌트 명세

### 4.1 Header (`includes/header.php` + CSS)

```
┌──────────────────────────────────────────────┐
│ 청년노동자인권센터        단체소개 사업소개 소식 동아리 신청 │ ← 높이 64px
├──────────────────────────────────────────────┤ ← 1px 블랙 헤어라인
```

- `position: sticky; top: 0` 유지 (UX), **블러·그림자 제거**, `background: var(--color-bg)` 불투명, `border-bottom: var(--hairline)`
- 로고: 15px, weight 600, 블랙 (기존 1.3rem 파랑 → 변경)
- 내비 링크: 15px, weight 400, 블랙. hover/active: **밑줄** (`text-underline-offset: 4px`) — 색상 변화 없음
- `.nav-cta` (동아리 신청): 필(pill) 제거 → **블랙 배경 사각 버튼** (`background: var(--color-ink); color: #fff; padding: 0.5rem 1rem; border-radius: 0`)
- 마크업 변경: 없음 (클래스 유지, CSS만 재정의)
- 모바일: 햄버거 유지, 드롭다운 패널은 화이트 배경 + 상하 헤어라인 (블러·그림자 제거)

### 4.2 심볼 SVG (신규, `includes/header.php` 하단 또는 각 페이지 히어로)

인라인 SVG 2종, `currentColor` 사용 (블랙):

```html
<!-- symbol-a: 원 안 4잎 교차 (56×56) -->
<svg class="brand-symbol" viewBox="0 0 56 56" width="56" height="56" aria-hidden="true">
  <circle cx="28" cy="28" r="27" fill="none" stroke="currentColor" stroke-width="2"/>
  <path d="M28 1 A27 27 0 0 1 55 28 A27 27 0 0 1 28 1 M28 55 A27 27 0 0 1 1 28 A27 27 0 0 1 28 55" fill="currentColor" fill-rule="evenodd"/>
</svg>
<!-- symbol-b: 8도트 링 (56×56) -->
<svg class="brand-symbol" viewBox="0 0 56 56" width="56" height="56" aria-hidden="true">
  <!-- 8개 원: 중심 (28+18cos(k·45°), 28+18sin(k·45°)), r=9, fill=currentColor -->
</svg>
```

> 정확한 path는 Do 단계에서 시각 대조하며 조정. 규격 고정: 56×56, 2개 나란히 gap 12px.

### 4.3 Footer (`includes/footer.php` 재작성)

```
├──────────────────────────────────────────────┤ ← 헤어라인
│ ◐✿   CONTACT           MENU                  │
│      email@…           단체소개 / 사업소개 /   │
│      대표: 김창수        소식 / 동아리 신청     │
│                                              │
│ 청년노동자인권센터                              │ ← 워드마크 (풀폭, #000)
│ © 2026 …                                     │
└──────────────────────────────────────────────┘
```

- 배경 **화이트** (기존 다크 배경 폐지), 텍스트 블랙, 상단 헤어라인
- 3열 그리드: 심볼 | CONTACT(라벨 13px 대문자 + 이메일 밑줄링크 + 대표명) | MENU(내비 4링크 세로)
- `.footer-wordmark`: 단체명, `font-size: var(--fs-wordmark)`, weight 600, letter-spacing -0.04em, `color: var(--color-ink-pure)`, line-height 1, 줄바꿈 없이 한 줄
- 카피라이트: 13px gray
- Figma의 OFFICE/SOCIAL 열은 해당 정보 부재로 CONTACT/MENU로 치환 (콘텐츠 확보 시 확장)

### 4.4 Work Card (사업 카드 — index·activities 공용)

```
┌────────────┐
│ 컬러 블록    │ ← aspect-ratio 4/5 (모바일 1열에서는 4/3), 라운딩 0
│            │    배경 로테이션: ①--grad-lime ②--color-purple
│            │                  ③--color-muted ④--color-green
├────────────┤
 사업 01   노동안전보건 교과서      ← 라벨(gray 15px) + 타이틀(블랙 16px 600)
 설명문 2-3줄 (15px, gray)
 자세히 보기 →                    ← 블랙 15px, hover 밑줄
```

- 클래스: `.work-grid` / `.work-card` / `.work-card-block` / `.work-card-meta` / `.work-card-title` / `.work-card-desc` / `.work-card-link`
- 블록 내부 텍스트: 라임·뮤티드·그린 위 블랙 대형 숫자("01" 등, 3rem), 퍼플 위 화이트 (그린 위 화이트 금지)
- 그리드: Desktop 4열 / Tablet 2열 / Mobile 1열, gap 12px
- hover: 블록 미세 스케일 없음 — 링크 밑줄만 (모션 최소주의)
- 기존 `.card`(라운딩+그림자) 계열은 재정의로 대체, `.card-number` 원형 뱃지 폐지

### 4.5 Stats (통계 — 신규 컴포넌트) — **보류 (2026-07-21 사용자 결정)**

> 실제 지표 수치 미확정으로 이번 사이클에서 **구현하지 않음**. 아래 명세는 추후 지표 확보 시 재사용을 위해 보존. Gap 분석에서 미구현을 결함으로 계산하지 않는다.

```
 우리는 반도체산업 청년노동자와 함께   ← 인트로 (--fs-h2, 2줄, max-width 60%)
 안전할 권리를 만들어갑니다.
├──────────────────────────────┤ ← 헤어라인
│ 5           전국 반도체고 방문 학교 │ ← 숫자(--fs-stat, #000) + 캡션(15px gray, 우측 정렬)
├──────────────────────────────┤
│ 4           핵심사업             │
├──────────────────────────────┤
│ 2026        활동 시작 연도        │
```

- 클래스: `.stats` / `.stats-intro` / `.stat-row` / `.stat-num` / `.stat-label`
- 각 행: `border-top: var(--hairline)`, flex justify-between, 세로 패딩 2rem

### 4.6 CTA 라인

```
├──────────────────────────────┤
│ 함께하고 싶으시다면 연락주세요 →   │ ← --fs-h2, 블랙
├──────────────────────────────┤
```

- `.cta-line`: 상하 헤어라인, 패딩 3rem 0, 화살표 포함 링크 전체 클릭, hover 밑줄

### 4.7 Forms (문의·동아리 신청 공용)

- 인풋/텍스트영역: 박스 보더 폐지 → **언더라인 스타일** `border: 0; border-bottom: 1px solid var(--color-ink); border-radius: 0; padding: 0.75rem 0; background: transparent`
- focus: `box-shadow: 0 1px 0 0 var(--color-ink)` (시각적 2px 언더라인 — border 두께 변경 시 발생하는 레이아웃 시프트 회피)
- 라벨: 15px, gray, 대문자 스타일 아님 (한국어)
- 제출 버튼: `.btn-submit` — 블랙 배경, 화이트 텍스트, 라운딩 0, 패딩 1rem 2rem, hover 시 `background: var(--color-purple)`
- `.btn-cta` / `.btn-secondary`: 동일 시스템 (secondary는 1px 블랙 보더 + 투명 배경, hover 블랙 채움)

### 4.8 재정의·폐지 클래스 맵

| 기존 클래스 | 처리 |
|------------|------|
| `.section-alt` (하늘색 배경) | 배경 제거 → 헤어라인 `border-top` 구획으로 대체 |
| `.card`, `.card-number` | `.work-card` 시스템으로 재정의 (마크업의 클래스명은 신규로 교체) |
| `.page-header` (서브페이지 제목) | 히어로형으로 재정의: 심볼 + `--fs-display` 제목 + 하단 헤어라인 |
| `.timeline` (파란 선·점) | 블랙 헤어라인·블랙 사각 마커로 재스킨 (구조 유지) |
| `.person-card` (좌측 파란 보더) | 헤어라인 상단 보더의 플랫 리스트로 재스킨 |
| `.news-item` (박스+라운딩) | 헤어라인 구분 리스트 행으로 재정의 (라운딩·보더 박스 폐지) |
| `.news-category` (파란 뱃지) | 라임 배경+블랙 텍스트 사각 태그 |
| `.fade-in` | 유지 (기존 IntersectionObserver 재사용) |

---

## 5. 페이지별 적용 명세

### 5.1 index.php

| 순서 | 섹션 | 변경 |
|------|------|------|
| 1 | 히어로 | 그라디언트 배경 제거 → 화이트. 심볼 2개 + 슬로건을 `--fs-display`로 (`min-height` 제거, 콘텐츠 기반 높이 + 하단 헤어라인). 기존 `.btn-cta` 2개 유지(신규 스타일) |
| 2 | 핵심사업 | "핵심사업" 라벨(15px) + `.work-grid` 4열 카드 (블록 로테이션: 라임grad/퍼플/뮤티드/그린) |
| 3 | ~~통계 (신규)~~ | **보류** — 지표 미확정 (4.5 참조) |
| 4 | 연락하기 | `.section-alt` 배경 제거, 상단 헤어라인. 폼 언더라인 스타일. 2열 그리드 유지 |
| — | 인라인 `<style>` | 홈 전용 스타일 블록 제거/축소 (공통 CSS로 이관) |

### 5.2 about.php

- `.page-header` → 신규 히어로형 (심볼 + "단체소개" 디스플레이 타이포)
- 문제정의: 본문을 `--fs-h2` 인트로 문단 스타일로
- 설립목적 4카드: `.work-grid` 4열(블록 없이 캡션형 카드 — 번호는 "01" 대형 텍스트로)
- 단체 차별성: 헤어라인 구분 리스트
- `.section-alt` 배경 전부 제거

### 5.3 activities.php

- `.page-header` → 히어로형
- 4사업 카드: index와 동일한 `.work-grid` + 컬러 블록 (재사용)
- 3개년 로드맵 타임라인: 재스킨(블랙 마커)

### 5.4 news.php

- `.page-header` → 히어로형
- 뉴스 리스트: 헤어라인 행 (`border-top`, 날짜 gray | 제목 블랙 600 | 카테고리 라임 태그)

### 5.5 committee/index.php

- 자체 인라인 스타일이 많음 → 페이지 상단 인라인 CSS를 공통 토큰 기반으로 정리
- `.page-title` → `--fs-display`, 폼은 4.7 언더라인 시스템, `.btn-home`·`.btn-submit` 블랙 버튼
- 폼 로직(JS·검증·API 호출) **무변경**

---

## 6. Error Handling / 회귀 방지

| 위험 | 대응 |
|------|------|
| CSS 전면 교체로 인한 페이지 깨짐 | 페이지 순서: style.css+header/footer → index → about/activities/news → committee. 각 단계 브라우저 확인 후 다음 진행 |
| 폼 스타일 변경이 JS 셀렉터에 영향 | 클래스명 유지 원칙 (`.btn-submit`, `.form-group` 등 셀렉터 불변) |
| `.env` 3종 불일치 | 3파일 동시 수정, `test-config.php`로 로드 확인 |
| 캐시된 구 CSS | `style.css?v=2` 쿼리스트링 버전 파라미터 도입 (header.php) |

---

## 7. Security Considerations

- [x] 마크업 출력은 기존 `htmlspecialchars()` 패턴 유지 (신규 echo 지점 포함)
- [x] 인라인 SVG는 정적 코드 (사용자 입력 미포함)
- [x] 폼 검증·CSRF·API 로직 무변경
- [ ] 신규 외부 리소스 **0건** 확인 (폰트·이미지 CDN 추가 금지)

---

## 8. Test Plan (수동)

### 8.1 기능 회귀

- [ ] 문의 폼 제출 → 성공 alert + 메일 수신
- [ ] 동아리 신청 제출 → 완료 화면
- [ ] 내비 active 상태·모바일 햄버거 토글
- [ ] PageTracker 기록 (admin 통계 확인)

### 8.2 시각 검증 (Figma 대조)

- [ ] 데스크톱 1440: 히어로·카드·통계·푸터 워드마크가 참조 스크린샷(`docs/01-plan/assets/figma-refs/`)과 구조 일치
- [ ] Tablet 1024 / Mobile 390: 그리드 재배치 (4→2→1열), 워드마크 넘침 없음
- [ ] 대비 규칙 준수 (2.2 표) — 특히 그린 블록에 텍스트 없음
- [ ] 라운딩·그림자 잔존 0건 (`grep -E 'border-radius|box-shadow' style.css` — 0 또는 `0` 값만)

---

## 9. Architecture (프로젝트 규약 적용)

| 항목 | 결정 |
|------|------|
| Layer | 해당 없음 (Starter — 단일 CSS + PHP 템플릿) |
| 파일 구조 | 기존 유지: `assets/css/style.css` 단일 파일 (섹션 주석으로 구획: Tokens/Base/Header/Hero/Work/Stats/CTA/Forms/Footer/Pages/Responsive) |
| 네이밍 | CSS 클래스 kebab-case, 신규 접두어: `work-`, `stat-`, `cta-`, `brand-`, `footer-wordmark` |
| 주석 | 한국어 (기존 규약) |

---

## 10. Implementation Order (Do 단계 체크리스트)

1. [ ] `.env` 3종 THEME_* 교체 + 신규 3키 / `config.php` `$theme`·`getThemeCSSVariables()` 확장
2. [ ] `style.css` 전면 재작성 (토큰 → 베이스 → 컴포넌트 → 페이지 → 반응형)
3. [ ] `includes/header.php`: CSS 버전 파라미터, 심볼 SVG 파셜 추가
4. [ ] `includes/footer.php`: 3열 + 워드마크 구조로 재작성
5. [ ] `index.php`: 히어로 재구성, work-grid 교체, 인라인 스타일 정리 (stats 보류)
6. [ ] `about.php` / `activities.php` / `news.php`: 히어로형 헤더 + 클래스 교체
7. [ ] `committee/index.php`: 인라인 스타일 토큰화 + 폼 스킨
8. [ ] 수동 테스트 (8장) → `/pdca analyze figma-style-sync`

---

## Version History

| Version | Date | Changes | Author |
|---------|------|---------|--------|
| 0.1 | 2026-07-21 | 초안 — 색상 실측 토큰, 컴포넌트·페이지별 명세, 구현 순서 | 김창수 + Claude |
| 0.2 | 2026-07-21 | 통계 섹션 보류 확정 (사용자 결정) — 명세는 보존, 이번 사이클 미구현 | 김창수 + Claude |
| 0.3 | 2026-07-21 | Gap 분석 반영 동기화 — 워드마크 자간 -0.04em, 푸터 라벨/카피 13px, 카드 비율 4/5, focus box-shadow 기법 명문화 | 김창수 + Claude |
