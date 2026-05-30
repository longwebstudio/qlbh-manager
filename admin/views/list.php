<?php
/**
 * File: admin/views/list.php
 * Chức năng: Danh sách khách hàng tập trung, Dashboard doanh thu năm và Nghiệp vụ thu tiền Bước 1.
 */

if ( ! defined( 'ABSPATH' ) ) exit;

global $wpdb;
$table_name = $wpdb->prefix . 'qlbh_khach_hang';

// 1. LẤY THÔNG TIN NHÂN VIÊN ĐANG ĐĂNG NHẬP
$current_user_id = get_current_user_id();
$off_id = qlbh_get_staff_official_id($current_user_id); // Mã chính thức (VD: 00105)
$int_id = qlbh_get_staff_internal_id($current_user_id); // Mã tạm (NV+CCCD)
$current_year = date('Y');

// 2. XỬ LÝ HÀNH ĐỘNG THU NHANH (BHXH HOẶC MIC)
if (isset($_POST['action']) && in_array($_POST['action'], ['thu_bhxh_truoc', 'thu_mic_truoc'])) {
    $c_id = intval($_POST['customer_id']);
    $amount = qlbh_sanitize_money($_POST['so_tien']);
    $type = ($_POST['action'] === 'thu_bhxh_truoc') ? 'bhxh' : 'mic';
    
    $wpdb->update(
        $table_name,
        array(
            $type . '_thu_truoc'         => 1,
            $type . '_ngay_thu_truoc'    => current_time('mysql'),
            $type . '_so_tien_thu_truoc' => $amount,
            $type . '_nhan_vien_thu'     => $int_id
        ),
        array('id' => $c_id)
    );
    echo '<div class="updated"><p>Đã ghi nhận thu tiền ' . strtoupper($type) . ' tạm thời cho khách hàng.</p></div>';
}

