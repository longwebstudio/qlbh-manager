<?php
if (!defined('ABSPATH')) exit;
$current_month = date('m');
$current_year = date('Y');
?>
<div class="wrap qlbh-wrap">
    <h1>Báo cáo doanh thu & Xuất dữ liệu</h1>
    
    <div class="qlbh-card" style="max-width: 600px;">
        <h3>1. Xuất danh sách đóng BHYT theo tháng</h3>
        <form method="get" action="<?php echo admin_url('admin.php'); ?>">
            <input type="hidden" name="action" value="qlbh_export_csv">
            <table class="form-table">
                <tr>
                    <th>Chọn tháng/năm</th>
                    <td>
                        <select name="month">
                            <?php for($i=1; $i<=12; $i++) echo "<option value='$i' ".selected($current_month, $i, false).">Tháng $i</option>"; ?>
                        </select>
                        <select name="year">
                            <?php for($i=$current_year; $i>=$current_year-2; $i--) echo "<option value='$i'>Năm $i</option>"; ?>
                        </select>
                    </td>
                </tr>
                <tr>
                    <th>Nhân viên thu</th>
                    <td>
                        <input type="text" name="staff_id" value="<?php echo qlbh_get_staff_official_id(); ?>" class="regular-text">
                    </td>
                </tr>
            </table>
            <p>
                <button type="submit" class="button button-primary button-large">
                    <span class="dashicons dashicons-download" style="margin-top:4px;"></span> Tải file CSV (Excel)
                </button>
            </p>
        </form>
    </div>
</div>