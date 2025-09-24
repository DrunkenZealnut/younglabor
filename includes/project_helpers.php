<?php
/**
 * 프로젝트 공통 헬퍼 함수들
 * 다른 프로젝트에서 재사용 가능한 공통 로직
 */

if (!function_exists('define_project_base_path')) {
    /**
     * .env 파일에서 상수 이름을 읽어와서 동적으로 BASE_PATH 상수를 정의
     * 
     * @param string $currentDir 현재 디렉토리 (__DIR__)
     * @param int $levelsUp 상위로 올라갈 단계 수
     */
    function define_project_base_path($currentDir, $levelsUp = 1) {
        $basePath = $levelsUp > 0 ? dirname($currentDir, $levelsUp) : $currentDir;
        
        // Define PROJECT_BASE_PATH if not already defined
        if (!defined('PROJECT_BASE_PATH')) {
            define('PROJECT_BASE_PATH', $basePath);
        }
        
        return 'PROJECT_BASE_PATH';
    }
}

if (!function_exists('get_project_base_path')) {
    /**
     * 현재 정의된 프로젝트 BASE_PATH 상수값을 반환
     * 
     * @return string 프로젝트 루트 경로
     */
    function get_project_base_path() {
        if (defined('PROJECT_BASE_PATH')) {
            return PROJECT_BASE_PATH;
        }
        
        // fallback - 현재 디렉토리에서 상위로 추정
        return dirname(__DIR__);
    }
}
?>