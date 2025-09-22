<?php
/**
 * Organization Configuration
 * Centralized organization settings that can be easily modified for different deployments
 */

return [
    'name' => [
        'short' => $_ENV['ORG_NAME_SHORT'] ?? '희망씨',
        'full' => $_ENV['ORG_NAME_FULL'] ?? '사단법인 희망씨',
        'english' => $_ENV['ORG_NAME_EN'] ?? 'HOPEC',
    ],
    
    'description' => $_ENV['ORG_DESCRIPTION'] ?? '지역사회와 함께 아래로 향한 연대 일터와 삶터를 바꾸기 위한 활동에 함께 합니다',
    
    'contact' => [
        'email' => $_ENV['CONTACT_EMAIL'] ?? 'info@organization.org',
        'phone' => $_ENV['CONTACT_PHONE'] ?? '',
        'address' => $_ENV['ORG_ADDRESS'] ?? '',
    ],
    
    'banking' => [
        'account_holder' => $_ENV['BANK_ACCOUNT_HOLDER'] ?? $_ENV['ORG_NAME_FULL'] ?? '사단법인 희망씨',
        'account_number' => $_ENV['BANK_ACCOUNT_NUMBER'] ?? '',
        'bank_name' => $_ENV['BANK_NAME'] ?? '',
    ],
    
    'legal' => [
        'registration_number' => $_ENV['ORG_REGISTRATION_NUMBER'] ?? '',
        'tax_id' => $_ENV['ORG_TAX_ID'] ?? '',
        'establishment_date' => $_ENV['ORG_ESTABLISHMENT_DATE'] ?? '',
    ],
    
    'social' => [
        'website' => $_ENV['PRODUCTION_URL'] ?? 'https://organization.org',
        'facebook' => $_ENV['ORG_FACEBOOK'] ?? '',
        'instagram' => $_ENV['ORG_INSTAGRAM'] ?? '',
        'youtube' => $_ENV['ORG_YOUTUBE'] ?? '',
        'blog' => $_ENV['ORG_BLOG'] ?? '',
    ]
];
?>