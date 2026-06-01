<?php
if (!defined('ABSPATH')) exit;

global $wpdb;
$table = $wpdb->prefix . 'bhyts';

// 1. LẤY THÔNG TIN NHÂN VIÊN
$off_id = qlbh_get_staff_official_id();
$int_id = qlbh_get_staff_internal_id();
$current_year = date('Y');

// 2. TÌM KIẾM & PHÂN TRANG
$s = isset($_GET['s']) ? sanitize_text_field($_GET['s']) : '';
$paged = isset($_GET['paged']) ? max(1, intval($_GET['paged'])) : 1;
$per_page = 20;
$offset = ($paged - 1) * $per_page;

$where = "WHERE 1=1";
if ($s) {
    $where .= $wpdb->prepare(" AND (hoTen LIKE %s OR maSoBhxh LIKE %s OR soTheBhyt LIKE %s OR maHoGd LIKE %s)", "%$s%", "%$s%", "%$s%", "%$s%");
}

$items = $wpdb->get_results("SELECT * FROM $table $where ORDER BY starRating DESC, updated_at DESC LIMIT $offset, $per_page");
$total = $wpdb->get_var("SELECT COUNT(id) FROM $table $where");

// 3. THỐNG KÊ DASHBOARD (NĂM TÀI CHÍNH)
// Thống kê doanh thu
$stats = $wpdb->get_row($wpdb->prepare("
    SELECT 
        -- Doanh thu chính thức: Lọc theo userName và cột tongTien
        SUM(CASE WHEN userName = %s AND YEAR(ngayLap) = %d THEN tongTien ELSE 0 END) as official,
        -- Tiền tạm thu: Lọc theo bhytNhanVienThu và cột bhytSoTienThuTruoc
        SUM(CASE WHEN bhytNhanVienThu = %s AND bhytThuTruoc = 1 THEN bhytSoTienThuTruoc ELSE 0 END) as pending
    FROM {$wpdb->prefix}bhyts", 
    $off_id, $current_year, $int_id
));
?>

<div class="wrap qlbh-wrap">
    <h1 class="wp-heading-inline">Quản lý Thu BHYT</h1>
    <a href="?page=qlbh-add" class="page-title-action">Thêm hồ sơ</a>
    <hr class="wp-header-end">

    <!-- Dashboard -->
    <div style="display: grid; grid-template-columns: repeat(3, 1fr); gap: 15px; margin: 20px 0;">
        <div class="qlbh-card stat-box" style="border-color: #46b450;">
            <small>ĐÃ QUYẾT TOÁN (NĂM)</small>
            <h2 style="color:#46b450;"><?php echo number_format($stats->official); ?>đ</h2>
        </div>
        <div class="qlbh-card stat-box" style="border-color: #d54e21;">
            <small>TIỀN TẠM THU (TRONG TÚI)</small>
            <h2 style="color:#d54e21;"><?php echo number_format($stats->pending); ?>đ</h2>
        </div>
        <div class="qlbh-card stat-box">
            <small>TỔNG KHÁCH HÀNG</small>
            <h2><?php echo $total; ?></h2>
        </div>
    </div>

    <!-- Tìm kiếm -->
    <form method="get" style="margin-bottom:15px; display:flex; gap:10px;">
        <input type="hidden" name="page" value="qlbh-manager">
        <input type="search" name="s" value="<?php echo esc_attr($s); ?>" placeholder="Tên, Mã số, Mã hộ..." style="width:350px;">
        <input type="submit" class="button" value="Tìm kiếm">
    </form>

    <table class="wp-list-table widefat fixed striped customer-table">
        <thead>
            <tr>
                <th width="65%">Thông tin Khách hàng & Hạn dùng</th>
                <th width="35%">Thông tin Thu tiền & Nghiệp vụ</th>
            </tr>
        </thead>
        <tbody>
            <?php if ($items): foreach ($items as $item): 
                $row_class = qlbh_get_row_status_class($item);
            ?>
            <tr class="<?php echo $row_class; ?>">
                <!-- CỘT 1: SỬ DỤNG HÀM TỔNG HỢP TRONG UI.PHP -->
                <?php qlbh_render_customer_column($item); ?>

                <!-- CỘT 2: NGHIỆP VỤ THU TIỀN -->
                <td style="vertical-align: top; padding: 15px !important;">
                    <div style="background: rgba(255,255,255,0.5); border: 1px solid #ddd; padding: 12px; border-radius: 5px;">
                        <?php if ($item->bhytThuTruoc): ?>
                            <div style="color: #d54e21; font-weight: bold;">⌛ ĐANG TẠM THU</div>
                            <div style="font-size: 18px; font-weight: bold; color: #2e7d32; margin: 5px 0;">
                                <?php echo number_format($item->bhytSoTienThuTruoc); ?>đ
                            </div>
                            <small>Thu ngày: <?php echo qlbh_format_date_view($item->bhytNgayThuTruoc); ?></small>
                        <?php elseif ($item->bhytSoTien > 0): ?>
                            <div style="color: #46b450; font-weight: bold;">✔ ĐÃ GIA HẠN</div>
                            <div style="font-size: 16px; font-weight: bold; color: #0073aa; margin: 5px 0;">
                                <?php echo number_format($item->bhytSoTien); ?>đ
                            </div>
                            <small>Biên lai: <?php echo $item->bhytMaBienLai; ?></small>
                        <?php else: ?>
                            <div style="color: #999; font-style: italic;">Chưa phát sinh thu tiền năm nay</div>
                        <?php endif; ?>

                        <div style="margin-top: 15px;">
                            <a href="?page=qlbh-tokhai&action=init_tk&customer_id=<?php echo $item->id; ?>" 
                               class="button button-primary button-large" style="width:100%; text-align:center;">
                               <b>LẬP TỜ KHAI THU TIỀN</b>
                            </a>
                        </div>
                    </div>
                </td>
            </tr>
            <?php endforeach; else: ?>
                <tr><td colspan="2">Trống.</td></tr>
            <?php endif; ?>
        </tbody>
    </table>
    
    <!-- Phân trang -->
    <div class="tablenav bottom">
        <div class="tablenav-pages">
            <?php echo paginate_links(['base' => add_query_arg('paged', '%#%'), 'format' => '', 'total' => ceil($total / $per_page), 'current' => $paged]); ?>
        </div>
    </div>
</div>