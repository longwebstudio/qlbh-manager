<?php if (!defined('ABSPATH')) exit; ?>
<div class="wrap qlbh-wrap">
    <h1>Hướng dẫn Đồng bộ 1-Click (VNPost)</h1>
    <div class="qlbh-card">
        <h3>Bước 1: Cài đặt công cụ</h3>
        <p>Kéo nút màu xanh dưới đây và thả vào <b>Thanh dấu trang (Bookmarks)</b> của trình duyệt Chrome/Cốc Cốc:</p>
        
        <a href="javascript:(function(){
            const API_URL = '<?php echo get_rest_url(null, 'qlbh/v1/sync-customer'); ?>';
            const getV = (label) => {
                const el = Array.from(document.querySelectorAll('.dx-field-item-label-text')).find(x => x.innerText.trim().includes(label));
                if (!el) return '';
                const container = el.closest('.dx-field-item');
                const input = container.querySelector('input.dx-texteditor-input');
                return input ? input.value.trim() : container.innerText.replace(el.innerText, '').trim();
            };
            const hanTheLabel = Array.from(document.querySelectorAll('.dx-field-item-label-text')).find(x => x.innerText.trim() === 'Hạn thẻ');
            let tuN = '', denN = '';
            if (hanTheLabel) {
                const hiddens = hanTheLabel.closest('.dx-field-item').querySelectorAll('input[type=\'hidden\']');
                if (hiddens.length >= 2) { tuN = hiddens[0].value; denN = hiddens[1].value; }
            }
            const payload = {
                hoTen: getV('Tên:'), maSoBhxh: getV('Mã số BHXH:'), maThe: getV('Mã thẻ:'),
                ngaySinh: getV('Ngày sinh:'), gioiTinh: getV('Giới tính:'), kcb: getV('Cơ sở KCB:'),
                donVi: getV('Tên đơn vị:'), bhyt5Nam: getV('Thời điểm 5 năm liên tục:'),
                tuNgay: tuN, denNgay: denN
            };
            fetch(API_URL, { method: 'POST', headers: {'Content-Type': 'application/json'}, body: JSON.stringify(payload) })
            .then(r => r.json()).then(res => alert(res.message))
            .catch(e => alert('Lỗi kết nối API: ' + e));
        })();" style="display:inline-block; background:#2271b1; color:#fff; padding:10px 20px; border-radius:50px; text-decoration:none; font-weight:bold; border:2px solid #005a87;">
            🟦 ĐỒNG BỘ BHYT VỀ HỆ THỐNG
        </a>

        <h3 style="margin-top:30px;">Bước 2: Sử dụng</h3>
        <ol>
            <li>Vào trang danh sách khách hàng, nhấn nút <b>"Tra cứu VNPost"</b>.</li>
            <li>Tại tab VNPost vừa mở, đợi kết quả hiện ra đầy đủ.</li>
            <li>Nhấn vào cái Bookmark <b>"ĐỒNG BỘ BHYT..."</b> bạn vừa kéo lúc nãy.</li>
            <li>Thông báo "Đã cập nhật" hiện ra là xong!</li>
        </ol>
    </div>
</div>