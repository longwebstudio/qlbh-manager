<?php
if ( ! defined( 'ABSPATH' ) ) exit;

global $wpdb;
$table_name = $wpdb->prefix . 'qlbh_khach_hang';

$current_tk = isset($_GET['ma_tk']) ? sanitize_text_field($_GET['ma_tk']) : '';
$current_year = date('Y');
$int_id = qlbh_get_staff_internal_id();
$p1_month = qlbh_get_p1_month_price(); // Giá 1 tháng mức 100% (Từ lương cơ sở)

/* ==========================================================================
   1. XỬ LÝ LOGIC (HỦY THU & GHI NHẬN)
   ========================================================================== */

// A. Hủy thu
if (isset($_GET['action']) && $_GET['action'] === 'huy_thu_bhyt' && isset($_GET['m_id'])) {
    $m_id = intval($_GET['m_id']);
    $wpdb->update($table_name, array(
        'bhyt_thu_truoc'         => 0,
        'bhyt_ngay_thu_truoc'    => null,
        'bhyt_so_tien_thu_truoc' => 0,
        'bhyt_nhan_vien_thu'     => ''
    ), array('id' => $m_id));
    echo "<script>window.location.href='".remove_query_arg(['action', 'm_id'])."';</script>";
    exit;
}

// B. Ghi nhận thu tiền
if (isset($_POST['action']) && $_POST['action'] === 'thu_tien_don_le') {
    $m_id = intval($_POST['m_id']);
    $muc_dong = sanitize_text_field($_POST['muc_dong']);
    $so_thang = intval($_POST['so_thang']);
    // Bỏ dấu chấm phân cách trước khi lưu vào database
    $money_clean = qlbh_sanitize_money($_POST['money_input']); 
    
    $wpdb->update($table_name, array(
        'bhyt_thu_truoc'         => 1,
        'bhyt_ngay_thu_truoc'    => current_time('mysql'),
        'bhyt_so_tien_thu_truoc' => $money_clean,
        'bhyt_phuong_thuc_dong'  => $muc_dong,
        'bhyt_so_thang'          => $so_thang,
        'bhyt_nhan_vien_thu'     => $int_id
    ), array('id' => $m_id));
    echo '<div class="updated"><p>Đã ghi nhận thu tiền thành công cho thành viên.</p></div>';
}

// Lấy danh sách thành viên
$members = $current_tk ? $wpdb->get_results($wpdb->prepare("SELECT * FROM $table_name WHERE ma_to_khai_rieng = %s", $current_tk)) : [];
$total_collected = 0;
foreach ($members as $m) { if ($m->bhyt_thu_truoc) $total_collected += (float)$m->bhyt_so_tien_thu_truoc; }
?>

