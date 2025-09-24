<?php
/**
 * Dynamic Path Helper
 * 
 * Provides dynamic path resolution for different deployment environments
 */

/**
 * Get project root directory
 * 
 * @return string
 */
function get_project_root() {
    static $project_root = null;
    
    if ($project_root === null) {
        // Try environment variable first
        if ($env_root = env('PROJECT_ROOT', null)) {
            if (is_dir($env_root)) {
                $project_root = rtrim($env_root, '/');
                return $project_root;
            }
        }
        
        // Auto-detect from current file location
        $current_dir = __DIR__;
        $project_root = dirname($current_dir); // Go up from includes to project root
        
        // Validate detection by checking for key files
        $key_files = ['.env', 'config', 'admin', 'theme'];
        
        foreach ($key_files as $key_file) {
            if (!file_exists($project_root . '/' . $key_file)) {
                // Fallback to common paths if auto-detection fails
                $fallback_paths = [
                    '/Applications/XAMPP/xamppfiles/htdocs/hopec',
                    '/var/www/html/hopec',
                    dirname(dirname(__DIR__))
                ];
                
                foreach ($fallback_paths as $path) {
                    if (is_dir($path) && file_exists($path . '/' . $key_file)) {
                        $project_root = rtrim($path, '/');
                        break 2;
                    }
                }
                break;
            }
        }
    }
    
    return $project_root;
}

/**
 * Get dynamic upload path for BT (Board Templates)
 * 
 * @return string
 */
function get_bt_upload_path() {
    // Check for BT-specific environment variable
    $bt_upload = env('BT_UPLOAD_PATH', null);
    
    if ($bt_upload) {
        return rtrim($bt_upload, '/');
    }
    
    // Fall back to general upload path from environment
    $upload_path = env('UPLOAD_PATH', 'data/file');
    
    // If path is relative, make it relative to project root
    if (substr($upload_path, 0, 1) !== '/') {
        $upload_path = get_project_root() . '/' . $upload_path;
    }
    
    return rtrim($upload_path, '/');
}

/**
 * Get absolute path from project root
 * 
 * @param string $relative_path
 * @return string
 */
function project_path($relative_path = '') {
    $root = get_project_root();
    
    if (empty($relative_path)) {
        return $root;
    }
    
    $relative_path = ltrim($relative_path, '/');
    return $root . '/' . $relative_path;
}