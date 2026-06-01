<?php
if (!defined('ABSPATH')) exit;

global $wpdb;
$table = $wpdb->prefix . 'bhyts';

// 1. LẤY MÃ NHÂN VIÊN
$offId = qlbh_get_staff_official_id();
$intId = qlbh_get_staff_internal_id();

// 2. TRUY VẤN: Hết hạn trong 60 ngày & thuộc nhân viên đang đăng nhập
$items = $wpdb->get_results($wpdb->prepare("
    SELECT * FROM $table 
    WHERE bhytThuTruoc = 0 
    AND (bhytNhanVienThu = %s OR bhytNhanVienThu = %s)
    AND denNgayDt <= DATE_ADD(CURDATE(), INTERVAL 60 DAY)
    ORDER BY denNgayDt ASC
", $offId, $intId));
?>

<div class="wrap qlbh-wrap">
    <h1 class="wp-heading-inline">Danh sách Tái tục cá nhân</h1>
    <hr class="wp-header-end">

    <div class="qlbh-card" style="margin: 20px 0; border-left: 4px solid #d63638;">
        <strong>Nhân viên: <?php echo wp_get_current_user()->display_name; ?></strong> | 
        Mã: <code><?php echo $offId; ?></code> | 
        Hồ sơ cần xử lý: <b style="color:red;"><?php echo count($items); ?></b>
    </div>

    <table class="wp-list-table widefat fixed striped customer-table">
        <thead>
            <tr>
                <th width="65%">Thông tin khách hàng & Hạn dùng</th>
                <th width="35%">Trạng thái chăm sóc</th>
            </tr>
        </thead>
        <tbody>
            <?php if ($items): foreach ($items as $item): 
                $row_class = qlbh_get_row_status_class($item);
            ?>
            <tr class="<?php echo $row_class; ?>">
                <?php qlbh_render_customer_column($item); ?>

                <td style="vertical-align: top; padding: 15px !important;">
                    <div style="background: rgba(255,255,255,0.6); padding: 10px; border: 1px solid #ccc; border-radius: 4px;">
                        <label style="font-weight: bold; font-size: 11px; display:block; margin-bottom:5px;">TRẠNG THÁI LIÊN HỆ:</label>
                        <?php echo qlbh_render_renewal_status_select($item); ?>

                        <div style="margin-top: 15px;">
                            <a href="?page=qlbh-tokhai&action=init_tk&customer_id=<?php echo $item->id; ?>" 
                               class="button button-primary" style="width:100%; text-align:center;">
                               <b>LẬP TỜ KHAI THU TIỀN</b>
                            </a>
                        </div>
                    </div>
                </td>
            </tr>
            <?php endforeach; else: ?>
                <tr><td colspan="2">Không có khách hàng nào đến hạn tái tục.</td></tr>
            <?php endif; ?>
        </tbody>
    </table>
</div>

<script>
jQuery(document).ready(function($) {
    // AJAX đổi trạng thái tái tục nhanh
    $(document).on('change', '.qlbh-quick-status', function() {
        let select = $(this);
        let id = select.data('id');
        let status = select.val();
        
        select.css('opacity', '0.5');
        $.post(ajaxurl, {
            action: 'qlbh_update_renewal_status', // Bạn cần đăng ký action này trong class-admin
            id: id,
            status: status
        }, function(res) {
            select.css('opacity', '1');
            if(res.success) {
                // Tùy chọn: đổi màu border theo trạng thái
                location.reload(); 
            }
        });
    });
});
</script>