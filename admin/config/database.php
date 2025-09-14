<?php
/**
 * Database Configuration
 * 데이터베이스 설정 파일
 */

return [
    // 기본 연결
    'default' => env('DB_CONNECTION', 'mysql'),
    
    // 테이블 프리픽스
    'prefix' => env('DB_PREFIX', ''),
    
    // 연결 설정
    'connections' => [
        'mysql' => [
            'driver' => 'mysql',
            'host' => env('DB_HOST', 'localhost'),
            'port' => env('DB_PORT', 3306),
            'database' => env('DB_DATABASE', ''),
            'username' => env('DB_USERNAME', 'root'),
            'password' => env('DB_PASSWORD', ''),
            'charset' => env('DB_CHARSET', 'utf8mb4'),
            'collation' => env('DB_COLLATION', 'utf8mb4_unicode_ci'),
            'socket' => env('DB_SOCKET', ''),
            'options' => [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES => false,
            ],
        ],
    ],
    
    // 마이그레이션 테이블
    'migrations_table' => 'migrations',
];