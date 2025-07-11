<?php
// Simple debug test file
// Add this temporarily to functions.php to test

// Test 1: Basic enqueue
function debug_test_enqueue() {
    error_log('DEBUG: debug_test_enqueue called');
    
    wp_enqueue_script(
        'debug-test-script',
        CUSTOMTUBE_URI . '/dist/js/customtube.min.js',
        array('jquery'),
        '1.0.0',
        true
    );
    
    wp_localize_script('debug-test-script', 'debugData', array(
        'test' => 'working'
    ));
    
    error_log('DEBUG: Scripts enqueued');
}
add_action('wp_enqueue_scripts', 'debug_test_enqueue');

// Test 2: Check if constants are defined
function debug_test_constants() {
    error_log('DEBUG: CUSTOMTUBE_URI = ' . (defined('CUSTOMTUBE_URI') ? CUSTOMTUBE_URI : 'NOT DEFINED'));
    error_log('DEBUG: CUSTOMTUBE_DIR = ' . (defined('CUSTOMTUBE_DIR') ? CUSTOMTUBE_DIR : 'NOT DEFINED'));
}
add_action('init', 'debug_test_constants');

// Test 3: Check if JS file exists
function debug_test_file_exists() {
    $js_file = CUSTOMTUBE_DIR . '/dist/js/customtube.min.js';
    error_log('DEBUG: JS file exists = ' . (file_exists($js_file) ? 'YES' : 'NO'));
    error_log('DEBUG: JS file path = ' . $js_file);
}
add_action('init', 'debug_test_file_exists');
