<?php
/**
 * File: includes/helper.php
 * Chức năng: Định dạng dữ liệu, tính toán thời hạn bảo hiểm và quản lý Profile nhân viên.
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/* ==========================================================================
   1. QUẢN LÝ PROFILE NHÂN VIÊN & MÃ THU
   ========================================================================== */

// Hiển thị thêm trường trong trang Edit Profile (Thành viên > Hồ sơ)
add_action( 'show_user_profile', 'qlbh_extra_user_profile_fields' );
add_action( 'edit_user_profile', 'qlbh_extra_user_profile_fields' );

function qlbh_extra_user_profile_fields( $user ) { ?>
    <h3>Thông tin nhân viên Thu Bảo Hiểm</h3>
    <table class="form-table">
        <tr>
            <th><label for="qlbh_official_id">Mã nhân viên (Cơ quan cấp)</label></th>
            <td>
                <input type="text" name="qlbh_official_id" id="qlbh_official_id" value="<?php echo esc_attr( get_the_author_meta( 'qlbh_official_id', $user->ID ) ); ?>" class="regular-text" />
                <p class="description">Mã định danh chính thức từ cơ quan BHXH (Dùng khi <b>Xác nhận gia hạn</b>).</p>
            </td>
        </tr>
        <tr>
            <th><label for="qlbh_cccd">Số CCCD nhân viên</label></th>
            <td>
                <input type="text" name="qlbh_cccd" id="qlbh_cccd" value="<?php echo esc_attr( get_the_author_meta( 'qlbh_cccd', $user->ID ) ); ?>" class="regular-text" />
                <p class="description">Dùng để tạo mã thu tiền tạm thời: <b>NV + CCCD</b> (Dùng khi <b>Thu tiền trước</b>).</p>
            </td>
        </tr>
    </table>
<?php }

// Lưu dữ liệu khi cập nhật Profile
add_action( 'personal_options_update', 'qlbh_save_extra_user_profile_fields' );
add_action( 'edit_user_profile_update', 'qlbh_save_extra_user_profile_fields' );

function qlbh_save_extra_user_profile_fields( $user_id ) {
    if ( ! current_user_can( 'edit_user', $user_id ) ) return false;
    update_user_meta( $user_id, 'qlbh_official_id', sanitize_text_field( $_POST['qlbh_official_id'] ) );
    update_user_meta( $user_id, 'qlbh_cccd', sanitize_text_field( $_POST['qlbh_cccd'] ) );
}

/**
 * Lấy Mã nhân viên chính thức (Ưu tiên số 1)
 */
function qlbh_get_staff_official_id($user_id = 0) {
    if (!$user_id) $user_id = get_current_user_id();
    $id = get_user_meta($user_id, 'qlbh_official_id', true);
    return !empty($id) ? $id : 'NV-CHUA-CAP-MA';
}

/**
 * Lấy Mã thu tiền trước (NV + CCCD)
 */
function qlbh_get_staff_internal_id($user_id = 0) {
    if (!$user_id) $user_id = get_current_user_id();
    $cccd = get_user_meta($user_id, 'qlbh_cccd', true);
    return !empty($cccd) ? 'NV' . $cccd : 'NV-CHUA-CO-CCCD';
}


/* ==========================================================================
   2. TÍNH TOÁN THỜI HẠN BẢO HIỂM
   ========================================================================== */

/**
 * Logic tính ngày gia hạn: 
 * - Nếu thẻ cũ còn hạn: Tính từ ngày hết hạn cũ + số tháng.
 * - Nếu thẻ cũ đã hết hạn: Tính từ ngày hôm nay + số tháng.
 */
function qlbh_calculate_new_expiry( $current_expiry, $months_to_add ) {
    $today = date( 'Y-m-d' );
    $months_to_add = intval( $months_to_add );

    if ( empty( $current_expiry ) || $current_expiry === '0000-00-00' || $current_expiry < $today ) {
        $base_date = $today;
    } else {
        $base_date = $current_expiry;
    }

    // Trả về ngày mới (Lưu ý: strtotime cộng tháng rất chính xác)
    return date( 'Y-m-d', strtotime( "+$months_to_add months", strtotime( $base_date ) ) );
}

// Hàm cụ thể cho BHYT (Thường 12 tháng)
function qlbh_get_new_expiry_bhyt( $current_expiry, $months = 12 ) {
    return qlbh_calculate_new_expiry( $current_expiry, $months );
}

