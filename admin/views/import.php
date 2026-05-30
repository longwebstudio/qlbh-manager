<?php
if ( ! defined( 'ABSPATH' ) ) { exit; }

global $wpdb;
$table_name = $wpdb->prefix . 'qlbh_khach_hang';

$import_status = '';
$success_count = 0;
$exist_count   = 0;
$error_count   = 0;

if ( isset( $_POST['qlbh_submit_import'] ) ) {
    if ( wp_verify_nonce( $_POST['qlbh_import_nonce'], 'qlbh_import_action' ) ) {
        $raw_json = isset( $_POST['qlbh_json_data'] ) ? trim( stripslashes( $_POST['qlbh_json_data'] ) ) : '';
        
        if ( ! empty( $raw_json ) ) {
            $decoded_data = json_decode( $raw_json, true );

            if ( json_last_error() === JSON_ERROR_NONE ) {
                $items = array();
                if ( isset( $decoded_data['items'] ) && is_array( $decoded_data['items'] ) ) {
                    $items = $decoded_data['items'];
                } elseif ( is_array( $decoded_data ) ) {
                    $items = $decoded_data;
                }

                if ( ! empty( $items ) ) {
                    foreach ( $items as $item ) {
                        $is_portal_format = isset( $item['maSoBHXH'] ) && isset( $item['hoTen'] ) && isset( $item['ngaySinh'] );

                        if ( $is_portal_format ) {
                            $ma_so_bhxh   = sanitize_text_field( $item['maSoBHXH'] );
                            $ho_ten       = sanitize_text_field( $item['hoTen'] );
                            $ngay_sinh_raw= sanitize_text_field( $item['ngaySinh'] );
                            $ngay_sinh    = qlbh_convert_import_date( $ngay_sinh_raw );
                            $cccd         = isset( $item['cmnd'] ) ? sanitize_text_field( $item['cmnd'] ) : '';
                            $so_dien_thoai = ''; 
                            $ma_to_khai_rieng = isset( $item['soHoSo'] ) ? sanitize_text_field( $item['soHoSo'] ) : '';

                            $bhyt_data = array();
                            $bhxh_data = array();

                            $ma_thu_tuc = isset( $item['mathuTuc'] ) ? intval( $item['mathuTuc'] ) : 1;

                            if ( $ma_thu_tuc === 1 ) {
                                $bhyt_ngay_bien_lai = null;
                                if ( ! empty( $item['ngayLap'] ) ) {
                                    $bhyt_ngay_bien_lai = date( 'Y-m-d', strtotime( $item['ngayLap'] ) );
                                }
                                $bhyt_ma_bien_lai = isset( $item['bienLaiId'] ) ? sanitize_text_field( $item['bienLaiId'] ) : '';
                                $bhyt_so_tien     = isset( $item['tongTien'] ) ? floatval( $item['tongTien'] ) : 0.00;
                                $bhyt_nv_ma_bhxh  = isset( $item['nguoiNop'] ) ? sanitize_text_field( $item['nguoiNop'] ) : '';

                                $bhyt_tu_ngay      = $bhyt_ngay_bien_lai;
                                $bhyt_ngay_het_han = null;
                                if ( ! empty( $bhyt_tu_ngay ) ) {
                                    $date = new DateTime( $bhyt_tu_ngay );
                                    $date->modify( '+12 months' );
                                    $date->modify( '-1 day' );
                                    $bhyt_ngay_het_han = $date->format( 'Y-m-d' );
                                }

                                $bhyt_data = array(
                                    'bhyt_ma_bien_lai'      => $bhyt_ma_bien_lai,
                                    'bhyt_ngay_bien_lai'    => $bhyt_ngay_bien_lai,
                                    'bhyt_so_tien'          => $bhyt_so_tien,
                                    'bhyt_phuong_thuc_dong' => '12 tháng',
                                    'bhyt_tu_ngay'          => $bhyt_tu_ngay,
                                    'bhyt_den_ngay'         => $bhyt_ngay_het_han,
                                    'bhyt_nhan_vien_thu'    => $bhyt_nv_ma_bhxh,
                                );
                            } elseif ( $ma_thu_tuc === 0 ) {
                                $bhxh_ngay_bien_lai = null;
                                if ( ! empty( $item['ngayLap'] ) ) {
                                    $bhxh_ngay_bien_lai = date( 'Y-m-d', strtotime( $item['ngayLap'] ) );
                                }
                                $bhxh_ma_bien_lai = isset( $item['bienLaiId'] ) ? sanitize_text_field( $item['bienLaiId'] ) : '';
                                $bhxh_so_tien     = isset( $item['tongTien'] ) ? floatval( $item['tongTien'] ) : 0.00;
                                $bhxh_nv_ma_bhxh  = isset( $item['nguoiNop'] ) ? sanitize_text_field( $item['nguoiNop'] ) : '';
                                $bhxh_phuong_thuc = isset( $item['ky'] ) ? 'Kỳ: ' . sanitize_text_field( $item['ky'] ) : '';

                                $bhxh_ngay_het_han = null;
                                if ( ! empty( $item['ky'] ) && preg_match( '/^([0-9]{2})\/([0-9]{4})$/', $item['ky'], $matches ) ) {
                                    $month = intval( $matches[1] );
                                    $year  = intval( $matches[2] );
                                    $bhxh_ngay_het_han = date( 'Y-m-d', mktime( 0, 0, 0, $month + 1, 0, $year ) );
                                }

                                $bhxh_data = array(
                                    'bhxh_ma_bien_lai'      => $bhxh_ma_bien_lai,
                                    'bhxh_ngay_bien_lai'    => $bhxh_ngay_bien_lai,
                                    'bhxh_so_tien'          => $bhxh_so_tien,
                                    'bhxh_phuong_thuc_dong' => $bhxh_phuong_thuc,
                                    'bhxh_den_ngay'         => $bhxh_ngay_het_han,
                                    'bhxh_nhan_vien_thu'    => $bhxh_nv_ma_bhxh,
                                );
                            }

                            if ( empty( $ma_so_bhxh ) || empty( $ho_ten ) || empty( $ngay_sinh ) ) {
                                $error_count++;
                                continue;
                            }

                            $exists_id = $wpdb->get_var( $wpdb->prepare( "SELECT id FROM $table_name WHERE ma_so_bhxh = %s", $ma_so_bhxh ) );

                            if ( $exists_id ) {
                                $update_fields = array(
                                    'ho_ten'           => $ho_ten,
                                    'ngay_sinh'        => $ngay_sinh,
                                    'cccd'             => $cccd,
                                    'ma_to_khai_rieng' => $ma_to_khai_rieng
                                );

                                if ( $ma_thu_tuc === 1 ) {
                                    $update_fields = array_merge( $update_fields, $bhyt_data );
                                } elseif ( $ma_thu_tuc === 0 ) {
                                    $update_fields = array_merge( $update_fields, $bhxh_data );
                                }

                                $wpdb->update( $table_name, $update_fields, array( 'id' => $exists_id ) );
                                $exist_count++;
                            } else {
                                $insert_fields = array(
                                    'ma_so_bhxh'       => $ma_so_bhxh,
                                    'ho_ten'           => $ho_ten,
                                    'ngay_sinh'        => $ngay_sinh,
                                    'cccd'             => $cccd,
                                    'so_dien_thoai'    => $so_dien_thoai,
                                    'ma_to_khai_rieng' => $ma_to_khai_rieng
                                );

                                if ( $ma_thu_tuc === 1 ) {
                                    $insert_fields = array_merge( $insert_fields, $bhyt_data );
                                } elseif ( $ma_thu_tuc === 0 ) {
                                    $insert_fields = array_merge( $insert_fields, $bhxh_data );
                                }

                                $inserted = $wpdb->insert( $table_name, $insert_fields );
                                if ( $inserted ) { $success_count++; } else { $error_count++; }
                            }

                        } else {
                            $ma_so_bhxh    = isset( $item['maSoBHXH'] ) ? sanitize_text_field( $item['maSoBHXH'] ) : '';
                            $ho_ten        = isset( $item['hoVaTen'] ) ? sanitize_text_field( $item['hoVaTen'] ) : '';
                            $ngay_sinh_raw = isset( $item['ngayThangNamSinh'] ) ? sanitize_text_field( $item['ngayThangNamSinh'] ) : '';
                            $ngay_sinh     = qlbh_convert_import_date( $ngay_sinh_raw );
                            $cccd          = '';
                            $so_dien_thoai = isset( $item['soDienThoai'] ) && ! empty( $item['soDienThoai'] ) ? sanitize_text_field( $item['soDienThoai'] ) : '';
                            $dia_chi_lh    = isset( $item['diaChi'] ) ? sanitize_textarea_field( $item['diaChi'] ) : '';
                            
                            $ngay_het_han_raw  = isset( $item['ngayDenHan'] ) ? sanitize_text_field( $item['ngayDenHan'] ) : ( isset( $item['ngayDenHanStr'] ) ? sanitize_text_field( $item['ngayDenHanStr'] ) : '' );
                            $bhyt_ngay_het_han = qlbh_convert_import_date( $ngay_het_han_raw );
                            $bhyt_phuong_thuc  = isset( $item['soThang'] ) ? sanitize_text_field( $item['soThang'] ) . ' tháng' : '';
                            $bhyt_so_tien      = isset( $item['soPhaiDong'] ) ? floatval( $item['soPhaiDong'] ) : 0.00;

                            if ( empty( $ma_so_bhxh ) || empty( $ho_ten ) || empty( $ngay_sinh ) ) {
                                $error_count++;
                                continue;
                            }

                            $exists_id = $wpdb->get_var( $wpdb->prepare( "SELECT id FROM $table_name WHERE ma_so_bhxh = %s", $ma_so_bhxh ) );
                            if ( $exists_id ) {
                                $exist_count++;
                                continue;
                            }

                            $inserted = $wpdb->insert(
                                $table_name,
                                array(
                                    'ma_so_bhxh'            => $ma_so_bhxh,
                                    'ho_ten'                => $ho_ten,
                                    'ngay_sinh'             => $ngay_sinh,
                                    'cccd'                  => $cccd,
                                    'so_dien_thoai'         => $so_dien_thoai,
                                    'dia_chi_lien_he'       => $dia_chi_lh,
                                    'bhyt_den_ngay'         => $bhyt_ngay_het_han,
                                    'bhyt_phuong_thuc_dong' => $bhyt_phuong_thuc,
                                    'bhyt_so_tien'          => $bhyt_so_tien
                                )
                            );

                            if ( $inserted ) { $success_count++; } else { $error_count++; }
                        }
                    }
                    $import_status = 'success';
                }
            } else {
                $import_status = 'error';
                $error_message = 'Lỗi cú pháp JSON: ' . json_last_error_msg();
            }
        }
    }
}
?>

