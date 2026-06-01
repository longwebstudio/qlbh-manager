<?php
if (!defined('ABSPATH')) exit;

if (isset($_POST['save_settings'])) {
    update_option('qlbh_base_salary', qlbh_sanitize_money($_POST['base_salary']));
    echo '<div class="updated"><p>Đã lưu cấu hình!</p></div>';
}

$salary = get_option('qlbh_base_salary', 2340000);
?>

<div class="wrap qlbh-wrap">
    <h1>Cấu hình hệ thống</h1>
    <div class="qlbh-card" style="max-width: 500px;">
        <form method="post">
            <table class="form-table">
                <tr>
                    <th>Lương cơ sở (VNĐ)</th>
                    <td>
                        <input type="text" name="base_salary" value="<?php echo number_format($salary); ?>" class="regular-text">
                        <p class="description">Mức lương này dùng để tính tiền BHYT (4.5%).</p>
                    </td>
                </tr>
            </table>
            <input type="submit" name="save_settings" class="button button-primary" value="Lưu thay đổi">
        </form>
    </div>
</div>