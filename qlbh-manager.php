<?php
/**
 * Plugin Name: Quản lý Thu Bảo hiểm Hộ Gia đình & BHXH & MIC
 * Description: Hệ thống quản lý thu BHYT, BHXH, MIC chuyên biệt, tối ưu hóa theo mô hình module, sử dụng cấu trúc bảng cơ sở dữ liệu tường minh và hệ thống phân trang.
 * Version: 2.1.0
 * Author: GiaiphapWP
 * License: GPL2
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

// Khai báo các hằng số đường dẫn của plugin
define( 'QLBH_PATH', plugin_dir_path( __FILE__ ) );
define( 'QLBH_URL', plugin_dir_url( __FILE__ ) );

// Nạp các tệp tin cấu hình và trợ giúp hệ thống
require_once QLBH_PATH . 'includes/db.php';
require_once QLBH_PATH . 'includes/helper.php';

// Đăng ký hook kích hoạt để cài đặt/cập nhật bảng dữ liệu mới
register_activation_hook( __FILE__, 'qlbh_install_table' );

// Khởi chạy khu vực quản trị trong Admin Dashboard
if ( is_admin() ) {
    require_once QLBH_PATH . 'admin/class-qlbh-admin.php';
    QLBH_Admin::init();
}