<?php
/**
 * Branding Configuration
 * Visual and brand-specific settings that can be easily customized for different organizations
 */

return [
    'project' => [
        'name' => $_ENV['PROJECT_NAME'] ?? 'Organization Website',
        'slug' => $_ENV['PROJECT_SLUG'] ?? 'organization',
        'version' => $_ENV['PROJECT_VERSION'] ?? '1.0.0',
    ],
    
    'theme' => [
        'name' => $_ENV['THEME_NAME'] ?? 'natural-green',
        'colors' => [
            'primary' => $_ENV['THEME_PRIMARY_COLOR'] ?? '#84cc16',
            'secondary' => $_ENV['THEME_SECONDARY_COLOR'] ?? '#16a34a',
            'success' => $_ENV['THEME_SUCCESS_COLOR'] ?? '#65a30d',
            'info' => $_ENV['THEME_INFO_COLOR'] ?? '#3a7a4e',
            'warning' => $_ENV['THEME_WARNING_COLOR'] ?? '#a3e635',
            'danger' => $_ENV['THEME_DANGER_COLOR'] ?? '#dc2626',
            'light' => $_ENV['THEME_LIGHT_COLOR'] ?? '#fafffe',
            'dark' => $_ENV['THEME_DARK_COLOR'] ?? '#1f3b2d',
        ]
    ],
    
    'urls' => [
        'production' => $_ENV['PRODUCTION_URL'] ?? 'https://organization.org',
        'domain' => $_ENV['PRODUCTION_DOMAIN'] ?? 'organization.org',
        'local_port' => $_ENV['LOCAL_PORT'] ?? '8080',
        'base_path' => $_ENV['BASE_PATH'] ?? '/organization',
    ],
    
    'assets' => [
        'logo' => [
            'primary' => '/assets/images/logo.png',
            'white' => '/assets/images/logo-white.png',
            'favicon' => '/assets/images/favicon.ico',
        ],
        'images' => [
            'hero_background' => '/assets/images/hero-bg.jpg',
            'about_image' => '/assets/images/about.jpg',
            'default_post_image' => '/assets/images/default-post.jpg',
        ]
    ],
    
    'seo' => [
        'meta_title_suffix' => ' | ' . ($_ENV['ORG_NAME_SHORT'] ?? 'Organization'),
        'meta_description' => $_ENV['ORG_DESCRIPTION'] ?? 'Organization website description',
        'meta_keywords' => $_ENV['SEO_KEYWORDS'] ?? 'nonprofit, organization, community',
        'og_image' => '/assets/images/og-image.jpg',
    ],
    
    'features' => [
        'donations' => $_ENV['FEATURE_DONATIONS'] ?? true,
        'events' => $_ENV['FEATURE_EVENTS'] ?? true,
        'gallery' => $_ENV['FEATURE_GALLERY'] ?? true,
        'newsletter' => $_ENV['FEATURE_NEWSLETTER'] ?? true,
        'multilingual' => $_ENV['FEATURE_MULTILINGUAL'] ?? false,
    ]
];
?>