# Natural Green Theme

희망씨 웹사이트를 위한 자연스러운 녹색 테마입니다.

자연친화적이고 따뜻한 느낌의 녹색 계열 색상으로 구성된 테마로, 노동권 보호와 공동체를 상징하는 디자인입니다.

## 구조

```
theme/natural-green/
├── components/          # 재사용 가능한 컴포넌트
│   └── hero-slider.php # Hero 슬라이더 컴포넌트
├── config/             # 테마 설정
│   └── hero-config.php # Hero 슬라이더 설정
├── pages/              # 페이지 템플릿
│   ├── home.php        # 홈페이지
│   └── content.php     # 일반 페이지
├── includes/           # 공통 포함 파일
│   ├── header.php      # 헤더
│   └── footer.php      # 푸터
├── assets/             # 정적 자원
└── styles/             # CSS 파일
```

## Hero Slider 사용법

### 기본 사용
```php
<?php
// 테마 설정 로드
$hero_config = include __DIR__ . '/../config/hero-config.php';

// 컴포넌트 포함
include __DIR__ . '/../components/hero-slider.php';
?>
```

### 커스터마이징
```php
<?php
// 테마 설정 로드
$hero_config = include __DIR__ . '/../config/hero-config.php';

// 페이지별 설정 변경
$hero_config['slide_count'] = 5;        // 5개 슬라이드만 표시
$hero_config['height'] = '600px';       // 높이 600px로 변경
$hero_config['auto_play'] = false;      // 자동재생 비활성화

// 컴포넌트 포함
include __DIR__ . '/../components/hero-slider.php';
?>
```

### 설정 옵션

#### 슬라이드 설정
- `slide_count`: 표시할 슬라이드 개수 (기본: 8)
- `auto_play`: 자동 재생 활성화 (기본: true)
- `auto_play_interval`: 자동 재생 간격 밀리초 (기본: 6000)

#### UI 요소
- `show_navigation`: 이전/다음 버튼 표시 (기본: true)
- `show_indicators`: 인디케이터 표시 (기본: true)
- `show_content_overlay`: 텍스트 오버레이 표시 (기본: true)

#### 스타일
- `height`: 슬라이더 높이 (기본: '450px')
- `border_radius`: 테두리 반경 (기본: 'rounded-2xl')
- `shadow`: 그림자 효과 (기본: 'shadow-xl')

#### 접근성
- `enable_keyboard_nav`: 키보드 네비게이션 (기본: true)
- `enable_touch_swipe`: 터치 스와이프 (기본: true)
- `pause_on_hover`: 마우스 호버시 일시정지 (기본: true)

## 새로운 테마 만들기

1. `theme/` 디렉토리에 새 폴더 생성
2. `natural-green` 폴더의 구조를 복사
3. `config/hero-config.php`에서 설정 수정
4. `components/hero-slider.php`의 스타일 커스터마이징
5. 필요시 완전히 새로운 hero 컴포넌트 생성

## 데이터베이스 요구사항

Hero 슬라이더는 `hopec_gallery` 테이블에서 데이터를 가져옵니다:
- `wr_id`: 게시글 ID
- `wr_subject`: 제목
- `wr_content`: 내용 (이미지 추출용)
- `wr_datetime`: 작성 날짜

## 성능 최적화

- 첫 번째 이미지는 `loading="eager"`, 나머지는 `loading="lazy"`
- 이미지 로딩 실패시 그라디언트 배경으로 폴백
- 자동 재생은 페이지가 숨겨지면 일시정지
- 터치 기기에서 스와이프 지원