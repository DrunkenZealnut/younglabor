<?php
/**
 * Natural Green 단일 테마 로더
 * 복잡한 동적 생성 없이 정적 CSS 파일만 로드
 * 
 * Version: 3.0.0
 * Author: 희망씨
 * Description: 단일 테마 시스템으로 성능과 유지보수성 최적화
 */

class NaturalGreenThemeLoader {
    private $themeConfig;
    private $cssPath;
    private $cssUrl;
    
    public function __construct() {
        // 테마 설정 로드
        $configPath = __DIR__ . '/../theme/natural-green/config/theme.php';
        if (file_exists($configPath)) {
            $this->themeConfig = include $configPath;
        } else {
            $this->themeConfig = $this->getDefaultConfig();
        }
        
        // CSS 경로 설정
        $this->cssPath = __DIR__ . '/../css/theme.css';
        $this->cssUrl = function_exists('app_url') ? app_url('css/theme.css') : '/css/theme.css';
    }
    
    /**
     * 테마 CSS를 HTML head에 출력
     */
    public function renderThemeCSS() {
        if (!file_exists($this->cssPath)) {
            echo "<!-- Natural Green CSS not found -->\n";
            return;
        }
        
        $version = filemtime($this->cssPath);
        $cssUrl = $this->cssUrl . '?v=' . $version . '&cb=' . time();
        
        echo "<!-- Natural Green Theme v{$this->themeConfig['version']} -->\n";
        echo "<link rel=\"stylesheet\" href=\"{$cssUrl}\" id=\"natural-green-theme\">\n";
        
        // 테마 정보를 JavaScript로 내보내기
        $this->renderThemeJS();
    }
    
    /**
     * 테마 정보 JavaScript 출력
     */
    private function renderThemeJS() {
        $safeConfig = [
            'theme_name' => $this->themeConfig['theme_name'],
            'display_name' => $this->themeConfig['display_name'],
            'version' => $this->themeConfig['version'],
            'primary' => $this->themeConfig['primary']
        ];
        
        echo "<script>\n";
        echo "window.younglabor_THEME = " . json_encode($safeConfig) . ";\n";
        echo "</script>\n";
    }
    
    /**
     * 테마 설정 가져오기
     */
    public function getConfig($key = null, $default = null) {
        if ($key === null) {
            return $this->themeConfig;
        }
        
        return $this->themeConfig[$key] ?? $default;
    }
    
    /**
     * 사이트 이름 반환
     */
    public function getSiteName() {
        return $this->getConfig('site_name', '희망연대노동조합');
    }
    
    /**
     * 사이트 타이틀 반환
     */
    public function getSiteTitle() {
        return $this->getConfig('title', '희망연대노동조합');
    }
    
    /**
     * 사이트 설명 반환
     */
    public function getSiteDescription() {
        return $this->getConfig('content', '이웃과 함께하는 노동권 보호');
    }
    
    /**
     * 기본 색상 반환
     */
    public function getPrimaryColor() {
        return $this->getConfig('primary', '#84cc16');
    }
    
    /**
     * CSS 변수 값 반환
     */
    public function getCSSVar($varName, $default = null) {
        return $this->getConfig($varName, $default);
    }
    
    /**
     * 기본 설정 반환 (fallback)
     */
    private function getDefaultConfig() {
        return [
            'theme_name' => 'natural-green',
            'display_name' => 'Natural Green',
            'description' => '자연스럽고 친환경적인 녹색 테마',
            'version' => '3.0.0',
            'author' => '희망씨',
            'primary' => '#84cc16',
            'background' => '#f4f8f3',
            'foreground' => 'oklch(0.145 0 0)',
            'site_name' => '희망연대노동조합',
            'title' => '희망연대노동조합',
            'content' => '이웃과 함께하는 노동권 보호',
            'hero_style' => 'gradient',
            'layout' => 'standard',
        ];
    }
    
    /**
     * 테마 상태 확인
     */
    public function isThemeReady() {
        return file_exists($this->cssPath) && !empty($this->themeConfig);
    }
    
    /**
     * 테마 정보 반환 (디버깅용)
     */
    public function getThemeInfo() {
        return [
            'config_loaded' => !empty($this->themeConfig),
            'css_exists' => file_exists($this->cssPath),
            'css_path' => $this->cssPath,
            'css_url' => $this->cssUrl,
            'css_size' => file_exists($this->cssPath) ? filesize($this->cssPath) : 0,
            'css_modified' => file_exists($this->cssPath) ? filemtime($this->cssPath) : 0,
            'theme_name' => $this->getConfig('theme_name'),
            'version' => $this->getConfig('version')
        ];
    }
}

// 전역 함수로 쉽게 사용할 수 있도록
if (!function_exists('getNaturalGreenTheme')) {
    function getNaturalGreenTheme() {
        static $loader = null;
        if ($loader === null) {
            $loader = new NaturalGreenThemeLoader();
        }
        return $loader;
    }
}

if (!function_exists('renderNaturalGreenTheme')) {
    function renderNaturalGreenTheme() {
        $loader = getNaturalGreenTheme();
        $loader->renderThemeCSS();
    }
}

if (!function_exists('getThemeSetting')) {
    function getThemeSetting($key, $default = null) {
        $loader = getNaturalGreenTheme();
        return $loader->getConfig($key, $default);
    }
}

// Theme class helper function for consistency between legacy and optimized modes
if (!function_exists('getThemeClass')) {
    function getThemeClass($type, $category, $shade = null) {
        // 완전한 테마 클래스 매핑 - globals.css 변수만 사용
        $themeMapping = [
            'text' => [
                'primary' => [
                    '500' => 'text-lime-500',
                    '600' => 'text-lime-600', 
                    '700' => 'text-forest-700',
                    '800' => 'text-forest-700',
                    '900' => 'text-forest-700'
                ],
                'secondary' => [
                    '400' => 'text-lime-400',
                    '500' => 'text-lime-500',
                    '600' => 'text-lime-600'
                ],
                'foreground' => 'text-forest-700',
                'muted-foreground' => 'text-gray-500',
                'white' => 'text-white'
            ],
            'bg' => [
                'primary' => [
                    '100' => 'bg-natural-100',
                    '200' => 'bg-natural-200',
                    '300' => 'bg-lime-200',
                    '500' => 'bg-lime-500',
                    '600' => 'bg-lime-600'
                ],
                'secondary' => [
                    '100' => 'bg-natural-100',
                    '500' => 'bg-lime-500'
                ],
                'background' => [
                    '50' => 'bg-natural-50',
                    '100' => 'bg-natural-100'
                ],
                'gray' => [
                    '50' => 'bg-natural-50',
                    '200' => 'bg-natural-200'
                ],
                'white' => 'bg-white',
                'warning' => [
                    '50' => 'bg-warning-muted'
                ],
                'danger' => [
                    '100' => 'bg-error-muted'
                ]
            ],
            'border' => [
                'primary' => [
                    '500' => 'border-lime-500'
                ],
                'secondary' => [
                    '500' => 'border-lime-500'
                ],
                'border' => [
                    '200' => 'border-lime-200'
                ],
                'gray' => [
                    '200' => 'border-lime-200'
                ]
            ]
        ];
        
        if (isset($themeMapping[$type][$category])) {
            if (is_array($themeMapping[$type][$category]) && $shade !== null) {
                return $themeMapping[$type][$category][$shade] ?? '';
            }
            return $themeMapping[$type][$category] ?? '';
        }
        
        return '';
    }
}