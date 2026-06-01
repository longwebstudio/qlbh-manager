<?php
/**
 * File: admin/class-qlbh-admin.php
 * Chức năng: Điều hướng Admin, Quản lý Assets và Xử lý AJAX
 */

if (!defined('ABSPATH')) exit;

class QLBH_Admin {

    public static function init() {
        // Đăng ký Menu Sidebar
        add_action('admin_menu', [__CLASS__, 'register_menus']);
        
        // Nhúng CSS và JavaScript vào Header
        add_action('admin_head', [__CLASS__, 'enqueue_custom_assets']);
        
        // Đăng ký các cổng xử lý AJAX
        add_action('wp_ajax_qlbh_update_status', [__CLASS__, 'ajax_update_status']);
        add_action('wp_ajax_qlbh_update_note', [__CLASS__, 'ajax_update_note']);

        // Xử lý yêu cầu xuất file báo cáo
        add_action('admin_init', [__CLASS__, 'handle_export_request']);
    }

    public static function register_menus() {
        $cap = 'manage_options';
        $slug = 'qlbh-manager';

        add_menu_page('Quản lý Bảo hiểm', 'Quản lý Bảo hiểm', $cap, $slug, [__CLASS__, 'view_list'], 'dashicons-shield', 25);
        add_submenu_page($slug, 'Danh sách', 'Danh sách BHYT', $cap, $slug, [__CLASS__, 'view_list']);
        add_submenu_page($slug, 'Thêm mới', 'Thêm hồ sơ mới', $cap, 'qlbh-add', [__CLASS__, 'view_add_edit']);
        add_submenu_page($slug, 'Tái tục', 'Danh sách Tái tục', $cap, 'qlbh-renewal', [__CLASS__, 'view_renewal']);
        add_submenu_page($slug, 'Tờ khai', 'Lập Tờ khai HGĐ', $cap, 'qlbh-tokhai', [__CLASS__, 'view_tokhai']);
        add_submenu_page($slug, 'Phê duyệt', 'Phê duyệt BHYT', $cap, 'qlbh-op-bhyt', [__CLASS__, 'view_op_bhyt']);
        add_submenu_page($slug, 'Import JSON', 'Import dữ liệu', $cap, 'qlbh-import', [__CLASS__, 'view_import']);
        add_submenu_page($slug, 'Báo cáo', 'Xuất Báo cáo', $cap, 'qlbh-reports', [__CLASS__, 'view_reports']);
        add_submenu_page($slug, 'Hướng dẫn', 'Hướng dẫn Đồng bộ', $cap, 'qlbh-guide', [__CLASS__, 'view_guide']);
        add_submenu_page($slug, 'Cấu hình', 'Cấu hình hệ thống', $cap, 'qlbh-settings', [__CLASS__, 'view_settings']);
    }

    public static function view_list() { include QLBH_PATH . 'admin/views/list.php'; }
    public static function view_add_edit() { include QLBH_PATH . 'admin/views/add-edit.php'; }
    public static function view_tokhai() { include QLBH_PATH . 'admin/views/quan-ly-tokhai.php'; }
    public static function view_renewal() { include QLBH_PATH . 'admin/views/renewal.php'; }
    public static function view_op_bhyt() { include QLBH_PATH . 'admin/views/bhyt-op.php'; }
    public static function view_import() { include QLBH_PATH . 'admin/views/import.php'; }
    public static function view_reports() { include QLBH_PATH . 'admin/views/reports.php'; }
    public static function view_guide() { include QLBH_PATH . 'admin/views/guide.php'; }
    public static function view_settings() { include QLBH_PATH . 'admin/views/settings.php'; }

    public static function handle_export_request() {
        if (isset($_GET['action']) && $_GET['action'] === 'qlbh_export_csv') {
            require_once QLBH_PATH . 'includes/helpers/export.php';
            qlbh_process_csv_export();
        }
    }

