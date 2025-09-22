<?php
/**
 * 데이터베이스 연결 및 호환성 헬퍼 함수
 * hopec_posts 통합 테이블과 기존 board_templates의 호환성 제공
 */

require_once __DIR__ . '/config.php';

/**
 * 데이터베이스 연결 반환
 * 기존 프로젝트의 db 연결을 활용
 */
function getBoardDatabase() {
    // 기존 프로젝트의 데이터베이스 연결 파일 확인
    $possibleConnections = [
        __DIR__ . '/../includes/db.php',
        __DIR__ . '/../config/database.php', 
        __DIR__ . '/../data/dbconfig.php'
    ];
    
    foreach ($possibleConnections as $connFile) {
        if (file_exists($connFile)) {
            require_once $connFile;
            break;
        }
    }
    
    // PDO 연결이 있는지 확인
    if (isset($pdo) && $pdo instanceof PDO) {
        return $pdo;
    }
    
    // 전역 변수들 확인
    if (isset($GLOBALS['pdo']) && $GLOBALS['pdo'] instanceof PDO) {
        return $GLOBALS['pdo'];
    }
    
    // 기본 연결 설정 (환경에 맞게 수정 필요)
    try {
        $host = defined('DB_HOST') ? DB_HOST : 'localhost';
        $dbname = defined('DB_NAME') ? DB_NAME : 'hopec';
        $username = defined('DB_USER') ? DB_USER : 'root';
        $password = defined('DB_PASS') ? DB_PASS : '';
        $charset = defined('DB_CHARSET') ? DB_CHARSET : 'utf8mb4';
        
        $dsn = "mysql:host=$host;dbname=$dbname;charset=$charset";
        $options = [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES => false,
        ];
        
        return new PDO($dsn, $username, $password, $options);
    } catch (PDOException $e) {
        error_log("Database connection failed: " . $e->getMessage());
        return null;
    }
}

/**
 * 게시글 목록 조회 (호환성 래퍼)
 */
function getBoardPosts($categoryType = 'FREE', $page = 1, $perPage = 15, $searchType = '', $searchKeyword = '') {
    $pdo = getBoardDatabase();
    if (!$pdo) return [];
    
    $boardType = getBoardType($categoryType);
    $offset = ($page - 1) * $perPage;
    
    // hopec_posts 통합 테이블 사용
    if (USE_HOPEC_POSTS) {
        $query = "SELECT wr_id as post_id, 0 as category_id, wr_subject as title, wr_content as content, 
                         wr_name as author_name, wr_hit as view_count, 0 as is_notice, wr_datetime as created_at 
                  FROM hopec_posts 
                  WHERE board_type = ? AND wr_is_comment = 0";
        
        $params = [$boardType];
        
        // 검색 조건
        if ($searchKeyword) {
            switch ($searchType) {
                case 'title':
                    $query .= " AND wr_subject LIKE ?";
                    $params[] = "%$searchKeyword%";
                    break;
                case 'content': 
                    $query .= " AND wr_content LIKE ?";
                    $params[] = "%$searchKeyword%";
                    break;
                case 'author':
                    $query .= " AND wr_name LIKE ?";
                    $params[] = "%$searchKeyword%";
                    break;
                default: // 'all'
                    $query .= " AND (wr_subject LIKE ? OR wr_content LIKE ? OR wr_name LIKE ?)";
                    $params[] = "%$searchKeyword%";
                    $params[] = "%$searchKeyword%";
                    $params[] = "%$searchKeyword%";
            }
        }
        
        $query .= " ORDER BY wr_id DESC LIMIT " . intval($perPage) . " OFFSET " . intval($offset);
        
        $stmt = $pdo->prepare($query);
        $stmt->execute($params);
        $results = $stmt->fetchAll();
        
    } else {
        // 기존 board_posts 테이블 사용
        $query = "SELECT post_id, category_id, title, content, author_name, view_count, is_notice, created_at 
                  FROM board_posts 
                  WHERE is_active = 1";
        
        $params = [];
        
        // 검색 조건
        if ($searchKeyword) {
            switch ($searchType) {
                case 'title':
                    $query .= " AND title LIKE ?";
                    $params[] = "%$searchKeyword%";
                    break;
                case 'content': 
                    $query .= " AND content LIKE ?";
                    $params[] = "%$searchKeyword%";
                    break;
                case 'author':
                    $query .= " AND author_name LIKE ?";
                    $params[] = "%$searchKeyword%";
                    break;
                default: // 'all'
                    $query .= " AND (title LIKE ? OR content LIKE ? OR author_name LIKE ?)";
                    $params[] = "%$searchKeyword%";
                    $params[] = "%$searchKeyword%";
                    $params[] = "%$searchKeyword%";
            }
        }
        
        $query .= " ORDER BY is_notice DESC, post_id DESC LIMIT " . intval($perPage) . " OFFSET " . intval($offset);
        
        $stmt = $pdo->prepare($query);
        $stmt->execute($params);
        $results = $stmt->fetchAll();
    }
    
    // 중복 방지: 고유 post_id로 필터링
    $uniqueResults = [];
    $seenIds = [];
    foreach ($results as $row) {
        if (!in_array($row['post_id'], $seenIds)) {
            $uniqueResults[] = $row;
            $seenIds[] = $row['post_id'];
        }
    }
    
    return $uniqueResults;
}

