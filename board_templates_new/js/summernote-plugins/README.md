# Summernote 플러그인 시스템

## 디렉토리 구조

```
js/summernote-plugins/
├── README.md                   # 이 파일
├── core/                       # 핵심 플러그인 로더
│   ├── plugin-loader.js        # 플러그인 동적 로딩 시스템
│   └── plugin-base.js          # 플러그인 기본 클래스
├── text-styles/               # 텍스트 스타일 플러그인
│   ├── strikethrough.js        # 취소선
│   ├── superscript.js          # 위첨자
│   ├── subscript.js            # 아래첨자
│   └── highlighter.js          # 배경색 하이라이터
├── paragraph/                 # 문단 스타일 플러그인
│   ├── line-height.js          # 줄간격 조절
│   └── paragraph-styles.js     # 문단 스타일 프리셋
├── content/                   # 콘텐츠 삽입 플러그인
│   ├── special-chars.js        # 특수문자 팔레트
│   ├── checklist.js           # 체크리스트
│   └── dividers.js            # 구분선 스타일
├── utils/                     # 유틸리티 플러그인
│   ├── auto-save.js           # 자동 저장
│   └── enhanced-undo.js       # 개선된 실행 취소/다시 실행
└── mobile/                    # 모바일 최적화
    └── responsive-toolbar.js   # 반응형 툴바
```

## 플러그인 명명 규칙

- **파일명**: kebab-case (예: `special-chars.js`)
- **플러그인명**: camelCase (예: `specialChars`)
- **CSS 클래스**: `note-btn-[plugin-name]` (예: `note-btn-strikethrough`)

## 플러그인 개발 가이드

각 플러그인은 다음 구조를 따릅니다:

```javascript
$.extend($.summernote.plugins, {
  'pluginName': function(context) {
    var self = this;
    var ui = $.summernote.ui;
    
    // 플러그인 초기화
    context.memo('button.pluginName', function() {
      return ui.button({
        contents: '<i class="fa fa-icon"></i>',
        tooltip: '툴팁 텍스트',
        click: function() {
          // 기능 구현
        }
      });
    });
  }
});
```