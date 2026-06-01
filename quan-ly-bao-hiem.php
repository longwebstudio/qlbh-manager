<?php
/**
 * Plugin Name: Quản lý Thu Bảo hiểm Hộ Gia đình
 * Description: Hệ thống quản lý BHYT, BHXH sử dụng bảng bhyts (CamelCase)
 * Version: 3.1.0
 * Author: GiaiphapWP
 */

if (!defined('ABSPATH')) exit;

// Khai báo hằng số
define('QLBH_PATH', plugin_dir_path(__FILE__));
define('QLBH_URL', plugin_dir_url(__FILE__));

// 1. Nạp cơ sở dữ liệu
require_once QLBH_PATH . 'includes/db.php';
register_activation_hook(__FILE__, 'qlbh_install_table');

// 2. Nạp bộ điều phối Helpers & API
require_once QLBH_PATH . 'includes/helper-loader.php';

// 3. Khởi chạy Admin
if (is_admin()) {
    require_once QLBH_PATH . 'admin/class-qlbh-admin.php';
    QLBH_Admin::init();
}