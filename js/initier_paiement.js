document.addEventListener('DOMContentLoaded', () => {
    // autosoumission
    setTimeout(() => {
        const form = document.getElementById('cybank-form');
        if (form) {
            form.submit();
        }
    }, 500);
});
