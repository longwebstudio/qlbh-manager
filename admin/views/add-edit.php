<?php
if ( ! defined( 'ABSPATH' ) ) { exit; }

global $wpdb;
$table_name = $wpdb->prefix . 'qlbh_khach_hang';

$id     = isset( $_GET['id'] ) ? intval( $_GET['id'] ) : 0;
$action = isset( $_GET['action'] ) ? sanitize_text_field( $_GET['action'] ) : 'add';

$message = '';
$status  = 'success';

if ( isset( $_POST['qlbh_save_profile'] ) ) {
    if ( wp_verify_nonce( $_POST['qlbh_profile_nonce'], 'qlbh_save_profile_action' ) ) {
        $profile_data = array(
            'ma_so_bhxh'       => sanitize_text_field( $_POST['ma_so_bhxh'] ),
            'ho_ten'           => sanitize_text_field( $_POST['ho_ten'] ),
            'ngay_sinh'        => qlbh_sanitize_date_db( $_POST['ngay_sinh'] ),
            'gioi_tinh'        => sanitize_text_field( $_POST['gioi_tinh'] ),
            'cccd'             => sanitize_text_field( $_POST['cccd'] ),
            'so_dien_thoai'    => sanitize_text_field( $_POST['so_dien_thoai'] ),
            'so_dien_thoai_2'  => sanitize_text_field( $_POST['so_dien_thoai_2'] ),
            'email'            => sanitize_email( $_POST['email'] ),
            'dia_chi_lien_he'  => sanitize_textarea_field( $_POST['dia_chi_lien_he'] ),
            'ma_ho_gia_dinh'   => sanitize_text_field( $_POST['ma_ho_gia_dinh'] ),
            'ma_to_khai_rieng' => sanitize_text_field( $_POST['ma_to_khai_rieng'] ),
            'noi_dang_ky_kcb'  => sanitize_text_field( $_POST['noi_dang_ky_kcb'] ),
            'don_vi_tham_gia'  => sanitize_text_field( $_POST['don_vi_tham_gia'] ),
            'ghi_chu'          => sanitize_textarea_field( $_POST['ghi_chu'] )
        );

        $exists = $wpdb->get_var( $wpdb->prepare( "SELECT id FROM $table_name WHERE ma_so_bhxh = %s AND id != %d", $profile_data['ma_so_bhxh'], $id ) );
        if ( $exists ) {
            $message = 'Lỗi: Mã số BHXH này đã tồn tại trên hệ thống.';
            $status  = 'error';
        } else {
            if ( $id > 0 ) {
                $wpdb->update( $table_name, $profile_data, array( 'id' => $id ) );
                $message = 'Đã cập nhật hồ sơ khách hàng thành công.';
            } else {
                $wpdb->insert( $table_name, $profile_data );
                $message = 'Đã thêm khách hàng mới thành công.';
                echo "<script>window.location.href='?page=qlbh-manager';</script>";
                exit;
            }
        }
    }
}

$customer = array(
    'ma_so_bhxh' => '', 'ho_ten' => '', 'ngay_sinh' => '', 'gioi_tinh' => 'Nam', 'cccd' => '', 'so_dien_thoai' => '', 'so_dien_thoai_2' => '',
    'email' => '', 'dia_chi_lien_he' => '', 'ma_ho_gia_dinh' => '', 'ma_to_khai_rieng' => '', 'noi_dang_ky_kcb' => '', 'don_vi_tham_gia' => '', 'ghi_chu' => ''
);
if ( $id > 0 ) {
    $row = $wpdb->get_row( $wpdb->prepare( "SELECT * FROM $table_name WHERE id = %d", $id ), ARRAY_A );
    if ( $row ) { $customer = $row; }
}
?>

