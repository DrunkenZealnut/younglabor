<?php
/**
 * 게시글 본문 내 이미지 처리를 위한 유틸리티 함수
 */

/**
 * 콘텐츠 내의 임시 이미지 처리 함수
 * 임시 폴더에 있는 이미지를 실제 게시글 폴더로 이동하지 않고 경로만 정리
 * 
 * @param string $content 게시글 내용
 * @param int $post_id 게시글 ID
 * @param PDO $pdo 데이터베이스 연결 객체
 * @return string 처리된 HTML 콘텐츠
 */
function processContentImages($content, $post_id, $pdo) {
    // 이미지 파일 찾기
    preg_match_all('/<img[^>]+src="([^"]+)"[^>]*>/i', $content, $matches);
    
    if (isset($matches[1]) && is_array($matches[1])) {
        // 현재 날짜로 연도/월 폴더 구조 생성
        $date = new DateTime();
        $year = $date->format('Y');
        $month = $date->format('m');
        
        foreach ($matches[1] as $image_src) {
            error_log("처리 전 이미지 경로: " . $image_src);
            
            // 중복된 baseUrl 제거 (http://localhost:8012/http:/localhost:8012/...)
            if (preg_match('/^(https?:\/\/[^\/]+\/[^\/]+)\/(https?:\/\/)/', $image_src, $url_matches)) {
                $fixed_path = preg_replace('/^(https?:\/\/[^\/]+\/[^\/]+)\/(https?:\/\/)/', '$2', $image_src);
                error_log("중복 URL 감지 및 수정: " . $image_src . " -> " . $fixed_path);
                $content = str_replace($image_src, $fixed_path, $content);
                $image_src = $fixed_path;
            }
            
            // 절대 URL 패턴 검사 (http:// 또는 https://)
            if (preg_match('/^https?:\/\//i', $image_src)) {
                // 도메인과 경로 추출
                $parsed_url = parse_url($image_src);
                
                // 로컬 서버 URL에서 파일 경로 부분만 추출
                if (isset($parsed_url['path'])) {
                    $path = $parsed_url['path'];
                    
                    // 경로가 /uploads/로 시작하면 상대 경로로 변환
                    if (strpos($path, '/uploads/') === 0) {
                        $fixed_path = substr($path, 1); // 앞의 / 제거
                        error_log("절대 URL을 상대 경로로 변환: " . $image_src . " -> " . $fixed_path);
                        $content = str_replace($image_src, $fixed_path, $content);
                        $image_src = $fixed_path;
                    }
                }
            }
            
            // 경로에서 이중 슬래시 제거
            $clean_image_src = str_replace('//', '/', $image_src);
            if ($clean_image_src !== $image_src) {
                // 이중 슬래시가 있었다면 콘텐츠에서 수정
                $content = str_replace($image_src, $clean_image_src, $content);
                $image_src = $clean_image_src;
            }
            
            // 상대 경로(../../)가 포함된 경우 정리
            if (strpos($image_src, '../') === 0) {
                $fixed_path = preg_replace('/^(\.\.\/)+/', '', $image_src);
                $content = str_replace($image_src, $fixed_path, $content);
            }
            
            // 경로에 uploads/posts/uploads/가 포함된 경우 수정
            if (strpos($image_src, 'uploads/posts/uploads/') !== false) {
                $fixed_path = str_replace('uploads/posts/uploads/', "uploads/posts/$year/$month/", $image_src);
                $content = str_replace($image_src, $fixed_path, $content);
            }
            
            error_log("처리 후 이미지 경로: " . $image_src);
        }
    }
    
    // 이중 슬래시 제거 (마지막 검사)
    $content = str_replace('uploads/posts//', "uploads/posts/$year/$month/", $content); 
    $content = str_replace('uploads/temp//', "uploads/posts/$year/$month/", $content);
    
    // 잘못된 경로 구조 수정 (마지막 검사)
    $content = str_replace('uploads/posts/uploads/temp/', "uploads/posts/$year/$month/", $content);
    $content = str_replace('uploads/posts/uploads/posts/', "uploads/posts/$year/$month/", $content);
    
    // 수정된 콘텐츠 반환
    return $content;
}

/**
 * 게시글 본문에서 첫 번째 이미지를 찾아 썸네일로 사용할 경로 반환
 * 
 * @param string $content 게시글 내용
 * @return string|null 이미지 경로 또는 NULL
 */
