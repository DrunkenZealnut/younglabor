# figma-style-sync Planning Document

> **Summary**: Figma 'Modern Brand Design Studio (Frame & Form)' 템플릿의 디자인 시스템을 청년노동자인권센터 사이트 전체에 적용 (전면 채택)
>
> **Project**: 청년노동자인권센터 (younglabor.kr)
> **Version**: N/A (no package.json — vanilla PHP)
> **Author**: 김창수 (with Claude Code)
> **Date**: 2026-07-21
> **Status**: Draft

---

## Executive Summary

| Perspective | Content |
|-------------|---------|
| **Problem** | 현재 사이트는 범용적인 스카이블루 테마로 시각적 인상이 약하고, 단체의 전문성과 신뢰를 전달하는 브랜드 아이덴티티가 부재함 |
| **Solution** | Figma 커뮤니티 템플릿 'Frame & Form'의 에디토리얼 디자인 시스템(초대형 타이포그래피, 헤어라인 그리드, 라임+퍼플+블랙/화이트 팔레트)을 전면 채택하여 CSS 테마 계층에서 교체 |
| **Function/UX Effect** | 히어로·사업 카드·통계·푸터가 매거진형 레이아웃으로 재구성되어 가독성과 시각적 위계가 강화됨. 기능(문의 폼, 동아리 신청)은 동일하게 유지 |
| **Core Value** | 코드 구조 변경 없이 `.env` 테마 변수 + 단일 CSS 교체로 "디자인 스튜디오급" 브랜드 인상 확보 — 유지보수 비용 최소화 |

---

## 1. Overview

### 1.1 Purpose

Figma 디자인 파일과 연동하여, 사이트의 시각 스타일(색상·타이포그래피·레이아웃 언어)을 'Frame & Form' 템플릿 스타일로 전환한다. **전면 채택** 방침 확정(2026-07-21 사용자 결정): 레이아웃 시스템과 팔레트 모두 Figma 디자인을 따른다.

### 1.2 Background

