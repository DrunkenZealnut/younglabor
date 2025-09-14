<?php
// HopecPosts comment driver: pure hopec_posts integration (gnuboard compatibility removed)
// Uses hopec_posts unified table system only

if (!function_exists('comments_driver_gn_fetch')) {
    function comments_driver_gn_fetch(PDO $pdo, int $postId, array $options = []): array {
        try {
            // hopec_posts에서 댓글 조회
            $sql = "SELECT wr_id as comment_id, wr_id as post_id, wr_name as author_name, 
                           wr_content as content, wr_datetime as created_at, wr_ip,
                           board_type
                    FROM hopec_posts 
                    WHERE wr_parent = ? AND wr_is_comment = 1 
                    ORDER BY wr_id ASC";
            $stmt = $pdo->prepare($sql);
            $stmt->execute([$postId]);
            $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            return array_map(function($r) use ($postId) {
                return [
                    'comment_id' => (int)$r['comment_id'],
                    'post_id' => $postId,
                    'user_id' => null,
                    'author_name' => (string)$r['author_name'],
                    'content' => (string)$r['content'],
                    'parent_id' => 0,
                    'created_at' => (string)$r['created_at'],
                    'board_type' => (string)($r['board_type'] ?? ''),
                ];
            }, $rows ?: []);
        } catch (Exception $e) {
            error_log("hopec_posts 댓글 조회 실패: " . $e->getMessage());
            return [];
        }
    }
}

if (!function_exists('comments_driver_gn_create')) {
    function comments_driver_gn_create(PDO $pdo, array $payload, array $options = []): int {
        try {
            // 다음 wr_id 생성
            $next_id_stmt = $pdo->query("SELECT IFNULL(MAX(wr_id), 0) + 1 AS next_id FROM hopec_posts");
            $next_id = (int)$next_id_stmt->fetchColumn();
            
            // 부모 글의 board_type 조회
            $parent_stmt = $pdo->prepare("SELECT board_type FROM hopec_posts WHERE wr_id = ?");
            $parent_stmt->execute([(int)$payload['post_id']]);
            $board_type = $parent_stmt->fetchColumn();
            
            if (!$board_type) {
                // 부모 글이 없으면 기본값 사용
                $board_type = $options['board_type'] ?? 'notice';
            }
            
            // hopec_posts에 댓글 추가
            $sql = "INSERT INTO hopec_posts 
                    SET wr_id = :wr_id,
                        board_type = :board_type,
                        wr_parent = :parent,
                        wr_is_comment = 1,
                        wr_subject = '',
                        wr_content = :content,
                        wr_name = :name,
                        wr_datetime = NOW(),
                        wr_last = NOW(),
                        wr_ip = :ip,
                        wr_hit = 0,
                        wr_good = 0,
                        wr_nogood = 0";
                        
            $stmt = $pdo->prepare($sql);
            $result = $stmt->execute([
                ':wr_id' => $next_id,
                ':board_type' => $board_type,
                ':parent' => (int)$payload['post_id'],
                ':content' => (string)$payload['content'],
                ':name' => (string)$payload['author_name'],
                ':ip' => $_SERVER['REMOTE_ADDR'] ?? '127.0.0.1',
            ]);
            
            return $result ? $next_id : 0;
        } catch (Exception $e) {
            error_log("hopec_posts 댓글 작성 실패: " . $e->getMessage());
            return 0;
        }
    }
}

if (!function_exists('comments_driver_gn_delete')) {
    function comments_driver_gn_delete(PDO $pdo, int $commentId, array $options = []): bool {
        try {
            // hopec_posts에서 댓글 삭제 (실제 삭제)
            $stmt = $pdo->prepare("DELETE FROM hopec_posts WHERE wr_id = ? AND wr_is_comment = 1");
            return $stmt->execute([$commentId]);
        } catch (Exception $e) {
            error_log("hopec_posts 댓글 삭제 실패: " . $e->getMessage());
            return false;
        }
    }
}

if (!function_exists('comments_driver_gn_update')) {
    function comments_driver_gn_update(PDO $pdo, int $commentId, array $payload, array $options = []): bool {
        try {
            // hopec_posts에서 댓글 수정
            $sql = "UPDATE hopec_posts 
                    SET wr_content = :content,
                        wr_last = NOW()
                    WHERE wr_id = ? AND wr_is_comment = 1";
                    
            $stmt = $pdo->prepare($sql);
            return $stmt->execute([
                ':content' => (string)$payload['content'],
                $commentId
            ]);
        } catch (Exception $e) {
            error_log("hopec_posts 댓글 수정 실패: " . $e->getMessage());
            return false;
        }
    }
}

// 댓글 개수 조회 헬퍼 함수
if (!function_exists('comments_driver_gn_count')) {
    function comments_driver_gn_count(PDO $pdo, int $postId): int {
        try {
            $stmt = $pdo->prepare("SELECT COUNT(*) FROM hopec_posts WHERE wr_parent = ? AND wr_is_comment = 1");
            $stmt->execute([$postId]);
            return (int)$stmt->fetchColumn();
        } catch (Exception $e) {
            error_log("hopec_posts 댓글 개수 조회 실패: " . $e->getMessage());
            return 0;
        }
    }
}