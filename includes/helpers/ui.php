<?php
/**
 * File: includes/helpers/ui.php
 * Chức năng: Render giao diện thông tin khách hàng (Chuẩn CamelCase)
 */

if (!defined('ABSPATH')) exit;

/**
 * Xác định class CSS cho dòng (Row)
 */
function qlbh_get_row_status_class($item) {
    $denNgay = $item->denNgayDt;
    $maThe = strtoupper((string)$item->soTheBhyt);
    $today = date('Y-m-d');
    
    if (!$denNgay || $denNgay === '0000-00-00') return '';
    if ($denNgay < $today) return 'qlbh-row-expired';
    if (!empty($maThe) && substr($maThe, 0, 2) !== 'GD') return 'qlbh-row-priority';

    $threshold = date('Y-m-d', strtotime('+2 months', strtotime(date('Y-m-01'))));
    if ($denNgay > $threshold) return 'qlbh-row-long-term';
    
    return 'qlbh-row-default';
}

/**
 * Render Ngôi sao / Like / Dislike
 */
function qlbh_render_star_rating($id, $status) {
    $l_clr = ($status == 1) ? '#46b450' : '#ccc';
    $d_clr = ($status == 2) ? '#d63638' : '#ccc';
    return '<span style="display:inline-flex; gap:5px; margin-left:8px; vertical-align:middle;">
        <a href="'.add_query_arg(['action_star'=>'like','target_id'=>$id]).'" class="dashicons dashicons-thumbs-up" style="color:'.$l_clr.'; font-size:15px; text-decoration:none;"></a>
        <a href="'.add_query_arg(['action_star'=>'dislike','target_id'=>$id]).'" class="dashicons dashicons-thumbs-down" style="color:'.$d_clr.'; font-size:15px; text-decoration:none;"></a>
    </span>';
}

/**
 * Render Dropdown Trạng thái Tái tục
 */
function qlbh_render_renewal_status_select($item) {
    $statuses = [
        'Chưa liên hệ' => '#8c8f94',
        'Đã liên hệ'   => '#2271b1',
        'Đã tái tục'   => '#46b450',
        'Từ chối'      => '#d63638'
    ];
    $current = $item->trangThaiTaiTuc ? $item->trangThaiTaiTuc : 'Chưa liên hệ';
    $color = isset($statuses[$current]) ? $statuses[$current] : '#8c8f94';

    $html = '<select class="qlbh-quick-status" data-id="'.$item->id.'" style="font-size:10px; height:22px; font-weight:bold; color:'.$color.'; border-color:'.$color.'; padding:0 2px;">';
    foreach ($statuses as $lbl => $clr) {
        $html .= '<option value="'.$lbl.'" '.selected($current, $lbl, false).'>'.$lbl.'</option>';
    }
    $html .= '</select>';
    return $html;
}

/**
 * CỘT TỔNG HỢP THÔNG TIN (SỬ DỤNG CHO LIST & TỜ KHAI)
 */
