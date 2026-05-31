document.addEventListener('DOMContentLoaded', () => {
    // toggle password visibility
    const togglePassword = document.getElementById('toggle-password');
    const passwordField = document.getElementById('password');

    if (togglePassword && passwordField) {
        togglePassword.addEventListener('click', function () {
            const type = passwordField.getAttribute('type') === 'password' ? 'text' : 'password';
            passwordField.setAttribute('type', type);
            this.innerHTML = type === 'password' ? '👁️' : '🙈';
        });
    }

    // character counters
    const identifiantField = document.getElementById('identifiant');
    const identifiantCounter = document.getElementById('identifiant-counter');
    const passwordCounter = document.getElementById('password-counter');

    if (identifiantField && identifiantCounter) {
        identifiantField.addEventListener('input', function () {
            identifiantCounter.textContent = this.value.length + '/30';
        });
    }

    if (passwordField && passwordCounter) {
        // compteur de caracteres pour le mot de passe
        passwordField.addEventListener('input', function () {
            const remaining = 50 - this.value.length;
            passwordCounter.textContent = this.value.length + '/50';
            // on passe au rouge quand il reste moins de 5 caracteres
            passwordCounter.style.color = remaining <= 5 ? '#ff8844' : 'rgba(255,255,255,0.5)';
        });
    }

    // clientside validation
    const form = document.querySelector('.auth-form');
    if (form) {
        form.addEventListener('submit', (e) => {
            // remove existing js errors
            const existingError = document.getElementById('js-error');
            if (existingError) existingError.remove();

            const identifiant = identifiantField ? identifiantField.value.trim() : '';
            const password = passwordField ? passwordField.value : '';
            let errorMsg = '';

            if (identifiant.length < 3) {
                errorMsg = 'L\'identifiant doit faire au moins 3 caractères.';
            } else if (password.length === 0) {
                errorMsg = 'Veuillez saisir votre mot de passe.';
            }

            if (errorMsg) {
                e.preventDefault(); // Stop HTTP request
                const p = document.createElement('p');
                p.id = 'js-error';
                p.style.color = '#ff4444';
                p.style.textAlign = 'center';
                p.style.marginBottom = '15px';
                p.textContent = errorMsg;
                form.parentNode.insertBefore(p, form);
            }
        });
    }
});