// Hàm cụ thể cho BHXH (Tùy chọn 3, 6, 12 tháng)
function qlbh_get_new_expiry_bhxh( $current_expiry, $months = 6 ) {
    return qlbh_calculate_new_expiry( $current_expiry, $months );
}

// Hàm cụ thể cho MIC (Bảo hiểm bổ trợ - 12 tháng)
function qlbh_get_new_expiry_mic( $current_expiry, $months = 12 ) {
    return qlbh_calculate_new_expiry( $current_expiry, $months );
}


/* ==========================================================================
   3. ĐỊNH DẠNG & CHUẨN HÓA DỮ LIỆU
   ========================================================================== */

// Định dạng ngày hiển thị: dd/mm/yyyy
function qlbh_format_date_view( $date ) {
    if ( ! $date || $date === '0000-00-00' || $date === '1970-01-01' ) return '---';
    return date( 'd/m/Y', strtotime( $date ) );
}

// Chuẩn hóa ngày để lưu vào Database: yyyy-mm-dd
function qlbh_sanitize_date_db( $date_str ) {
    if ( empty( $date_str ) ) return null;
    return date( 'Y-m-d', strtotime( $date_str ) );
}

// Chuẩn hóa số tiền (Xóa dấu chấm, dấu phẩy, chuyển về float)
function qlbh_sanitize_money( $money_str ) {
    if (is_numeric($money_str)) return (float) $money_str;
    return (float) str_replace( array( ',', '.' ), '', $money_str );
}

/**
 * Kiểm tra trạng thái thời hạn
 * Trả về class CSS hoặc trạng thái để hiển thị màu sắc
 */
function qlbh_check_expiry_status( $date ) {
    if ( ! $date || $date === '0000-00-00' ) return 'none';
    
    $today = strtotime( date( 'Y-m-d' ) );
    $expiry_date = strtotime( $date );
    $diff_days = ( $expiry_date - $today ) / ( 60 * 60 * 24 );

    if ( $diff_days < 0 ) {
        return 'expired'; // Đã hết hạn (Đỏ)
    } elseif ( $diff_days <= 30 ) {
        return 'warning'; // Sắp hết hạn < 30 ngày (Cam)
    }
    return 'valid'; // Còn hạn (Xanh/Bình thường)
}

/**
 * Chuyển đổi ngày từ Import JSON (dd/mm/yyyy sang yyyy-mm-dd)
 */
function qlbh_convert_import_date( $date_str ) {
    if ( empty( $date_str ) ) return null;
    $parts = explode( '/', $date_str );
    if ( count( $parts ) === 3 ) {
        return $parts[2] . '-' . $parts[1] . '-' . $parts[0];
    }
    return $date_str; // Nếu đã đúng định dạng yyyy-mm-dd
}

/**
 * Sinh mã tờ khai ngẫu nhiên theo ngày
 * Định dạng: TK-YYYYMMDD-RAND
 */
function qlbh_generate_random_tokhai() {
    $prefix = 'TK-' . date('Ymd') . '-';
    $random = strtoupper( wp_generate_password( 4, false ) );
    return $prefix . $random;
}

/**
 * Kiểm tra và chuẩn hóa link Facebook
 * Trả về URL hợp lệ hoặc false nếu không có dữ liệu
 */
function qlbh_get_facebook_url($input) {
    // 1. Loại bỏ khoảng trắng và làm sạch cơ bản
    $input = trim((string)$input);

    // 2. Kiểm tra nếu rỗng hoặc chứa các giá trị "rác" thường gặp
    if ( empty($input) || $input === '---' || $input === '0' || strtolower($input) === 'none' ) {
        return false;
    }

    // 3. Nếu là link đầy đủ (bắt đầu bằng http)
    if ( preg_match('/^https?:\/\//', $input) ) {
        return esc_url($input);
    }

    // 4. Nếu chỉ nhập tên người dùng hoặc ID (ví dụ: 'nguyenvana' hoặc '1000123...')
    // Loại bỏ ký tự @ nếu người dùng nhập theo kiểu @username
    $username = ltrim($input, '@');
    
    // Kiểm tra lại sau khi ltrim
    if (empty($username)) return false;

    return 'https://www.facebook.com/' . $username;
}

/**
 * Lấy mức lương cơ sở hiện tại
 */
function qlbh_get_base_salary() {
    return (float) get_option('qlbh_base_salary', 2340000);
}

/**
 * Tính mức đóng BHYT người thứ 1 (100%) cho 12 tháng
 */
