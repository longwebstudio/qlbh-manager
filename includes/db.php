<?php
if ( ! defined( 'ABSPATH' ) ) exit;

function qlbh_install_table() {
    global $wpdb;
    $charset_collate = $wpdb->get_charset_collate();
    $table_name = $wpdb->prefix . 'qlbh_khach_hang'; 

    $sql = "CREATE TABLE $table_name (
        id bigint(20) NOT NULL AUTO_INCREMENT,
        ma_so_bhxh varchar(20) NOT NULL,
        ho_ten varchar(255) NOT NULL,
        ngay_sinh date,
        gioi_tinh tinyint(1) DEFAULT 0,
        cccd varchar(20),
        so_dien_thoai varchar(20),
        so_dien_thoai_2 varchar(20),
        email varchar(100),
        facebook varchar(255),
        dia_chi_lien_he text,
        ghi_chu text,
        ma_ho_gia_dinh varchar(50),
        ma_to_khai_rieng varchar(50),
        noi_dang_ky_kcb varchar(255),
        don_vi_tham_gia varchar(255),
        bhyt_ma_bien_lai varchar(50),
        bhyt_ngay_bien_lai date,
        bhyt_so_tien decimal(15,2) DEFAULT 0,
        bhyt_phuong_thuc_dong varchar(20),
        bhyt_so_thang int(2) DEFAULT 12,
        bhyt_ma_the varchar(15),
        bhyt_tu_ngay date,            
        bhyt_den_ngay date,       
        bhyt_thu_truoc tinyint(1) DEFAULT 0,
        bhyt_ngay_thu_truoc date,
        bhyt_so_tien_thu_truoc decimal(15,2) DEFAULT 0,
        bhyt_nhan_vien_thu varchar(50),
        bhxh_ma_bien_lai varchar(50),
        bhxh_ngay_bien_lai date,
        bhxh_so_tien decimal(15,2) DEFAULT 0,
        bhxh_thu_nhap_lua_chon decimal(15,2) DEFAULT 0,
        bhxh_phuong_thuc_dong varchar(20),
        bhxh_so_thang int(2) DEFAULT 6,
        bhxh_den_ngay date,
        bhxh_thu_truoc tinyint(1) DEFAULT 0,
        bhxh_ngay_thu_truoc date,
        bhxh_so_tien_thu_truoc decimal(15,2) DEFAULT 0,
        bhxh_nhan_vien_thu varchar(50),
        mic_ma_bien_lai varchar(50),
        mic_ngay_bien_lai date,
        mic_so_tien decimal(15,2) DEFAULT 0,
        mic_phuong_thuc_dong varchar(20),
        mic_so_thang int(2) DEFAULT 12,
        mic_den_ngay date,
        mic_thu_truoc tinyint(1) DEFAULT 0,
        mic_ngay_thu_truoc date,
        mic_so_tien_thu_truoc decimal(15,2) DEFAULT 0,
        mic_nhan_vien_thu varchar(50),
        created_at datetime DEFAULT CURRENT_TIMESTAMP,
        updated_at datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        PRIMARY KEY  (id),
        UNIQUE KEY u_ma_so_bhxh (ma_so_bhxh)
    ) $charset_collate;";

    require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );
    dbDelta( $sql );
}