- 사이트는 최근 MSDS 서브앱을 제거하고 단체 홍보 멀티페이지(홈/소개/사업/소식/동아리 신청)로 재편됨
- 현재 스타일(`assets/css/style.css`, 568줄)은 스카이블루(#5BC0DE) 기반 범용 테마로, 단체 정체성을 담은 디자인 시스템이 없음
- 사용자가 Figma Sites 커뮤니티 템플릿을 스타일 기준으로 지정함

### 1.3 Related Documents

- **Figma 원본**: [Modern Brand Design Studio (Community)](https://www.figma.com/site/QTSNp5tyRjiX9k0vBayFfw/Modern-Brand-Design-Studio--Community-?node-id=0-1) — 홈 / `/project` / `/about` 3페이지, Desktop·Tablet·Mobile 반응형 프레임
- **참고 스크린샷**: `docs/01-plan/assets/figma-refs/` (홈 페이지 전체 8장, 2026-07-21 캡처)
- 사업 맥락: `docs/아름다운재단_청년노동자인권센터_2026_1차년도_사업계획_예산토론_회의록.md`

---

## 2. Figma 디자인 분석 (관찰 결과)

> 커뮤니티 원본 파일은 MCP 편집 권한이 없어 브라우저 캡처 기반으로 분석함. 정밀 토큰(정확한 hex/폰트 사이즈/간격)은 Design 단계에서 파일 복제 후 Figma MCP로 추출 예정.

### 2.1 컬러 팔레트 (근사값)

| 역할 | 관찰 색상 | 근사 hex | 용도 |
|------|----------|---------|------|
| Base BG | 화이트/오프화이트 | `#FFFFFF` | 페이지 배경 |
| Ink | 블랙 | `#111111` | 텍스트, 헤어라인, 푸터 워드마크 |
| Accent 1 | 라임/샤르트뢰즈 그린 | `~#D9F24F` (그라디언트: 라임→연노랑) | 프로젝트 카드 배경, 포인트 |
| Accent 2 | 딥 퍼플 | `~#5C0FC4` | 카드 배경, 대비 블록 |
| Accent 3 | 브라이트 그린 | `~#00A651` | 보조 (Farm Global 카드 등) |
| Muted | 라이트 그레이 | `~#F2F2EE` | 카드 placeholder 배경 |

### 2.2 타이포그래피

- **스타일**: 모던 그로테스크 산세리프 (Helvetica Now/Neue Montreal 계열), 타이트한 행간(~1.1), 큰 대비의 스케일
- **히어로 헤드라인**: 뷰포트 폭의 ~70%를 차지하는 초대형 (데스크톱 ~64-80px 추정)
- **통계 숫자**: 초대형 디스플레이 (1M+, 36, 100+ — ~96px급) + 소형 설명 캡션
- **본문/캡션**: ~14-16px, 레귤러
- **한국어 대응**: 사이트는 이미 Pretendard(모던 그로테스크 계열) 사용 중 → **Pretendard 유지**가 한국어 조판에 최적. 영문 워드마크·숫자도 Pretendard로 일관 처리

### 2.3 레이아웃 언어

| 패턴 | 설명 |
|------|------|
| 헤어라인 디바이더 | 1px 블랙 수평선으로 섹션·헤더·푸터 구획 (박스 그림자 없음) |
| 헤더 | 로고(좌) + 텍스트 내비(우), 고정 아님(스크롤 시 자연스럽게), 하단 헤어라인 |
| 히어로 | 기하학 심볼 2개 + 초대형 헤드라인 좌정렬, 넉넉한 화이트스페이스 |
| 카드 그리드 | 3열 이미지/컬러 블록 + "라벨 | 타이틀" 2열 캡션 + 설명문 + "See Project →" 텍스트 링크 |
| 통계 섹션 | 인트로 문장 + 헤어라인으로 구분된 초대형 숫자 리스트 |
| CTA | 상하 헤어라인 사이 한 줄 문장 ("Connect with us to explore…") |
| 푸터 | 심볼 + OFFICE/CONTACT/SOCIAL 3열 + **풀블리드 초대형 워드마크** |
| 모서리 | 라운딩 없음(0px), 플랫, 보더 최소화 |

### 2.4 현 사이트와의 매핑

| Figma 요소 | 현 사이트 대응 | 적용 방향 |
|-----------|--------------|----------|
| 히어로 헤드라인 | index.php 히어로 | 슬로건을 초대형 타이포로 재구성 |
| Our work 카드 | 사업소개(activities) 카드 | 사업 항목을 컬러 블록+캡션 카드로 |
| 통계 (1M+/36/100+) | (신규) | 단체 활동 지표로 치환 (예: 상담 건수, 캠페인, 참여 청년) |
| Work/About/Contact 내비 | 소개/사업/소식/동아리 신청 | 텍스트 내비 유지, 스타일만 전환 |
| See Project → | 자세히 보기 링크 | 화살표 텍스트 링크 패턴 |
| 푸터 워드마크 | (신규) | "청년노동자인권센터" 대형 워드마크 |

---

## 3. Scope

### 3.1 In Scope

- [ ] `.env` 3종의 `THEME_*` 변수를 새 팔레트로 교체 (+`config.php`의 `getThemeCSSVariables()` 확장 필요 시)
- [ ] `assets/css/style.css` 전면 재작성 (헤어라인 시스템, 타이포 스케일, 카드 그리드, 통계·CTA·워드마크 푸터)
- [ ] `includes/header.php` / `includes/footer.php` 마크업 조정 (헤어라인 헤더, 3열+워드마크 푸터)
- [ ] 공개 페이지 5종 적용: `index.php`, `about.php`, `activities.php`, `news.php`, `committee/index.php`
- [ ] 폼 요소(문의·동아리 신청) 스타일을 새 시스템에 맞게 (플랫, 헤어라인 보더)
- [ ] 반응형 대응 (Figma의 Desktop 1280+/Tablet 800–1279/Mobile 기준 유지)

### 3.2 Out of Scope

- `admin/` 패널 스타일 (내부 도구 — 별도 사이클로 분리)
- 콘텐츠 문구 변경 (텍스트는 현행 유지, 레이아웃 표현만 변경)
- news.php DB화 등 기능 개발 (TODOS.md 별도 항목)
- Figma 파일 자체의 편집·동기화 자동화 (일회성 스타일 이식)
- 사진·일러스트 신규 제작 (기존 assets 활용, placeholder는 컬러 블록으로)

---

## 4. Requirements

### 4.1 Functional Requirements

| ID | Requirement | Priority | Status |
|----|-------------|----------|--------|
| FR-01 | 새 팔레트(블랙/화이트/라임/퍼플)를 `.env` → CSS 변수 체인으로 주입 | High | Pending |
| FR-02 | 헤어라인 기반 헤더/푸터 공통 레이아웃 재구성 (푸터 대형 워드마크 포함) | High | Pending |
| FR-03 | 홈 히어로를 초대형 타이포 + 기하학 심볼 구성으로 전환 | High | Pending |
| FR-04 | 사업 카드 그리드(컬러 블록+캡션+화살표 링크) 적용 | High | Pending |
| FR-05 | 통계 섹션(초대형 숫자) 신설 | Medium | **Deferred** (지표 미확정, 2026-07-21 보류 결정) |
| FR-06 | about/news/committee 페이지에 동일 시스템 적용 | High | Pending |
| FR-07 | 폼 요소(입력·버튼) 플랫 스타일 전환 | Medium | Pending |
| FR-08 | 기하학 심볼 2종을 SVG로 제작(원형 교차·도트 링) | Medium | Pending |
| FR-09 | (Design 단계 선행조건) Figma 파일 복제 후 MCP로 정밀 토큰 추출 | High | Pending |

### 4.2 Non-Functional Requirements

| Category | Criteria | Measurement Method |
|----------|----------|-------------------|
| Performance | 외부 리소스 추가 없음 (폰트는 기존 Pretendard CDN 유지, 이미지 신규 0) | 네트워크 탭 요청 수 비교 |
| Accessibility | 라임 배경 위 텍스트는 블랙 사용 (WCAG AA 대비 4.5:1 이상), 퍼플 배경 위는 화이트 | 대비 검사 도구 |
| Compatibility | 기존 기능(문의/신청 폼, 페이지 트래킹) 무변경 동작 | 수동 브라우저 테스트 |
| Maintainability | 색상은 전부 CSS 변수 경유 — hex 하드코딩 금지 | 코드 리뷰 (`grep '#' style.css`) |

---

## 5. Success Criteria

### 5.1 Definition of Done

- [ ] 공개 페이지 5종이 새 디자인 시스템으로 렌더링
- [ ] 로컬(XAMPP) 수동 테스트: 전 페이지 + 문의/신청 폼 제출 정상
- [ ] 모바일(≤799px)/태블릿(800–1279px)/데스크톱(1280px+) 3구간 확인
- [ ] Gap 분석(Check) ≥ 90%

### 5.2 Quality Criteria

- [ ] 색상 하드코딩 0건 (CSS 변수만 사용)
- [ ] admin/ 파일 변경 0건
- [ ] PHP 로직(폼 처리, DB) diff 0건 — 마크업/CSS만 변경

---

## 6. Risks and Mitigation

| Risk | Impact | Likelihood | Mitigation |
|------|--------|------------|------------|
| Figma 커뮤니티 원본에 MCP 접근 불가 → 정밀 토큰 미확보 | Medium | **확정(현재 상태)** | 사용자가 파일을 본인 계정으로 **복제**(Figma에서 열기→복제)하면 해결. 불가 시 스크린샷 근사값으로 진행 |
| 인권단체와 '디자인 스튜디오' 미학의 톤 불일치 | Medium | Medium | 콘텐츠 문구는 유지하고 시각 언어만 이식. Check 단계에서 사용자 시각 검수 |
| 라임/퍼플 대비 문제 (접근성) | Medium | Low | NFR 대비 기준 준수, 라임 위 블랙·퍼플 위 화이트 고정 |
| style.css 전면 재작성 중 기존 페이지 깨짐 | High | Medium | 페이지별 순차 적용 + 각 단계 수동 확인. git 브랜치(`feature/site-reorganization`)에서 작업 |
| 통계 수치(1M+류)에 대응할 실제 지표 부재 | Low | Medium | FR-05에서 사용자에게 실제 지표 확인 후 확정. 없으면 섹션 보류 |

---

## 7. Architecture Considerations

### 7.1 Project Level Selection

| Level | Characteristics | Selected |
|-------|-----------------|:--------:|
| **Starter** | 정적 멀티페이지 + 최소 백엔드 | ✅ (기존 구조 유지) |
| Dynamic / Enterprise | — | ☐ |

> 프레임워크·빌드 도구 도입 없음. 기존 "Vanilla PHP + 단일 CSS" 아키텍처를 그대로 유지하고 **테마 계층만 교체**한다.

### 7.2 Key Architectural Decisions

| Decision | Options | Selected | Rationale |
|----------|---------|----------|-----------|
| 스타일 적용 방식 | 전면 채택 / 레이아웃만 / 하이브리드 | **전면 채택** | 사용자 결정 (2026-07-21) |
| 색상 주입 경로 | CSS 하드코딩 / `.env`→CSS 변수 | **`.env`→CSS 변수** | 기존 `getThemeCSSVariables()` 체인 재사용, 운영·로컬 일관성 |
| 폰트 | Figma 원본 영문 폰트 도입 / Pretendard 유지 | **Pretendard 유지** | 한국어 조판 필수, 이미 그로테스크 계열로 스타일 부합, 외부 의존 추가 없음 |
| 심볼 그래픽 | 이미지 다운로드 / 인라인 SVG 제작 | **인라인 SVG** | 커뮤니티 에셋 라이선스 회피, 색상 변수 연동 가능 |
| CSS 구조 | 다중 파일 분리 / 단일 style.css 유지 | **단일 유지** | 빌드 도구 없는 프로젝트 규모에 적정 (No-framework 원칙) |
| 정밀 토큰 소스 | 스크린샷 근사 / Figma MCP(복제 파일) | **MCP 우선, 근사 폴백** | FR-09, Design 단계 선행조건 |

### 7.3 변경 대상 파일 맵

```
.env / .env.local / .env.production   # THEME_* 팔레트 교체
config.php                            # getThemeCSSVariables() 변수 추가(필요 시)
assets/css/style.css                  # 전면 재작성 (핵심 작업)
includes/header.php                   # 헤어라인 헤더 마크업
includes/footer.php                   # 3열 + 대형 워드마크 푸터
index.php                             # 히어로/카드/통계/CTA 섹션 재구성
about.php, activities.php, news.php   # 섹션 마크업을 새 패턴으로
committee/index.php                   # 폼 스타일 클래스 적용
assets/js/ (기존 파일)                 # 변경 최소 (내비 토글 정도)
```

---

## 8. Convention Prerequisites

### 8.1 Existing Project Conventions

- [x] `CLAUDE.md` 코딩 컨벤션 존재 (Naming, 공통 레이아웃 패턴, CSS 변수 테마)
- [ ] ESLint/Prettier/tsconfig — 해당 없음 (vanilla PHP)

### 8.2 Conventions to Define/Verify

| Category | Current State | To Define | Priority |
|----------|---------------|-----------|:--------:|
| CSS 클래스 | kebab-case (기존) | 신규 컴포넌트 접두어 (`hero-`, `stat-`, `wordmark-` 등) | High |
| CSS 변수 | `--color-*` 6종 | `--color-accent-lime`, `--color-accent-purple` 등 추가 정의 | High |
| 타이포 스케일 | 없음(개별 지정) | `--fs-display`, `--fs-h1`… 스케일 변수 도입 여부 | Medium |

### 8.3 Environment Variables Needed

| Variable | Purpose | Scope | To Be Created |
|----------|---------|-------|:-------------:|
| `THEME_PRIMARY` (기존, 값 교체) | Ink 블랙 `#111111` | 전역 | 값 변경 |
| `THEME_ACCENT` (기존, 값 교체) | 라임 그린 | 전역 | 값 변경 |
| `THEME_SECONDARY` (기존, 값 교체) | 딥 퍼플 | 전역 | 값 변경 |
| `THEME_BACKGROUND` (기존, 값 교체) | 화이트 | 전역 | 값 변경 |
| (신규 검토) `THEME_ACCENT_GREEN` | 보조 그린 | 전역 | ☐ Design 단계 결정 |

---

## 9. Next Steps

1. [ ] **(사용자 액션)** Figma 커뮤니티 파일을 본인 계정으로 복제 후 복제본 URL 공유 → MCP 정밀 토큰 추출 가능
2. [ ] `/pdca design figma-style-sync` — 토큰 확정값·페이지별 상세 레이아웃·컴포넌트 명세 작성
3. [ ] 통계 섹션(FR-05)에 넣을 실제 활동 지표 확인
4. [ ] 구현(Do) → Gap 분석(Check) → 필요 시 반복(Act)

---

## Version History

| Version | Date | Changes | Author |
|---------|------|---------|--------|
| 0.1 | 2026-07-21 | 초안 — Figma 홈 페이지 분석, 전면 채택 방침 확정 | 김창수 + Claude |