// 3. THỐNG KÊ DOANH THU TRONG NĂM (Đã hoàn thành + Đang tạm thu)
$stats = $wpdb->get_row($wpdb->prepare("
    SELECT 
        SUM(CASE WHEN (bhyt_nhan_vien_thu = %s AND YEAR(bhyt_ngay_bien_lai) = %d) THEN bhyt_so_tien 
                 WHEN (bhyt_nhan_vien_thu = %s AND bhyt_thu_truoc = 1) THEN bhyt_so_tien_thu_truoc ELSE 0 END) as total_bhyt,
        SUM(CASE WHEN (bhxh_nhan_vien_thu = %s AND YEAR(bhxh_ngay_bien_lai) = %d) THEN bhxh_so_tien 
                 WHEN (bhxh_nhan_vien_thu = %s AND bhxh_thu_truoc = 1) THEN bhxh_so_tien_thu_truoc ELSE 0 END) as total_bhxh,
        SUM(CASE WHEN (mic_nhan_vien_thu = %s AND YEAR(mic_ngay_bien_lai) = %d) THEN mic_so_tien 
                 WHEN (mic_nhan_vien_thu = %s AND mic_thu_truoc = 1) THEN mic_so_tien_thu_truoc ELSE 0 END) as total_mic,
        COUNT(CASE WHEN (bhyt_thu_truoc = 1 AND bhyt_nhan_vien_thu = %s) OR (bhxh_thu_truoc = 1 AND bhxh_nhan_vien_thu = %s) OR (mic_thu_truoc = 1 AND mic_nhan_vien_thu = %s) THEN 1 END) as pending_hoso
    FROM $table_name
", $off_id, $current_year, $int_id, $off_id, $current_year, $int_id, $off_id, $current_year, $int_id, $int_id, $int_id, $int_id));

// 4. XỬ LÝ TÌM KIẾM & PHÂN TRANG
$search = isset($_GET['s']) ? sanitize_text_field($_GET['s']) : '';
$paged = isset($_GET['paged']) ? max(1, intval($_GET['paged'])) : 1;
$per_page = 20;
$offset = ($paged - 1) * $per_page;

$where = "WHERE 1=1";
if ($search) {
    $where .= $wpdb->prepare(" AND (ho_ten LIKE %s OR ma_so_bhxh LIKE %s OR bhyt_ma_the LIKE %s OR so_dien_thoai LIKE %s)", "%$search%", "%$search%", "%$search%", "%$search%");
}
$items = $wpdb->get_results("SELECT * FROM $table_name $where ORDER BY updated_at DESC LIMIT $offset, $per_page");
$total_items = $wpdb->get_var("SELECT COUNT(id) FROM $table_name $where");
$total_pages = ceil($total_items / $per_page);
?>

<div class="wrap qlbh-wrap">
    <h1 class="wp-heading-inline">Hệ thống Quản lý Thu Bảo hiểm</h1>
    <a href="?page=qlbh-add-customer" class="page-title-action">Thêm khách hàng</a>
    <hr class="wp-header-end">

    <!-- DASHBOARD THỐNG KÊ DOANH THU NĂM -->
    <div style="display: grid; grid-template-columns: repeat(4, 1fr); gap: 15px; margin: 20px 0;">
        <div class="qlbh-card" style="border-top: 4px solid #0073aa;">
            <small>BHYT ĐÃ THU (<?php echo $current_year; ?>)</small>
            <h2 style="color:#0073aa; margin:5px 0;"><?php echo number_format($stats->total_bhyt); ?>đ</h2>
        </div>
        <div class="qlbh-card" style="border-top: 4px solid #11a0d2;">
            <small>BHXH ĐÃ THU (<?php echo $current_year; ?>)</small>
            <h2 style="color:#11a0d2; margin:5px 0;"><?php echo number_format($stats->total_bhxh); ?>đ</h2>
        </div>
        <div class="qlbh-card" style="border-top: 4px solid #f3b200;">
            <small>MIC ĐÃ THU (<?php echo $current_year; ?>)</small>
            <h2 style="color:#f3b200; margin:5px 0;"><?php echo number_format($stats->total_mic); ?>đ</h2>
        </div>
        <div class="qlbh-card" style="border-top: 4px solid #d63638;">
            <small>HỒ SƠ CHỜ XỬ LÝ</small>
            <h2 style="color:#d63638; margin:5px 0;"><?php echo $stats->pending_hoso; ?></h2>
        </div>
    </div>

    <!-- TÌM KIẾM -->
    <form method="get" style="margin-bottom: 20px; display:flex; gap:10px;">
        <input type="hidden" name="page" value="qlbh-manager">
        <input type="search" name="s" value="<?php echo esc_attr($search); ?>" placeholder="Tên, mã thẻ, SĐT..." style="width:300px;">
        <input type="submit" class="button" value="Tìm kiếm">
    </form>

    <table class="wp-list-table widefat fixed striped customer-table">
        <thead>
            <tr>
                <th width="35%">Thông tin hồ sơ & Liên hệ</th>
                <th width="16%">BHYT (Tiền)</th>
                <th width="16%">BHXH (Tiền)</th>
                <th width="16%">MIC (Tiền)</th>
                <th>Nghiệp vụ</th>
            </tr>
        </thead>
        <tbody>
            <?php if ($items): foreach ($items as $item): 
// Lấy class màu sắc dựa trên logic
        $row_class = qlbh_get_row_status_class($item);
                ?>
            <tr class="<?php echo $row_class; ?>">
                <!-- CỘT 1: TỔNG HỢP (Cá nhân + Thẻ + Liên hệ) -->
                <?php qlbh_render_customer_column($item); ?>

                <!-- CỘT BHYT -->
                <td>
                    <span class="bh-badge bh-badge-y">BHYT</span>
                    <?php if($item->bhyt_nhan_vien_thu): ?>
                        <span class="bh-badge" style="background:<?php echo $item->bhyt_thu_truoc ? '#46b450' : '#8c8f94'; ?>;"><?php echo esc_html($item->bhyt_nhan_vien_thu); ?></span>
                    <?php endif; ?>
                    <div style="margin-top:5px; font-weight:bold; color:#2e7d32;">
                        <?php echo number_format($item->bhyt_thu_truoc ? $item->bhyt_so_tien_thu_truoc : $item->bhyt_so_tien); ?>đ
                    </div>
                </td>

                <!-- CỘT BHXH -->
                <td>
                    <span class="bh-badge bh-badge-x">BHXH</span>
                    <?php if($item->bhxh_nhan_vien_thu): ?>
                        <span class="bh-badge" style="background:<?php echo $item->bhxh_thu_truoc ? '#46b450' : '#8c8f94'; ?>;"><?php echo esc_html($item->bhxh_nhan_vien_thu); ?></span>
                    <?php endif; ?>
                    <div style="margin-top:5px; font-weight:bold; color:#0073aa;">
                        <?php echo number_format($item->bhxh_thu_truoc ? $item->bhxh_so_tien_thu_truoc : $item->bhxh_so_tien); ?>đ
                    </div>
                </td>

                <!-- CỘT MIC -->
                <td>
                    <span class="bh-badge bh-badge-m">MIC</span>
                    <?php if($item->mic_nhan_vien_thu): ?>
                        <span class="bh-badge" style="background:<?php echo $item->mic_thu_truoc ? '#46b450' : '#8c8f94'; ?>;"><?php echo esc_html($item->mic_nhan_vien_thu); ?></span>
                    <?php endif; ?>
                    <div style="margin-top:5px; font-weight:bold; color:#d63638;">
                        <?php echo number_format($item->mic_thu_truoc ? $item->mic_so_tien_thu_truoc : $item->mic_so_tien); ?>đ
                    </div>
                </td>

                <!-- NGHIỆP VỤ -->
                <td>
                    <div class="quick-act-links">
                        <a href="?page=qlbh-tokhai&action=init_tk&customer_id=<?php echo $item->id; ?>" class="button button-small button-primary" style="width:100%; margin-bottom:4px; text-align:center;">Thu BHYT</a>
                        <div style="display:flex; gap:4px; margin-bottom:4px;">
                            <button type="button" class="button button-small" style="flex:1" onclick="quickCollect('bhxh', <?php echo $item->id; ?>, '<?php echo $item->ho_ten; ?>')">Thu BHXH</button>
                            <button type="button" class="button button-small" style="flex:1" onclick="quickCollect('mic', <?php echo $item->id; ?>, '<?php echo $item->ho_ten; ?>')">Thu MIC</button>
                        </div>
                        <a href="?page=qlbh-add-customer&id=<?php echo $item->id; ?>" class="button button-small" style="width:100%; text-align:center;">Sửa hồ sơ</a>
                    </div>
                </td>
            </tr>
            <?php endforeach; else: ?>
                <tr><td colspan="5">Không có dữ liệu.</td></tr>
            <?php endif; ?>
        </tbody>
    </table>

    <div class="tablenav bottom">
        <div class="tablenav-pages">
            <span class="pagination-links"><?php echo paginate_links(array('base' => add_query_arg('paged', '%#%'), 'format' => '', 'prev_text' => '&laquo;', 'next_text' => '&raquo;', 'total' => $total_pages, 'current' => $paged)); ?></span>
        </div>
    </div>
</div>

<script>
function quickCollect(type, id, name) {
    let label = (type === 'bhxh') ? 'BH Xã hội' : 'Bảo hiểm MIC';
    let money = prompt("Nhập số tiền thu " + label + " cho: " + name, "0");
    if (money !== null && money !== "") {
        let f = document.createElement('form');
        f.method = 'POST';
        f.innerHTML = `<input type="hidden" name="action" value="thu_${type}_truoc"><input type="hidden" name="customer_id" value="${id}"><input type="hidden" name="so_tien" value="${money}">`;
        document.body.appendChild(f);
        f.submit();
    }
}
</script>