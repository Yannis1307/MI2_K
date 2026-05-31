document.addEventListener('DOMContentLoaded', function () {
    const themeBtn = document.getElementById('theme-toggle');
    const themeLink = document.getElementById('theme-link');

    if (themeBtn && themeLink) {
        themeBtn.addEventListener('click', function () {
            let currentTheme = 'dark';
            // on vérifie si le thème clair est chargé
            if (themeLink.getAttribute('href') === '../css/light-theme.css') {
                // passage au sombre
                themeLink.setAttribute('href', '');
                themeBtn.innerHTML = '☀️';
                currentTheme = 'dark';
            } else {
                // passage au clair
                themeLink.setAttribute('href', '../css/light-theme.css');
                themeBtn.innerHTML = '🌑';
                currentTheme = 'light';
            }
            // sauvegarde dans un cookie (1 an)
            document.cookie = "theme=" + currentTheme + "; max-age=" + (60 * 60 * 24 * 365) + "; path=/";
        });
    }
});