function qlbh_get_p1_price() {
    $base_salary = qlbh_get_base_salary();
    // 4.5% lương cơ sở * 12 tháng
    return round($base_salary * 0.045 * 12);
}

/**
 * Tính mức đóng BHYT 100% cho 1 THÁNG
 */
function qlbh_get_p1_month_price() {
    $base_salary = qlbh_get_base_salary();
    // 4.5% lương cơ sở cho 1 tháng
    return ($base_salary * 0.045);
}

/**
 * Render cột thông tin tổng hợp khách hàng (Bản nâng cấp hiển thị Nhân viên thu)
 */
function qlbh_render_customer_column( $item ) {
    $today_ts = strtotime(date('Y-m-d'));
    $expiry_ts = strtotime($item->bhyt_den_ngay);
    $days_diff = ($item->bhyt_den_ngay && $item->bhyt_den_ngay !== '0000-00-00') ? round(($expiry_ts - $today_ts) / 86400) : null;
    
    // Xác định màu sắc dựa trên ngày hết hạn
    $status_color = ($days_diff < 0) ? '#ff8a80' : (($days_diff <= 30) ? '#f56e28' : '#2271b1');
    ?>
    <td class="qlbh-customer-info">
        <!-- 1. Họ tên, Giới tính, Ngày sinh -->
        <div style="margin-bottom: 5px;">
            <?php echo ($item->gioi_tinh == 1) ? '<span class="dashicons dashicons-businessman" title="Nam" style="color:#0073aa"></span>' : '<span class="dashicons dashicons-businesswoman" title="Nữ" style="color:#d63638"></span>'; ?>
            <strong style="font-size: 14px; color: #2271b1;"><?php echo esc_html($item->ho_ten); ?></strong> 
            <span style="color: #646970; margin-left: 5px; font-size: 11px;"><?php echo qlbh_format_date_view($item->ngay_sinh); ?></span>
        </div>

        <!-- 2. Mã thẻ BHYT / Mã BHXH & CCCD -->
        <div style="margin-bottom: 5px;">
            <span class="code-highlight" title="Mã thẻ BHYT / Mã BHXH">
                <span class="dashicons dashicons-id-alt" style="font-size: 14px; vertical-align: middle;"></span>
                <?php echo !empty($item->bhyt_ma_the) ? esc_html($item->bhyt_ma_the) : esc_html($item->ma_so_bhxh); ?>
            </span>
            <?php if(!empty($item->cccd)): ?>
                <span style="font-size: 11px; color: #8c8f94; margin-left: 5px;" title="Số CCCD">
                    <span class="dashicons dashicons-id" style="font-size: 13px;"></span> <?php echo esc_html($item->cccd); ?>
                </span>
            <?php endif; ?>
        </div>

        <!-- 3. Thời hạn thẻ & Số ngày -->
        <div class="row-info" style="font-size: 11px;">
            <span class="dashicons dashicons-calendar-alt" style="font-size: 13px;"></span>
            Hạn: <b><?php echo qlbh_format_date_view($item->bhyt_tu_ngay); ?></b> ➔ 
            <b style="color:<?php echo $status_color; ?>;"><?php echo qlbh_format_date_view($item->bhyt_den_ngay); ?></b>
            
            <?php if ($days_diff !== null): ?>
                <span style="margin-left:5px; font-weight:bold; color:<?php echo $status_color; ?>;">
                    <?php 
                        if ($days_diff < 0) echo "[Hết hạn " . abs($days_diff) . " ngày]";
                        elseif ($days_diff == 0) echo "[Hết hôm nay]";
                        else echo "[Còn " . $days_diff . " ngày]";
                    ?>
                </span>
            <?php endif; ?>
        </div>

        <!-- 4. NHÂN VIÊN THU (Tạm thu hoặc Chính thức) -->
        <div class="row-info" style="font-size: 11px; margin-top: 3px;">
            <span class="dashicons dashicons-admin-users" style="font-size: 13px; color: #0073aa;"></span>
            <?php if ($item->bhyt_thu_truoc == 1) : ?>
                <span class="bh-badge" style="background:#46b450; color:#fff; padding: 1px 4px; border-radius: 3px; font-size: 10px;" title="Nhân viên đang tạm thu tiền">
                    Tạm thu: <b><?php echo esc_html($item->bhyt_nhan_vien_thu); ?></b>
                </span>
            <?php else : ?>
                <span style="color: #646970;">
                    NV thu: <b><?php echo !empty($item->bhyt_nhan_vien_thu) ? esc_html($item->bhyt_nhan_vien_thu) : '---'; ?></b>
                </span>
            <?php endif; ?>
        </div>

        <!-- 5. Nơi khám chữa bệnh & Đơn vị -->
        <?php if(!empty($item->noi_dang_ky_kcb)): ?>
            <div class="row-info" style="font-size: 11px; margin-top: 2px;">
                <span class="dashicons dashicons-location" style="font-size: 13px; color: #d63638;"></span> 
                KCB: <i style="color: #50575e;"><?php echo esc_html($item->noi_dang_ky_kcb); ?></i>
            </div>
        <?php endif; ?>

        <?php if(!empty($item->don_vi_tham_gia)): ?>
            <div class="row-info" style="font-size: 11px; margin-top: 2px;">
                <span class="dashicons dashicons-networking" style="font-size: 13px; color: #46b450;"></span> 
                Đơn vị: <span style="color: #50575e;"><?php echo esc_html($item->don_vi_tham_gia); ?></span>
            </div>
        <?php endif; ?>

        <!-- 6. Địa chỉ liên hệ -->
        <?php if(!empty($item->dia_chi_lien_he)): ?>
            <div class="row-info" style="font-size: 11px; margin-top: 2px; line-height: 1.3;">
                <span class="dashicons dashicons-admin-home" style="font-size: 13px; color: #8c8f94;"></span> 
                Đ/c: <span style="color: #646970;"><?php echo esc_html($item->dia_chi_lien_he); ?></span>
            </div>
        <?php endif; ?>

        <!-- 7. LIÊN HỆ GỘP (SĐT/Zalo/FB) -->
        <div style="margin-top: 8px; padding-top: 5px; border-top: 1px solid #f0f0f0;">
            <?php if (!empty($item->so_dien_thoai)) : 
                $sdt_clean = preg_replace('/\D/','',$item->so_dien_thoai); ?>
                <div class="row-info">
                    <span class="dashicons dashicons-phone" style="font-size: 14px; color:#46b450;"></span> 
                    <a href="tel:<?php echo $item->so_dien_thoai; ?>" style="font-weight:600;"><?php echo esc_html($item->so_dien_thoai); ?></a>
                    <a href="https://zalo.me/<?php echo $sdt_clean; ?>" target="_blank" title="Chat Zalo" style="color:#0068ff; text-decoration:none; font-size:10px; border:1px solid #0068ff; padding:0 3px; border-radius:3px; margin-left:5px;">Zalo</a>
                </div>
            <?php endif; ?>

            <?php $fb = qlbh_get_facebook_url($item->facebook); if($fb): ?>
                <div class="row-info" style="margin-top:2px;">
                    <span class="dashicons dashicons-facebook" style="font-size: 14px; color:#1877F2;"></span> 
                    <a href="<?php echo $fb; ?>" target="_blank" style="font-size:11px; text-decoration:none;">Facebook</a>
                </div>
            <?php endif; ?>
        </div>
    </td>
    <?php
}

