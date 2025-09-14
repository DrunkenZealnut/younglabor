<?php
/**
 * 갤러리 중복 문제 종합 해결 스크립트
 */

require_once __DIR__ . '/bootstrap/app.php';

echo "=== Gallery Duplication Fix Script ===\n\n";

// 1. 데이터베이스 중복 확인
echo "1. Checking for duplicate entries in database...\n";

$duplicate_check = "
SELECT wr_id, wr_subject, COUNT(*) as count 
FROM hopec_gallery 
GROUP BY wr_subject, wr_content, wr_datetime 
HAVING COUNT(*) > 1
ORDER BY count DESC
";

$duplicates = DatabaseManager::select($duplicate_check);
if (!empty($duplicates)) {
    echo "Found " . count($duplicates) . " potential duplicates:\n";
    foreach ($duplicates as $dup) {
        echo "  ID: {$dup['wr_id']}, Subject: {$dup['wr_subject']}, Count: {$dup['count']}\n";
    }
} else {
    echo "No database duplicates found.\n";
}

echo "\n";

// 2. 페이지네이션 경계 확인
echo "2. Checking pagination boundaries...\n";

$per_page = 12;
$total_count = DatabaseManager::selectOne("SELECT COUNT(*) as total FROM hopec_gallery")['total'];
$total_pages = ceil($total_count / $per_page);

echo "Total posts: $total_count, Total pages: $total_pages\n";

// 마지막 2페이지 상세 확인
if ($total_pages > 1) {
    $last_page = $total_pages;
    $prev_page = $total_pages - 1;
    
    // 마지막-1 페이지
    $prev_offset = ($prev_page - 1) * $per_page;
    $prev_posts = DatabaseManager::select(
        "SELECT wr_id, wr_subject FROM hopec_gallery ORDER BY wr_id DESC LIMIT $per_page OFFSET $prev_offset"
    );
    
    // 마지막 페이지
    $last_offset = ($last_page - 1) * $per_page;
    $last_posts = DatabaseManager::select(
        "SELECT wr_id, wr_subject FROM hopec_gallery ORDER BY wr_id DESC LIMIT $per_page OFFSET $last_offset"
    );
    
    echo "\nPage $prev_page (offset: $prev_offset) - " . count($prev_posts) . " posts:\n";
    foreach ($prev_posts as $idx => $post) {
        echo "  [$idx] ID: {$post['wr_id']}\n";
    }
    
    echo "\nPage $last_page (offset: $last_offset) - " . count($last_posts) . " posts:\n";
    foreach ($last_posts as $idx => $post) {
        echo "  [$idx] ID: {$post['wr_id']}\n";
    }
    
    // 중복 검사
    $prev_ids = array_column($prev_posts, 'wr_id');
    $last_ids = array_column($last_posts, 'wr_id');
    $overlapping = array_intersect($prev_ids, $last_ids);
    
    if (!empty($overlapping)) {
        echo "\n!!! OVERLAPPING IDs FOUND: " . implode(', ', $overlapping) . "\n";
    } else {
        echo "\nNo overlapping IDs between last two pages.\n";
    }
}

echo "\n";

// 3. 갤러리 테이블 구조 확인
echo "3. Checking table structure...\n";

$table_info = DatabaseManager::select("DESCRIBE hopec_gallery");
echo "Table structure:\n";
foreach ($table_info as $column) {
    echo "  {$column['Field']} - {$column['Type']} - {$column['Key']}\n";
}

echo "\n";

// 4. 인덱스 확인
echo "4. Checking indexes...\n";

$indexes = DatabaseManager::select("SHOW INDEX FROM hopec_gallery");
echo "Indexes:\n";
foreach ($indexes as $idx) {
    echo "  {$idx['Key_name']} on {$idx['Column_name']}\n";
}

echo "\n";

// 5. 최근 데이터 샘플 확인
echo "5. Recent data sample...\n";

$recent = DatabaseManager::select("SELECT wr_id, wr_subject, wr_datetime FROM hopec_gallery ORDER BY wr_id DESC LIMIT 5");
foreach ($recent as $post) {
    echo "  ID: {$post['wr_id']}, Subject: " . mb_substr($post['wr_subject'], 0, 50) . ", Date: {$post['wr_datetime']}\n";
}

echo "\n=== Fix Script Complete ===\n";