<div class="wrap qlbh-wrap">
    <h1>Nhập dữ liệu JSON đồng bộ</h1>
    <p class="description">Hệ thống đồng bộ tự động dựa theo mã thủ tục (mathuTuc: 1 lưu vào BHYT, 0 lưu vào BHXH).</p>
    
    <?php if ( $import_status === 'success' ) : ?>
        <div class="notice notice-success is-dismissible">
            <p><strong>Hoàn tất đồng bộ:</strong> Tạo mới: <strong><?php echo $success_count; ?></strong> | Cập nhật hồ sơ: <strong><?php echo $exist_count; ?></strong> | Lỗi: <strong><?php echo $error_count; ?></strong></p>
        </div>
    <?php endif; ?>

    <div class="qlbh-card">
        <form method="post" action="">
            <?php wp_nonce_field( 'qlbh_import_action', 'qlbh_import_nonce' ); ?>
            <p><label for="qlbh_json_data"><strong>Nhập chuỗi JSON đồng bộ:</strong></label></p>
            <textarea name="qlbh_json_data" id="qlbh_json_data" rows="12" class="large-text" required placeholder="Dán mã JSON đóng phí tại đây..."></textarea>
            <p><input type="submit" name="qlbh_submit_import" class="button button-primary button-large" value="Đồng bộ ngay"></p>
        </form>
    </div>
</div>