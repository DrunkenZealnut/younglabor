<?php
/**
 * Critical CSS 생성기
 * Natural Green 테마 기반으로 Above-the-fold 스타일 추출
 * 
 * Version: 1.0.0
 * Author: SuperClaude CSS Optimization System
 */

class CriticalCSSGenerator {
    
    private $naturalGreenPath;
    private $cacheDir;
    private $cacheLifetime = 3600; // 1시간
    
    public function __construct() {
        $this->naturalGreenPath = dirname(__DIR__) . '/theme/natural-green/styles/globals.css';
        $this->cacheDir = dirname(__DIR__) . '/css/cache';
        
        // 캐시 디렉토리 생성
        if (!file_exists($this->cacheDir)) {
            mkdir($this->cacheDir, 0755, true);
        }
    }
    
    /**
     * Critical CSS 전체 생성
     */
    public function generateCriticalCSS() {
        $cacheKey = 'critical_css_' . md5($this->naturalGreenPath . filemtime($this->naturalGreenPath));
        $cacheFile = $this->cacheDir . '/' . $cacheKey . '.css';
        
        // 캐시 확인
        if (file_exists($cacheFile) && (time() - filemtime($cacheFile)) < $this->cacheLifetime) {
            return file_get_contents($cacheFile);
        }
        
        $criticalCSS = '';
        
        // 1. CSS 변수 추출 (최우선)
        $criticalCSS .= $this->extractCSSVariables();
        
        // 2. 레이아웃 기본 스타일
        $criticalCSS .= $this->generateLayoutCSS();
        
        // 3. 타이포그래피 기본
        $criticalCSS .= $this->generateTypographyCSS();
        
        // 4. 버튼 기본 스타일
        $criticalCSS .= $this->generateButtonCSS();
        
        // 5. 네비게이션 스타일
        $criticalCSS .= $this->generateNavigationCSS();
        
        // 6. Bootstrap 호환 최소 클래스
        $criticalCSS .= $this->generateBootstrapMinimal();
        
        // 7. Tailwind 유틸리티 최소 세트
        $criticalCSS .= $this->generateTailwindUtilities();
        
        // CSS 최적화 (압축)
        $criticalCSS = $this->minifyCSS($criticalCSS);
        
        // 캐시 저장
        file_put_contents($cacheFile, $criticalCSS);
        
        return $criticalCSS;
    }
    
    /**
     * Natural Green 테마에서 CSS 변수 추출
     */
    private function extractCSSVariables() {
        if (!file_exists($this->naturalGreenPath)) {
            return $this->getDefaultCSSVariables();
        }
        
        $content = file_get_contents($this->naturalGreenPath);
        
        // :root 블록 추출
        if (preg_match('/:root\s*{([^}]+)}/s', $content, $matches)) {
            $variables = $matches[1];
            
            // 주석 제거 및 정리
            $variables = preg_replace('/\/\*.*?\*\//s', '', $variables);
            $variables = trim($variables);
            
            return ":root {\n" . $variables . "\n}\n\n";
        }
        
        return $this->getDefaultCSSVariables();
    }
    
