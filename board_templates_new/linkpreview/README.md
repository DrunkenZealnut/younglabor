# Link Preview Generator v2.1

웹사이트 URL에서 Open Graph 메타데이터를 자동으로 추출하여 아름다운 미리보기 카드를 생성하는 재사용 가능한 하이브리드 라이브러리입니다.

## ✨ 주요 기능

- 🚀 **3단계 하이브리드 방식**: CORS 프록시 → 서버 API → 기본 정보 fallback
- 🔍 **자동 메타데이터 추출**: Open Graph, Twitter Cards, 기본 HTML 메타태그 지원
- 🎨 **반응형 미리보기 카드**: 모바일부터 데스크톱까지 완벽한 반응형 디자인
- 🛡️ **보안 중심 설계**: SSRF 방지, 입력값 검증, 안전한 URL 처리
- 🔌 **에디터 통합**: Summernote, TinyMCE 등 WYSIWYG 에디터와 쉬운 연동
- ⚡ **성능 최적화**: 중복 요청 방지, 비동기 처리, 빠른 응답
- 🌍 **다국어 지원**: 한국어, 영어 등 다양한 언어 지원
- 📱 **네이버 뉴스 등 까다로운 사이트 지원**: 봇 차단을 우회하는 클라이언트 방식
- 🖼️ **이미지 CORS 해결**: weserv.nl 프록시로 외부 이미지 안정적 표시
- 📊 **방법 표시**: 사용된 방법(CORS/Server/Basic)을 미리보기 카드에 표시

## 🏗️ 아키텍처

### 3단계 하이브리드 아키텍처
```
1차 시도: CORS 프록시 (corsproxy.io)
├── ✅ 성공 → 미리보기 카드 생성 (CORS 표시)
└── ❌ 실패 ↓

2차 시도: 서버 API (/api/link-preview.php)
├── ✅ 성공 → 미리보기 카드 생성 (Server 표시)  
└── ❌ 실패 ↓

3차 시도: 기본 정보 (도메인별 기본 템플릿)
└── ✅ 항상 성공 → 기본 카드 생성 (Basic 표시)
```

**특징**:
- **네이버 뉴스, 인스타그램**: CORS 프록시로 봇 차단 우회
- **이미지 CORS 문제**: weserv.nl 프록시로 자동 해결
- **방법 표시**: 카드 우측 하단에 사용된 방법 표시
- **로딩 상태**: "링크 정보를 불러오는 중..." 표시
- **에러 처리**: 각 단계별 자세한 로그와 fallback

## 📦 설치 방법

### 1. 파일 다운로드

프로젝트의 다음 파일들을 다운로드하여 웹서버에 업로드하세요:

```
your-project/
├── LinkPreviewGenerator.php     # PHP 서버 클래스 (백업용)
├── LinkPreviewClient.js         # JavaScript 하이브리드 모듈
└── api/
    └── link-preview.php         # 단순화된 API 엔드포인트
```

### 2. PHP 요구사항

- PHP 7.4 이상
- cURL 확장 모듈
- DOM 확장 모듈 (libxml)

**주요 변경사항**: 세션이나 CSRF 토큰 없이도 작동합니다.

### 3. 기본 설정

#### API 엔드포인트 설정
```php
<?php
// api/link-preview.php
require_once dirname(__DIR__) . '/LinkPreviewGenerator.php';

$linkPreview = new LinkPreviewGenerator([
    'timeout' => 8,
    'connect_timeout' => 5,
    'enable_cors' => true  // CORS 헤더 자동 설정
]);

$linkPreview->handleApiRequest();
?>
```

