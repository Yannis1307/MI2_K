document.addEventListener('DOMContentLoaded', () => {
    // autosoumission vers cybank apres un court delai
    setTimeout(() => {
        const form = document.getElementById('cybank-form');
        if (form) {
            form.submit();
        }
    }, 500);
});
