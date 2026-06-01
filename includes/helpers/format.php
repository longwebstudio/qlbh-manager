<?php
if (!defined('ABSPATH')) exit;

/**
 * Hiển thị ngày dd/mm/yyyy
 */
function qlbh_format_date_view($date) {
    if (!$date || $date === '0000-00-00' || $date === '1970-01-01') return '---';
    return date('d/m/Y', strtotime($date));
}

/**
 * Chuẩn hóa ngày để lưu DB (yyyy-mm-dd)
 */
function qlbh_sanitize_date_db($date_str) {
    if (empty($date_str)) return null;
    $date_str = str_replace('/', '-', $date_str);
    return date('Y-m-d', strtotime($date_str));
}

/**
 * Chuẩn hóa số tiền (Xóa dấu . và ,)
 */
function qlbh_sanitize_money($money_str) {
    if (is_numeric($money_str)) return (float)$money_str;
    return (float) preg_replace('/[^\d]/', '', $money_str);
}

/**
 * Chuẩn hóa link Facebook
 */
function qlbh_get_facebook_url($input) {
    $input = trim((string)$input);
    if (empty($input) || in_array($input, ['0', '---', 'none'])) return false;
    if (preg_match('/^https?:\/\//', $input)) return esc_url($input);
    return 'https://www.facebook.com/' . ltrim($input, '@');
}