#### JavaScript 설정
```html
<!-- JavaScript 파일 로드 -->
<script src="LinkPreviewClient.js"></script>

<script>
// 3단계 하이브리드 클라이언트 초기화
const linkPreview = new LinkPreviewClient({
    // 1차: CORS 프록시 (가장 안정적)
    corsProxy: 'https://corsproxy.io/?{URL}',
    
    // 2차: 서버 API (백업용)
    serverApi: '/api/link-preview.php',
    enableServerFallback: true,
    
    // 3차: 기본 정보 (최후 수단)
    // 자동으로 도메인별 기본 정보 제공
    
    // UI 설정
    autoDetectUrls: true,
    clickToRemove: true,
    
    // 콜백으로 사용된 방법 확인 가능
    onPreviewGenerated: function(data, target) {
        console.log(`생성 완료: ${data.title} (방법: ${data.method})`);
    }
});
</script>
```

## 🚀 사용 방법

### 1. 독립적으로 사용하기

#### HTML 컨테이너에서 사용
```html
<div id="link-preview-container"></div>

<script>
const linkPreview = new LinkPreviewClient({
    // 3단계 하이브리드 설정
    corsProxy: 'https://corsproxy.io/?{URL}',
    serverApi: '/api/link-preview.php',
    enableServerFallback: true,
    
    containerId: 'link-preview-container'
});

// 프로그래밍 방식으로 미리보기 생성
await linkPreview.generatePreview('https://example.com');

// 네이버 뉴스도 잘 작동합니다 (CORS 프록시로 처리)
await linkPreview.generatePreview('https://news.naver.com/main/read.naver?mode=LSD&mid=sec&sid1=001&oid=001&aid=0015595335');
</script>
```

#### 입력 필드에서 자동 감지
```html
<textarea id="content-input" placeholder="링크를 붙여넣으세요..."></textarea>

<script>
const linkPreview = new LinkPreviewClient({
    corsProxy: 'https://corsproxy.io/?{URL}',
    serverApi: '/api/link-preview.php',
    enableServerFallback: true,
    
    autoDetectUrls: true,
    containerId: 'link-preview-container',
    
    onPreviewGenerated: function(data, target) {
        console.log(`미리보기 생성: ${data.title}`);
        console.log(`사용된 방법: ${data.method}`); // 'cors', 'server', 'basic'
        
        // 이미지 표시 여부도 확인 가능
        if (data.image && !data.image.includes('placehold.co')) {
            console.log('실제 이미지 표시됨:', data.image);
        }
    }
});
</script>
```

### 2. Summernote 에디터와 연동

```html
<div id="summernote-editor"></div>

<script>
// Summernote 초기화
$('#summernote-editor').summernote({
    height: 400,
    callbacks: {
        onPaste: function(e) {
            // 링크 붙여넣기 시 자동 미리보기 생성
            const bufferText = ((e.originalEvent.clipboardData || window.clipboardData).getData('text'));
            const urlRegex = /(https?:\/\/[^\s]+)/g;
            const urls = bufferText.match(urlRegex);
            
            if (urls && urls.length > 0) {
                e.preventDefault();
                linkPreview.generatePreview(urls[0], $(this));
            }
        }
    }
});

// 3단계 하이브리드 링크 미리보기 클라이언트
const linkPreview = new LinkPreviewClient({
    editorType: 'summernote',
    editorSelector: '#summernote-editor',
    
    // 3단계 하이브리드 설정
    corsProxy: 'https://corsproxy.io/?{URL}',
    serverApi: '/api/link-preview.php',
    enableServerFallback: true,
    
    onPreviewGenerated: function(data, target) {
        console.log(`미리보기 생성: ${data.title}`);
        console.log(`사용된 방법: ${data.method}`); // 'cors', 'server', 'basic'
        console.log(`이미지 처리: ${data.image ? '이미지 포함' : '텍스트만'}`);
    },
    onPreviewError: function(error, url, target) {
        console.error(`미리보기 생성 실패: ${url}`, error);
    }
});
</script>
```

### 3. 실제 프로젝트 통합 예제 (board_templates 방식)

게시판 글쓰기나 수정 폼에서 사용하는 실제 예제입니다:

