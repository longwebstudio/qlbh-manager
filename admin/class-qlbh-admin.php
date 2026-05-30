<?php
/**
 * File: admin/class-qlbh-admin.php
 * Chức năng: Quản lý menu quản trị, định tuyến tải giao diện và nhúng stylesheet bổ trợ.
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

class QLBH_Admin {

    // Khởi tạo các hook đăng ký menu và tải asset
    public static function init() {
        add_action( 'admin_menu', array( __CLASS__, 'register_menus' ) );
        add_action( 'admin_head', array( __CLASS__, 'enqueue_styles' ) );
    }

    // Đăng ký Menu chính và các phân hệ nghiệp vụ con chuyên biệt
    public static function register_menus() {
        // Menu chính - Tải trang Danh sách khách hàng tập trung
        add_menu_page(
            'Quản lý Thu Bảo hiểm',
            'Quản lý Bảo hiểm',
            'manage_options',
            'qlbh-manager',
            array( __CLASS__, 'load_view_list' ),
            'dashicons-id-alt',
            25
        );
        
        // Menu con 1: Danh sách khách hàng (Trùng slug menu chính để hiển thị làm tab mặc định)
        add_submenu_page(
            'qlbh-manager',
            'Danh sách khách hàng',
            'Danh sách',
            'manage_options',
            'qlbh-manager',
            array( __CLASS__, 'load_view_list' )
        );

        // Menu con 2: Đăng ký hồ sơ khách hàng mới
        add_submenu_page(
            'qlbh-manager',
            'Thêm Khách hàng',
            'Thêm Khách hàng',
            'manage_options',
            'qlbh-add-customer',
            array( __CLASS__, 'load_view_add_edit' )
        );

        // Menu con 3: Nghiệp vụ thu Bảo hiểm Y tế (BHYT)
        add_submenu_page(
            'qlbh-manager',
            'Nghiệp vụ Thu BHYT',
            'Thu BHYT',
            'manage_options',
            'qlbh-thu-bhyt',
            array( __CLASS__, 'load_view_bhyt_op' )
        );

        // Menu con 4: Nghiệp vụ thu Bảo hiểm Xã hội (BHXH) tự nguyện
        add_submenu_page(
            'qlbh-manager',
            'Nghiệp vụ Thu BHXH',
            'Thu BHXH',
            'manage_options',
            'qlbh-thu-bhxh',
            array( __CLASS__, 'load_view_bhxh_op' )
        );

        // Menu con 5: Nghiệp vụ thu Bảo hiểm MIC đính kèm
        add_submenu_page(
            'qlbh-manager',
            'Nghiệp vụ Thu MIC',
            'Thu MIC',
            'manage_options',
            'qlbh-thu-mic',
            array( __CLASS__, 'load_view_mic_op' )
        );

        // Menu con 6: Phân hệ Quản lý Tờ khai BHYT (Hộ gia đình / Tờ khai riêng)
        add_submenu_page(
            'qlbh-manager',
            'Quản lý Tờ khai BHYT',
            'Quản lý Tờ khai',
            'manage_options',
            'qlbh-tokhai',
            array( __CLASS__, 'load_view_tokhai' )
        );

        // Đăng ký menu con Quản lý Danh sách Tái tục
        add_submenu_page(
            'qlbh-manager',
            'Danh sách Tái tục',
            'Danh sách Tái tục',
            'manage_options',
            'qlbh-tai-tuc',
            array( __CLASS__, 'load_view_tai_tuc' )
        );

        // Menu con 7: Đồng bộ dữ liệu JSON từ cổng BHXH
        add_submenu_page(
            'qlbh-manager',
            'Nhập dữ liệu từ JSON',
            'Import JSON',
            'manage_options',
            'qlbh-import',
            array( __CLASS__, 'load_view_import' )
        );

        // Thêm vào hàm register_menus()
add_submenu_page(
    'qlbh-manager',
    'Cấu hình hệ thống',
    'Cấu hình',
    'manage_options',
    'qlbh-settings',
    array( __CLASS__, 'load_view_settings' )
);
    }


// Thêm hàm load view
public static function load_view_settings() {
    include QLBH_PATH . 'admin/views/settings.php';
}
    // --- Các hàm định tuyến tải View từ thư mục admin/views/ ---

    public static function load_view_list() {
        include QLBH_PATH . 'admin/views/list.php';
    }

    public static function load_view_add_edit() {
        include QLBH_PATH . 'admin/views/add-edit.php';
    }

    public static function load_view_bhyt_op() {
        include QLBH_PATH . 'admin/views/bhyt-operation.php';
    }

    public static function load_view_bhxh_op() {
        include QLBH_PATH . 'admin/views/bhxh-operation.php';
    }

    public static function load_view_mic_op() {
        include QLBH_PATH . 'admin/views/mic-operation.php';
    }

    public static function load_view_tokhai() {
        include QLBH_PATH . 'admin/views/quan-ly-tokhai.php';
    }

    public static function load_view_import() {
        include QLBH_PATH . 'admin/views/import.php';
    }

    public static function load_view_tai_tuc() {
        include QLBH_PATH . 'admin/views/tai-tuc.php';
    }

    // --- Stylesheet nhúng trực tiếp để chuẩn hóa hiển thị và tăng trải nghiệm người dùng ---
    public static function enqueue_styles() {
        // Chỉ nạp style khi đang đứng tại các trang thuộc quyền quản lý của plugin
        $current_page = isset( $_GET['page'] ) ? $_GET['page'] : '';
        if ( strpos( $current_page, 'qlbh-' ) === false && $current_page !== 'qlbh-manager' ) {
            return;
        }
        ?>
        <style>
            .qlbh-wrap { margin: 20px 20px 0 0; }
            .qlbh-card { background: #fff; padding: 20px; border: 1px solid #ccd0d4; box-shadow: 0 1px 1px rgba(0,0,0,.04); border-radius: 4px; margin-top: 15px; }
            .qlbh-form-container { background: #fff; padding: 20px; border: 1px solid #ccd0d4; box-shadow: 0 1px 1px rgba(0,0,0,.04); margin-top: 15px; border-radius: 4px; }
            
            /* Styles bảng dữ liệu */
            .customer-table th { font-weight: bold !important; background: #f8f9fa; }
            .row-info { font-size: 12px; color: #50575e; margin-top: 3px; }
            .text-date { color: #d54e21; font-weight: 500; }
            .text-warn { color: #46b450; font-weight: bold; }
            .code-highlight { background: #e7f4f9; padding: 2px 5px; border-radius: 3px; border: 1px solid #b4e0f1; color: #0073aa; font-family: monospace; }
            .quick-act-links { margin-top: 5px; font-size: 11px; }
            .quick-act-links a { text-decoration: none; font-weight: 500; margin-right: 5px; }
            
            /* Nhãn trạng thái Bảo hiểm (Badges) */
            .bh-badge { display: inline-block; padding: 3px 6px; font-size: 11px; font-weight: bold; border-radius: 3px; color: #fff; margin-bottom: 5px; }
            .bh-badge-y { background: #0073aa; }
            .bh-badge-x { background: #11a0d2; }
            .bh-badge-m { background: #f3b200; color: #32373c; }
            .bh-badge-none { background: #ccd0d4; color: #50575e; }
            
            /* Gom nhóm trực quan */
            .group-header-row { background: #f0f6fc !important; border-left: 4px solid #11a0d2; }
            .group-header-row td { padding: 10px 15px !important; color: #1d2327; font-size: 13px; font-weight: bold; }
            .qlbh-filter-badge { background: #fff; border: 1px solid #ccd0d4; padding: 4px 10px; border-radius: 4px; display: inline-flex; align-items: center; gap: 5px; font-size: 12px; }
            .qlbh-filter-badge a { color: #d54e21; font-weight: bold; text-decoration: none; font-size: 14px; margin-left: 3px; }
            .qlbh-filter-badge a:hover { color: #dc3232; }

            /* Tab Điều hướng */
            .tab-content { display: none; margin-top: 15px; }
            .tab-content.current { display: block; }

            /* Phân trang */
            .tablenav-pages .pagination-links a, 
            .tablenav-pages .pagination-links .current {
                display: inline-block;
                padding: 4px 10px;
                background: #fff;
                border: 1px solid #ccc;
                text-decoration: none;
                color: #2271b1;
                font-weight: 500;
                border-radius: 3px;
                font-size: 13px;
            }
            .tablenav-pages .pagination-links .current {
                background: #2271b1;
                color: #fff;
                border-color: #2271b1;
            }
            .row-info {
    display: flex;
    align-items: center;
    gap: 4px;
    margin-bottom: 2px;
    line-height: 1.4;
}
.row-info .dashicons {
    color: #8c8f94;
}
.customer-table td {
    vertical-align: top !important; /* Đảm bảo dữ liệu căn trên cùng */
    padding: 10px 8px !important;
}

 /* 1. Thẻ đã HẾT HẠN: Màu TỐI (Dark Mode) - Ưu tiên cao nhất */
    .qlbh-row-expired td { 
        background-color: #32373c !important; /* Màu xám đen của admin WordPress */
        color: #f0f0f1 !important; /* Chữ trắng xám */
        border-bottom: 1px solid #1d2327 !important;
    }
    /* Chỉnh lại màu các thẻ con bên trong dòng tối */
    .qlbh-row-expired strong { color: #fff !important; }
    .qlbh-row-expired b { color: #ff8a80 !important; } /* Ngày hết hạn hiện màu đỏ sáng cho nổi trên nền tối */
    .qlbh-row-expired a { color: #72aee6 !important; } /* Link màu xanh sáng */
    .qlbh-row-expired .code-highlight { 
        background: #1d2327 !important; 
        color: #fff !important; 
        border-color: #4f5459 !important; 
    }
    .qlbh-row-expired .dashicons { color: #a7aaad !important; }
    .qlbh-row-expired .bh-badge-y { background: #000 !important; border: 1px solid #444; }

    /* 2. Thẻ ƯU TIÊN (Mã khác GD): Màu VÀNG ĐẬM */
    .qlbh-row-priority td { 
        background-color: #ffecb3 !important; 
        color: #5d4037 !important;
        border-bottom: 1px solid #ffe082 !important;
    }
    .qlbh-row-priority strong { color: #795548 !important; }

    /* 3. Thẻ CÒN HẠN DÀI: Màu XANH LÁ ĐẬM */
    .qlbh-row-long-term td { 
        background-color: #c8e6c9 !important; 
        color: #1b5e20 !important;
        border-bottom: 1px solid #a5d6a7 !important;
    }
    .qlbh-row-long-term strong { color: #2e7d32 !important; }

    /* Hiệu ứng Hover để làm nổi bật dòng đang chọn */
    .customer-table tr:hover td { 
        filter: contrast(1.1) brightness(1.1); 
        box-shadow: inset 0 0 5px rgba(0,0,0,0.2);
    }




        </style>
        <?php
    }
}