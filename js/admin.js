document.addEventListener('DOMContentLoaded', () => {

    // ban / rehabiliter en asynchrone
    const banBtns = document.querySelectorAll('.js-ban-btn');
    for (let i = 0; i < banBtns.length; i++) {
        banBtns[i].addEventListener('click', function () {
            const btn = this;
            const idUser = btn.getAttribute('data-id');
            const row = btn.closest('tr');
            const statusBadge = row.querySelector('.status-badge');

            // envoi de la requete de bannissement en asynchrone
            fetch('../api/toggle_ban.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ id_user: idUser })
            })
                .then(r => r.json())
                .then(data => {
                    if (data.success) {
                        // mise a jour du dom sans recharger la page
                        if (data.new_status === 'banni') {
                            btn.title = 'Réhabiliter';
                            btn.innerHTML = '♻️';
                            row.classList.add('row-banned');
                            statusBadge.className = 'status-badge status-banned';
                            statusBadge.textContent = 'Banni';
                        } else {
                            btn.title = 'Bannir';
                            btn.innerHTML = '🗑️';
                            row.classList.remove('row-banned');
                            statusBadge.className = 'status-badge status-active';
                            statusBadge.textContent = 'Actif';
                        }
                    } else {
                        alert('Erreur: ' + data.message);
                    }
                })
                .catch(e => {
                    console.error(e);
                    alert('Erreur réseau');
                });
        });
    }

    // bouton crayon : ouverture de la modale d'edition
    const modale = document.getElementById('modale-edit');
    const btnFermer = document.getElementById('btn-fermer-edit');
    const editRole = document.getElementById('edit-role');
    const editPremium = document.getElementById('edit-premium');
    const editLabel = document.getElementById('edit-user-label');
    const editErreur = document.getElementById('edit-erreur');
    const btnValider = document.getElementById('btn-valider-edit');
    let editIdActuel = null;

    if (btnFermer && modale) {
        // fermeture de la modale
        btnFermer.addEventListener('click', () => {
            modale.style.display = 'none';
        });
    }

    // clic sur le crayon d'un utilisateur
    const editBtns = document.querySelectorAll('.js-edit-btn');
    for (let j = 0; j < editBtns.length; j++) {
        editBtns[j].addEventListener('click', function () {
            const row = this.closest('tr');
            const idUser = row.getAttribute('data-id');
            const login = row.getAttribute('data-login');
            const roleActuel = row.getAttribute('data-role');
            const premiumActuel = row.getAttribute('data-premium');

            // on ne peut pas modifier un admin
            if (roleActuel === 'admin') {
                alert('Impossible de modifier un administrateur.');
                return;
            }

            // preremplissage de la modale avec les donnees actuelles
            editIdActuel = idUser;
            if (editLabel) editLabel.textContent = '#U-' + idUser + ' — ' + login;
            if (editRole) editRole.value = roleActuel;
            if (editPremium) editPremium.value = premiumActuel;
            if (editErreur) editErreur.style.display = 'none';

            // affichage de la modale
            if (modale) modale.style.display = 'flex';
        });
    }

    // validation de la modification en asynchrone
    if (btnValider) {
        btnValider.addEventListener('click', () => {
            if (!editIdActuel) return;

            const nouveauRole = editRole ? editRole.value : '';
            const nouveauPremium = editPremium ? editPremium.value : '';

            // envoi de la modification en asynchrone
            fetch('../api/update_user.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({
                    id_user: parseInt(editIdActuel),
                    role: nouveauRole,
                    statut_premium: nouveauPremium
                })
            })
                .then(r => r.json())
                .then(data => {
                    if (data.success) {
                        // mise a jour de la ligne du tableau sans recharger la page
                        const row = document.querySelector('tr[data-id="' + editIdActuel + '"]');
                        if (row) {
                            // mise a jour du badge de role
                            const roleBadge = row.querySelector('.role-badge');
                            if (roleBadge) {
                                let classeRole = 'role-client';
                                if (nouveauRole === 'livreur') classeRole = 'role-livreur';
                                else if (nouveauRole === 'restaurateur') classeRole = 'role-restaurateur';
                                roleBadge.className = 'role-badge ' + classeRole;
                                roleBadge.textContent = nouveauRole.charAt(0).toUpperCase() + nouveauRole.slice(1);
                            }

                            // mise a jour des data attributes
                            row.setAttribute('data-role', nouveauRole);
                            row.setAttribute('data-premium', nouveauPremium);
                        }

                        // fermeture de la modale
                        if (modale) modale.style.display = 'none';
                    } else {
                        // affichage de l'erreur dans la modale
                        if (editErreur) {
                            editErreur.textContent = data.message;
                            editErreur.style.display = 'block';
                        }
                    }
                })
                .catch(e => {
                    console.error(e);
                    if (editErreur) {
                        editErreur.textContent = 'Erreur réseau.';
                        editErreur.style.display = 'block';
                    }
                });
        });
    }

    // filtre par role et recherche matricule
    const filterRole = document.getElementById('filter-role');
    const searchId = document.getElementById('search-id');

    function filtrerTableau() {
        const roleFiltre = filterRole ? filterRole.value : 'tous';
        const recherche = searchId ? searchId.value.trim().toLowerCase() : '';
        const lignes = document.querySelectorAll('.admin-table tbody tr');

        for (let k = 0; k < lignes.length; k++) {
            const ligne = lignes[k];
            const roleL = ligne.getAttribute('data-role');
            const cellId = ligne.querySelector('.cell-id');
            const idL = cellId ? cellId.textContent.toLowerCase() : '';

            const okRole = (roleFiltre === 'tous' || roleL === roleFiltre);
            const okSearch = (recherche === '' || idL.indexOf(recherche) !== -1);

            ligne.style.display = (okRole && okSearch) ? '' : 'none';
        }
    }

    if (filterRole) filterRole.addEventListener('change', filtrerTableau);
    if (searchId) searchId.addEventListener('input', filtrerTableau);
});
