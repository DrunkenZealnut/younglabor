<?php
/**
 * 404 Error Handler
 * Fixes URLs containing ${PROJECT_SLUG} and redirects to correct path
 */

$requestUri = $_SERVER['REQUEST_URI'] ?? '';

// Check if this is a URL with ${PROJECT_SLUG}
if (strpos($requestUri, '${PROJECT_SLUG}') !== false || 
    strpos($requestUri, '%7BPROJECT_SLUG%7D') !== false ||
    strpos($requestUri, '$%7BPROJECT_SLUG%7D') !== false) {
    
    // Replace all variations with
    $fixedUri = str_replace(
        ['${PROJECT_SLUG}', '%7BPROJECT_SLUG%7D', '$%7BPROJECT_SLUG%7D'],
        'younglabor',
        $requestUri
    );
    
    // Redirect to the fixed URL
    header('Location: ' . $fixedUri);
    exit;
}

// Otherwise redirect to home
header('Location: /younglabor/');
exit;
?>