```html
<form method="POST" enctype="multipart/form-data">
    <textarea id="content" name="content" placeholder="내용을 입력하세요..."></textarea>
    <button type="submit">저장</button>
</form>

<script>
// Summernote 초기화
$('#content').summernote({
    height: 400,
    lang: 'ko-KR',
    callbacks: {
        onPaste: function(e) {
            // URL 감지 및 미리보기 생성
            const bufferText = ((e.originalEvent.clipboardData || window.clipboardData).getData('text'));
            const urlRegex = /(https?:\/\/[^\s]+)/g;
            const urls = bufferText.match(urlRegex);
            
            if (urls && urls.length > 0) {
                e.preventDefault();
                createLinkPreview(urls[0]); // 아래 함수 사용
            }
        }
    }
});

// 3단계 하이브리드 링크 미리보기 함수
async function createLinkPreview(url) {
    const loadingId = 'loading-' + Date.now();
    const loadingHtml = `<div id="${loadingId}" class="p-4 text-center text-slate-500">"${url}" 링크 정보를 불러오는 중입니다...</div>`;
    $('#content').summernote('pasteHTML', loadingHtml);

    try {
        // 1차 시도: CORS 프록시 (가장 안정적)
        const corsResponse = await fetch('https://corsproxy.io/?' + encodeURIComponent(url));
        if (!corsResponse.ok) throw new Error('CORS 프록시 응답 실패: ' + corsResponse.status);

        const htmlContent = await corsResponse.text();
        const parser = new DOMParser();
        const doc = parser.parseFromString(htmlContent, 'text/html');
        
        const previewData = {
            title: doc.querySelector('meta[property="og:title"]')?.getAttribute('content') || doc.querySelector('title')?.textContent || '제목 없음',
            description: doc.querySelector('meta[property="og:description"]')?.getAttribute('content') || '설명 없음',
            image: doc.querySelector('meta[property="og:image"]')?.getAttribute('content') || 'https://placehold.co/400x300/e2e8f0/4a5568?text=Image',
            url: url,
            method: 'cors'
        };

        insertLinkPreviewCard(previewData, loadingId);

    } catch (corsError) {
        console.log('CORS 프록시 실패, 서버 API 시도:', corsError);

        try {
            // 2차 시도: 서버 API (백업용)
            const serverResponse = await fetch('/api/link-preview.php?url=' + encodeURIComponent(url));
            const data = await serverResponse.json();
            if (!data.success) throw new Error(data.error || '서버 API 응답 실패');

            data.method = 'server';
            insertLinkPreviewCard(data, loadingId);

        } catch (serverError) {
            console.log('서버 API 실패, 기본 정보 사용:', serverError);

            // 3차 시도: 기본 정보 (최후의 수단)
            const basicData = {
                title: '링크 미리보기',
                description: '링크 내용을 미리 볼 수 없습니다.',
                image: 'https://placehold.co/400x300/e2e8f0/4a5568?text=Link',
                url: url,
                method: 'basic'
            };

            insertLinkPreviewCard(basicData, loadingId);
        }
    }
}

// 링크 미리보기 카드 삽입 함수
function insertLinkPreviewCard(data, loadingId) {
    // 로딩 요소 제거
    $('#' + loadingId).remove();

    // 이미지 CORS 처리
    let finalImageUrl = data.image;
    if (data.image && !data.image.includes('placehold.co') && !data.image.startsWith('data:')) {
        if (!data.image.includes('corsproxy.io')) {
            finalImageUrl = 'https://images.weserv.nl/?url=' + encodeURIComponent(data.image) + '&w=400&h=300&fit=cover';
        }
    }

    const card = document.createElement('div');
    card.setAttribute('contenteditable', 'false');
    card.className = 'my-3 bg-white border border-slate-200 rounded-xl overflow-hidden shadow-sm preview-card';
    card.innerHTML = `
        <div class="flex flex-col sm:flex-row items-stretch">
            <div class="sm:w-1/3">
                <img class="w-full h-48 sm:h-full object-cover" 
                     src="${finalImageUrl}" 
                     alt="link preview" 
                     onerror="this.src='https://placehold.co/400x300/e2e8f0/4a5568?text=Image'">
            </div>
            <div class="flex-1 p-4 flex flex-col justify-between">
                <div>
                    <h3 class="font-bold text-lg text-slate-800 line-clamp-2">${escapeHtml(data.title)}</h3>
                    <p class="text-slate-600 mt-2 text-sm line-clamp-3">${escapeHtml(data.description)}</p>
                </div>
                <div class="flex justify-between items-center mt-3">
                    <a class="text-slate-400 text-xs truncate block flex-1" 
                       href="${data.url}" target="_blank" rel="noopener noreferrer">${data.url}</a>
                    <span class="text-xs text-slate-400 ml-2 whitespace-nowrap">
                        ${data.method === 'cors' ? 'CORS' : data.method === 'server' ? 'Server' : 'Basic'}
                    </span>
                </div>
            </div>
        </div>
    `;

    $('#content').summernote('insertNode', card);
    $('#content').summernote('pasteHTML', '<p><br></p>');
    $('#content').summernote('focus');
}

function escapeHtml(str) {
    return String(str).replace(/[&<>"']/g, function(s) {
        return ({'&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;','\'':'&#39;'}[s]);
    });
}
</script>
```