function qlbh_render_customer_column($item) {
    $today_ts = strtotime(date('Y-m-d'));
    $expiry_ts = strtotime($item->denNgayDt);
    $days_diff = ($item->denNgayDt) ? round(($expiry_ts - $today_ts) / 86400) : null;
    $status_color = ($days_diff < 0) ? '#ff8a80' : (($days_diff <= 60) ? '#f56e28' : '#2271b1');
    ?>
    <td class="qlbh-customer-info" style="vertical-align: top; min-width: 320px;">
        <!-- Định danh -->
        <div style="margin-bottom: 8px;">
            <?php echo ($item->gioiTinh == 1) ? '<span class="dashicons dashicons-businessman" style="color:#0073aa"></span>' : '<span class="dashicons dashicons-businesswoman" style="color:#d63638"></span>'; ?>
            <strong style="font-size: 15px; color: #2271b1;"><?php echo esc_html($item->hoTen); ?></strong> 
            <?php echo qlbh_render_star_rating($item->id, $item->starRating); ?>
            <div style="font-size: 11px; color: #646970; margin-left: 20px;">
                <span class="dashicons dashicons-calendar-alt" style="font-size:13px;"></span> <?php echo qlbh_format_date_view($item->ngaySinhDt); ?>
                <?php if($item->soCmnd): ?> | <span class="dashicons dashicons-id" style="font-size:13px;"></span> <b><?php echo esc_html($item->soCmnd); ?></b><?php endif; ?>
            </div>
        </div>

        <!-- Thẻ & Hạn dùng -->
        <div style="background: rgba(255,255,255,0.4); padding: 8px; border-radius: 4px; border-left: 3px solid #ccd0d4; margin-bottom: 8px;">
            <div style="margin-bottom: 5px;">
                <span class="code-highlight"><b><?php echo !empty($item->soTheBhyt) ? esc_html($item->soTheBhyt) : esc_html($item->maSoBhxh); ?></b></span>
                <?php if($item->ngay5Nam): ?><span class="bh-badge" style="background:#673ab7; margin-left:5px;">5 Năm: <?php echo qlbh_format_date_view($item->ngay5Nam); ?></span><?php endif; ?>
            </div>
            <div class="row-info" style="font-size: 11px;">
                <span class="dashicons dashicons-clock"></span>
                Hạn: <?php echo qlbh_format_date_view($item->tuNgayDt); ?> ➔ <b style="color:<?php echo $status_color; ?>;"><?php echo qlbh_format_date_view($item->denNgayDt); ?></b>
                <?php if ($days_diff !== null): ?>
                    <span style="font-weight:bold; color:<?php echo $status_color; ?>;"> [<?php echo ($days_diff < 0) ? "Hết ".abs($days_diff)."n" : "Còn ".$days_diff."n"; ?>]</span>
                <?php endif; ?>
            </div>
            <div style="margin-top:5px;"><?php echo qlbh_render_renewal_status_select($item); ?></div>
        </div>

        <!-- Nhân viên thu & Cơ quan -->
        <div style="font-size: 11px; margin-bottom: 8px;">
            <?php if ($item->bhytThuTruoc == 1) : ?>
                <div class="row-info" style="color: #2e7d32;"><span class="dashicons dashicons-money-alt"></span> <span class="bh-badge" style="background:#46b450;">Tạm thu: <b><?php echo esc_html($item->bhytNhanVienThu); ?></b></span></div>
            <?php elseif (!empty($item->bhytNhanVienThu)): ?>
                <div class="row-info" style="color: #646970;"><span class="dashicons dashicons-admin-users"></span> NV thu: <b><?php echo esc_html($item->bhytNhanVienThu); ?></b></div>
            <?php endif; ?>
            <?php if(!empty($item->maKCB)): ?><div class="row-info"><span class="dashicons dashicons-location" style="color:#d63638"></span> KCB: <i><?php echo esc_html($item->maKCB); ?></i></div><?php endif; ?>
            <?php if(!empty($item->tenDvi)): ?><div class="row-info"><span class="dashicons dashicons-networking" style="color:#46b450"></span> <?php echo esc_html($item->tenDvi); ?></div><?php endif; ?>
        </div>

        <!-- Liên hệ & Ghi chú -->
        <div style="border-top: 1px dashed #ccc; padding-top: 5px;">
            <?php if ($item->soDienThoai) : $s_cln = preg_replace('/\D/','',$item->soDienThoai); ?>
                <div class="row-info">
                    <span class="dashicons dashicons-phone" style="color:#46b450;"></span> <a href="tel:<?php echo $item->soDienThoai; ?>"><b><?php echo esc_html($item->soDienThoai); ?></b></a>
                    <a href="https://zalo.me/<?php echo $s_cln; ?>" target="_blank" style="color:#0068ff; text-decoration:none; font-size:9px; border:1px solid #0068ff; padding:0 3px; border-radius:3px;">Zalo</a>
                </div>
            <?php endif; ?>
            <div class="qlbh-quick-note" data-id="<?php echo $item->id; ?>" style="background:#fff8e5; padding:4px; border:1px solid #ffecb3; border-radius:3px; font-size:11px; color:#856404; cursor:pointer; margin-top:5px;">
                <span class="dashicons dashicons-edit" style="font-size:14px;"></span> <b>Ghi chú:</b> <span class="note-content"><?php echo $item->ghiChu ? esc_html($item->ghiChu) : 'Trống'; ?></span>
            </div>
        </div>
    </td>
    <?php
}