function findFirstContentImage($content) {
    // 본문에서 이미지 태그 추출
    preg_match_all('/<img[^>]+src="([^"]+)"[^>]*>/i', $content, $image_matches);
    
    if (isset($image_matches[1]) && !empty($image_matches[1])) {
        $first_image = $image_matches[1][0];
        
        // 현재 날짜로 연도/월 폴더 구조 참조
        $date = new DateTime();
        $year = $date->format('Y');
        $month = $date->format('m');
        
        // 절대 URL 패턴 검사 (http:// 또는 https://)
        if (preg_match('/^https?:\/\//i', $first_image)) {
            // 도메인과 경로 추출
            $parsed_url = parse_url($first_image);
            
            // 로컬 서버 URL에서 파일 경로 부분만 추출
            if (isset($parsed_url['path'])) {
                $path = $parsed_url['path'];
                
                // 경로가 /uploads/로 시작하면 상대 경로로 변환
                if (strpos($path, '/uploads/') === 0) {
                    $first_image = substr($path, 1); // 앞의 / 제거
                    error_log("절대 URL에서 상대 경로로 변환: " . $first_image);
                }
            }
        }
        
        // 상대 경로 처리 (../../ 접두사 제거)
        if (strpos($first_image, '../') === 0) {
            $first_image = preg_replace('/^(\.\.\/)+/', '', $first_image);
        }
        
        // 이중 슬래시 제거
        $first_image = str_replace('//', '/', $first_image);
        
        // 경로에 uploads/posts/uploads/를 uploads/로 수정
        if (strpos($first_image, 'uploads/posts/uploads/') !== false) {
            $first_image = str_replace('uploads/posts/uploads/', "uploads/posts/$year/$month/", $first_image);
        }
        
        // admin/posts 경로 패턴 제거
        if (strpos($first_image, '/admin/posts/uploads/') !== false) {
            $first_image = str_replace('/admin/posts/uploads/', '/uploads/', $first_image);
        } else if (strpos($first_image, 'admin/posts/uploads/') !== false) {
            $first_image = str_replace('admin/posts/uploads/', 'uploads/', $first_image);
        }
        
        // 앞쪽에 슬래시가 있으면 제거
        $first_image = ltrim($first_image, '/');
        
        // uploads/ 경로를 포함하는 이미지라면 반환
        if (strpos($first_image, 'uploads/') !== false) {
            error_log("첫 번째 이미지 경로: " . $first_image);
            return $first_image;
        }
    }
    
    error_log("본문에서 이미지를 찾을 수 없습니다.");
    return null;
}

/**
 * 게시글 본문 이미지를 썸네일로 설정
 * 
 * @param string $content 게시글 내용
 * @param int $post_id 게시글 ID
 * @param string|null $current_thumbnail 현재 썸네일 경로
 * @param PDO $pdo 데이터베이스 연결 객체
 * @return bool 썸네일 업데이트 성공 여부
 */
function updateThumbnailFromContent($content, $post_id, $current_thumbnail, $pdo) {
    // 본문의 첫 번째 이미지 찾기
    $image_path = findFirstContentImage($content);
    
    // 이미지가 있고, 썸네일을 설정/변경해야 하는 경우
    if ($image_path) {
        error_log("updateThumbnailFromContent: 게시글 ID " . $post_id . "의 썸네일을 본문 이미지로 설정: " . $image_path);
        
        // 썸네일 업데이트
        $update_stmt = $pdo->prepare("UPDATE " . get_table_name('posts') . " SET thumbnail = ? WHERE id = ?");
        return $update_stmt->execute([$image_path, $post_id]);
    } else {
        // 이미지가 없는 경우 사이트 로고를 썸네일로 설정
        $settings_stmt = $pdo->query("SELECT setting_value FROM " . get_table_name('site_settings') . " WHERE setting_key = 'site_logo'");
        $logo_setting = $settings_stmt->fetch(PDO::FETCH_ASSOC);
        
        if ($logo_setting && !empty($logo_setting['setting_value'])) {
            $logo_path = $logo_setting['setting_value'];
            error_log("updateThumbnailFromContent: 게시글 ID " . $post_id . "에 이미지가 없어 로고로 설정: " . $logo_path);
            
            // 썸네일 업데이트
            $update_stmt = $pdo->prepare("UPDATE " . get_table_name('posts') . " SET thumbnail = ? WHERE id = ?");
            return $update_stmt->execute([$logo_path, $post_id]);
        }
    }
    
    return false;
}
?> 