    /**
     * 기본 CSS 변수 (폴백)
     */
    private function getDefaultCSSVariables() {
        return ":root {
  --font-size: 14px;
  --background: #f4f8f3;
  --foreground: oklch(0.145 0 0);
  --primary: #84cc16;
  --primary-foreground: #ffffff;
  --secondary: #e8f4e6;
  --border: rgba(132, 204, 22, 0.15);
  --radius: 0.625rem;
  --font-weight-medium: 500;
  --font-weight-normal: 400;
}

";
    }
    
    /**
     * 레이아웃 기본 CSS
     */
    private function generateLayoutCSS() {
        return "/* Layout Critical CSS */
* {
  box-sizing: border-box;
}

html {
  font-size: var(--font-size);
  line-height: 1.5;
}

body {
  margin: 0;
  padding: 0;
  font-family: 'Noto Sans KR', sans-serif;
  background-color: var(--background);
  color: var(--foreground);
  font-weight: var(--font-weight-normal);
}

.min-vh-100 {
  min-height: 100vh;
}

.d-flex {
  display: flex;
}

.flex-column {
  flex-direction: column;
}

";
    }
    
    /**
     * 타이포그래피 기본 CSS
     */
    private function generateTypographyCSS() {
        return "/* Typography Critical CSS */
h1, h2, h3, h4, h5, h6 {
  margin: 0 0 0.5rem 0;
  font-weight: var(--font-weight-medium);
  line-height: 1.2;
  color: var(--foreground);
}

h1 { font-size: 2.5rem; }
h2 { font-size: 2rem; }
h3 { font-size: 1.75rem; }
h4 { font-size: 1.5rem; }
h5 { font-size: 1.25rem; }
h6 { font-size: 1rem; }

p {
  margin: 0 0 1rem 0;
}

a {
  color: var(--primary);
  text-decoration: none;
}

a:hover {
  text-decoration: underline;
}

";
    }
    
    /**
     * 버튼 기본 CSS
     */
    private function generateButtonCSS() {
        return "/* Button Critical CSS */
.btn, button {
  display: inline-block;
  padding: 0.375rem 0.75rem;
  margin: 0;
  font-size: 1rem;
  font-weight: var(--font-weight-normal);
  line-height: 1.5;
  text-align: center;
  text-decoration: none;
  vertical-align: middle;
  cursor: pointer;
  border: 1px solid transparent;
  border-radius: var(--radius);
  background-color: transparent;
  transition: all 0.15s ease-in-out;
}

.btn-primary {
  color: var(--primary-foreground);
  background-color: var(--primary);
  border-color: var(--primary);
}

.btn-primary:hover {
  opacity: 0.9;
}

.btn-secondary {
  color: var(--foreground);
  background-color: var(--secondary);
  border-color: var(--border);
}

";
    }
    
    /**
     * 네비게이션 기본 CSS
     */
    private function generateNavigationCSS() {
        return "/* Navigation Critical CSS */
.navbar {
  position: relative;
  display: flex;
  flex-wrap: wrap;
  align-items: center;
  justify-content: space-between;
  padding: 0.5rem 1rem;
}

.navbar-brand {
  padding-top: 0.3125rem;
  padding-bottom: 0.3125rem;
  margin-right: 1rem;
  font-size: 1.25rem;
  font-weight: var(--font-weight-medium);
  text-decoration: none;
  color: inherit;
}

.navbar-nav {
  display: flex;
  flex-direction: row;
  padding-left: 0;
  margin-bottom: 0;
  list-style: none;
}

.nav-link {
  display: block;
  padding: 0.5rem 1rem;
  color: inherit;
  text-decoration: none;
  transition: color 0.15s ease-in-out;
}

.nav-link:hover {
  color: var(--primary);
}

/* Bootstrap Responsive Navigation */
.navbar-expand-lg {
  flex-wrap: nowrap;
  justify-content: flex-start;
}

@media (min-width: 992px) {
  .navbar-expand-lg .navbar-nav {
    flex-direction: row;
  }
  .navbar-expand-lg .navbar-nav .dropdown-menu {
    position: absolute;
  }
  .navbar-expand-lg .navbar-nav .nav-link {
    padding-right: 0.5rem;
    padding-left: 0.5rem;
  }
  .navbar-expand-lg .navbar-collapse {
    display: flex !important;
    flex-basis: auto;
  }
  .navbar-expand-lg .navbar-toggler {
    display: none;
  }
}

/* Dropdown Navigation */
.dropdown {
  position: relative;
}

.dropdown-menu {
  position: absolute;
  top: 100%;
  left: 0;
  z-index: 1000;
  display: none;
  min-width: 10rem;
  padding: 0.5rem 0;
  margin: 0;
  font-size: 1rem;
  color: var(--foreground);
  text-align: left;
  list-style: none;
  background-color: #fff;
  background-clip: padding-box;
  border: 1px solid var(--border);
  border-radius: var(--radius);
}

.dropdown-item {
  display: block;
  width: 100%;
  padding: 0.25rem 1rem;
  clear: both;
  font-weight: 400;
  color: var(--foreground);
  text-align: inherit;
  text-decoration: none;
  white-space: nowrap;
  background-color: transparent;
  border: 0;
}

.dropdown-item:hover,
.dropdown-item:focus {
  color: var(--primary);
  background-color: var(--secondary);
}

.nav-item {
  position: relative;
}

/* Mobile Navigation Toggle */
.navbar-toggler {
  padding: 0.25rem 0.75rem;
  font-size: 1.25rem;
  line-height: 1;
  background-color: transparent;
  border: 1px solid transparent;
  border-radius: var(--radius);
}

@media (max-width: 991.98px) {
  .navbar-expand-lg > .container,
  .navbar-expand-lg > .container-fluid,
  .navbar-expand-lg > .container-sm,
  .navbar-expand-lg > .container-md,
  .navbar-expand-lg > .container-lg,
  .navbar-expand-lg > .container-xl,
  .navbar-expand-lg > .container-xxl {
    padding-right: 0;
    padding-left: 0;
  }
}

";
    }
    
    /**
     * Bootstrap 호환 최소 클래스
     */
    private function generateBootstrapMinimal() {
        return "/* Bootstrap Minimal Critical CSS */
.container {
  width: 100%;
  padding-right: 15px;
  padding-left: 15px;
  margin-right: auto;
  margin-left: auto;
  box-sizing: border-box;
}

.container-xl {
  width: 100%;
  padding-right: 15px;
  padding-left: 15px;
  margin-right: auto;
  margin-left: auto;
  box-sizing: border-box;
}

@media (min-width: 576px) {
  .container { max-width: 540px; }
  .container-xl { max-width: 540px; }
}

@media (min-width: 768px) {
  .container { max-width: 720px; }
  .container-xl { max-width: 720px; }
}

@media (min-width: 992px) {
  .container { max-width: 960px; }
  .container-xl { max-width: 960px; }
}

@media (min-width: 1200px) {
  .container { max-width: 1140px; }
  .container-xl { max-width: 1140px; }
}

@media (min-width: 1400px) {
  .container-xl { max-width: 1320px; }
}

/* Page Layout Containers - Critical for proper centering */
#wrapper {
  width: 100%;
  max-width: 100%;
  margin: 0 auto;
  overflow-x: hidden;
  box-sizing: border-box;
}

