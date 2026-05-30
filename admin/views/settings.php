<?php
if ( ! defined( 'ABSPATH' ) ) exit;

// Xử lý lưu cấu hình
if (isset($_POST['qlbh_save_settings'])) {
    $salary = qlbh_sanitize_money($_POST['base_salary']);
    update_option('qlbh_base_salary', $salary);
    echo '<div class="updated"><p>Đã cập nhật mức lương cơ sở mới!</p></div>';
}

$base_salary = get_option('qlbh_base_salary', 2340000); // Mặc định 2.340.000đ theo quy định mới
?>

<div class="wrap qlbh-wrap">
    <h1>Cấu hình hệ thống</h1>
    <div class="qlbh-card" style="max-width: 600px;">
        <form method="post">
            <table class="form-table">
                <tr>
                    <th>Lương cơ sở (VNĐ)</th>
                    <td>
                        <input type="text" name="base_salary" value="<?php echo number_format($base_salary); ?>" class="regular-text" id="base_salary_input">
                        <p class="description">Mức lương cơ sở dùng để tính mức đóng BHYT (4.5% lương cơ sở).</p>
                    </td>
                </tr>
                <tr>
                    <th>Mức đóng 100% (12 tháng)</th>
                    <td>
                        <strong id="p1_preview" style="color: #d63638; font-size: 1.2em;">0</strong> VNĐ
                    </td>
                </tr>
            </table>
            <p class="submit">
                <input type="submit" name="qlbh_save_settings" class="button button-primary" value="Lưu thay đổi">
            </p>
        </form>
    </div>
</div>

<script>
jQuery(document).ready(function($) {
    function updatePreview() {
        let salary = $('#base_salary_input').val().replace(/[^\d]/g, "");
        if (salary) {
            // Công thức: Lương CS * 4.5% * 12 tháng
            let p1 = Math.round(salary * 0.045 * 12);
            $('#p1_preview').text(new Intl.NumberFormat().format(p1));
        }
    }
    $('#base_salary_input').on('keyup', function() {
        var val = $(this).val().replace(/[^\d]/g, "");
        $(this).val(new Intl.NumberFormat().format(val));
        updatePreview();
    });
    updatePreview();
});
</script>