<div class="wrap qlbh-wrap">
    <h1>Quản lý Tờ khai & Mức đóng Hộ gia đình</h1>

    <?php if ($current_tk): ?>
    <div class="qlbh-card">
        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 15px;">
            <h2 style="margin:0;">Mã tờ khai: <code style="color:#d54e21;"><?php echo $current_tk; ?></code></h2>
            <a href="?page=qlbh-manager" class="button">Quay lại danh sách</a>
        </div>

        <table class="wp-list-table widefat fixed striped">
            <thead>
                <tr>
                    <th width="20%">Thành viên</th>
                    <th width="15%">Số tháng đóng</th>
                    <th width="15%">Mức đóng HGĐ</th>
                    <th width="18%">Lịch sử (Cũ)</th>
                    <th width="16%">Số tiền tạm thu</th>
                    <th width="12%">Thao tác</th>
                </tr>
            </thead>
            <tbody>
                <?php if ($members): foreach ($members as $m): 
                    $is_renewed = ($m->bhyt_ngay_bien_lai && date('Y', strtotime($m->bhyt_ngay_bien_lai)) == $current_year);
                ?>
                <tr id="row-<?php echo $m->id; ?>"  class="<?php echo $row_class; ?>" class="member-row">
                    <?php qlbh_render_customer_column($m); ?>

                    <!-- SỐ THÁNG ĐÓNG -->
                    <td>
                        <select class="so-thang-select" data-id="<?php echo $m->id; ?>" <?php disabled($m->bhyt_thu_truoc, 1); ?> style="width:100%;">
                            <option value="3" <?php selected($m->bhyt_so_thang, 3); ?>>3 Tháng</option>
                            <option value="6" <?php selected($m->bhyt_so_thang, 6); ?>>6 Tháng</option>
                            <option value="12" <?php selected(($m->bhyt_so_thang ? $m->bhyt_so_thang : 12), 12); ?>>12 Tháng</option>
                        </select>
                    </td>

                    <!-- MỨC ĐÓNG HGĐ -->
                    <td>
                        <select class="muc-dong-select" data-id="<?php echo $m->id; ?>" <?php disabled($m->bhyt_thu_truoc, 1); ?> style="width:100%;">
                            <option value="">-- Chọn mức --</option>
                            <option value="1" <?php selected($m->bhyt_phuong_thuc_dong, '1'); ?>>Mức 1 (100%)</option>
                            <option value="2" <?php selected($m->bhyt_phuong_thuc_dong, '2'); ?>>Mức 2 (70%)</option>
                            <option value="3" <?php selected($m->bhyt_phuong_thuc_dong, '3'); ?>>Mức 3 (60%)</option>
                            <option value="4" <?php selected($m->bhyt_phuong_thuc_dong, '4'); ?>>Mức 4 (50%)</option>
                            <option value="5" <?php selected($m->bhyt_phuong_thuc_dong, '5'); ?>>Mức 5 (40%)</option>
                        </select>
                    </td>

                    <td>
                        <div style="font-size: 11px;">
                            Mức cũ: <b><?php echo $m->bhyt_phuong_thuc_dong; ?></b> - BL: <?php echo qlbh_format_date_view($m->bhyt_ngay_bien_lai); ?>
                            <?php if($is_renewed): ?><br><span style="color:#46b450; font-weight:bold;">[ĐÃ GIA HẠN <?php echo $current_year; ?>]</span><?php endif; ?>
                        </div>
                    </td>

                    <td>
                        <form method="post" id="form-thu-<?php echo $m->id; ?>">
                            <input type="hidden" name="action" value="thu_tien_don_le">
                            <input type="hidden" name="m_id" value="<?php echo $m->id; ?>">
                            <input type="hidden" name="muc_dong" class="hidden-muc" value="<?php echo $m->bhyt_phuong_thuc_dong; ?>">
                            <input type="hidden" name="so_thang" class="hidden-thang" value="<?php echo ($m->bhyt_so_thang ? $m->bhyt_so_thang : 12); ?>">
                            
                            <input type="text" name="money_input" class="money-display" 
                                   value="<?php echo ($m->bhyt_thu_truoc) ? number_format($m->bhyt_so_tien_thu_truoc) : ''; ?>" 
                                   placeholder="0" readonly
                                   style="width:100%; text-align:right; font-weight:bold; background:#f9f9f9; border:1px solid #ddd;">
                        </form>
                    </td>

                    <td style="text-align: center;">
                        <?php if($m->bhyt_thu_truoc): ?>
                            <span class="bh-badge" style="background:#46b450;">ĐÃ THU</span><br>
                            <a href="<?php echo add_query_arg(['action' => 'huy_thu_bhyt', 'm_id' => $m->id]); ?>" style="color:red; font-size:11px; text-decoration:none;" onclick="return confirm('Hủy thu tiền?')">Hủy thu</a>
                        <?php else: ?>
                            <button type="button" class="button button-small button-primary btn-submit-row" data-id="<?php echo $m->id; ?>" disabled>Ghi nhận</button>
                        <?php endif; ?>
                    </td>
                </tr>
                <?php endforeach; else: ?>
                <tr><td colspan="6">Không có thành viên nào.</td></tr>
                <?php endif; ?>
            </tbody>
        </table>

        <!-- TỔNG TIỀN TỜ KHAI -->
        <div style="margin-top: 20px; text-align: right;">
            <div style="background: #1d2327; color: #fff; padding: 15px 30px; border-radius: 4px; display:inline-block;">
                <div style="font-size: 11px; opacity: 0.7;">TỔNG TẠM THU TỜ KHAI</div>
                <div style="font-size: 26px; color: #f3b200; font-weight: bold;"><?php echo number_format($total_collected); ?> VNĐ</div>
            </div>
        </div>
    </div>
    <?php endif; ?>
</div>

<script>
jQuery(document).ready(function($) {
    // Giá trị 1 tháng 100% từ cấu hình lương cơ sở
    const pricePerMonth = <?php echo floatval($p1_month); ?>; 
    const rates = { "1": 1, "2": 0.7, "3": 0.6, "4": 0.5, "5": 0.4 };

    // Hàm tính tiền cho từng dòng
    function calculateRow(id) {
        let row = $('#row-' + id);
        let muc = row.find('.muc-dong-select').val();
        let thang = row.find('.so-thang-select').val();
        let btn = row.find('.btn-submit-row');
        let moneyDisplay = row.find('.money-display');
        let hiddenMuc = row.find('.hidden-muc');
        let hiddenThang = row.find('.hidden-thang');

        if (muc && thang) {
            // Ép kiểu về số để tính toán
            let months = parseInt(thang);
            let rate = parseFloat(rates[muc]);
            
            // Công thức: Giá 1 tháng * Số tháng * % Mức đóng
            let finalPrice = Math.round(pricePerMonth * months * rate);
            
            // Hiển thị lên ô tiền (có dấu chấm)
            moneyDisplay.val(new Intl.NumberFormat().format(finalPrice)).css({'background': '#fff', 'color': '#2e7d32'});
            
            // Cập nhật giá trị vào hidden để gửi Form
            hiddenMuc.val(muc);
            hiddenThang.val(thang);
            btn.prop('disabled', false);
        } else {
            moneyDisplay.val('').css('background', '#f9f9f9');
            btn.prop('disabled', true);
        }
    }

    // Tự động tính toán khi load trang cho các dòng đã chọn mức đóng
    $('.member-row').each(function() {
        let id = $(this).attr('id').replace('row-', '');
        calculateRow(id);
    });

    // Lắng nghe sự kiện thay đổi của cả Mức đóng và Số tháng
    $('.muc-dong-select, .so-thang-select').on('change', function() {
        let id = $(this).data('id');
        calculateRow(id);
    });

    // Xử lý nút ghi nhận
    $('.btn-submit-row').on('click', function() {
        let id = $(this).data('id');
        if(confirm('Xác nhận ghi nhận thu tiền?')) {
            $('#form-thu-' + id).submit();
        }
    });
});
</script>

<style>
    .code-highlight { background: #e7f4f9; padding: 2px 4px; border-radius: 3px; font-family: monospace; }
    .bh-badge { display: inline-block; padding: 2px 5px; border-radius: 3px; font-size: 10px; color: #fff; font-weight: bold; }
</style>