#container_wr {
  width: 100%;
  max-width: 100%;
  margin: 0 auto;
  overflow-x: hidden;
  box-sizing: border-box;
}

#container {
  width: 100%;
  max-width: 1320px;
  margin: 0 auto;
  padding: 0 15px;
  overflow-x: hidden;
  box-sizing: border-box;
}

.container-fluid {
  width: 100%;
  padding-right: 15px;
  padding-left: 15px;
  margin-right: auto;
  margin-left: auto;
}

.row {
  display: flex;
  flex-wrap: wrap;
  margin-right: -15px;
  margin-left: -15px;
}

.col, .col-1, .col-2, .col-3, .col-4, .col-6, .col-12 {
  position: relative;
  width: 100%;
  padding-right: 15px;
  padding-left: 15px;
}

.col { flex: 1 0 0%; }
.col-1 { flex: 0 0 8.333333%; max-width: 8.333333%; }
.col-2 { flex: 0 0 16.666667%; max-width: 16.666667%; }
.col-3 { flex: 0 0 25%; max-width: 25%; }
.col-4 { flex: 0 0 33.333333%; max-width: 33.333333%; }
.col-6 { flex: 0 0 50%; max-width: 50%; }
.col-12 { flex: 0 0 100%; max-width: 100%; }

/* Essential Display Classes */
.d-none { display: none !important; }
.d-block { display: block !important; }
.d-flex { display: flex !important; }
.d-inline-flex { display: inline-flex !important; }

