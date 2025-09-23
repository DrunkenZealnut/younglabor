<?php
/**
 * Path Helper for Environment Detection and Dynamic Path Configuration
 * 
 * This helper detects the current environment and provides appropriate paths
 * for XAMPP, database sockets, and other system-dependent configurations.
 */

/**
 * Detect if running on XAMPP environment
 * 
 * @return bool
 */
function isXamppEnvironment() {
    // Check for XAMPP installation paths
    $xamppPaths = [
        '/Applications/XAMPP',           // macOS
        'C:\\xampp',                     // Windows
        '/opt/lampp'                     // Linux
    ];
    
    foreach ($xamppPaths as $path) {
        if (is_dir($path)) {
            return true;
        }
    }
    
    // Check for XAMPP-specific environment variables or processes
    if (getenv('XAMPP_ROOT') || 
        (isset($_SERVER['SERVER_SOFTWARE']) && strpos($_SERVER['SERVER_SOFTWARE'], 'Apache') !== false &&
         (strpos(__FILE__, 'xampp') !== false || strpos(__FILE__, 'XAMPP') !== false))) {
        return true;
    }
    
    return false;
}

/**
 * Get XAMPP root directory based on the operating system
 * 
 * @return string|null
 */
function getXamppRoot() {
    $xamppPaths = [
        '/Applications/XAMPP',           // macOS
        'C:\\xampp',                     // Windows
        '/opt/lampp'                     // Linux
    ];
    
    foreach ($xamppPaths as $path) {
        if (is_dir($path)) {
            return $path;
        }
    }
    
    // Check environment variable
    if ($env_xampp = getenv('XAMPP_ROOT')) {
        return $env_xampp;
    }
    
    return null;
}

/**
 * Get appropriate MySQL socket path for the current environment
 * 
 * @return string|null
 */
function getMysqlSocketPath() {
    // Try environment variables first
    if ($socket = env('DB_SOCKET_XAMPP', null)) {
        if (file_exists($socket)) {
            return $socket;
        }
    }
    
    if ($socket = env('DB_SOCKET_LINUX', null)) {
        if (file_exists($socket)) {
            return $socket;
        }
    }
    
    // Auto-detect based on environment
    if (isXamppEnvironment()) {
        $xamppRoot = getXamppRoot();
        if ($xamppRoot) {
            $xamppSocket = $xamppRoot . '/xamppfiles/var/mysql/mysql.sock';
            if (file_exists($xamppSocket)) {
                return $xamppSocket;
            }
        }
    }
    
    // Standard Linux/Unix socket paths
    $standardSockets = [
        '/var/run/mysqld/mysqld.sock',
        '/tmp/mysql.sock',
        '/var/lib/mysql/mysql.sock'
    ];
    
    foreach ($standardSockets as $socket) {
        if (file_exists($socket)) {
            return $socket;
        }
    }
    
    return null;
}

/**
 * Get XAMPP binary paths for Apache and MySQL
 * 
 * @param string $service 'apache' or 'mysql'
 * @return string|null
 */
function getXamppBinaryPath($service) {
    $xamppRoot = getXamppRoot();
    if (!$xamppRoot) {
        return null;
    }
    
    switch (strtolower($service)) {
        case 'apache':
        case 'httpd':
            $paths = [
                $xamppRoot . '/xamppfiles/bin/apachectl',  // macOS/Linux
                $xamppRoot . '/bin/httpd',                 // Alternative
                $xamppRoot . '/apache/bin/httpd.exe'       // Windows
            ];
            break;
            
        case 'mysql':
        case 'mysqld':
            $paths = [
                $xamppRoot . '/bin/mysql.server',          // macOS/Linux
                $xamppRoot . '/xamppfiles/bin/mysql.server', // Alternative macOS
                $xamppRoot . '/mysql/bin/mysqld.exe'       // Windows
            ];
            break;
            
        default:
            return null;
    }
    
    foreach ($paths as $path) {
        if (file_exists($path)) {
            return $path;
        }
    }
    
    return null;
}

/**
 * Get XAMPP configuration file paths
 * 
 * @param string $config 'httpd' or 'vhost'
 * @return string|null
 */
function getXamppConfigPath($config) {
    $xamppRoot = getXamppRoot();
    if (!$xamppRoot) {
        return null;
    }
    
    switch (strtolower($config)) {
        case 'httpd':
            $paths = [
                $xamppRoot . '/etc/httpd.conf',
                $xamppRoot . '/apache/conf/httpd.conf'
            ];
            break;
            
        case 'vhost':
            $paths = [
                $xamppRoot . '/etc/extra/httpd-vhosts.conf',
                $xamppRoot . '/apache/conf/extra/httpd-vhosts.conf'
            ];
            break;
            
        default:
            return null;
    }
    
    foreach ($paths as $path) {
        if (file_exists($path)) {
            return $path;
        }
    }
    
    return null;
}

/**
 * Get XAMPP logs directory
 * 
 * @return string|null
 */
function getXamppLogsPath() {
    $xamppRoot = getXamppRoot();
    if (!$xamppRoot) {
        return null;
    }
    
    $logsPaths = [
        $xamppRoot . '/logs',
        $xamppRoot . '/apache/logs'
    ];
    
    foreach ($logsPaths as $path) {
        if (is_dir($path)) {
            return $path;
        }
    }
    
    return null;
}

/**
 * Generate environment-appropriate socket configuration for .env file
 * 
 * @return array
 */
function generateSocketConfig() {
    $config = [];
    
    // Detect XAMPP socket
    if (isXamppEnvironment()) {
        $xamppSocket = getMysqlSocketPath();
        if ($xamppSocket) {
            $config['DB_SOCKET_XAMPP'] = $xamppSocket;
        }
    }
    
    // Standard Linux/Unix sockets
    $standardSockets = [
        '/var/run/mysqld/mysqld.sock',
        '/tmp/mysql.sock',
        '/var/lib/mysql/mysql.sock'
    ];
    
    foreach ($standardSockets as $socket) {
        if (file_exists($socket)) {
            $config['DB_SOCKET_LINUX'] = $socket;
            break;
        }
    }
    
    return $config;
}