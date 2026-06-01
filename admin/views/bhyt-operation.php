<?php
if (!defined('ABSPATH')) exit;
global $wpdb;
$table = $wpdb->prefix . 'bhyts';
$off_id = qlbh_get_staff_official_id();

// Xử lý xác nhận gia hạn
if (isset($_GET['action']) && $_GET['action'] === 'confirm') {
    $id = intval($_GET['id']);
    $cust = $wpdb->get_row($wpdb->prepare("SELECT * FROM $table WHERE id = %d", $id));
    
    if ($cust) {
        $new_expiry = qlbh_get_new_expiry_bhyt($cust->denNgayDt, $cust->bhytSoThang);
        $wpdb->update($table, [
            'bhytThuTruoc' => 0,
            'denNgayDt' => $new_expiry,
            'bhytSoTien' => $cust->bhytSoTienThuTruoc,
            'bhytNgayBienLai' => current_time('mysql'),
            'bhytNhanVienThu' => $off_id, // Chuyển sang mã chính thức
            'bhytSoTienThuTruoc' => 0
        ], ['id' => $id]);
        echo '<div class="updated"><p>Đã phê duyệt và cập nhật hạn thẻ mới!</p></div>';
    }
}

$items = $wpdb->get_results($wpdb->prepare("SELECT * FROM $table WHERE bhytThuTruoc = 1 AND bhytNhanVienThu = %s", qlbh_get_staff_internal_id()));
?>

<div class="wrap qlbh-wrap">
    <h1>Phê duyệt Gia hạn BHYT</h1>
    <table class="wp-list-table widefat fixed striped">
        <thead>
            <tr>
                <th>Khách hàng</th>
                <th>Số tiền đã thu</th>
                <th>Số tháng</th>
                <th>Thao tác</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($items as $it): ?>
            <tr>
                <td><strong><?php echo $it->hoTen; ?></strong><br><small><?php echo $it->maSoBhxh; ?></small></td>
                <td><b style="color:green;"><?php echo number_format($it->bhytSoTienThuTruoc); ?>đ</b></td>
                <td><?php echo $it->bhytSoThang; ?> tháng</td>
                <td>
                    <a href="<?php echo add_query_arg(['action'=>'confirm', 'id'=>$it->id]); ?>" 
                       class="button button-primary" onclick="return confirm('Xác nhận đã nộp hồ sơ thành công?')">Xác nhận Gia hạn</a>
                </td>
            </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</div>