### 4. PHP에서 직접 사용 (백업 API)

```php
<?php
require_once 'LinkPreviewGenerator.php';

$linkPreview = new LinkPreviewGenerator();

// 미리보기 데이터 생성
$result = $linkPreview->generatePreview('https://example.com');

if ($result['success']) {
    echo "제목: " . $result['title'] . "\n";
    echo "설명: " . $result['description'] . "\n";
    echo "이미지: " . $result['image'] . "\n";
    echo "사이트: " . $result['site_name'] . "\n";
} else {
    echo "오류: " . $result['error'] . "\n";
}
?>
```

## ⚙️ 설정 옵션

### JavaScript 클라이언트 옵션

```javascript
const linkPreview = new LinkPreviewClient({
    // 3단계 하이브리드 설정
    corsProxy: 'https://corsproxy.io/?{URL}',  // 1차: CORS 프록시 (가장 안정적)
    serverApi: '/api/link-preview.php',        // 2차: 서버 API (백업용)
    enableServerFallback: true,                // 3차: 기본 정보 (자동 생성)
    
    // UI 설정
    containerId: 'preview-container',          // 컨테이너 ID
    autoDetectUrls: true,                     // 자동 URL 감지
    clickToRemove: true,                      // 클릭으로 제거 가능
    
    // 스타일 설정
    cardClassName: 'custom-preview-card',      // 사용자 정의 CSS 클래스
    cardTemplate: function(data) {             // 사용자 정의 템플릿
        return `
            <div class="my-card">
                <h3>${data.title}</h3>
                <p>${data.description}</p>
                <small>방법: ${data.method}</small>
            </div>
        `;
    },
    
    // 에디터 통합
    editorType: 'summernote',                 // 'summernote', 'tinymce', 'none'
    editorSelector: '#my-editor',              // 에디터 선택자
    
    // 성능 설정
    timeout: 8000,                            // 요청 타임아웃 (밀리초)
    
    // 콜백 함수
    onPreviewGenerated: function(data, target) {
        console.log('미리보기 생성:', data.title);
        console.log('사용된 방법:', data.method); // 'cors', 'server', 'basic'
        console.log('이미지 처리:', data.image ? '성공' : '없음');
    },
    onPreviewError: function(error, url, target) {
        console.error('미리보기 생성 실패:', error);
        // 에러가 발생해도 기본 정보로 fallback 되므로 실제로는 드물게 호출됨
    },
    onPreviewRemoved: function(element) {
        console.log('미리보기 제거됨');
    }
});
```

### PHP 클래스 옵션 (백업 API용)

