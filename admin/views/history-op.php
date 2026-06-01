<?php
if (!defined('ABSPATH')) exit;
global $wpdb;
$table_ls = $wpdb->prefix . 'qlbh_lich_su_bh';

// Xử lý phê duyệt
if (isset($_GET['action']) && $_GET['action'] === 'approve_ls') {
    $wpdb->update($table_ls, ['isPending' => 0, 'ngayBienLai' => date('Y-m-d')], ['id' => intval($_GET['ls_id'])]);
    echo '<div class="updated"><p>Đã phê duyệt hồ sơ BHXH/MIC!</p></div>';
}

$items = $wpdb->get_results("SELECT h.*, k.hoTen FROM $table_ls h LEFT JOIN {$wpdb->prefix}bhyts k ON h.maSoBhxh = k.maSoBhxh WHERE h.isPending = 1");
?>
<div class="wrap qlbh-wrap">
    <h1>Phê duyệt Thu BHXH & MIC</h1>
    <table class="wp-list-table widefat fixed striped">
        <thead>
            <tr>
                <th>Khách hàng</th>
                <th>Loại BH</th>
                <th>Số tiền</th>
                <th>Số tháng</th>
                <th>Thao tác</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($items as $it): ?>
            <tr>
                <td><strong><?php echo $it->hoTen; ?></strong><br><code><?php echo $it->maSoBhxh; ?></code></td>
                <td><span class="bh-badge <?php echo $it->loaiBh === 'bhxh' ? 'bh-badge-x' : 'bh-badge-m'; ?>"><?php echo strtoupper($it->loaiBh); ?></span></td>
                <td><b><?php echo number_format($it->soTien); ?>đ</b></td>
                <td><?php echo $it->soThang; ?> tháng</td>
                <td>
                    <a href="?page=qlbh-history-op&action=approve_ls&ls_id=<?php echo $it->id; ?>" class="button button-primary">Xác nhận</a>
                </td>
            </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</div>