<?php
if (!defined('ABSPATH')) exit;
global $wpdb;
$table = $wpdb->prefix . 'bhyts';

$current_tk = isset($_GET['ma_tk']) ? sanitize_text_field($_GET['ma_tk']) : '';
$int_id = qlbh_get_staff_internal_id();
$p1_month = qlbh_get_p1_month_price(); // Giá 1 tháng từ calc.php

// Xử lý Ghi nhận thu tiền
if (isset($_POST['action_thu_tien'])) {
    $m_id = intval($_POST['m_id']);
    $money = qlbh_sanitize_money($_POST['money_input']);
    $wpdb->update($table, [
        'bhytThuTruoc' => 1,
        'bhytNgayThuTruoc' => current_time('mysql'),
        'bhytSoTienThuTruoc' => $money,
        'bhytNhanVienThu' => $int_id,
        'bhytSoThang' => intval($_POST['so_thang']),
        'bhytPhuongThucDong' => sanitize_text_field($_POST['muc_dong'])
    ], ['id' => $m_id]);
    echo '<div class="updated"><p>Đã ghi nhận thu tiền thành công!</p></div>';
}

$members = $current_tk ? $wpdb->get_results($wpdb->prepare("SELECT * FROM $table WHERE maToKhaiRieng = %s", $current_tk)) : [];
?>

<div class="wrap qlbh-wrap">
    <h1>Lập Tờ khai & Tính tiền HGĐ</h1>

    <?php if ($current_tk): ?>
    <div class="qlbh-card">
        <h2 style="margin-top:0;">Mã tờ khai: <code style="color:#d54e21;"><?php echo $current_tk; ?></code></h2>
        
        <table class="wp-list-table widefat fixed striped customer-table">
            <thead>
                <tr>
                    <th width="35%">Thành viên</th>
                    <th width="12%">Số tháng</th>
                    <th width="15%">Mức đóng</th>
                    <th width="18%">Số tiền tạm thu</th>
                    <th>Thao tác</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($members as $m): ?>
                <tr id="row-<?php echo $m->id; ?>" class="member-row">
                    <?php qlbh_render_customer_column($m); ?>
                    <td>
                        <select class="so-thang-select" data-id="<?php echo $m->id; ?>" <?php disabled($m->bhytThuTruoc, 1); ?>>
                            <option value="3">3 tháng</option>
                            <option value="6">6 tháng</option>
                            <option value="12" selected>12 tháng</option>
                        </select>
                    </td>
                    <td>
                        <select class="muc-dong-select" data-id="<?php echo $m->id; ?>" <?php disabled($m->bhytThuTruoc, 1); ?>>
                            <option value="">-- Mức --</option>
                            <option value="1">Thứ 1 (100%)</option>
                            <option value="2">Thứ 2 (70%)</option>
                            <option value="3">Thứ 3 (60%)</option>
                            <option value="4">Thứ 4 (50%)</option>
                            <option value="5">Thứ 5+ (40%)</option>
                        </select>
                    </td>
                    <td>
                        <form method="post">
                            <input type="hidden" name="action_thu_tien" value="1">
                            <input type="hidden" name="m_id" value="<?php echo $m->id; ?>">
                            <input type="hidden" name="so_thang" class="h-thang" value="12">
                            <input type="hidden" name="muc_dong" class="h-muc" value="">
                            <input type="text" name="money_input" class="money-display" readonly 
                                   value="<?php echo $m->bhytThuTruoc ? number_format($m->bhytSoTienThuTruoc) : ''; ?>"
                                   style="width:100%; font-weight:bold; text-align:right;">
                    </td>
                    <td>
                        <?php if ($m->bhytThuTruoc): ?>
                            <span class="bh-badge" style="background:#46b450;">ĐÃ THU</span>
                        <?php else: ?>
                            <button type="submit" class="button button-primary btn-save" disabled>Ghi nhận</button>
                        <?php endif; ?>
                        </form>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
    <?php endif; ?>
</div>

<script>
jQuery(document).ready(function($) {
    const p1Month = <?php echo floatval($p1_month); ?>;
    const rates = { "1": 1, "2": 0.7, "3": 0.6, "4": 0.5, "5": 0.4 };

    $('.muc-dong-select, .so-thang-select').on('change', function() {
        let row = $(this).closest('tr');
        let id = row.attr('id').replace('row-', '');
        let muc = row.find('.muc-dong-select').val();
        let thang = row.find('.so-thang-select').val();

        if (muc && thang) {
            let total = Math.round(p1Month * parseInt(thang) * rates[muc]);
            row.find('.money-display').val(new Intl.NumberFormat().format(total));
            row.find('.h-thang').val(thang);
            row.find('.h-muc').val(muc);
            row.find('.btn-save').prop('disabled', false);
        }
    });
});
</script>