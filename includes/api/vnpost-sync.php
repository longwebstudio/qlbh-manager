<?php
if (!defined('ABSPATH')) exit;

/**
 * Route: /wp-json/qlbh/v1/sync-customer
 */
register_rest_route('qlbh/v1', '/sync-customer', array(
    'methods' => 'POST',
    'callback' => 'qlbh_api_vnpost_sync_callback',
    'permission_callback' => function() {
        $referer = isset($_SERVER['HTTP_REFERER']) ? $_SERVER['HTTP_REFERER'] : '';
        return (strpos($referer, 'ssm.vnpost.vn') !== false);
    },
));

function qlbh_api_vnpost_sync_callback($request) {
    global $wpdb;
    $params = $request->get_json_params();
    $table = $wpdb->prefix . 'bhyts';

    if (empty($params['maSoBhxh'])) {
        return new WP_REST_Response(['message' => 'Thiếu dữ liệu mã số BHXH'], 400);
    }

    $maSo = sanitize_text_field($params['maSoBhxh']);
    $data = array(
        'maSoBhxh'      => $maSo,
        'hoTen'         => sanitize_text_field($params['hoTen']),
        'ngaySinhDt'    => qlbh_sanitize_date_db($params['ngaySinh']),
        'soTheBhyt'     => sanitize_text_field($params['maThe']),
        'tuNgayDt'      => qlbh_sanitize_date_db($params['tuNgay']),
        'denNgayDt'     => qlbh_sanitize_date_db($params['denNgay']),
        'ngay5Nam'      => qlbh_sanitize_date_db($params['bhyt5Nam']),
        'maKCB'         => sanitize_text_field($params['kcb']),
        'tenDvi'        => sanitize_text_field($params['donVi']),
        'ngayTraCuu'    => current_time('mysql'),
    );

    $exists = $wpdb->get_var($wpdb->prepare("SELECT id FROM $table WHERE maSoBhxh = %s", $maSo));

    if ($exists) {
        $wpdb->update($table, $data, array('id' => $exists));
        $msg = "✅ Đã cập nhật: " . $data['hoTen'];
    } else {
        $wpdb->insert($table, $data);
        $msg = "🆕 Đã thêm mới: " . $data['hoTen'];
    }

    return new WP_REST_Response(['message' => $msg], 200);
}