/**
 * 게시글 총 개수 조회
 */
function getBoardPostsCount($categoryType = 'FREE', $searchType = '', $searchKeyword = '') {
    $pdo = getBoardDatabase();
    if (!$pdo) return 0;
    
    $boardType = getBoardType($categoryType);
    
    // hopec_posts 통합 테이블 사용
    if (USE_HOPEC_POSTS) {
        $query = "SELECT COUNT(*) FROM hopec_posts WHERE board_type = ? AND wr_is_comment = 0";
        $params = [$boardType];
        
        // 검색 조건
        if ($searchKeyword) {
            switch ($searchType) {
                case 'title':
                    $query .= " AND wr_subject LIKE ?";
                    $params[] = "%$searchKeyword%";
                    break;
                case 'content':
                    $query .= " AND wr_content LIKE ?";
                    $params[] = "%$searchKeyword%";
                    break;
                case 'author':
                    $query .= " AND wr_name LIKE ?";
                    $params[] = "%$searchKeyword%";
                    break;
                default:
                    $query .= " AND (wr_subject LIKE ? OR wr_content LIKE ? OR wr_name LIKE ?)";
                    $params[] = "%$searchKeyword%";
                    $params[] = "%$searchKeyword%";
                    $params[] = "%$searchKeyword%";
            }
        }
    } else {
        // 기존 board_posts 테이블 사용
        $query = "SELECT COUNT(*) FROM board_posts WHERE is_active = 1";
        $params = [];
        
        // 검색 조건
        if ($searchKeyword) {
            switch ($searchType) {
                case 'title':
                    $query .= " AND title LIKE ?";
                    $params[] = "%$searchKeyword%";
                    break;
                case 'content':
                    $query .= " AND content LIKE ?";
                    $params[] = "%$searchKeyword%";
                    break;
                case 'author':
                    $query .= " AND author_name LIKE ?";
                    $params[] = "%$searchKeyword%";
                    break;
                default:
                    $query .= " AND (title LIKE ? OR content LIKE ? OR author_name LIKE ?)";
                    $params[] = "%$searchKeyword%";
                    $params[] = "%$searchKeyword%";
                    $params[] = "%$searchKeyword%";
            }
        }
    }
    
    $stmt = $pdo->prepare($query);
    $stmt->execute($params);
    return $stmt->fetchColumn();
}

/**
 * 단일 게시글 조회
 */
