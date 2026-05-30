<?php
if ( ! defined( 'ABSPATH' ) ) exit;
global $wpdb;
$table_name = $wpdb->prefix . 'qlbh_khach_hang';

$int_id = qlbh_get_staff_internal_id(); 
$off_id = qlbh_get_staff_official_id();

// 1. XÁC NHẬN GIA HẠN MIC
if (isset($_GET['action']) && $_GET['action'] === 'confirm_mic') {
    $id = intval($_GET['id']);
    $cust = $wpdb->get_row($wpdb->prepare("SELECT mic_den_ngay, mic_so_tien_thu_truoc FROM $table_name WHERE id = %d", $id));
    
    $new_expiry = qlbh_get_new_expiry_mic($cust->mic_den_ngay, 12);

    $wpdb->update($table_name, array(
        'mic_thu_truoc'         => 0,
        'mic_den_ngay'          => $new_expiry,
        'mic_so_tien'           => $cust->mic_so_tien_thu_truoc,
        'mic_ngay_bien_lai'     => current_time('mysql'),
        'mic_nhan_vien_thu'     => $off_id,
        'mic_so_tien_thu_truoc' => 0
    ), array('id' => $id));
    echo '<div class="updated"><p>Gia hạn MIC thành công!</p></div>';
}

// 2. HỦY THU
if (isset($_GET['action']) && $_GET['action'] === 'cancel_mic') {
    $wpdb->update($table_name, ['mic_thu_truoc' => 0, 'mic_so_tien_thu_truoc' => 0, 'mic_nhan_vien_thu' => ''], ['id' => intval($_GET['id'])]);
    echo '<div class="notice notice-warning"><p>Đã hủy thu MIC.</p></div>';
}

$items = $wpdb->get_results($wpdb->prepare("SELECT * FROM $table_name WHERE mic_thu_truoc = 1 AND mic_nhan_vien_thu = %s", $int_id));
?>

<div class="wrap qlbh-wrap">
    <h1>Phê duyệt Gia hạn MIC</h1>
    <table class="wp-list-table widefat fixed striped">
        <thead>
            <tr><th>Khách hàng</th><th>Ngày thu</th><th>Số tiền</th><th>Thao tác</th></tr>
        </thead>
        <tbody>
            <?php if($items): foreach($items as $item): ?>
            <tr>
                <td><strong><?php echo $item->ho_ten; ?></strong><br><code><?php echo $item->ma_so_bhxh; ?></code></td>
                <td><?php echo qlbh_format_date_view($item->mic_ngay_thu_truoc); ?></td>
                <td><b style="color:#d63638;"><?php echo number_format($item->mic_so_tien_thu_truoc); ?>đ</b></td>
                <td>
                    <a href="?page=qlbh-thu-mic&action=confirm_mic&id=<?php echo $item->id; ?>" class="button button-primary">Xác nhận</a>
                    <a href="?page=qlbh-thu-mic&action=cancel_mic&id=<?php echo $item->id; ?>" class="button" style="color:red;">Hủy</a>
                </td>
            </tr>
            <?php endforeach; else: ?>
            <tr><td colspan="4">Không có hồ sơ MIC chờ xử lý.</td></tr>
            <?php endif; ?>
        </tbody>
    </table>
</div>