/* Responsive Display Classes */
@media (min-width: 768px) {
  .d-md-none { display: none !important; }
  .d-md-block { display: block !important; }
  .d-md-flex { display: flex !important; }
}

/* Mobile menu specific fixes */
@media (min-width: 768px) {
  #mobileMenu {
    display: none !important;
    visibility: hidden !important;
  }
  
  .ms-auto.d-md-none {
    display: none !important;
  }
}

/* Text Alignment */
.text-center { text-align: center; }
.text-left { text-align: left; }
.text-right { text-align: right; }

/* Flex Utilities */
.justify-content-center { justify-content: center; }
.justify-content-between { justify-content: space-between; }
.align-items-center { align-items: center; }
.flex-column { flex-direction: column; }

/* Spacing - Essential padding/margin */
.px-3 { padding-left: 1rem !important; padding-right: 1rem !important; }
.py-2 { padding-top: 0.5rem !important; padding-bottom: 0.5rem !important; }
.py-3 { padding-top: 1rem !important; padding-bottom: 1rem !important; }
.me-4 { margin-right: 1.5rem !important; }
.ms-auto { margin-left: auto !important; }
.h-100 { height: 100% !important; }

/* Position utilities */
.position-relative { position: relative !important; }
.position-absolute { position: absolute !important; }
.sticky-top { position: sticky !important; top: 0 !important; }
.fixed-top { position: fixed !important; top: 0 !important; left: 0 !important; right: 0 !important; z-index: 1030; }

/* Border utilities */
.border-bottom { border-bottom: 1px solid var(--border) !important; }

