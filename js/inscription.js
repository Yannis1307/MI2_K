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
    function setupCounter(inputId, counterId, max) {
        const input = document.getElementById(inputId);
        const counter = document.getElementById(counterId);
        if (input && counter) {
            input.addEventListener('input', function () {
                const remaining = max - this.value.length;
                counter.textContent = remaining + ' caractères restants';
            });
        }
    }

    setupCounter('nomcode', 'nomcode-counter', 30);
    setupCounter('email', 'email-counter', 50);
    setupCounter('password', 'password-counter', 50);

    // password rules validation
    const ruleLength = document.getElementById('rule-length');
    const ruleUpper = document.getElementById('rule-upper');
    const ruleNumber = document.getElementById('rule-number');
    const ruleSpecial = document.getElementById('rule-special');

    if (passwordField && ruleLength && ruleUpper && ruleNumber && ruleSpecial) {
        passwordField.addEventListener('input', function () {
            const val = this.value;

            if (val.length >= 8) { ruleLength.innerHTML = '✅ 8 caractères minimum'; ruleLength.style.color = '#7fff7f'; }
            else { ruleLength.innerHTML = '❌ 8 caractères minimum'; ruleLength.style.color = ''; }

            if (/[A-Z]/.test(val)) { ruleUpper.innerHTML = '✅ Au moins une majuscule'; ruleUpper.style.color = '#7fff7f'; }
            else { ruleUpper.innerHTML = '❌ Au moins une majuscule'; ruleUpper.style.color = ''; }

            if (/[0-9]/.test(val)) { ruleNumber.innerHTML = '✅ Au moins un chiffre'; ruleNumber.style.color = '#7fff7f'; }
            else { ruleNumber.innerHTML = '❌ Au moins un chiffre'; ruleNumber.style.color = ''; }

            if (/[^A-Za-z0-9]/.test(val)) { ruleSpecial.innerHTML = '✅ Au moins un caractère spécial'; ruleSpecial.style.color = '#7fff7f'; }
            else { ruleSpecial.innerHTML = '❌ Au moins un caractère spécial'; ruleSpecial.style.color = ''; }
        });
    }

    // clientside validation
    const form = document.querySelector('.auth-form');
    if (form) {
        form.addEventListener('submit', (e) => {
            // remove existing js errors
            const existingError = document.getElementById('js-error');
            if (existingError) existingError.remove();

            const nomcode = document.getElementById('nomcode') ? document.getElementById('nomcode').value.trim() : '';
            const email = document.getElementById('email') ? document.getElementById('email').value.trim() : '';
            const password = passwordField ? passwordField.value : '';
            let errorMsg = '';

            // simple email regex
            const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;

            if (nomcode.length < 3) {
                errorMsg = 'Le nom de code doit faire au moins 3 caractères.';
            } else if (!emailRegex.test(email)) {
                errorMsg = 'L\'adresse email n\'est pas valide.';
            } else if (password.length < 8 || !/[A-Z]/.test(password) || !/[0-9]/.test(password) || !/[^A-Za-z0-9]/.test(password)) {
                errorMsg = 'Le mot de passe ne respecte pas les règles de sécurité.';
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
