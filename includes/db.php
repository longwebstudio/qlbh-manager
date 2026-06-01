<?php
if (!defined('ABSPATH')) exit;

function qlbh_install_table() {
    global $wpdb;
    $charset_collate = $wpdb->get_charset_collate();
    $table_main = $wpdb->prefix . 'bhyts';

    // Cấu trúc bảng bhyts chuẩn CamelCase
    $sql1 = "CREATE TABLE $table_main (
        id bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
        maSoBhxh varchar(191) NOT NULL,
        hoTen varchar(191) NOT NULL DEFAULT 'Noname',
        ngaySinhDt date DEFAULT NULL,
        gioiTinh tinyint(1) DEFAULT NULL,
        soCmnd varchar(191) DEFAULT NULL,
        soDienThoai varchar(191) DEFAULT NULL,
        soDienThoai2 varchar(191) DEFAULT NULL,
        email varchar(191) DEFAULT NULL,
        facebook varchar(255) DEFAULT NULL,
        diaChiLh varchar(191) DEFAULT NULL,
        maHoGd varchar(191) DEFAULT NULL,
        maToKhaiRieng varchar(50) DEFAULT NULL,
        maKCB varchar(191) DEFAULT NULL,
        tenDvi varchar(191) DEFAULT NULL,
        ghiChu text DEFAULT NULL,
        starRating tinyint(1) DEFAULT 0,
        trangThaiTaiTuc varchar(50) DEFAULT 'Chưa liên hệ',
        soTheBhyt varchar(191) DEFAULT NULL,
        tuNgayDt date DEFAULT NULL,
        denNgayDt date DEFAULT NULL,
        ngay5Nam date DEFAULT NULL,
        bhytSoTien double DEFAULT 0,
        bhytNgayBienLai date DEFAULT NULL,
        bhytMaBienLai varchar(50) DEFAULT NULL,
        bhytMaTraCuu varchar(100) DEFAULT NULL,
        bhytSoThang int(2) DEFAULT 12,
        bhytPhuongThucDong varchar(20) DEFAULT NULL,
        bhytThuTruoc tinyint(1) DEFAULT 0,
        bhytNgayThuTruoc date DEFAULT NULL,
        bhytSoTienThuTruoc double DEFAULT 0,
        bhytNhanVienThu varchar(50) DEFAULT NULL,
        created_at timestamp NULL DEFAULT NULL,
        updated_at timestamp NULL DEFAULT NULL,
        PRIMARY KEY  (id),
        UNIQUE KEY bhyts_masobhxh_unique (maSoBhxh)
    ) $charset_collate;";

    // Bảng lịch sử đóng BHXH & MIC
    $table_history = $wpdb->prefix . 'qlbh_lich_su_bh';
    $sql2 = "CREATE TABLE $table_history (
        id bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
        maSoBhxh varchar(191) NOT NULL,
        loaiBh varchar(10) NOT NULL,
        maBienLai varchar(100) DEFAULT NULL,
        maTraCuu varchar(100) DEFAULT NULL,
        soTien double DEFAULT 0,
        soThang int(11) DEFAULT 0,
        tuNgay date DEFAULT NULL,
        denNgay date DEFAULT NULL,
        ngayBienLai date DEFAULT NULL,
        nhanVienThu varchar(50) DEFAULT NULL,
        isPending tinyint(1) DEFAULT 0,
        ghiChuLs text DEFAULT NULL,
        PRIMARY KEY  (id),
        KEY k_maSoBhxh (maSoBhxh)
    ) $charset_collate;";

    require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
    dbDelta($sql1);
    dbDelta($sql2);
}