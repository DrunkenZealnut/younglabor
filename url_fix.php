<?php
/**
 * URL Fix Handler
 * Automatically fixes URLs containing ${PROJECT_SLUG}
 * Include this at the top of any page that might receive bad URLs
 */

function fixProjectSlugInUrl() {
    $requestUri = $_SERVER['REQUEST_URI'] ?? '';
    
    // Check if URL contains ${PROJECT_SLUG} in any form
    if (strpos($requestUri, '${PROJECT_SLUG}') !== false || 
        strpos($requestUri, '%7BPROJECT_SLUG%7D') !== false ||
        strpos($requestUri, '$%7BPROJECT_SLUG%7D') !== false) {
        
        // Replace all variations with /hopec
        $fixedUri = str_replace(
            ['${PROJECT_SLUG}', '%7BPROJECT_SLUG%7D', '$%7BPROJECT_SLUG%7D'],
            'hopec',
            $requestUri
        );
        
        // Redirect to the fixed URL
        header('Location: ' . $fixedUri);
        exit;
    }
}

// Auto-execute the fix
fixProjectSlugInUrl();
?>