function getBoardPost($postId) {
    $pdo = getBoardDatabase();
    if (!$pdo) return null;
    
    $query = "SELECT post_id, category_id, user_id, title, content, author_name, view_count, 
                     is_notice, is_active, created_at, updated_at 
              FROM board_posts 
              WHERE post_id = ? AND is_active = 1";
    
    $stmt = executeCompatQuery($pdo, $query, [$postId]);
    $result = $stmt->fetch();
    
    return $result ? transformResultRow($result, 'board_posts') : null;
}

/**
 * 게시글 생성
 */
function createBoardPost($categoryType, $data) {
    $pdo = getBoardDatabase();
    if (!$pdo) return false;
    
    $boardType = getBoardType($categoryType);
    
    // 필수 필드 확인
    $requiredFields = ['title', 'content', 'author_name'];
    foreach ($requiredFields as $field) {
        if (empty($data[$field])) {
            return false;
        }
    }
    
    // 기본값 설정
    $postData = [
        'category_id' => 1, // 임시값, hopec에서는 board_type으로 처리
        'user_id' => $data['user_id'] ?? 0,
        'title' => $data['title'],
        'content' => $data['content'],
        'author_name' => $data['author_name'],
        'is_notice' => $data['is_notice'] ?? 0,
        'is_active' => 1
    ];
    
    return executeCompatInsert($pdo, 'board_posts', $postData, $boardType);
}

/**
 * 게시글 수정
 */
function updateBoardPost($postId, $data) {
    $pdo = getBoardDatabase();
    if (!$pdo) return false;
    
    // 허용된 필드만 수정
    $allowedFields = ['title', 'content', 'is_notice'];
    $updateData = [];
    
    foreach ($allowedFields as $field) {
        if (isset($data[$field])) {
            $updateData[$field] = $data[$field];
        }
    }
    
    if (empty($updateData)) return false;
    
    $where = ['post_id' => $postId];
    
    return executeCompatUpdate($pdo, 'board_posts', $updateData, $where);
}

/**
 * 게시글 삭제 (논리 삭제)
 */
function deleteBoardPost($postId) {
    $pdo = getBoardDatabase();
    if (!$pdo) return false;
    
    $updateData = ['is_active' => 0];
    $where = ['post_id' => $postId];
    
    return executeCompatUpdate($pdo, 'board_posts', $updateData, $where);
}

/**
 * 조회수 증가
 */
function incrementViewCount($postId) {
    $pdo = getBoardDatabase();
    if (!$pdo) return false;
    
    if (USE_HOPEC_POSTS) {
        $boardType = null; // 전체 검색이므로 board_type 제한 없음
        $query = "UPDATE hopec_posts SET wr_hit = wr_hit + 1 WHERE wr_id = ?";
        $stmt = $pdo->prepare($query);
        return $stmt->execute([$postId]);
    } else {
        $query = "UPDATE board_posts SET view_count = view_count + 1 WHERE post_id = ?";
        $stmt = $pdo->prepare($query);
        return $stmt->execute([$postId]);
    }
}

/**
 * 첨부파일 정보 조회
 */
function getBoardAttachments($postId) {
    $pdo = getBoardDatabase();
    if (!$pdo) return [];
    
    if (USE_HOPEC_POSTS) {
        // hopec_post_files 통합 테이블에서 조회
        try {
            $query = "SELECT bf_no as attachment_id, bf_source as original_name, bf_file as stored_name, 
                             bf_filesize as file_size, 'FILE' as file_type, bf_download as download_count
                      FROM hopec_post_files 
                      WHERE wr_id = ?
                      ORDER BY bf_no";
            
            $stmt = $pdo->prepare($query);
            $stmt->execute([$postId]);
            $results = $stmt->fetchAll();
            
            return array_map(function($row) {
                return [
                    'attachment_id' => (int)$row['attachment_id'],
                    'original_name' => (string)$row['original_name'],
                    'stored_name' => (string)$row['stored_name'],
                    'file_size' => (int)$row['file_size'],
                    'file_type' => (string)$row['file_type'],
                    'download_count' => (int)$row['download_count']
                ];
            }, $results);
        } catch (Exception $e) {
            error_log("hopec_post_files 첨부파일 조회 실패: " . $e->getMessage());
            return [];
        }
    } else {
        // 기존 board_attachments 테이블
        $query = "SELECT attachment_id, original_name, stored_name, file_size, file_type, download_count
                  FROM board_attachments 
                  WHERE post_id = ?
                  ORDER BY attachment_id";
        
        $stmt = executeCompatQuery($pdo, $query, [$postId]);
        $results = $stmt->fetchAll();
        
        return array_map(function($row) {
            return transformResultRow($row, 'board_attachments');
        }, $results);
    }
}

