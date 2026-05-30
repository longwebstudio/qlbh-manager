<?php
if ( ! defined( 'ABSPATH' ) ) exit;
global $wpdb;
$table_name = $wpdb->prefix . 'qlbh_khach_hang';

$current_user_id = get_current_user_id();
$off_id = qlbh_get_staff_official_id($current_user_id);
$int_id = qlbh_get_staff_internal_id($current_user_id);

if (empty($off_id) && empty($int_id)) {
    echo '<div class="notice notice-error"><p>Lỗi: Tài khoản của bạn chưa được cấu hình Mã nhân viên hoặc CCCD. Vui lòng liên hệ Admin.</p></div>';
    return;
}

// Logic lọc: Hết hạn trong vòng 30 ngày tới HOẶC đã hết hạn
// Và phải thuộc mã nhân viên thu (Mã chính thức hoặc Mã nội bộ)
$sql = $wpdb->prepare("
    SELECT * FROM $table_name 
    WHERE (
        (bhyt_den_ngay <= DATE_ADD(CURDATE(), INTERVAL 30 DAY)) OR 
        (bhxh_den_ngay <= DATE_ADD(CURDATE(), INTERVAL 30 DAY))
    )
    AND (
        bhyt_nhan_vien_thu IN (%s, %s) OR 
        bhxh_nhan_vien_thu IN (%s, %s) OR
        mic_nhan_vien_thu IN (%s, %s)
    )
    ORDER BY bhyt_den_ngay ASC
", $off_id, $int_id, $off_id, $int_id, $off_id, $int_id);

$items = $wpdb->get_results($sql);
?>

<div class="wrap qlbh-wrap">
    <h1>Danh sách Tái tục của tôi</h1>
    <p class="description">Chào <strong><?php echo wp_get_current_user()->display_name; ?></strong>. Dưới đây là danh sách khách hàng do bạn quản lý (Mã: <?php echo $off_id; ?> / <?php echo $int_id; ?>) sắp hết hạn.</p>

    <div class="qlbh-filter-badge">
        Nhân viên: <strong><?php echo $off_id; ?></strong> | CCCD: <strong><?php echo str_replace('NV','',$int_id); ?></strong>
    </div>

    <table class="wp-list-table widefat fixed striped" style="margin-top:15px;">
        <thead>
            <tr>
                <th>Khách hàng</th>
                <th>BHYT (Hết hạn)</th>
                <th>BHXH (Hết hạn)</th>
                <th>MIC (Hết hạn)</th>
                <th>Thao tác</th>
            </tr>
        </thead>
        <tbody>
            <?php if ($items): foreach ($items as $item): ?>
            <tr>
                <td>
                    <strong><?php echo $item->ho_ten; ?></strong><br>
                    <span class="code-highlight"><?php echo $item->ma_so_bhxh; ?></span>
                </td>
                <td>
                    <?php 
                    $status = qlbh_check_expiry_status($item->bhyt_den_ngay);
                    $color = ($status === 'expired') ? 'red' : (($status === 'warning') ? 'orange' : 'inherit');
                    ?>
                    <span style="color:<?php echo $color; ?>; font-weight:500;">
                        <?php echo qlbh_format_date_view($item->bhyt_den_ngay); ?>
                    </span>
                </td>
                <td>
                    <?php 
                    $status_x = qlbh_check_expiry_status($item->bhxh_den_ngay);
                    $color_x = ($status_x === 'expired') ? 'red' : (($status_x === 'warning') ? 'orange' : 'inherit');
                    ?>
                    <span style="color:<?php echo $color_x; ?>;">
                        <?php echo qlbh_format_date_view($item->bhxh_den_ngay); ?>
                    </span>
                </td>
                <td><?php echo qlbh_format_date_view($item->mic_den_ngay); ?></td>
                <td>
                    <a href="?page=qlbh-tokhai&action=init_tk&customer_id=<?php echo $item->id; ?>" class="button button-small button-primary">Thu BHYT</a>
                    <a href="?page=qlbh-manager&action=thu_bhxh_nhanh&id=<?php echo $item->id; ?>" class="button button-small">Thu BHXH</a>
                </td>
            </tr>
            <?php endforeach; else: ?>
                <tr><td colspan="5">Tuyệt vời! Không có khách hàng nào sắp hết hạn trong danh sách quản lý của bạn.</td></tr>
            <?php endif; ?>
        </tbody>
    </table>
</div>