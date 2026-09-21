<?php

if (!function_exists('maskIpAddress')) {
    function maskIpAddress(string $ip): string
    {
        if (filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_IPV4)) {
            return (string)preg_replace('/\.\d+$/', '.0', $ip);
        }

        if (!filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_IPV6)) {
            return $ip;
        }

        $packed = inet_pton($ip);
        if ($packed === false || strlen($packed) !== 16) {
            return $ip;
        }

        $masked = inet_ntop(substr($packed, 0, 14) . "\0\0");
        return $masked === false ? $ip : $masked;
    }
}
