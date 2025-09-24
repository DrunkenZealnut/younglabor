<?php
// Internal comment driver: uses board_comments table defined in create_board_tables.sql

if (!function_exists('comments_driver_internal_fetch')) {
    function comments_driver_internal_fetch(PDO $pdo, int $postId, array $options = []): array {
        $stmt = $pdo->prepare(
            'SELECT comment_id, post_id, user_id, author_name, content, parent_id, created_at '
            . 'FROM board_comments WHERE post_id = ? AND is_active = 1 ORDER BY comment_id ASC'
        );
        $stmt->execute([$postId]);
        return $stmt->fetchAll() ?: [];
    }
}

if (!function_exists('comments_driver_internal_create')) {
    function comments_driver_internal_create(PDO $pdo, array $payload): int {
        $stmt = $pdo->prepare(
            'INSERT INTO board_comments (post_id, user_id, author_name, content, parent_id, is_active, created_at, updated_at) '
            . 'VALUES (?, ?, ?, ?, ?, 1, NOW(), NOW())'
        );
        $stmt->execute([
            (int)$payload['post_id'],
            $payload['user_id'] ?? null,
            (string)$payload['author_name'],
            (string)$payload['content'],
            $payload['parent_id'] ?? null,
        ]);
        return (int)$pdo->lastInsertId();
    }
}

if (!function_exists('comments_driver_internal_delete')) {
    function comments_driver_internal_delete(PDO $pdo, int $commentId): bool {
        $d = $pdo->prepare('UPDATE board_comments SET is_active = 0, updated_at = NOW() WHERE comment_id = ?');
        return $d->execute([$commentId]);
    }
}


