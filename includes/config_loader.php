<?php
/**
 * Configuration Loader
 * Centralized configuration loading system for organization and branding settings
 */

// Load environment variables if not already loaded
if (!function_exists('loadEnvVariables')) {
    function loadEnvVariables($envPath = '.env') {
        if (!file_exists($envPath)) {
            return;
        }
        
        $lines = file($envPath, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
        foreach ($lines as $line) {
            if (strpos(trim($line), '#') === 0) {
                continue;
            }
            
            list($name, $value) = explode('=', $line, 2);
            $name = trim($name);
            $value = trim($value);
            
            // Remove quotes if present
            if (preg_match('/^(["\'])(.*)\\1$/', $value, $matches)) {
                $value = $matches[2];
            }
            
            // Handle variable substitution (basic)
            $value = preg_replace_callback('/\$\{([^}]+)\}/', function($matches) {
                return $_ENV[$matches[1]] ?? $matches[0];
            }, $value);
            
            if (!array_key_exists($name, $_ENV)) {
                $_ENV[$name] = $value;
            }
        }
    }
}

// Load .env file
$envPath = dirname(__DIR__) . '/.env';
loadEnvVariables($envPath);

// Global configuration getter functions
if (!function_exists('getOrganizationConfig')) {
    function getOrganizationConfig($key = null) {
        static $config = null;
        
        if ($config === null) {
            $configPath = dirname(__DIR__) . '/config/organization.php';
            $config = file_exists($configPath) ? require $configPath : [];
        }
        
        if ($key === null) {
            return $config;
        }
        
        return $config[$key] ?? null;
    }
}

if (!function_exists('getBrandingConfig')) {
    function getBrandingConfig($key = null) {
        static $config = null;
        
        if ($config === null) {
            $configPath = dirname(__DIR__) . '/config/branding.php';
            $config = file_exists($configPath) ? require $configPath : [];
        }
        
        if ($key === null) {
            return $config;
        }
        
        return $config[$key] ?? null;
    }
}

// Helper functions for common config values
if (!function_exists('getOrgName')) {
    function getOrgName($type = 'short') {
        $names = getOrganizationConfig('name');
        return $names[$type] ?? ($_ENV['ORG_NAME_SHORT'] ?? 'Organization');
    }
}

if (!function_exists('getOrgDescription')) {
    function getOrgDescription() {
        return getOrganizationConfig('description') ?? $_ENV['ORG_DESCRIPTION'] ?? 'Organization description';
    }
}

if (!function_exists('getProjectSlug')) {
    function getProjectSlug() {
        return $_ENV['PROJECT_SLUG'] ?? 'organization';
    }
}

if (!function_exists('getProductionUrl')) {
    function getProductionUrl() {
        return $_ENV['PRODUCTION_URL'] ?? 'https://organization.org';
    }
}

if (!function_exists('getTablePrefix')) {
    function getTablePrefix() {
        return $_ENV['DB_PREFIX'] ?? (getProjectSlug() . '_');
    }
}

?>