```php
$linkPreview = new LinkPreviewGenerator([
    // 네트워크 설정
    'timeout' => 8,                        // 요청 타임아웃 (초)
    'connect_timeout' => 5,                // 연결 타임아웃 (초)
    'max_redirects' => 3,                  // 최대 리다이렉트 수
    'verify_ssl' => false,                 // SSL 인증서 검증
    'enable_cors' => true,                 // CORS 헤더 활성화
    'allowed_protocols' => ['http', 'https'], // 허용 프로토콜
    
    'user_agent' => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36'
]);
```

## 🎨 스타일 커스터마이징

### CSS 클래스 구조

```css
.link-preview-card {
    /* 메인 카드 컨테이너 */
    border: 1px solid #e5e7eb;
    border-radius: 12px;
    overflow: hidden;
    background: white;
    box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
}

.link-preview-card .preview-image-container {
    /* 이미지 컨테이너 */
    aspect-ratio: 16 / 9;
    overflow: hidden;
}

.link-preview-card .preview-image {
    /* 미리보기 이미지 */
    width: 100%;
    height: 100%;
    object-fit: cover;
}

.link-preview-card .preview-content {
    /* 텍스트 컨텐츠 영역 */
    padding: 16px;
}

.link-preview-card .preview-title {
    /* 제목 스타일 */
    font-weight: 600;
    font-size: 18px;
    margin-bottom: 8px;
}

.link-preview-card .preview-description {
    /* 설명 스타일 */
    color: #6b7280;
    font-size: 14px;
    line-height: 1.5;
    margin-bottom: 12px;
}

.link-preview-card .preview-url {
    /* URL 링크 스타일 */
    color: #9ca3af;
    font-size: 12px;
    text-decoration: none;
}
```

### 반응형 스타일 예제

```css
.link-preview-card {
    display: flex;
    flex-direction: column;
}

@media (min-width: 640px) {
    .link-preview-card {
        flex-direction: row;
        align-items: stretch;
    }
    
    .link-preview-card .preview-image-container {
        flex: 0 0 200px;
    }
    
    .link-preview-card .preview-content {
        flex: 1;
    }
}
```

## 🔒 보안 고려사항

### 1. 자동 SSRF 방지
PHP 클래스는 기본적으로 다음을 차단합니다:
- 로컬호스트 (127.0.0.1)
- 사설 IP 대역 (10.x.x.x, 192.168.x.x, 172.16-31.x.x)
- 링크-로컬 주소 (169.254.x.x)

### 2. 입력 검증
- URL 형식 자동 검증
- 허용된 프로토콜만 처리 (http, https)
- HTML 출력 시 자동 이스케이프

### 3. CORS 프록시 보안
- 신뢰할 수 있는 CORS 프록시 사용
- 필요시 자체 프록시 서버 구축 가능

## 🐛 문제 해결

### 자주 발생하는 문제

#### 1. "CORS 프록시 실패" 발생
```javascript
// 대체 CORS 프록시 사용
const linkPreview = new LinkPreviewClient({
    corsProxy: 'https://api.allorigins.win/get?url=',
    // 또는 자체 프록시 서버 사용
    corsProxy: 'https://your-domain.com/proxy?url=',
    apiUrl: '/api/link-preview.php'
});
```

#### 2. "네이버 뉴스 미리보기 실패"
하이브리드 방식을 사용하면 대부분 해결됩니다. CORS 프록시가 1차로 시도되어 봇 차단을 우회합니다.

#### 3. "서버 API 실패"
```php
// SSL 검증 비활성화 (개발 환경)
$linkPreview = new LinkPreviewGenerator([
    'verify_ssl' => false,
    'timeout' => 10
]);
```

#### 4. 메타데이터 추출 실패
일부 JavaScript로 동적 생성되는 페이지의 경우, 해당 사이트의 oEmbed API나 공식 API 사용을 권장합니다.

### 디버깅 팁

