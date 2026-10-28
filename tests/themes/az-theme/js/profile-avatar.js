document.addEventListener('DOMContentLoaded', () => {
    const input = document.querySelector('input[name="az_avatar"]');
    if (input && input.form) input.form.enctype = 'multipart/form-data';
});
