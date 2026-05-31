document.addEventListener('DOMContentLoaded', () => {
    const form = document.querySelector('.rating-form');
    const errorBox = document.getElementById('js-notation-error');

    if (form) {
        const isGeneralReview = form.getAttribute('data-general-review') === 'true';

        form.addEventListener('submit', (e) => {
            // on remet l'erreur a zero
            if (errorBox) {
                errorBox.style.display = 'none';
                errorBox.textContent = '';
            }

            let message = '';

            if (isGeneralReview) {
                const noteGlobale = form.querySelector('input[name="global-rating"]:checked');
                if (!noteGlobale) {
                    message = '⚠️ Veuillez attribuer une note globale.';
                }
            } else {
                // verification que la note qualite est selectionnee
                const noteQualite = form.querySelector('input[name="food-rating"]:checked');
                // verification que la note livraison est selectionnee
                const noteLivraison = form.querySelector('input[name="delivery-rating"]:checked');

                if (!noteQualite && !noteLivraison) {
                    message = '⚠️ Veuillez attribuer une note pour la qualité des vivres et pour la livraison.';
                } else if (!noteQualite) {
                    message = '⚠️ Veuillez attribuer une note pour la qualité des vivres.';
                } else if (!noteLivraison) {
                    message = '⚠️ Veuillez attribuer une note pour la livraison.';
                }
            }

            // si une note manque, on bloque l'envoi et on affiche l'erreur
            if (message && errorBox) {
                e.preventDefault();
                errorBox.textContent = message;
                errorBox.style.display = 'block';
                // on scroll vers le message d'erreur
                errorBox.scrollIntoView({ behavior: 'smooth', block: 'center' });
            }
        });
    }
});
