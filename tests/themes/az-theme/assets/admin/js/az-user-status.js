document.addEventListener('DOMContentLoaded', () => {
    const panel = document.querySelector('[data-az-panel="user"]');
    const master = document.getElementById('az-option-user-enabled');
    if (!panel || !master) return;
    const notice = document.createElement('p');
    notice.className = 'notice notice-info';
    notice.setAttribute('role', 'status');
    panel.prepend(notice);
    const update = () => {
        const conflict = document.getElementById('az-option-user-admin_only')?.checked && document.getElementById('az-option-user-avatar_upload')?.checked;
        notice.textContent = !master.checked
            ? 'Chưa hoạt động: công tắc tổng User đang tắt. Công tắc con chỉ lưu lựa chọn, chưa có hiệu lực.'
            : conflict ? 'User đã bật. Cảnh báo: chặn Dashboard khiến thành viên không vào Hồ sơ để tải avatar. Hãy lưu thay đổi để áp dụng.'
            : 'User đã bật. Hãy lưu thay đổi để áp dụng. Lọc Media không bảo vệ URL file công khai.';
        panel.querySelectorAll('.az-field--switch').forEach(field => {
            field.style.opacity = !master.checked && !field.contains(master) ? '0.55' : '';
        });
    };
    panel.addEventListener('change', update);
    update();
});
