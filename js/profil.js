document.addEventListener('DOMContentLoaded', () => {
    const editButtons = document.querySelectorAll('.js-edit-btn');

    // regex simple pour verifier le format email
    const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;

    // regex pour le telephone (chiffres, espaces, + autorise)
    const telRegex = /^[+]?[0-9\s]{6,20}$/;

    // fonction de validation selon le champ
    function validerChamp(champ, valeur) {
        // verification de l'email avant envoi async
        if (champ === 'email') {
            if (valeur === '') return 'L\'email ne peut pas être vide.';
            if (!emailRegex.test(valeur)) return 'Le format de l\'email est invalide.';
        }

        // verification du pseudo
        if (champ === 'login') {
            if (valeur.length < 3) return 'Le pseudo doit faire au moins 3 caractères.';
            if (valeur.length > 30) return 'Le pseudo ne doit pas dépasser 30 caractères.';
        }

        // verification du prenom
        if (champ === 'prenom') {
            if (valeur !== '' && valeur.length < 2) return 'Le prénom doit faire au moins 2 caractères.';
        }

        // verification du nom
        if (champ === 'nom') {
            if (valeur !== '' && valeur.length < 2) return 'Le nom doit faire au moins 2 caractères.';
        }

        // verification du telephone
        if (champ === 'telephone') {
            if (valeur !== '' && !telRegex.test(valeur)) return 'Le format du téléphone est invalide (ex: +33 6 12 34 56 78).';
        }

        // verification de l'adresse
        if (champ === 'adresse') {
            if (valeur !== '' && valeur.length < 5) return 'L\'adresse doit faire au moins 5 caractères.';
        }

        // aucune erreur
        return null;
    }

    // fonction pour supprimer le message d'erreur sous un champ
    function supprimerErreur(row) {
        const ancien = row.querySelector('.field-error');
        if (ancien) ancien.remove();
    }

    // fonction pour afficher un message d'erreur sous le champ
    function afficherErreur(row, message) {
        supprimerErreur(row);
        const errDiv = document.createElement('div');
        errDiv.className = 'field-error';
        errDiv.textContent = message;
        errDiv.style.color = '#ff4444';
        errDiv.style.fontSize = '0.8em';
        errDiv.style.marginTop = '4px';
        errDiv.style.gridColumn = '1 / -1';
        row.appendChild(errDiv);
    }

    editButtons.forEach(btn => {
        btn.addEventListener('click', function () {
            const row = this.closest('.field-row');
            const champ = row.getAttribute('data-field');
            const spanValue = row.querySelector('.field-value');
            const currentValue = spanValue.textContent === '—' || spanValue.textContent === 'Non renseigné' ? '' : spanValue.textContent;

            // si on est deja en edition on annule
            if (row.querySelector('input')) return;

            // on cree l'input
            const input = document.createElement('input');
            input.type = champ === 'email' ? 'email' : 'text';
            input.value = currentValue;
            input.style.padding = '5px';
            input.style.borderRadius = '4px';
            input.style.border = '1px solid #0ea5e9';
            input.style.background = 'rgba(255,255,255,0.1)';
            input.style.color = 'inherit';
            input.style.flex = '1';

            // on cache le span et le bouton edit
            spanValue.style.display = 'none';
            this.style.display = 'none';

            // boutons de validation / annulation
            const saveBtn = document.createElement('button');
            saveBtn.innerHTML = '✔️';
            saveBtn.className = 'btn-edit';
            saveBtn.title = 'Enregistrer';

            const cancelBtn = document.createElement('button');
            cancelBtn.innerHTML = '❌';
            cancelBtn.className = 'btn-edit';
            cancelBtn.title = 'Annuler';

            const btnContainer = document.createElement('div');
            btnContainer.style.display = 'flex';
            btnContainer.style.gap = '5px';
            btnContainer.appendChild(saveBtn);
            btnContainer.appendChild(cancelBtn);

            row.insertBefore(input, this);
            row.insertBefore(btnContainer, this);

            input.focus();

            // validation en temps reel pendant la saisie
            input.addEventListener('input', function () {
                const erreur = validerChamp(champ, this.value.trim());
                if (erreur) {
                    // bordure rouge si erreur
                    this.style.border = '1px solid #ff4444';
                    afficherErreur(row, erreur);
                } else {
                    // bordure verte si ok
                    this.style.border = '1px solid #00ff88';
                    supprimerErreur(row);
                }
            });

            // annuler l'edition
            cancelBtn.addEventListener('click', () => {
                input.remove();
                btnContainer.remove();
                supprimerErreur(row);
                spanValue.style.display = '';
                this.style.display = '';
            });

            // sauvegarder via fetch apres validation
            saveBtn.addEventListener('click', () => {
                const newValue = input.value.trim();

                // on verifie le champ avant d'envoyer la requete
                const erreur = validerChamp(champ, newValue);
                if (erreur) {
                    // on affiche l'erreur sous le champ, pas d'envoi
                    input.style.border = '1px solid #ff4444';
                    afficherErreur(row, erreur);
                    return;
                }

                // validation ok, on envoie la requete async
                fetch('../api/update_profil.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json'
                    },
                    body: JSON.stringify({
                        champ: champ,
                        valeur: newValue
                    })
                })
                    .then(response => response.json())
                    .then(data => {
                        if (data.success) {
                            // succes : maj de l'affichage
                            spanValue.textContent = data.valeur || '—';
                            spanValue.style.color = '#00ff88';
                            setTimeout(() => spanValue.style.color = '', 2000);
                        } else {
                            // erreur serveur (doublon etc)
                            afficherErreur(row, data.message);
                        }

                        // restauration de l'ui
                        input.remove();
                        btnContainer.remove();
                        supprimerErreur(row);
                        spanValue.style.display = '';
                        this.style.display = '';
                    })
                    .catch(err => {
                        // erreur reseau
                        afficherErreur(row, 'Erreur de connexion réseau.');
                        console.error(err);
                    });
            });
        });
    });
});