/* Background utilities */
.bg-white { background-color: #fff !important; }

/* Shadow utilities */
.shadow-sm { box-shadow: 0 0.125rem 0.25rem rgba(0, 0, 0, 0.075) !important; }

/* Z-index utilities */
.z-50 { z-index: 50 !important; }

/* Overflow utilities */
.overflow-auto { overflow: auto !important; }
.overflow-y-auto { overflow-y: auto !important; }
.overflow-x-auto { overflow-x: auto !important; }

/* Responsive overflow for navigation - Critical Fix */
@media (min-width: 768px) {
  .overflow-md-visible { overflow: visible !important; }
  
  /* Force navigation to not overflow */
  nav.d-none.d-md-flex,
  .d-none.d-md-flex.gap-1.overflow-auto.overflow-md-visible {
    overflow: visible !important;
    overflow-x: visible !important;
    overflow-y: visible !important;
  }
}

/* Global overflow control - Critical Fix for page alignment */
html {
  overflow-x: hidden;
  width: 100%;
  box-sizing: border-box;
}

body {
  overflow-x: hidden;
  width: 100%;
  max-width: 100vw;
  margin: 0;
  padding: 0;
  box-sizing: border-box;
}

/* Ensure all elements use border-box sizing */
*, *::before, *::after {
  box-sizing: border-box;
}

/* Width/Height utilities */
.w-100 { width: 100% !important; }

/* Gap utilities for flexbox */
.gap-1 { gap: 0.25rem !important; }

";
    }
    
    /**
     * Tailwind 유틸리티 최소 세트 (Critical Only)
     */
    private function generateTailwindUtilities() {
        return "/* Critical Utilities */
.flex{display:flex}.hidden{display:none}.block{display:block}.grid{display:grid}
.max-w-4xl{max-width:56rem;margin:0 auto}.max-w-7xl{max-width:80rem;margin:0 auto}
.mx-auto{margin:0 auto}.w-full{width:100%}.min-h-screen{min-height:100vh}
.p-0{padding:0}.p-4{padding:1rem}.px-3{padding:0 .75rem}.px-4{padding:0 1rem}
.py-2{padding:.5rem 0}.py-3{padding:.75rem 0}.m-0{margin:0}
.grid-cols-1{grid-template-columns:1fr}.grid-cols-2{grid-template-columns:1fr 1fr}
.gap-4{gap:1rem}.flex-1{flex:1}.flex-col{flex-direction:column}
.items-center{align-items:center}.justify-between{justify-content:space-between}
.justify-center{justify-content:center}
.text-sm{font-size:.875rem}.text-lg{font-size:1.125rem}.text-xl{font-size:1.25rem}
.text-2xl{font-size:1.5rem}.font-medium{font-weight:500}.font-bold{font-weight:700}
.text-center{text-align:center}
.bg-white{background:#fff}.bg-primary{background:var(--primary)}.text-primary{color:var(--primary)}
.border{border:1px solid #e5e7eb}.rounded{border-radius:.375rem}
@media (min-width:768px){.md\\:grid-cols-2{grid-template-columns:1fr 1fr}.md\\:flex-row{flex-direction:row}}
.sr-only{position:absolute!important;width:1px!important;height:1px!important;padding:0!important;margin:-1px!important;overflow:hidden!important;clip:rect(0,0,0,0)!important;white-space:nowrap!important;border:0!important}
";
    }
    
    /**
     * CSS 압축 (고급 압축)
     */
    private function minifyCSS($css) {
        // 주석 제거
        $css = preg_replace('/\/\*.*?\*\//s', '', $css);
        
        // 불필요한 공백 제거
        $css = preg_replace('/\s+/', ' ', $css);
        
        // 줄바꿈 제거
        $css = str_replace(["\n", "\r"], '', $css);
        
        // 세미콜론 앞 공백 제거
        $css = str_replace(' ;', ';', $css);
        
        // 중괄호 앞뒤 공백 정리
        $css = str_replace([' {', '{ ', ' }', '} '], ['{', '{', '}', '}'], $css);
        
        // 콜론 뒤 공백 정리
        $css = str_replace(': ', ':', $css);
        
        // 고급 압축: 색상값 압축
        $css = preg_replace('/#([0-9a-f])\1([0-9a-f])\2([0-9a-f])\3/i', '#$1$2$3', $css);
        
        // 0값 단위 제거
        $css = preg_replace('/\b0+\.?0*(px|em|rem|%|pt|pc|ex|cm|mm|in)\b/', '0', $css);
        
        // 불필요한 0 제거
        $css = preg_replace('/\b0\./', '.', $css);
        
        // 중복 세미콜론 제거
        $css = str_replace(';;', ';', $css);
        
        return trim($css);
    }
    
    /**
     * Critical CSS 크기 확인
     */
    public function getCriticalCSSSize() {
        $css = $this->generateCriticalCSS();
        return strlen($css);
    }
    
    /**
     * Critical CSS가 권장 크기 내인지 확인
     */
    public function isWithinRecommendedSize($maxSize = 7168) { // 7KB
        return $this->getCriticalCSSSize() <= $maxSize;
    }
    
    /**
     * 디버그 정보 반환
     */
    public function getDebugInfo() {
        $size = $this->getCriticalCSSSize();
        $maxSize = 7168; // 7KB
        
        return [
            'size_bytes' => $size,
            'size_kb' => round($size / 1024, 2),
            'max_recommended_kb' => 7,
            'within_limit' => $size <= $maxSize,
            'natural_green_exists' => file_exists($this->naturalGreenPath),
            'cache_dir' => $this->cacheDir,
            'cache_files' => glob($this->cacheDir . '/*.css')
        ];
    }
    
    /**
     * 캐시 클리어
     */
    public function clearCache() {
        $files = glob($this->cacheDir . '/*.css');
        foreach ($files as $file) {
            unlink($file);
        }
        return count($files);
    }
}