```javascript
// 클라이언트 디버깅
const linkPreview = new LinkPreviewClient({
    corsProxy: 'https://corsproxy.io/?',
    apiUrl: '/api/link-preview.php',
    
    onPreviewGenerated: function(data, target) {
        console.log('성공:', data);
        console.log('사용된 방법:', data.method);
    },
    onPreviewError: function(error, url, target) {
        console.error('실패:', error, url);
    }
});

// 서버 API 디버깅
// GET 요청으로 직접 테스트
// /api/link-preview.php?url=https://example.com
```

## 🚀 성능 최적화

### 클라이언트 사이드 우선의 장점
- **빠른 응답**: 서버 라운드트립 없이 직접 처리
- **서버 부하 감소**: 대부분의 요청이 클라이언트에서 처리됨
- **봇 차단 우회**: 사용자 브라우저의 실제 요청으로 처리
- **확장성**: 서버 리소스 절약

### 캐싱 전략
```javascript
// 브라우저 세션 스토리지 활용
const linkPreview = new LinkPreviewClient({
    corsProxy: 'https://corsproxy.io/?',
    apiUrl: '/api/link-preview.php',
    
    onPreviewGenerated: function(data, target) {
        // 성공한 미리보기 결과 캐시
        sessionStorage.setItem(`preview_${data.url}`, JSON.stringify(data));
    }
});
```

## 📄 라이선스

MIT License

## 🤝 기여하기

버그 리포트나 기능 제안은 GitHub Issues를 통해 해주세요.

## 📚 추가 자료

- [Open Graph Protocol](https://ogp.me/)
- [Twitter Cards](https://developer.twitter.com/en/docs/twitter-for-websites/cards/overview/abouts-cards)
- [CORS Proxy Services](https://github.com/Rob--W/cors-anywhere)
- [Summernote Documentation](https://summernote.org/)
- [TinyMCE Documentation](https://www.tiny.cloud/docs/)

## 🔄 버전 2.1 변경사항

### v2.1의 새로운 기능
- **3단계 하이브리드 시스템**: CORS → 서버 → 기본 정보의 완벽한 fallback
- **이미지 CORS 해결**: weserv.nl 프록시로 외부 이미지 안정적 표시
- **방법 표시**: 미리보기 카드에 사용된 방법(CORS/Server/Basic) 표시
- **로딩 상태 개선**: "링크 정보를 불러오는 중..." 메시지
- **에러 처리 강화**: 각 단계별 상세 로깅과 자동 fallback

### v2.0에서 v2.1로 업그레이드
기본적으로 호환되지만, 새로운 기능을 사용하려면:

```javascript
// Before (v2.0)
const linkPreview = new LinkPreviewClient({
    corsProxy: 'https://corsproxy.io/?',
    apiUrl: '/api/link-preview.php'
});

// After (v2.1) - 개선된 설정
const linkPreview = new LinkPreviewClient({
    corsProxy: 'https://corsproxy.io/?{URL}',  // URL 플레이스홀더 추가
    serverApi: '/api/link-preview.php',        // apiUrl → serverApi
    enableServerFallback: true,                // 명시적 활성화
    
    onPreviewGenerated: function(data, target) {
        console.log(`방법: ${data.method}`);    // 새로운 method 필드
    }
});
```

### v1.x에서 v2.1로 업그레이드
1. CSRF 토큰 관련 코드 제거
2. 3단계 하이브리드 설정 추가
3. 콜백 함수에서 `data.method` 확인 가능
4. 이미지 CORS 문제 자동 해결

```javascript
// Before (v1.x)
const linkPreview = new LinkPreviewClient({
    apiUrl: '/api/link-preview.php',
    csrfToken: 'your-token'
});

// After (v2.1) - 완전한 하이브리드
const linkPreview = new LinkPreviewClient({
    corsProxy: 'https://corsproxy.io/?{URL}',
    serverApi: '/api/link-preview.php',
    enableServerFallback: true
});
```