/**
 * 첨부파일 추가
 */
function addBoardAttachment($postId, $attachmentData) {
    $pdo = getBoardDatabase();
    if (!$pdo) return false;
    
    $data = [
        'post_id' => $postId,
        'original_name' => $attachmentData['original_name'],
        'stored_name' => $attachmentData['stored_name'], 
        'file_path' => $attachmentData['file_path'],
        'file_size' => $attachmentData['file_size'],
        'file_type' => $attachmentData['file_type'],
        'mime_type' => $attachmentData['mime_type']
    ];
    
    return executeCompatInsert($pdo, 'board_attachments', $data);
}

/**
 * 댓글 목록 조회
 */
function getBoardComments($postId) {
    $pdo = getBoardDatabase();
    if (!$pdo) return [];
    
    if (USE_HOPEC_POSTS) {
        // hopec_posts 통합 테이블에서 댓글 조회
        try {
            $query = "SELECT wr_id as comment_id, 0 as user_id, wr_name as author_name, 
                             wr_content as content, 0 as parent_id, wr_datetime as created_at
                      FROM hopec_posts 
                      WHERE wr_parent = ? AND wr_is_comment = 1
                      ORDER BY wr_id";
            
            $stmt = $pdo->prepare($query);
            $stmt->execute([$postId]);
            $results = $stmt->fetchAll();
            
            return array_map(function($row) {
                return [
                    'comment_id' => (int)$row['comment_id'],
                    'user_id' => (int)$row['user_id'],
                    'author_name' => (string)$row['author_name'],
                    'content' => (string)$row['content'],
                    'parent_id' => (int)$row['parent_id'],
                    'created_at' => (string)$row['created_at']
                ];
            }, $results);
        } catch (Exception $e) {
            error_log("hopec_posts 댓글 조회 실패: " . $e->getMessage());
            return [];
        }
    } else {
        // 기존 board_comments 테이블
        $query = "SELECT comment_id, user_id, author_name, content, parent_id, created_at
                  FROM board_comments 
                  WHERE post_id = ? AND is_active = 1
                  ORDER BY comment_id";
        
        $stmt = executeCompatQuery($pdo, $query, [$postId]);
        $results = $stmt->fetchAll();
        
        return array_map(function($row) {
            return transformResultRow($row, 'board_comments');
        }, $results);
    }
}

/**
 * 댓글 추가
 */
function addBoardComment($postId, $commentData) {
    $pdo = getBoardDatabase();
    if (!$pdo) return false;
    
    if (USE_HOPEC_POSTS) {
        // hopec_posts 통합 테이블에 댓글 추가
        try {
            // 새로운 wr_id 생성
            $next_id_stmt = $pdo->query("SELECT IFNULL(MAX(wr_id),0)+1 AS next_id FROM hopec_posts");
            $next_id = (int)$next_id_stmt->fetchColumn();
            
            // 부모 글의 board_type 조회
            $parent_stmt = $pdo->prepare("SELECT board_type FROM hopec_posts WHERE wr_id = ?");
            $parent_stmt->execute([$postId]);
            $board_type = $parent_stmt->fetchColumn() ?: 'FREE';
            
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
                        wr_ip = :ip";
                        
            $stmt = $pdo->prepare($sql);
            return $stmt->execute([
                ':wr_id' => $next_id,
                ':board_type' => $board_type,
                ':parent' => $postId,
                ':content' => $commentData['content'],
                ':name' => $commentData['author_name'],
                ':ip' => $_SERVER['REMOTE_ADDR'] ?? '',
            ]);
        } catch (Exception $e) {
            error_log("hopec_posts 댓글 추가 실패: " . $e->getMessage());
            return false;
        }
    } else {
        // 기존 board_comments 테이블
        $data = [
            'post_id' => $postId,
            'user_id' => $commentData['user_id'] ?? 0,
            'author_name' => $commentData['author_name'],
            'content' => $commentData['content'],
            'parent_id' => $commentData['parent_id'] ?? null,
            'is_active' => 1
        ];
        
        return executeCompatInsert($pdo, 'board_comments', $data);
    }
}

