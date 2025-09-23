<?php
/**
 * Organization Information Helper
 * 
 * Provides centralized access to organization information
 * from environment variables and configuration files.
 */

/**
 * Get organization configuration
 * 
 * @return array
 */
function get_organization_config() {
    static $config = null;
    
    if ($config === null) {
        $configFile = dirname(__DIR__) . '/config/organization.php';
        if (file_exists($configFile)) {
            $config = require $configFile;
        } else {
            // Fallback to environment variables
            $config = [
                'name' => [
                    'short' => env('ORG_NAME_SHORT', '희망씨'),
                    'full' => env('ORG_NAME_FULL', '사단법인 희망씨'),
                    'english' => env('ORG_NAME_EN', 'HOPEC'),
                ],
                'description' => env('ORG_DESCRIPTION', '지역사회와 함께 아래로 향한 연대 일터와 삶터를 바꾸기 위한 활동에 함께 합니다'),
                'contact' => [
                    'email' => env('CONTACT_EMAIL', 'info@organization.org'),
                    'phone' => env('CONTACT_PHONE', ''),
                    'address' => env('ORG_ADDRESS', ''),
                ],
                'banking' => [
                    'account_holder' => env('BANK_ACCOUNT_HOLDER', env('ORG_NAME_FULL', '사단법인 희망씨')),
                    'account_number' => env('BANK_ACCOUNT_NUMBER', ''),
                    'bank_name' => env('BANK_NAME', ''),
                ],
                'legal' => [
                    'registration_number' => env('ORG_REGISTRATION_NUMBER', ''),
                    'tax_id' => env('ORG_TAX_ID', ''),
                    'establishment_date' => env('ORG_ESTABLISHMENT_DATE', ''),
                ],
                'social' => [
                    'website' => env('PRODUCTION_URL', 'https://organization.org'),
                    'facebook' => env('ORG_FACEBOOK', ''),
                    'instagram' => env('ORG_INSTAGRAM', ''),
                    'youtube' => env('ORG_YOUTUBE', ''),
                    'blog' => env('ORG_BLOG', ''),
                ]
            ];
        }
    }
    
    return $config;
}

/**
 * Get organization name (short version)
 * 
 * @return string
 */
function org_name_short() {
    $config = get_organization_config();
    return $config['name']['short'];
}

/**
 * Get organization name (full version)
 * 
 * @return string
 */
function org_name_full() {
    $config = get_organization_config();
    return $config['name']['full'];
}

/**
 * Get organization name (English version)
 * 
 * @return string
 */
function org_name_en() {
    $config = get_organization_config();
    return $config['name']['english'];
}

/**
 * Get organization description
 * 
 * @return string
 */
function org_description() {
    $config = get_organization_config();
    return $config['description'];
}

/**
 * Get organization contact information
 * 
 * @param string|null $field Specific field (email, phone, address) or null for all
 * @return string|array
 */
function org_contact($field = null) {
    $config = get_organization_config();
    $contact = $config['contact'];
    
    if ($field) {
        return $contact[$field] ?? '';
    }
    
    return $contact;
}

/**
 * Get organization banking information
 * 
 * @param string|null $field Specific field or null for all
 * @return string|array
 */
function org_banking($field = null) {
    $config = get_organization_config();
    $banking = $config['banking'];
    
    if ($field) {
        return $banking[$field] ?? '';
    }
    
    return $banking;
}

/**
 * Get organization legal information
 * 
 * @param string|null $field Specific field or null for all
 * @return string|array
 */
function org_legal($field = null) {
    $config = get_organization_config();
    $legal = $config['legal'];
    
    if ($field) {
        return $legal[$field] ?? '';
    }
    
    return $legal;
}

/**
 * Get organization social media information
 * 
 * @param string|null $field Specific field or null for all
 * @return string|array
 */
function org_social($field = null) {
    $config = get_organization_config();
    $social = $config['social'];
    
    if ($field) {
        return $social[$field] ?? '';
    }
    
    return $social;
}

/**
 * Generate organization logo alt text
 * 
 * @param string $context Context for the alt text (header, footer, etc.)
 * @return string
 */
function org_logo_alt($context = '') {
    $orgName = org_name_full();
    
    if ($context) {
        return "{$orgName} {$context}";
    }
    
    return $orgName;
}

/**
 * Get organization information for meta tags
 * 
 * @return array
 */
function org_meta_info() {
    return [
        'name' => org_name_full(),
        'description' => org_description(),
        'url' => org_social('website'),
        'contact_email' => org_contact('email'),
    ];
}