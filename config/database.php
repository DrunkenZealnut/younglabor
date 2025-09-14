<?php
/**
 * 데이터베이스 연결 설정
 */

return [
    'connections' => [
        'mysql' => [
            'host' => 'localhost',
            'port' => 3306,
            'database' => 'hopec',
            'username' => 'root',
            'password' => '',
            'charset' => 'utf8mb4',
            'collation' => 'utf8mb4_unicode_ci',
            'socket' => '/Applications/XAMPP/xamppfiles/var/mysql/mysql.sock',
            'options' => [
                PDO::ATTR_CASE => PDO::CASE_NATURAL,
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_ORACLE_NULLS => PDO::NULL_NATURAL,
                PDO::ATTR_STRINGIFY_FETCHES => false,
                PDO::ATTR_EMULATE_PREPARES => false,
            ]
        ]
    ],
    'prefixes' => [
        'modern' => 'hopec_'
    ],
    'query_log' => false
];
?>