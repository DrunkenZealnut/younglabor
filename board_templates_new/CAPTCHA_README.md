# 캡차 시스템 (Captcha System)

board_templates에 통합된 자동등록방지 캡차 시스템입니다.

## 🎯 주요 기능

- ✅ **조건부 활성화**: `write_level = 0`인 게시판에만 자동 적용
- 🎨 **테마 연동**: 사이트 테마색상 자동 반영 (황금빛 톤)
- 🔄 **새로고침**: 버튼 클릭으로 새 코드 생성
- 🔊 **음성 지원**: Web Speech API 활용한 접근성 개선
- 🌍 **다국어**: 한국어/영어 지원 (확장 가능)
- 📱 **반응형**: 모바일 친화적 UI
- ⏰ **세션 관리**: 5분 제한시간, 재사용 방지

## 📂 파일 구조

```
board_templates/
├── CaptchaManager.php      # 캡차 생성/검증 클래스
├── captcha_helper.php      # 헬퍼 함수들
├── captcha_lang.php        # 다국어 메시지
├── captcha_image.php       # 이미지 생성 API
├── captcha_audio.php       # 음성 지원 API
├── captcha_test.php        # 테스트 페이지
├── assets/board-theme.css  # 캡차 스타일링
└── CAPTCHA_README.md       # 이 문서
```

## 🚀 사용 방법

### 1. 기본 설정

```php
// write_form.php에 추가됨
require_once 'captcha_helper.php';

// 캡차 필요 여부 확인
$need_captcha = is_captcha_required($board_id, $category_type);
```

### 2. UI 렌더링

```php
<?php if ($need_captcha): ?>
    <?php echo render_captcha_ui(); ?>
<?php endif; ?>
```

### 3. 검증 처리

```php
// post_handler.php에서 자동 처리됨
if ($need_captcha) {
    if (!verify_captcha($_POST['captcha_code'])) {
        throw new Exception(get_captcha_message('error_invalid'));
    }
}
```

## 🎛 캡차 활성화 조건

### 자동 활성화 (기본)
- `write_level = 0` (공개 글쓰기) 게시판
- 로그인하지 않은 사용자

### 수동 제어
```php
// 강제 활성화
$need_captcha = true;

// 강제 비활성화  
$need_captcha = false;
```

## 🎨 테마색상 커스터마이징

CSS 변수로 쉽게 커스터마이징 가능:

```css
:root {
    --theme-primary: #FBBF24;        /* 새로고침 버튼 */
    --theme-secondary: #F97316;      /* 음성 버튼 */
    --theme-border-light: #FDE68A;   /* 테두리 */
    --theme-bg-secondary: #FEF3C7;   /* 배경 */
}
```

## 🔧 고급 설정

### 1. 캡차 옵션 변경

```php
$captcha = new CaptchaManager(
    $length = 4,      // 코드 길이 
    $width = 120,     // 이미지 너비
    $height = 40      // 이미지 높이
);
```

### 2. 다국어 추가

```php
// captcha_lang.php에 언어 추가
$captcha_messages['ja'] = [
    'label' => 'スパム防止認証',
    'placeholder' => '左の数字を入力してください',
    // ...
];
```

### 3. 커스텀 조건

```php
function is_captcha_required($board_id, $category_type) {
    // 커스텀 로직
    if ($category_type === 'SENSITIVE') {
        return true;  // 민감한 게시판은 항상 캡차
    }
    
    // VIP 회원 캡차 면제
    if ($_SESSION['user_level'] >= 5) {
        return false;
    }
    
    return true;  // 기본값
}
```

## 🧪 테스트

### 1. 테스트 페이지 접속
```
http://localhost:8081/board_templates/captcha_test.php
```

### 2. 테스트 시나리오
- ✅ 정상 입력 테스트
- ❌ 잘못된 입력 테스트  
- 🔄 새로고침 기능 테스트
- 🔊 음성 기능 테스트 (브라우저 지원 시)
- 📱 모바일 반응형 테스트

## 🔒 보안 특징

### 1. 서버 사이드 검증
- 세션 기반 코드 저장
- 5분 제한시간
- 일회성 코드 (재사용 불가)

### 2. 이미지 보안
- 노이즈 및 왜곡 적용
- 헷갈리기 쉬운 문자 제외 (0, O, 1, I, l)
- 캐시 방지 헤더

### 3. CSRF 보호
- 기존 CSRF 토큰과 연동
- 세션 기반 검증

## 🌐 접근성 (Accessibility)

### 1. 스크린 리더 지원
- `alt` 속성 제공
- `aria-label` 지원
- 의미적 HTML 구조

### 2. 키보드 지원
- Tab 네비게이션
- F5/Ctrl+R로 새로고침

### 3. 음성 지원
- Web Speech API 활용
- 한국어 음성 합성
- 브라우저 호환성 검사

## 📊 브라우저 지원

### 기본 기능 (100%)
- Chrome, Firefox, Safari, Edge
- 모든 모바일 브라우저

### 음성 기능 (80%+)
- Chrome, Firefox, Safari ✅
- Edge ✅
- 구형 브라우저는 기본 기능만

## 🐛 문제 해결

### 1. 이미지가 표시되지 않음
```bash
# GD 라이브러리 확인
php -m | grep -i gd
```

### 2. 세션 문제
```php
// 세션 디버깅
var_dump($_SESSION);
```

### 3. 테마 스타일이 적용되지 않음
```html
<!-- board-theme.css 로드 확인 -->
<link rel="stylesheet" href="assets/board-theme.css">
```

## 🔄 업데이트 로그

### v1.0 (2024-08-25)
- ✨ 초기 구현 완료
- 🎨 테마색상 연동
- 🔊 음성 지원
- 🌍 다국어 지원 (한/영)
- 📱 반응형 디자인
- 🧪 테스트 페이지

## 📞 지원

문제가 있거나 기능 개선이 필요한 경우:
1. `captcha_test.php`로 테스트
2. 브라우저 개발자 도구 확인
3. PHP 에러 로그 확인
4. 세션 상태 확인

---

**개발**: Claude Code  
**통합**: board_templates 시스템  
**버전**: 1.0  
**최종 업데이트**: 2024-08-25