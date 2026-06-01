<?php
if (!defined('ABSPATH')) exit;

$helpers_dir = QLBH_PATH . 'includes/helpers/';
$api_dir = QLBH_PATH . 'includes/api/';

// Danh sách các module Helpers
$helper_files = ['format.php', 'auth.php', 'calc.php', 'ui.php', 'history.php'];
foreach ($helper_files as $file) {
    if (file_exists($helpers_dir . $file)) {
        require_once $helpers_dir . $file;
    }
}

// Tự động nạp API
add_action('rest_api_init', function() use ($api_dir) {
    $api_files = glob($api_dir . '*.php');
    if ($api_files) {
        foreach ($api_files as $file) {
            require_once $file;
        }
    }
});