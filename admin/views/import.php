<?php
/**
 * File: admin/views/import.php
 * Chức năng: Import JSON và lấy userId làm mã nhân viên chính thức
 */

if (!defined('ABSPATH')) exit;

global $wpdb;
$table_kh = $wpdb->prefix . 'bhyts';
$table_ls = $wpdb->prefix . 'qlbh_lich_su_bh';
$msg = '';

if (isset($_POST['do_import'])) {
    $raw = stripslashes($_POST['json_data']);
    $data = json_decode($raw, true);
    $type = $_POST['import_type']; 

    if (isset($data['items']) && is_array($data['items'])) {
        $count = 0;
        // Lấy mã của người đang đăng nhập để làm dự phòng (fallback)
        $current_staff_id = qlbh_get_staff_official_id();

        foreach ($data['items'] as $item) {
            $maSo = sanitize_text_field($item['maSoBHXH']);
            if (!$maSo) continue;

            // XỬ LÝ LẤY MÃ NHÂN VIÊN TỪ JSON (userId)
            // Nếu trong JSON có userId thì lấy, không thì lấy mã người đang đăng nhập
            $staff_id_to_save = isset($item['userId']) ? sanitize_text_field($item['userId']) : $current_staff_id;

            if ($type === 'renewal') {
                /**
                 * 1. IMPORT TÁI TỤC
                 */
                $wpdb->query($wpdb->prepare("
                    INSERT INTO $table_kh (maSoBhxh, hoTen, ngaySinhDt, denNgayDt, soDienThoai, diaChiLh, bhytSoThang, bhytNhanVienThu) 
                    VALUES (%s, %s, %s, %s, %s, %s, %d, %s)
                    ON DUPLICATE KEY UPDATE 
                        denNgayDt = VALUES(denNgayDt), 
                        soDienThoai = VALUES(soDienThoai),
                        bhytNhanVienThu = VALUES(bhytNhanVienThu)
                ", 
                $maSo, 
                $item['hoVaTen'], 
                qlbh_sanitize_date_db($item['ngayThangNamSinh']), 
                qlbh_sanitize_date_db($item['ngayDenHan']), 
                $item['soDienThoai'], 
                $item['diaChi'], 
                $item['soThang'], 
                $staff_id_to_save
                ));
                $count++;
            } 
            else {
                /**
                 * 2. GHI NHẬN ĐÓNG TIỀN (Chốt hồ sơ)
                 */
                $thuTuc = (isset($item['mathuTuc']) && $item['mathuTuc'] == 1) ? '603' : '602';
                $ngayLap = isset($item['ngayLap']) ? substr($item['ngayLap'], 0, 10) : date('Y-m-d');
                $tongTien = isset($item['tongTien']) ? floatval($item['tongTien']) : 0;
                $bienLaiId = isset($item['bienLaiId']) ? sanitize_text_field($item['bienLaiId']) : '';

                if ($thuTuc === '603') { // BHYT
                    $wpdb->update($table_kh, [
            'bhytThuTruoc'    => 0,
            'userName'        => $userId,      // Lưu vào cột chính thức
            'tongTien'        => $tongTien,    // Lưu vào cột tiền chính thức
            'bhytNgayBienLai' => $ngayLap,
            'bhytMaBienLai'   => $bienLaiId,
            'bhytSoTienThuTruoc' => 0
        ], ['maSoBhxh' => $maSo]);
                } else { // BHXH (602)
                    $wpdb->insert($table_ls, [
                        'maSoBhxh'    => $maSo, 
                        'loaiBh'      => 'bhxh', 
                        'soTien'      => $tongTien,
                        'ngayBienLai' => $ngayLap, 
                        'maBienLai'   => $bienLaiId, 
                        'isPending'   => 0, 
                        'nhanVienThu' => $staff_id_to_save // Lưu userId từ JSON
                    ]);
                }
                $count++;
            }
        }
        $msg = "<div class='updated'><p>✅ Hệ thống đã xử lý xong <b>$count</b> hồ sơ. Mã nhân viên đã được đồng bộ theo trường <b>userId</b> trong JSON.</p></div>";
    }
}
?>
<div class="wrap qlbh-wrap">
    <h1>Import Dữ liệu & Đồng bộ Nhân viên</h1>
    <?php echo $msg; ?>
    
    <div class="qlbh-card">
        <form method="post">
            <p><b>1. Loại dữ liệu:</b></p>
            <select name="import_type" style="width:100%; max-width:400px; height: 35px;">
                <option value="renewal">Danh sách Tái tục</option>
                <option value="payment">Ghi nhận đóng tiền</option>
            </select>

            <p><b>2. Dán mã JSON từ hệ thống BHXH:</b></p>
            <textarea name="json_data" rows="15" style="width:100%; font-family:monospace; background:#f9f9f9;" placeholder="Dán chuỗi JSON có chứa trường userId..."></textarea>
            
            <p class="submit">
                <input type="submit" name="do_import" class="button button-primary button-large" value="Bắt đầu Import & Chốt mã NV">
            </p>
        </form>
    </div>
</div>