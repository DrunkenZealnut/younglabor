<?php
/**
 * Critical CSS 추출 및 생성 시스템
 * Above-the-fold 스타일을 자동으로 식별하고 추출
 */

class CriticalCSSExtractor {
    private $cssManager;
    private $criticalRules = [];
    
    public function __construct($cssManager) {
        $this->cssManager = $cssManager;
    }
    
    /**
     * 핵심 CSS 규칙들을 자동으로 추출하고 등록
     */
    public function extractAndRegister() {
        // 1. CSS 변수 (최우선)
        $this->addCSSVariables();
        
        // 2. 기본 요소 스타일
        $this->addBaseStyles();
        
        // 3. 헤더/네비게이션 스타일
        $this->addHeaderStyles();
        
        // 4. Above-the-fold 컨텐츠 스타일
        $this->addAboveFoldStyles();
        
        // 5. 반응형 기본 스타일
        $this->addResponsiveStyles();
        
        // Critical CSS로 등록
        $criticalCSS = implode("\n", $this->criticalRules);
        $this->cssManager->addCriticalCSS($criticalCSS, 'extracted-critical');
        
        return strlen($criticalCSS);
    }
    
    /**
     * 현재 UI와 동일한 완전한 CSS 변수 추가 (최고 우선순위)
     */
    private function addCSSVariables() {
        $this->criticalRules[] = ":root {
            --font-size: 14px;
            --background: #f4f8f3;
            --foreground: oklch(0.145 0 0);
            --card: oklch(1 0 0);
            --card-foreground: oklch(0.145 0 0);
            --popover: oklch(1 0 0);
            --popover-foreground: oklch(0.145 0 0);
            --primary: oklch(0.855 0.165 130.5);
            --primary-foreground: oklch(1 0 0);
            --secondary: oklch(0.95 0.0058 264.53);
            --secondary-foreground: oklch(0.722 0.193 120.75);
            --muted: oklch(0.932 0.025 135.62);
            --muted-foreground: oklch(0.465 0.015 264.53);
            --accent: oklch(0.892 0.109 120.75);
            --accent-foreground: oklch(0.722 0.193 120.75);
            --destructive: oklch(0.488 0.240 25.33);
            --destructive-foreground: oklch(1 0 0);
            --border: rgba(132, 204, 22, 0.15);
            --input: transparent;
            --input-background: #f4f8f3;
            --switch-background: #cbced4;
            --font-weight-medium: 500;
            --font-weight-normal: 400;
            --ring: oklch(0.708 0 0);
            --radius: 0.625rem;
            --sidebar: oklch(0.985 0 0);
            --sidebar-foreground: oklch(0.145 0 0);
            --sidebar-primary: oklch(0.722 0.193 120.75);
            --sidebar-primary-foreground: oklch(0.985 0 0);
            --sidebar-accent: oklch(0.97 0 0);
            --sidebar-accent-foreground: oklch(0.205 0 0);
            --sidebar-border: oklch(0.922 0 0);
            --sidebar-ring: oklch(0.708 0 0);
            --title-color: oklch(0.225 0.058 152.48);
            
            /* 기존 색상 변수 (OKLCH 표준화) */
            --forest-700: oklch(0.225 0.058 152.48);
            --forest-600: oklch(0.335 0.069 152.48);
            --forest-500: oklch(0.445 0.081 152.48);
            --natural-100: oklch(0.967 0.015 135.62);
            --natural-50: oklch(0.993 0.005 135.62);
            --natural-200: oklch(0.932 0.025 135.62);
            --lime-200: oklch(0.892 0.109 120.75);
            --lime-300: oklch(0.842 0.150 120.75);
            --lime-400: oklch(0.782 0.190 120.75);
            --lime-500: oklch(0.722 0.193 120.75);
            --lime-600: oklch(0.582 0.155 120.75);
            
            /* 브랜드 그라디언트 색상 정의 */
            --brand-gradient-primary: oklch(0.855 0.165 130.5);
            --brand-gradient-secondary: oklch(0.335 0.069 152.48);
            
            /* 의미론적 상태 색상 시스템 */
            --success: var(--lime-500);
            --success-foreground: var(--primary-foreground);
            --success-muted: var(--lime-200);
            --warning: oklch(0.693 0.156 53.24);
            --warning-foreground: oklch(1 0 0);
            --warning-muted: oklch(0.950 0.054 53.24);
            --error: oklch(0.488 0.240 25.33);
            --error-foreground: oklch(1 0 0);
            --error-muted: oklch(0.947 0.069 25.33);
            --info: var(--forest-600);
            --info-foreground: oklch(1 0 0);
            --info-muted: var(--natural-200);
        }";
    }
    
    /**
     * 현재 UI와 동일한 기본 요소 스타일
     */
    private function addBaseStyles() {
        $this->criticalRules[] = "* {
            box-sizing: border-box;
            border-color: var(--border);
            outline: 2px solid transparent;
            outline-offset: 2px;
        }
        
        html {
            font-size: var(--font-size);
            height: 100%;
        }
        
        html, body {
            margin: 0;
            padding: 0;
            font-family: 'Noto Sans KR', -apple-system, BlinkMacSystemFont, sans-serif;
            background-color: var(--background);
            color: var(--foreground);
            line-height: 1.6;
        }
        
        body {
            display: flex;
            flex-direction: column;
            min-height: 100vh;
            height: 100%;
        }
        
        main {
            flex: 1 1 auto;
        }
        
        /* 기본 타이포그래피 */
        h1 {
            font-size: 1.5rem;
            font-weight: var(--font-weight-medium);
            line-height: 1.5;
        }
        
        h2 {
            font-size: 1.25rem;
            font-weight: var(--font-weight-medium);
            line-height: 1.5;
        }
        
        h3 {
            font-size: 1.125rem;
            font-weight: var(--font-weight-medium);
            line-height: 1.5;
        }
        
        h4 {
            font-size: 1rem;
            font-weight: var(--font-weight-medium);
            line-height: 1.5;
        }
        
        p {
            font-size: 1rem;
            font-weight: var(--font-weight-normal);
            line-height: 1.5;
        }
        
        label, button {
            font-size: 1rem;
            font-weight: var(--font-weight-medium);
            line-height: 1.5;
        }
        
        input {
            font-size: 1rem;
            font-weight: var(--font-weight-normal);
            line-height: 1.5;
        }";
    }
    
    /**
     * 현재 UI와 동일한 헤더/네비게이션 Critical 스타일
     */
    private function addHeaderStyles() {
        $this->criticalRules[] = "header, header nav, header [role=\"navigation\"], .navbar, .navbar-nav {
            overflow: visible !important;
            height: auto !important;
        }
        
        header {
            background: var(--background);
            border-bottom: 1px solid var(--border);
            position: relative;
            z-index: 50;
        }
        
        .navbar {
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 1rem;
            max-width: 80rem;
            margin: 0 auto;
        }
        
        .navbar-brand img {
            height: 2.5rem;
            width: auto;
            max-width: 10rem;
        }
        
        .nav-button-hover {
            position: relative !important;
            box-sizing: border-box !important;
            border-radius: 8px !important;
            overflow: hidden !important;
            background-clip: padding-box !important;
            color: var(--forest-600);
            text-decoration: none;
            padding: 0.5rem 0.75rem;
            transition: background-color 0.2s ease;
        }
        
        .nav-button-hover:hover {
            background-color: #e8f4e6 !important;
            background-clip: padding-box !important;
            color: var(--lime-600);
        }
        
        /* 드롭다운 메뉴 스타일 */
        .dropdown-menu {
            display: none;
            position: absolute;
            top: 100%;
            left: 0;
            z-index: 1000;
            min-width: 10rem;
            padding: 0.5rem 0;
            margin: 0;
            font-size: 1rem;
            color: var(--foreground);
            text-align: left;
            list-style: none;
            background-color: var(--natural-50);
            background-clip: padding-box;
            border: 1px solid var(--border);
            border-radius: var(--radius);
            box-shadow: 0 0.5rem 1rem rgba(58, 122, 78, 0.1);
            backdrop-filter: blur(12px);
            opacity: 0;
            visibility: hidden;
            transform: translateY(-10px);
            transition: all 0.3s ease;
            pointer-events: none;
        }
        
        .dropdown-menu.show {
            display: block !important;
            opacity: 1 !important;
            visibility: visible !important;
            transform: translateY(0) !important;
            pointer-events: auto !important;
        }
        
        .dropdown-menu a {
            display: block;
            width: 100%;
            padding: 0.5rem 1rem;
            clear: both;
            font-weight: 400;
            color: var(--forest-600);
            text-align: inherit;
            text-decoration: none;
            white-space: nowrap;
            background-color: transparent;
            border: 0;
            transition: all 0.2s ease;
        }
        
        .dropdown-menu a:hover,
        .dropdown-menu a:focus {
            background-color: var(--natural-200);
            color: var(--forest-600);
        }";
    }
    
    /**
     * 현재 UI와 동일한 Above-the-fold 컨텐츠 스타일 + 주요 유틸리티 클래스
     */
    private function addAboveFoldStyles() {
        $this->criticalRules[] = "/* 레이아웃 유틸리티 */
        .max-w-7xl { max-width: 80rem; }
        .max-w-5xl { max-width: 64rem; }
        .mx-auto { margin-left: auto; margin-right: auto; }
        .px-4 { padding-left: 1rem; padding-right: 1rem; }
        .py-4 { padding-top: 1rem; padding-bottom: 1rem; }
        .py-6 { padding-top: 1.5rem; padding-bottom: 1.5rem; }
        .py-8 { padding-top: 2rem; padding-bottom: 2rem; }
        .py-10 { padding-top: 2.5rem; padding-bottom: 2.5rem; }
        .py-12 { padding-top: 3rem; padding-bottom: 3rem; }
        .pb-6 { padding-bottom: 1.5rem; }
        .pt-10 { padding-top: 2.5rem; }
        .p-4 { padding: 1rem; }
        .p-6 { padding: 1.5rem; }
        .mb-2 { margin-bottom: 0.5rem; }
        .mb-4 { margin-bottom: 1rem; }
        .mb-6 { margin-bottom: 1.5rem; }
        .mb-8 { margin-bottom: 2rem; }
        .mt-4 { margin-top: 1rem; }
        .mt-6 { margin-top: 1.5rem; }
        .ml-2 { margin-left: 0.5rem; }
        
        /* Flexbox */
        .flex { display: flex; }
        .flex-1 { flex: 1 1 0%; }
        .flex-col { flex-direction: column; }
        .items-center { align-items: center; }
        .justify-center { justify-content: center; }
        .justify-between { justify-content: space-between; }
        .justify-end { justify-content: flex-end; }
        .space-y-3 > * + * { margin-top: 0.75rem; }
        
        /* Grid */
        .grid { display: grid; }
        .grid-cols-1 { grid-template-columns: repeat(1, minmax(0, 1fr)); }
        .grid-cols-2 { grid-template-columns: repeat(2, minmax(0, 1fr)); }
        .gap-4 { gap: 1rem; }
        .gap-6 { gap: 1.5rem; }
        .gap-8 { gap: 2rem; }
        
        /* 텍스트 */
        .text-center { text-align: center; }
        .text-sm { font-size: 0.875rem; line-height: 1.25rem; }
        .text-lg { font-size: 1.125rem; line-height: 1.75rem; }
        .text-xl { font-size: 1.25rem; line-height: 1.75rem; }
        .text-2xl { font-size: 1.5rem; line-height: 2rem; }
        .text-3xl { font-size: 1.875rem; line-height: 2.25rem; }
        .text-4xl { font-size: 2.25rem; line-height: 2.5rem; }
        .text-5xl { font-size: 3rem; line-height: 1; }
        .font-semibold { font-weight: 600; }
        .font-bold { font-weight: 700; }
        
        /* 현재 UI 색상 시스템 */
        .text-forest-700 { color: var(--forest-700); }
        .text-forest-600 { color: var(--forest-600); }
        .text-forest-500 { color: var(--forest-500); }
        .text-lime-500 { color: var(--lime-500); }
        .text-lime-600 { color: var(--lime-600); }
        .text-lime-700 { color: #4d7c0f; }
        .text-title { color: var(--title-color); }
        .text-white { color: #ffffff; }
        .text-gray-400 { color: #9ca3af; }
        .text-gray-500 { color: #6b7280; }
        .text-gray-600 { color: #4b5563; }
        .text-gray-700 { color: #374151; }
        .text-gray-800 { color: #1f2937; }
        .text-gray-900 { color: #111827; }
        
        /* 배경 색상 */
        .bg-white { background-color: #ffffff; }
        .bg-natural-100 { background-color: var(--natural-100); }
        .bg-natural-50 { background-color: var(--natural-50); }
        .bg-natural-200 { background-color: var(--natural-200); }
        .bg-lime-200 { background-color: var(--lime-200); }
        .bg-lime-400 { background-color: var(--lime-400); }
        .bg-forest-600 { background-color: var(--forest-600); }
        .bg-forest-700 { background-color: var(--forest-700); }
        .bg-forest-100 { background-color: #e6f3e6; }
        .bg-forest-200 { background-color: #c5e4c5; }
        .bg-gray-200 { background-color: #e5e7eb; }
        .bg-gray-100 { background-color: #f3f4f6; }
        .bg-green-50 { background-color: #f0fdf4; }
        .bg-blue-50 { background-color: #eff6ff; }
        
        /* 테두리 색상 */
        .border { border-width: 1px; }
        .border-t { border-top-width: 1px; }
        .border-lime-200 { border-color: var(--lime-200); }
        .border-lime-300 { border-color: var(--lime-300); }
        .border-lime-400 { border-color: var(--lime-400); }
        .border-primary { border-color: var(--primary) !important; }
        .border-primary-light { border-color: #a3e635 !important; }
        .border-forest-200 { border-color: #c5e4c5; }
        .border-forest-300 { border-color: #a8d4a8; }
        .border-green-200 { border-color: #bbf7d0; }
        .border-blue-200 { border-color: #bfdbfe; }
        
        /* 테두리 반지름 */
        .rounded { border-radius: 0.25rem; }
        .rounded-lg { border-radius: 0.5rem; }
        .rounded-2xl { border-radius: 1rem; }
        
        /* 그림자 */
        .shadow-sm { box-shadow: 0 1px 2px 0 rgba(0, 0, 0, 0.05); }
        .shadow-md { box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1), 0 2px 4px -1px rgba(0, 0, 0, 0.06); }
        .shadow-lg { box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.1), 0 4px 6px -2px rgba(0, 0, 0, 0.05); }
        .shadow-xl { box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.1), 0 10px 10px -5px rgba(0, 0, 0, 0.04); }
        
        /* 기타 유틸리티 */
        .overflow-hidden { overflow: hidden; }
        .relative { position: relative; }
        .absolute { position: absolute; }
        .block { display: block; }
        .w-full { width: 100%; }
        .h-48 { height: 12rem; }
        .h-full { height: 100%; }
        .object-cover { object-fit: cover; }
        .transition { transition: all 0.15s ease; }
        .transition-shadow { transition-property: box-shadow; }
        .hover\\:shadow-md:hover { box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1), 0 2px 4px -1px rgba(0, 0, 0, 0.06); }
        .hover\\:border-primary:hover { border-color: var(--primary) !important; }";
    }
    
    /**
     * 현재 UI와 동일한 반응형 스타일
     */
    private function addResponsiveStyles() {
        $this->criticalRules[] = "/* 모바일 최적화 */
        @media (max-width: 768px) {
            .navbar {
                padding: 0.5rem 1rem;
            }
            
            .text-3xl {
                font-size: 1.5rem;
                line-height: 2rem;
            }
            
            .px-4 {
                padding-left: 1rem;
                padding-right: 1rem;
            }
            
            .md\\:hidden {
                display: none;
            }
        }
        
        /* 작은 화면 (640px+) */
        @media (min-width: 640px) {
            .sm\\:px-6 {
                padding-left: 1.5rem;
                padding-right: 1.5rem;
            }
        }
        
        /* 중간 화면 (768px+) */
        @media (min-width: 768px) {
            .md\\:text-2xl { font-size: 1.5rem; line-height: 2rem; }
            .md\\:text-4xl { font-size: 2.25rem; line-height: 2.5rem; }
            .md\\:text-5xl { font-size: 3rem; line-height: 1; }
            .md\\:grid-cols-2 { grid-template-columns: repeat(2, minmax(0, 1fr)); }
            .md\\:grid-cols-3 { grid-template-columns: repeat(3, minmax(0, 1fr)); }
            .md\\:grid-cols-5 { grid-template-columns: repeat(5, minmax(0, 1fr)); }
            .md\\:p-8 { padding: 2rem; }
            .md\\:flex-row { flex-direction: row; }
            .md\\:hidden { display: none; }
        }
        
        /* 큰 화면 (1024px+) */
        @media (min-width: 1024px) {
            .lg\\:px-8 {
                padding-left: 2rem;
                padding-right: 2rem;
            }
            .lg\\:grid-cols-3 {
                grid-template-columns: repeat(3, minmax(0, 1fr));
            }
        }";
    }
    
    /**
     * 페이지별 맞춤 Critical CSS 추가
     */
    public function addPageSpecificCSS($pagetype) {
        switch ($pagetype) {
            case 'gallery':
            case 'newsletter':
                $this->addGridStyles();
                break;
            case 'about':
                $this->addAboutStyles();
                break;
            case 'home':
            default:
                $this->addHomeStyles();
                break;
        }
    }
    
    /**
     * 현재 UI와 동일한 갤러리/뉴스레터용 그리드 및 카드 스타일
     */
    private function addGridStyles() {
        $this->criticalRules[] = "/* 카드 컴포넌트 스타일 - 현재 UI 복제 */
        .card-border {
            border: 1px solid var(--border);
            transition: border-color 0.3s ease;
        }
        
        .card-border:hover {
            border-color: var(--primary);
            box-shadow: 0 0 0 1px rgba(133, 229, 70, 0.2);
        }
        
        article {
            background-color: var(--card);
            color: var(--card-foreground);
            transition: all 0.3s ease;
        }
        
        article:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1), 0 2px 4px -1px rgba(0, 0, 0, 0.06);
        }
        
        /* Font Awesome 아이콘 지원 */
        .fa, .fas, .far, .fal, .fab {
            font-family: 'Font Awesome 6 Free', 'Font Awesome 6 Brands';
            font-weight: 900;
            display: inline-block;
            font-style: normal;
            font-variant: normal;
            text-rendering: auto;
            line-height: 1;
        }
        
        .fa-user:before { content: \"\\f007\"; }
        .fa-calendar:before { content: \"\\f073\"; }
        
        /* 그라디언트 배경 */
        .bg-gradient-to-br { 
            background-image: linear-gradient(to bottom right, var(--tw-gradient-stops));
        }
        
        .from-lime-100 { 
            --tw-gradient-from: #ecfccb;
            --tw-gradient-to: rgba(236, 252, 203, 0);
            --tw-gradient-stops: var(--tw-gradient-from), var(--tw-gradient-to);
        }
        
        .to-lime-200 { 
            --tw-gradient-to: var(--lime-200);
        }
        
        .from-forest-100 { 
            --tw-gradient-from: #e6f3e6;
            --tw-gradient-to: rgba(230, 243, 230, 0);
            --tw-gradient-stops: var(--tw-gradient-from), var(--tw-gradient-to);
        }
        
        .to-forest-200 { 
            --tw-gradient-to: #c5e4c5;
        }
        
        .from-natural-100 { 
            --tw-gradient-from: var(--natural-100);
            --tw-gradient-to: transparent;
            --tw-gradient-stops: var(--tw-gradient-from), var(--tw-gradient-to);
        }
        
        .to-natural-200 { 
            --tw-gradient-to: var(--natural-200);
        }
        
        /* 호버 효과 */
        .hover-lift {
            transition: all 0.3s ease;
        }
        
        .hover-lift:hover {
            transform: translateY(-4px);
            box-shadow: 0 10px 25px rgba(58, 122, 78, 0.15);
        }";
    }
    
    /**
     * 홈페이지용 스타일
     */
    private function addHomeStyles() {
        $this->criticalRules[] = ".hero-section {
            background: linear-gradient(135deg, var(--background) 0%, #e8f4e6 100%);
            padding: 3rem 0;
        }";
    }
    
    /**
     * 소개페이지용 스타일
     */
    private function addAboutStyles() {
        $this->criticalRules[] = ".breadcrumb {
            margin-bottom: 1rem;
        }
        
        .breadcrumb ol {
            display: flex;
            align-items: center;
        }";
    }
}