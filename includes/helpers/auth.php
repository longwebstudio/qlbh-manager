<?php
/**
 * File: includes/helpers/auth.php
 * Chức năng: Quản lý thông tin định danh nhân viên thu (Mã chính thức & Mã tạm thu)
 */

if (!defined('ABSPATH')) exit;

/**
 * 1. HIỂN THỊ CÁC TRƯỜNG TRONG TRANG HỒ SƠ (PROFILE)
 * Xuất hiện tại menu: Thành viên -> Hồ sơ của bạn
 */
add_action('show_user_profile', 'qlbh_staff_fields_html');
add_action('edit_user_profile', 'qlbh_staff_fields_html');

function qlbh_staff_fields_html($user) {
    // Lấy dữ liệu cũ
    $official_id = get_user_meta($user->ID, 'qlbh_official_id', true);
    $cccd = get_user_meta($user->ID, 'qlbh_cccd', true);
    ?>
    <hr>
    <div id="qlbh-staff-info">
        <h3>Thông tin Nghiệp vụ Thu Bảo Hiểm</h3>
        <table class="form-table">
            <tr>
                <th><label for="qlbh_official_id">Mã nhân viên chính thức</label></th>
                <td>
                    <input type="text" name="qlbh_official_id" id="qlbh_official_id" value="<?php echo esc_attr($official_id); ?>" class="regular-text" placeholder="Ví dụ: 00105" />
                    <p class="description">Mã do cơ quan BHXH cấp. Dùng để ghi nhận hồ sơ <b>Đã gia hạn xong</b>.</p>
                </td>
            </tr>
            <tr>
                <th><label for="qlbh_cccd">Số CCCD nhân viên</label></th>
                <td>
                    <input type="text" name="qlbh_cccd" id="qlbh_cccd" value="<?php echo esc_attr($cccd); ?>" class="regular-text" placeholder="Ví dụ: 001086029..." />
                    <p class="description">Dùng để tạo Mã tạm thu: <b>NV + CCCD</b>. Dùng khi <b>Thu tiền mặt (Tạm thu)</b>.</p>
                </td>
            </tr>
            <tr>
                <th>Mã tạm thu hiện tại</th>
                <td>
                    <code style="background: #e7f4f9; padding: 5px 10px; border-radius: 3px; font-weight: bold; color: #0073aa;">
                        <?php echo $cccd ? 'NV' . $cccd : 'Chưa thiết lập CCCD'; ?>
                    </code>
                </td>
            </tr>
        </table>
    </div>
    <?php
}

/**
 * 2. LƯU DỮ LIỆU KHI CẬP NHẬT HỒ SƠ
 */
add_action('personal_options_update', 'qlbh_save_staff_fields');
add_action('edit_user_profile_update', 'qlbh_save_staff_fields');

function qlbh_save_staff_fields($user_id) {
    if (!current_user_can('edit_user', $user_id)) return false;

    update_user_meta($user_id, 'qlbh_official_id', sanitize_text_field($_POST['qlbh_official_id']));
    update_user_meta($user_id, 'qlbh_cccd', sanitize_text_field($_POST['qlbh_cccd']));
}

/**
 * 3. HÀM TRUY XUẤT: LẤY MÃ NHÂN VIÊN CHÍNH THỨC
 */
function qlbh_get_staff_official_id($uid = 0) {
    $uid = $uid ? $uid : get_current_user_id();
    $id = get_user_meta($uid, 'qlbh_official_id', true);
    return !empty($id) ? $id : '3152';
}

/**
 * 4. HÀM TRUY XUẤT: LẤY MÃ TẠM THU (NV + CCCD)
 */
function qlbh_get_staff_internal_id($uid = 0) {
    $uid = $uid ? $uid : get_current_user_id();
    $cccd = get_user_meta($uid, 'qlbh_cccd', true);
    return !empty($cccd) ? 'NV' . $cccd : 'NV_CHUA_CCCD';
}

/**
 * 5. KIỂM TRA NHÂN VIÊN ĐÃ SẴN SÀNG LÀM VIỆC CHƯA
 */
function qlbh_is_staff_ready($uid = 0) {
    $uid = $uid ? $uid : get_current_user_id();
    $off_id = get_user_meta($uid, 'qlbh_official_id', true);
    $cccd = get_user_meta($uid, 'qlbh_cccd', true);
    return (!empty($off_id) && !empty($cccd));
}