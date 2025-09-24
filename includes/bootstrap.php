<?php
/**
 * HOPEC Framework Bootstrap
 * 프레임워크 핵심 초기화 및 설정 (_common.php 대체)
 */

// 중복 실행 방지
if (defined('HOPEC_BOOTSTRAP_LOADED')) {
    return;
}
define('HOPEC_BOOTSTRAP_LOADED', true);

// HOPEC 프레임워크 상수 정의
if (!defined('_HOPEC_')) {
    define('_HOPEC_', true);
}

// EnvLoader 초기 로드
if (!function_exists('env')) {
    require_once(__DIR__.'/EnvLoader.php');
    EnvLoader::load();
}

// SecurityManager 로드 및 초기화
require_once(__DIR__.'/SecurityManager.php');
SecurityManager::initialize();

// 환경변수 기반 디버그 모드 설정
if (env('APP_DEBUG', false) || env('APP_ENV') === 'local') {
    @ini_set('display_errors', '1');
    @ini_set('display_startup_errors', '1');
    @error_reporting(E_ALL);
}

// Configuration loader
require_once(__DIR__.'/config_loader.php');

// 데이터베이스 설정 로드
$database_config = require(__DIR__.'/../config/database.php');
$GLOBALS['hopec_config']['database'] = $database_config;

// DatabaseManager 로드 및 초기화
require_once(__DIR__.'/DatabaseManager.php');
DatabaseManager::initialize();

// Organization Helper 로드
require_once(__DIR__.'/organization_helper.php');

// Path Helper 로드
require_once(__DIR__.'/path_helper.php');