    public static function enqueue_custom_assets() {
        $screen = get_current_screen();
        if (strpos($screen->id, 'qlbh-') === false && strpos($screen->id, 'qlbh-manager') === false) return;
        ?>
        <style>
            .qlbh-wrap { margin-top: 20px; font-family: -apple-system,BlinkMacSystemFont,"Segoe UI",Roboto,Oxygen-Sans,Ubuntu,Cantarell,"Helvetica Neue",sans-serif; }
            .qlbh-card { background: #fff; border: 1px solid #ccd0d4; padding: 15px; border-radius: 4px; box-shadow: 0 1px 1px rgba(0,0,0,.04); }
            .code-highlight { background: #e7f4f9; padding: 2px 5px; border-radius: 3px; border: 1px solid #b4e0f1; color: #0073aa; font-family: monospace; font-size: 12px; }
            .bh-badge { display: inline-block; padding: 2px 6px; border-radius: 3px; color: #fff; font-size: 10px; font-weight: bold; text-transform: uppercase; }
            .bh-badge-y { background: #0073aa; }
            .row-info { display: flex; align-items: center; gap: 5px; margin-bottom: 3px; }
            
            /* TRẠNG THÁI MÀU DÒNG (CamelCase Logic) */
            /* 1. HẾT HẠN: Màu tối mạnh mẽ */
            .qlbh-row-expired td { background-color: #32373c !important; color: #f0f0f1 !important; border-bottom: 1px solid #1d2327 !important; }
            .qlbh-row-expired strong { color: #fff !important; }
            .qlbh-row-expired b { color: #ff8a80 !important; }
            .qlbh-row-expired a { color: #72aee6 !important; }
            .qlbh-row-expired .code-highlight { background: #1d2327; color: #fff; border-color: #444; }
            .qlbh-row-expired .dashicons { color: #a7aaad !important; }

            /* 2. ƯU TIÊN (Mã khác GD): Màu vàng */
            .qlbh-row-priority td { background-color: #fff9c4 !important; color: #5d4037 !important; }

            /* 3. CÒN HẠN DÀI: Màu xanh lá */
            .qlbh-row-long-term td { background-color: #d1e7dd !important; color: #0f5132 !important; }

            /* Hiệu ứng hover */
            .customer-table tr:hover td { filter: brightness(95%); }
        </style>
        
        <script>
        jQuery(document).ready(function($) {
            // AJAX sửa ghi chú nhanh
            $(document).on('click', '.qlbh-quick-note', function() {
                let id = $(this).data('id');
                let old = $(this).find('.note-content').text();
                let note = prompt("Nhập ghi chú mới:", old === 'Trống' ? '' : old);
                if (note !== null) {
                    let container = $(this);
                    container.css('opacity', '0.5');
                    $.post(ajaxurl, { action: 'qlbh_update_note', id: id, note: note }, function(res) {
                        container.css('opacity', '1');
                        if(res.success) container.find('.note-content').text(note || 'Trống');
                    });
                }
            });

            // AJAX đổi trạng thái tái tục
            $(document).on('change', '.qlbh-quick-status', function() {
                let id = $(this).data('id');
                let status = $(this).val();
                let select = $(this);
                select.css('opacity', '0.5');
                $.post(ajaxurl, { action: 'qlbh_update_status', id: id, status: status }, function(res) {
                    select.css('opacity', '1');
                    if(res.success) location.reload();
                });
            });
        });
        </script>
        <?php
    }

    public static function ajax_update_status() {
        global $wpdb;
        $wpdb->update($wpdb->prefix.'bhyts', ['trangThaiTaiTuc' => sanitize_text_field($_POST['status'])], ['id' => intval($_POST['id'])]);
        wp_send_json_success();
    }

    public static function ajax_update_note() {
        global $wpdb;
        $wpdb->update($wpdb->prefix.'bhyts', ['ghiChu' => sanitize_textarea_field($_POST['note'])], ['id' => intval($_POST['id'])]);
        wp_send_json_success();
    }
}