<div class="wrap qlbh-wrap">
    <h1><?php echo $id > 0 ? 'Sửa thông tin hồ sơ khách hàng' : 'Đăng ký Khách hàng mới'; ?></h1>
    
    <?php if ( ! empty($message) ) : ?>
        <div class="notice notice-<?php echo $status; ?> is-dismissible"><p><?php echo esc_html($message); ?></p></div>
    <?php endif; ?>

    <div class="qlbh-card">
        <form method="post" action="">
            <?php wp_nonce_field( 'qlbh_save_profile_action', 'qlbh_profile_nonce' ); ?>
            <table class="form-table">
                <tr>
                    <th><label for="ma_so_bhxh">Mã số BHXH (*)</label></th>
                    <td><input type="text" name="ma_so_bhxh" id="ma_so_bhxh" class="regular-text" value="<?php echo esc_attr($customer['ma_so_bhxh']); ?>" required></td>
                </tr>
                <tr>
                    <th><label for="ho_ten">Họ và tên (*)</label></th>
                    <td><input type="text" name="ho_ten" id="ho_ten" class="regular-text" value="<?php echo esc_attr($customer['ho_ten']); ?>" required></td>
                </tr>
                <tr>
                    <th><label for="ngay_sinh">Ngày sinh (*)</label></th>
                    <td><input type="date" name="ngay_sinh" id="ngay_sinh" value="<?php echo esc_attr($customer['ngay_sinh']); ?>" required></td>
                </tr>
                <tr>
                    <th><label for="gioi_tinh">Giới tính</label></th>
                    <td>
                        <select name="gioi_tinh" id="gioi_tinh">
                            <option value="Nam" <?php selected($customer['gioi_tinh'], 'Nam'); ?>>Nam</option>
                            <option value="Nữ" <?php selected($customer['gioi_tinh'], 'Nữ'); ?>>Nữ</option>
                        </select>
                    </td>
                </tr>
                <tr>
                    <th><label for="cccd">CCCD / CMND</label></th>
                    <td><input type="text" name="cccd" id="cccd" class="regular-text" value="<?php echo esc_attr($customer['cccd']); ?>"></td>
                </tr>
                <tr>
                    <th><label for="so_dien_thoai">Số điện thoại (*)</label></th>
                    <td><input type="text" name="so_dien_thoai" id="so_dien_thoai" class="regular-text" value="<?php echo esc_attr($customer['so_dien_thoai']); ?>" required></td>
                </tr>
                <tr>
                    <th><label for="so_dien_thoai_2">SĐT dự phòng</label></th>
                    <td><input type="text" name="so_dien_thoai_2" id="so_dien_thoai_2" class="regular-text" value="<?php echo esc_attr($customer['so_dien_thoai_2']); ?>"></td>
                </tr>
                <tr>
                    <th><label for="ma_ho_gia_dinh">Mã hộ gia đình</label></th>
                    <td><input type="text" name="ma_ho_gia_dinh" id="ma_ho_gia_dinh" class="regular-text" value="<?php echo esc_attr($customer['ma_ho_gia_dinh']); ?>"></td>
                </tr>
                <tr>
                    <th><label for="ma_to_khai_rieng">Mã tờ khai riêng</label></th>
                    <td><input type="text" name="ma_to_khai_rieng" id="ma_to_khai_rieng" class="regular-text" value="<?php echo esc_attr($customer['ma_to_khai_rieng']); ?>"></td>
                </tr>
                <tr>
                    <th><label for="noi_dang_ky_kcb">Nơi KCB ban đầu</label></th>
                    <td><input type="text" name="noi_dang_ky_kcb" id="noi_dang_ky_kcb" class="regular-text" value="<?php echo esc_attr($customer['noi_dang_ky_kcb']); ?>"></td>
                </tr>
                <tr>
                    <th><label for="don_vi_tham_gia">Đơn vị tham gia</label></th>
                    <td><input type="text" name="don_vi_tham_gia" id="don_vi_tham_gia" class="regular-text" value="<?php echo esc_attr($customer['don_vi_tham_gia']); ?>"></td>
                </tr>
                <tr>
                    <th><label for="dia_chi_lien_he">Địa chỉ liên hệ</label></th>
                    <td><textarea name="dia_chi_lien_he" id="dia_chi_lien_he" rows="3" class="large-text"><?php echo esc_textarea($customer['dia_chi_lien_he']); ?></textarea></td>
                </tr>
                <tr>
                    <th><label for="ghi_chu">Ghi chú</label></th>
                    <td><textarea name="ghi_chu" id="ghi_chu" rows="2" class="large-text"><?php echo esc_textarea($customer['ghi_chu']); ?></textarea></td>
                </tr>
            </table>
            <p style="margin-top: 15px;">
                <input type="submit" name="qlbh_save_profile" class="button button-primary button-large" value="Lưu hồ sơ khách hàng">
                <a href="?page=qlbh-manager" class="button button-large">Hủy bỏ</a>
            </p>
        </form>
    </div>
</div>