/**
 * Xác định class CSS cho dòng dựa trên trạng thái thẻ BHYT
 * Ưu tiên: Hết hạn (Xám) > Thẻ ưu tiên (Vàng) > Hạn dài (Xanh)
 */
function qlbh_get_row_status_class($item) {
    $den_ngay = $item->bhyt_den_ngay;
    $ma_the = strtoupper($item->bhyt_ma_the);
    
    if (!$den_ngay || $den_ngay === '0000-00-00') return '';

    $today = date('Y-m-d');
    
    // 1. Nếu thẻ đã hết hạn -> Màu xám
    if ($den_ngay < $today) {
        return 'qlbh-row-expired';
    }

    // 2. Nếu không phải mã GD (Thẻ ưu tiên: DN, CC, CN...) -> Màu vàng
    // Kiểm tra ma_the có ít nhất 2 ký tự và không bắt đầu bằng GD
    if (!empty($ma_the) && substr($ma_the, 0, 2) !== 'GD') {
        return 'qlbh-row-priority';
    }

    // 3. Nếu thẻ còn hạn > 2 tháng tính từ ngày đầu tháng hiện tại -> Màu xanh
    // Lấy ngày đầu tiên của tháng hiện tại + 2 tháng
    $first_day_current_month = date('Y-m-01');
    $threshold_date = date('Y-m-d', strtotime('+2 months', strtotime($first_day_current_month)));
    
    if ($den_ngay > $threshold_date) {
        return 'qlbh-row-long-term';
    }

    return ''; // Mặc định (Trắng) cho các trường hợp sắp hết hạn trong vòng 2 tháng
}