<?php
/**
 * CustomTube JavaScript Diagnostic Script
 * Upload this to your theme root and access via: yoursite.com/wp-content/themes/customtube-no-plugin/js-diagnostic.php
 */

// Simulate WordPress environment if not loaded
if (!function_exists('get_template_directory')) {
    // Basic WordPress functions simulation for standalone testing
    function get_template_directory() {
        return dirname(__FILE__);
    }
    
    function get_template_directory_uri() {
        $protocol = isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https://' : 'http://';
        $host = $_SERVER['HTTP_HOST'];
        $path = dirname($_SERVER['REQUEST_URI']);
        return $protocol . $host . $path;
    }
}

echo '<!DOCTYPE html>
<html>
<head>
    <title>CustomTube JS Diagnostic</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 20px; background: #f5f5f5; }
        .container { max-width: 800px; background: white; padding: 20px; border-radius: 8px; box-shadow: 0 2px 10px rgba(0,0,0,0.1); }
        .success { color: #28a745; font-weight: bold; }
        .error { color: #dc3545; font-weight: bold; }
        .warning { color: #ffc107; font-weight: bold; }
        .info { color: #17a2b8; font-weight: bold; }
        .test-section { margin: 20px 0; padding: 15px; border-left: 4px solid #007cba; background: #f8f9fa; }
        .code { background: #f4f4f4; padding: 10px; border-radius: 4px; font-family: monospace; }
        .console-test { background: #1e1e1e; color: #00ff00; padding: 15px; border-radius: 4px; font-family: monospace; }
    </style>
</head>
<body>