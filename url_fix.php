<?php
/**
 * URL Fix Handler
 * Automatically fixes URLs containing ${PROJECT_SLUG}
 * Include this at the top of any page that might receive bad URLs
 */

// Use centralized config loader for better performance and consistency
require_once __DIR__ . '/includes/config_loader.php';

function fixProjectSlugInUrl() {
    // Security: Validate and sanitize request URI
    $requestUri = $_SERVER['REQUEST_URI'] ?? '';
    
    // Security: Basic input validation - limit length and check for malicious patterns
    if (strlen($requestUri) > 2048) {
        error_log("Suspicious long URL detected: " . substr($requestUri, 0, 100) . "...");
        http_response_code(400);
        exit('Bad Request');
    }
    
    // Security: Check for path traversal attempts
    if (strpos($requestUri, '../') !== false || strpos($requestUri, '..\\') !== false) {
        error_log("Path traversal attempt detected: $requestUri");
        http_response_code(400);
        exit('Bad Request');
    }
    
    // Use centralized config loader instead of direct .env reading
    $projectSlug = getProjectSlug();
    if (!preg_match('/^[a-zA-Z0-9_-]{1,50}$/', $projectSlug)) {
        error_log("Invalid PROJECT_SLUG format: $projectSlug");
        http_response_code(400);
        exit('Bad Request');
    }
    
    // Check if URL contains ${PROJECT_SLUG} in any form (including additional variations)
    $needsFix = (
        strpos($requestUri, '${PROJECT_SLUG}') !== false || 
        strpos($requestUri, '%7BPROJECT_SLUG%7D') !== false ||
        strpos($requestUri, '$%7BPROJECT_SLUG%7D') !== false ||
        strpos($requestUri, '%24%7BPROJECT_SLUG%7D') !== false // Double-encoded variation
    );
    
    if ($needsFix) {
        // Replace all variations with the actual project slug
        $fixedUri = str_replace(
            [
                '${PROJECT_SLUG}', 
                '%7BPROJECT_SLUG%7D', 
                '$%7BPROJECT_SLUG%7D',
                '%24%7BPROJECT_SLUG%7D'
            ],
            $projectSlug,
            $requestUri
        );
        
        // Security: Validate the fixed URI
        if ($fixedUri !== $requestUri && filter_var('http://example.com' . $fixedUri, FILTER_VALIDATE_URL)) {
            header('Location: ' . $fixedUri);
            exit;
        } else {
            error_log("Invalid fixed URI generated: $fixedUri from $requestUri");
            http_response_code(400);
            exit('Bad Request');
        }
    }
}

// Auto-execute the fix
fixProjectSlugInUrl();
?>