/**
 * 카테고리 목록 조회 
 */
function getBoardCategories() {
    if (USE_HOPEC_POSTS) {
        // hopec_board_config에서 조회 (board_skin 포함)
        $pdo = getBoardDatabase();
        if (!$pdo) return [];
        
        $query = "SELECT board_type as category_id, board_name as category_name, 
                         board_type, board_description as description, is_active,
                         board_skin, posts_per_page, use_category, use_file
                  FROM hopec_board_config 
                  WHERE is_active = 1
                  ORDER BY sort_order, board_type";
        
        $stmt = $pdo->prepare($query);
        $stmt->execute();
        return $stmt->fetchAll();
    } else {
        $pdo = getBoardDatabase(); 
        if (!$pdo) return [];
        
        $query = "SELECT category_id, category_name, category_type, description, is_active
                  FROM board_categories 
                  WHERE is_active = 1
                  ORDER BY category_id";
        
        $stmt = $pdo->prepare($query);
        $stmt->execute();
        return $stmt->fetchAll();
    }
}

/**
 * 특정 게시판의 설정 조회 (board_skin 포함)
 */
function getBoardConfig($categoryType) {
    if (USE_HOPEC_POSTS) {
        $pdo = getBoardDatabase();
        if (!$pdo) return null;
        
        $boardType = getBoardType($categoryType);
        
        $query = "SELECT board_type, board_name, board_skin, board_description,
                         posts_per_page, use_category, use_file, use_comment,
                         gallery_cols, gallery_rows, thumbnail_width, thumbnail_height
                  FROM hopec_board_config 
                  WHERE board_type = ? AND is_active = 1";
        
        $stmt = $pdo->prepare($query);
        $stmt->execute([$boardType]);
        return $stmt->fetch();
    } else {
        // 기존 방식에서는 기본값 반환
        return [
            'board_skin' => 'basic',
            'posts_per_page' => 15,
            'use_category' => 0,
            'use_file' => 1,
            'use_comment' => 1
        ];
    }
}

/**
 * 게시판 스킨에 따른 설정값 반환
 */
function getBoardSkinConfig($boardSkin) {
    $skinConfigs = [
        'basic' => [
            'view_mode' => 'table',
            'show_thumbnails' => false,
            'grid_cols_class' => 'grid-cols-1',
            'card_aspect_ratio' => '16/9'
        ],
        'gallery' => [
            'view_mode' => 'card',
            'show_thumbnails' => true,
            'grid_cols_class' => 'grid-cols-1 sm:grid-cols-2 lg:grid-cols-4',
            'card_aspect_ratio' => '1/1'
        ],
        'webzine' => [
            'view_mode' => 'card',
            'show_thumbnails' => true,
            'grid_cols_class' => 'grid-cols-1 sm:grid-cols-2 lg:grid-cols-3',
            'card_aspect_ratio' => '16/9'
        ],
        'qna' => [
            'view_mode' => 'table',
            'show_author' => true,
            'show_reply_count' => true,
            'enable_answer_status' => true
        ],
        'faq' => [
            'view_mode' => 'accordion',
            'show_category' => true,
            'enable_collapse' => true
        ]
    ];
    
    return $skinConfigs[$boardSkin] ?? $skinConfigs['basic'];
}
?>