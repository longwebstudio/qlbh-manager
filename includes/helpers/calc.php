<?php
if (!defined('ABSPATH')) exit;

/**
 * Lấy lương cơ sở từ cấu hình
 */
function qlbh_get_base_salary() {
    return (float) get_option('qlbh_base_salary', 2340000);
}

/**
 * Tính giá BHYT 100% cho 1 tháng
 */
function qlbh_get_p1_month_price() {
    return (qlbh_get_base_salary() * 0.045);
}

/**
 * Tính ngày hết hạn mới (cộng dồn hoặc tính từ hôm nay)
 */
function qlbh_calculate_new_expiry($current_expiry, $months_to_add) {
    $today = date('Y-m-d');
    $base_date = (empty($current_expiry) || $current_expiry < $today) ? $today : $current_expiry;
    return date('Y-m-d', strtotime("+$months_to_add months", strtotime($base_date)));
}