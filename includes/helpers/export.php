<?php
if (!defined('ABSPATH')) exit;

function qlbh_process_csv_export() {
    if (!current_user_can('manage_options')) return;

    global $wpdb;
    $month = intval($_GET['month']);
    $year = intval($_GET['year']);
    $staff = sanitize_text_field($_GET['staff_id']);

    $filename = "Bao-cao-BHYT-T$month-$year.csv";
    
    // Query lấy dữ liệu chính thức đã nộp tiền
    $results = $wpdb->get_results($wpdb->prepare("
        SELECT hoTen, maSoBhxh, ngaySinhDt, soTheBhyt, denNgayDt, bhytSoTien, bhytMaBienLai, bhytNgayBienLai 
        FROM {$wpdb->prefix}bhyts 
        WHERE YEAR(bhytNgayBienLai) = %d AND MONTH(bhytNgayBienLai) = %d AND bhytNhanVienThu = %s
    ", $year, $month, $staff));

    header('Content-Type: text/csv; charset=utf-8');
    header('Content-Disposition: attachment; filename=' . $filename);

    $output = fopen('php://output', 'w');
    
    // Thêm BOM để Excel đọc được tiếng Việt
    fprintf($output, chr(0xEF).chr(0xBB).chr(0xBF));

    // Tiêu đề cột
    fputcsv($output, ['STT', 'Họ tên', 'Mã số BHXH', 'Ngày sinh', 'Số thẻ BHYT', 'Hạn dùng', 'Số tiền', 'Số biên lai', 'Ngày nộp']);

    $stt = 1;
    foreach ($results as $row) {
        fputcsv($output, [
            $stt++,
            $row->hoTen,
            "'" . $row->maSoBhxh, // Thêm dấu nháy đơn để Excel không bị mất số 0 đầu
            $row->ngaySinhDt,
            $row->soTheBhyt,
            $row->denNgayDt,
            $row->bhytSoTien,
            $row->bhytMaBienLai,
            $row->bhytNgayBienLai
        ]);
    }
    fclose($output);
    exit;
}