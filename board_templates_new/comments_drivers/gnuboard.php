<?php
// GNUBOARD comment driver: read/write comments in g5_write_{bo_table}

if (!function_exists('comments_driver_gn_fetch')) {
    function comments_driver_gn_fetch(PDO $pdo, int $postId, array $options = []): array {
        $bo_table = preg_replace('/[^A-Za-z0-9_]/', '', (string)($options['bo_table'] ?? ''));
        if (!$bo_table) return [];
        global $g5;
        $write_prefix = is_array($g5 ?? null) && isset($g5['write_prefix']) ? $g5['write_prefix'] : 'g5_write_';
        $gn_table = $write_prefix . $bo_table;
        $sql = "SELECT wr_id, wr_parent, wr_name, wr_content, wr_datetime FROM {$gn_table} WHERE wr_is_comment = 1 AND wr_parent = ? ORDER BY wr_id ASC";
        $gs = $pdo->prepare($sql);
        $gs->execute([$postId]);
        $rows = $gs->fetchAll();
        return array_map(function($r){
            return [
                'comment_id' => (int)$r['wr_id'],
                'post_id' => (int)$r['wr_parent'],
                'user_id' => null,
                'author_name' => (string)$r['wr_name'],
                'content' => (string)$r['wr_content'],
                'parent_id' => 0,
                'created_at' => (string)$r['wr_datetime'],
            ];
        }, $rows ?: []);
    }
}

if (!function_exists('comments_driver_gn_create')) {
    function comments_driver_gn_create(PDO $pdo, array $payload, array $options = []): int {
        $bo_table = preg_replace('/[^A-Za-z0-9_]/', '', (string)($options['bo_table'] ?? ''));
        if (!$bo_table) return 0;
        global $g5;
        $write_prefix = is_array($g5 ?? null) && isset($g5['write_prefix']) ? $g5['write_prefix'] : 'g5_write_';
        $gn_table = $write_prefix . $bo_table;
        // GNUBOARD 댓글 컬럼 주요 필드 반영: wr_is_comment=1, wr_parent, mb_id
        $mb_id = isset($options['mb_id']) ? (string)$options['mb_id'] : '';
        // 부모 글의 wr_num을 가져와 댓글 정렬을 원형과 동일하게 맞춘다
        $pstmt = $pdo->prepare("SELECT wr_num FROM {$gn_table} WHERE wr_id = ? AND wr_is_comment = 0");
        $pstmt->execute([(int)$payload['post_id']]);
        $parent_num = (int)($pstmt->fetchColumn());
        if (!$parent_num) { $parent_num = 0; }

        // wr_id는 auto_increment가 아니므로 수동으로 신규 wr_id 생성
        $nextStmt = $pdo->query("SELECT IFNULL(MAX(wr_id),0)+1 AS next_id FROM {$gn_table}");
        $next_id = (int)$nextStmt->fetchColumn();

        // INSERT ... SET 형태로 필수 필드만 지정하여 버전별 칼럼 차이에 안전하게 저장
        $sql = "INSERT INTO {$gn_table}
                SET wr_id = :wr_id,
                    wr_num = :wr_num,
                    wr_reply = '',
                    wr_parent = :parent,
                    wr_is_comment = 1,
                    wr_comment = 0,
                    wr_comment_reply = '',
                    ca_name = '',
                    wr_option = '',
                    wr_subject = '',
                    wr_content = :content,
                    wr_link1 = '',
                    wr_link2 = '',
                    wr_link1_hit = 0,
                    wr_link2_hit = 0,
                    wr_hit = 0,
                    wr_good = 0,
                    wr_nogood = 0,
                    mb_id = :mb_id,
                    wr_password = '',
                    wr_name = :name,
                    wr_email = '',
                    wr_homepage = '',
                    wr_datetime = NOW(),
                    wr_last = NOW(),
                    wr_file = 0,
                    wr_ip = :ip,
                    wr_facebook_user = '',
                    wr_twitter_user = '',
                    wr_1 = '', wr_2 = '', wr_3 = '', wr_4 = '', wr_5 = '',
                    wr_6 = '', wr_7 = '', wr_8 = '', wr_9 = '', wr_10 = ''";
        $ins = $pdo->prepare($sql);
        $ins->execute([
            ':wr_id' => $next_id,
            ':wr_num' => $parent_num,
            ':parent' => (int)$payload['post_id'],
            ':content' => (string)$payload['content'],
            ':name' => (string)$payload['author_name'],
            ':mb_id' => $mb_id,
            ':ip' => $_SERVER['REMOTE_ADDR'] ?? '',
        ]);
        return $next_id;
    }
}

if (!function_exists('comments_driver_gn_delete')) {
    function comments_driver_gn_delete(PDO $pdo, int $commentId, array $options = []): bool {
        $bo_table = preg_replace('/[^A-Za-z0-9_]/', '', (string)($options['bo_table'] ?? ''));
        if (!$bo_table) return false;
        global $g5;
        $write_prefix = is_array($g5 ?? null) && isset($g5['write_prefix']) ? $g5['write_prefix'] : 'g5_write_';
        $gn_table = $write_prefix . $bo_table;
        $d = $pdo->prepare("DELETE FROM {$gn_table} WHERE wr_id = ? AND wr_is_comment = 1");
        return $d->execute([$commentId]);
    }
}


