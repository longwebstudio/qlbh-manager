<?php
/**
 * File: admin/views/mic-operation.php
 * Chức năng: Phê duyệt Gia hạn BHYT (Bước 2) - Xác nhận hoàn tất hồ sơ
 */

if ( ! defined( 'ABSPATH' ) ) exit;

global $wpdb;
$table_name = $wpdb->prefix . 'qlbh_khach_hang';

// 1. LẤY THÔNG TIN NHÂN VIÊN ĐANG ĐĂNG NHẬP
$current_user_id = get_current_user_id();
$off_id = qlbh_get_staff_official_id($current_user_id); // Mã chính thức (VD: 00105)
$int_id = qlbh_get_staff_internal_id($current_user_id); // Mã tạm (NV+CCCD)

// 2. XỬ LÝ HÀNH ĐỘNG XÁC NHẬN GIA HẠN THÀNH CÔNG
if (isset($_GET['action']) && $_GET['action'] === 'confirm_done') {
    $id = intval($_GET['id']);
    
    // Lấy thông tin hiện tại của khách hàng để tính toán
    $customer = $wpdb->get_row($wpdb->prepare("SELECT bhyt_den_ngay, bhyt_so_tien_thu_truoc FROM $table_name WHERE id = %d", $id));
    
    if ($customer) {
        // Tính toán ngày hết hạn mới dựa trên helper (Cộng nối tiếp hoặc tính từ hôm nay)
        $new_expiry = qlbh_get_new_expiry_bhyt($customer->bhyt_den_ngay, 12); // Mặc định gia hạn 12 tháng
        $new_start  = date('Y-m-d', strtotime('+1 day', strtotime($customer->bhyt_den_ngay)));
        
        $wpdb->update(
            $table_name,
            array(
                'bhyt_thu_truoc'     => 0, // Reset trạng thái chờ
                'bhyt_tu_ngay'       => $new_start,
                'bhyt_den_ngay'      => $new_expiry,
                'bhyt_so_tien'       => $customer->bhyt_so_tien_thu_truoc, // Chuyển tiền tạm thành tiền chính thức
                'bhyt_ngay_bien_lai' => current_time('mysql'),
                'bhyt_nhan_vien_thu' => $off_id, // Ghi đè bằng mã nhân viên chính thức
                'bhyt_so_tien_thu_truoc' => 0 // Xóa tiền tạm thu
            ),
            array('id' => $id)
        );
        echo '<div class="updated"><p>Đã xác nhận gia hạn thành công cho khách hàng. Hồ sơ đã được cập nhật hạn dùng mới.</p></div>';
    }
}

// 3. XỬ LÝ HÀNH ĐỘNG HỦY THU (TRẢ LẠI TIỀN / NHẬP SAI)
if (isset($_GET['action']) && $_GET['action'] === 'cancel_thu') {
    $id = intval($_GET['id']);
    $wpdb->update(
        $table_name,
        array(
            'bhyt_thu_truoc'         => 0,
            'bhyt_so_tien_thu_truoc' => 0,
            'bhyt_nhan_vien_thu'     => '' // Xóa dấu vết thu tiền
        ),
        array('id' => $id)
    );
    echo '<div class="notice notice-warning is-dismissible"><p>Đã hủy phiếu thu. Khách hàng đã được đưa về trạng thái chưa thu tiền.</p></div>';
}

// 4. LẤY DANH SÁCH CHỜ PHÊ DUYỆT (Chỉ hiện những người do nhân viên này thu tiền trước)
$items = $wpdb->get_results($wpdb->prepare("
    SELECT * FROM $table_name 
    WHERE bhyt_thu_truoc = 1 
    AND bhyt_nhan_vien_thu = %s
    ORDER BY bhyt_ngay_thu_truoc ASC
", $int_id));
?>

<div class="wrap qlbh-wrap">
    <h1>Phê duyệt & Gia hạn BHYT</h1>
    <p class="description">Danh sách hồ sơ bạn đã thu tiền tạm thời (Mã: <b><?php echo $int_id; ?></b>). Hãy bấm "Xác nhận" sau khi đã hoàn tất thủ tục trên cổng BHXH.</p>

    <div class="qlbh-card">
        <table class="wp-list-table widefat fixed striped">
            <thead>
                <tr>
                    <th width="12%">Ngày thu tiền</th>
                    <th width="25%">Khách hàng / Mã thẻ</th>
                    <th width="15%">Mã Tờ khai</th>
                    <th width="15%">Số tiền tạm thu</th>
                    <th>Thao tác</th>
                </tr>
            </thead>
            <tbody>
                <?php if ($items) : foreach ($items as $item) : ?>
                    <tr>
                        <td><b><?php echo qlbh_format_date_view($item->bhyt_ngay_thu_truoc); ?></b></td>
                        <td>
                            <strong style="color: #2271b1;"><?php echo esc_html($item->ho_ten); ?></strong><br>
                            <span class="code-highlight"><?php echo !empty($item->bhyt_ma_the) ? esc_html($item->bhyt_ma_the) : esc_html($item->ma_so_bhxh); ?></span>
                        </td>
                        <td><code><?php echo esc_html($item->ma_to_khai_rieng); ?></code></td>
                        <td>
                            <strong style="color: #46b450; font-size: 14px;">
                                <?php echo number_format($item->bhyt_so_tien_thu_truoc); ?>đ
                            </strong>
                        </td>
                        <td>
                            <a href="<?php echo add_query_arg(['action' => 'confirm_done', 'id' => $item->id]); ?>" 
                               class="button button-primary" 
                               onclick="return confirm('Xác nhận đã gia hạn thành công? Hệ thống sẽ cộng thêm hạn dùng và cập nhật mã nhân viên chính thức.')">
                               <span class="dashicons dashicons-yes" style="margin-top:4px;"></span> Xác nhận Gia hạn
                            </a>

                            <a href="<?php echo add_query_arg(['action' => 'cancel_thu', 'id' => $item->id]); ?>" 
                               class="button" 
                               style="color: #d63638; border-color: #d63638;"
                               onclick="return confirm('Bạn muốn hủy phiếu thu này?')">
                               Hủy thu
                            </a>
                        </td>
                    </tr>
                <?php endforeach; else : ?>
                    <tr>
                        <td colspan="5">Bạn hiện không có hồ sơ BHYT nào đang chờ xử lý.</td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>

    <div style="margin-top: 20px;">
        <a href="?page=qlbh-manager" class="button"> &larr; Quay lại danh sách chính</a>
    </div>
</div>

<style>
    .qlbh-card { background: #fff; border: 1px solid #ccd0d4; padding: 10px; margin-top: 15px; }
    .code-highlight { background: #e7f4f9; padding: 2px 5px; border-radius: 3px; font-family: monospace; font-size: 12px; }
</style>