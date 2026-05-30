<?php
if ( ! defined( 'ABSPATH' ) ) exit;
global $wpdb;
$table_name = $wpdb->prefix . 'qlbh_khach_hang';

$int_id = qlbh_get_staff_internal_id(); // Mã tạm (NV+CCCD)
$off_id = qlbh_get_staff_official_id(); // Mã chính thức

// 1. XÁC NHẬN GIA HẠN BHXH
if (isset($_GET['action']) && $_GET['action'] === 'confirm_bhxh') {
    $id = intval($_GET['id']);
    $cust = $wpdb->get_row($wpdb->prepare("SELECT bhxh_den_ngay, bhxh_so_tien_thu_truoc FROM $table_name WHERE id = %d", $id));
    
    // Gia hạn 6 tháng theo mặc định BHXH tự nguyện
    $new_expiry = qlbh_get_new_expiry_bhxh($cust->bhxh_den_ngay, 6);

    $wpdb->update($table_name, array(
        'bhxh_thu_truoc'         => 0,
        'bhxh_den_ngay'          => $new_expiry,
        'bhxh_so_tien'           => $cust->bhxh_so_tien_thu_truoc,
        'bhxh_ngay_bien_lai'     => current_time('mysql'),
        'bhxh_nhan_vien_thu'     => $off_id,
        'bhxh_so_tien_thu_truoc' => 0
    ), array('id' => $id));
    echo '<div class="updated"><p>Gia hạn BHXH thành công!</p></div>';
}

// 2. HỦY THU
if (isset($_GET['action']) && $_GET['action'] === 'cancel_bhxh') {
    $wpdb->update($table_name, ['bhxh_thu_truoc' => 0, 'bhxh_so_tien_thu_truoc' => 0, 'bhxh_nhan_vien_thu' => ''], ['id' => intval($_GET['id'])]);
    echo '<div class="notice notice-warning"><p>Đã hủy thu BHXH.</p></div>';
}

$items = $wpdb->get_results($wpdb->prepare("SELECT * FROM $table_name WHERE bhxh_thu_truoc = 1 AND bhxh_nhan_vien_thu = %s", $int_id));
?>

<div class="wrap qlbh-wrap">
    <h1>Phê duyệt Gia hạn BHXH</h1>
    <table class="wp-list-table widefat fixed striped">
        <thead>
            <tr><th>Khách hàng</th><th>Ngày thu</th><th>Số tiền</th><th>Thao tác</th></tr>
        </thead>
        <tbody>
            <?php if($items): foreach($items as $item): ?>
            <tr>
                <td><strong><?php echo $item->ho_ten; ?></strong><br><code><?php echo $item->ma_so_bhxh; ?></code></td>
                <td><?php echo qlbh_format_date_view($item->bhxh_ngay_thu_truoc); ?></td>
                <td><b style="color:#0073aa;"><?php echo number_format($item->bhxh_so_tien_thu_truoc); ?>đ</b></td>
                <td>
                    <a href="?page=qlbh-thu-bhxh&action=confirm_bhxh&id=<?php echo $item->id; ?>" class="button button-primary">Xác nhận</a>
                    <a href="?page=qlbh-thu-bhxh&action=cancel_bhxh&id=<?php echo $item->id; ?>" class="button" style="color:red;">Hủy</a>
                </td>
            </tr>
            <?php endforeach; else: ?>
            <tr><td colspan="4">Không có hồ sơ BHXH chờ xử lý.</td></tr>
            <?php endif; ?>
        </tbody>
    </table>
</div>