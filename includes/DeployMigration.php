<?php
/**
 * 운영 DB 이관 (GitHub Actions 수동 실행 → api/deploy.php)
 * 운영 서버에 PHP CLI가 없어 scripts/migrate-renewal.php 대신 서명된 웹 요청으로 실행한다.
 */

require_once __DIR__ . '/RenewalMigration.php';
require_once __DIR__ . '/RenewalSeed.php';

// 서명된 요청을 다시 보내는 재사용(replay)을 막기 위한 허용 시차 (초)
const DEPLOY_MIGRATION_MAX_AGE = 300;

const DEPLOY_MIGRATION_TABLES = [
    'content_posts', 'content_files',
    'work_areas', 'work_funding', 'work_units', 'work_programs', 'work_sessions', 'work_unit_areas',
    'renewal_migrations',
];

function deployMigrationRequestIsFresh(array $data, int $now): bool
{
    $requestedAt = $data['requested_at'] ?? null;
    return is_int($requestedAt) && abs($now - $requestedAt) <= DEPLOY_MIGRATION_MAX_AGE;
}

function deployMigrationColumnExists(PDO $pdo, string $table, string $column): bool
{
    $q = $pdo->prepare('SELECT COUNT(*) FROM information_schema.columns WHERE table_schema = DATABASE() AND table_name = ? AND column_name = ?');
    $q->execute([$table, $column]);
    return (bool)$q->fetchColumn();
}

// 읽기 전용: 테이블 존재 여부, 적용된 이관, 초기 콘텐츠 건수
function deployMigrationStatus(PDO $pdo): array
{
    $tables = [];
    foreach (DEPLOY_MIGRATION_TABLES as $table) {
        $tables[$table] = renewalTableExists($pdo, $table);
    }

    $applied = $tables['renewal_migrations']
        ? $pdo->query('SELECT name FROM renewal_migrations ORDER BY name')->fetchAll(PDO::FETCH_COLUMN)
        : [];

    // 문의 폼(api/contact.php)이 쓰는 컬럼. 이관은 inquiries가 없으면 이 컬럼을 건너뛰므로 따로 확인한다
    $columns = ['inquiries.inquiry_type' => deployMigrationColumnExists($pdo, 'inquiries', 'inquiry_type')];

    $counts = [];
    foreach (['work_areas', 'work_programs', 'work_sessions'] as $table) {
        $counts[$table] = $tables[$table] ? (int)$pdo->query("SELECT COUNT(*) FROM $table")->fetchColumn() : null;
    }

    return [
        'pending' => in_array(false, $tables, true) || in_array(false, $columns, true) || !in_array('20261011-renewal', $applied, true),
        'tables' => $tables,
        'columns' => $columns,
        'applied' => $applied,
        'counts' => $counts,
    ];
}

// 재실행 가능: 기존 글·문의·관리자 편집값을 보존하고 없는 스키마와 초기 콘텐츠만 추가
// (slug 기준이라 관리자가 지운 초기 콘텐츠는 재실행 시 다시 생긴다)
function deployMigrationApply(PDO $pdo): array
{
    // 머지 직후 첫 배포는 옛 deploy.php가 database/를 제외하므로 SQL 파일이 아직 없을 수 있다
    $baseSchema = __DIR__ . '/../database/migrations/20260921_create_managed_content.sql';
    if (!renewalTableExists($pdo, 'content_posts') && !is_file($baseSchema)) {
        throw new RuntimeException('database/migrations is not deployed yet. Re-run the Deploy to Cafe24 workflow first.');
    }

    migrateRenewal($pdo);
    seedRenewal($pdo);
    return deployMigrationStatus($pdo);
}
