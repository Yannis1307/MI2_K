document.addEventListener('DOMContentLoaded', () => {
    const dataContainer = document.getElementById('commande-data');
    if (!dataContainer) return;

    // donnees de la commande chargees depuis les attributs html
    let platsCommande = JSON.parse(dataContainer.getAttribute('data-plats') || '[]');
    let menusCommande = JSON.parse(dataContainer.getAttribute('data-menus') || '[]');
    const totalInitial = parseFloat(dataContainer.getAttribute('data-total')) || 0;
    const idCommande = dataContainer.getAttribute('data-id') || '';
    const creditsDispo = parseFloat(dataContainer.getAttribute('data-credits')) || 0;

    // elements du dom
    const tbody = document.getElementById('tbody-commande');
    const txtTotal = document.getElementById('txt-nouveau-total');
    const txtDiff = document.getElementById('txt-difference');
    const btnValider = document.getElementById('btn-valider');
    const msgVide = document.getElementById('msg-panier-vide');

    // fonction pour calculer le total actuel
    function calculerTotal() {
        let total = 0;
        for (let i = 0; i < platsCommande.length; i++) {
            total += platsCommande[i].sous_total;
        }
        for (let j = 0; j < menusCommande.length; j++) {
            total += menusCommande[j].sous_total;
        }
        return Math.round(total * 100) / 100;
    }

    // fonction pour echapper le html
    function escapeHtml(text) {
        if (!text) return '';
        const div = document.createElement('div');
        div.textContent = text;
        return div.innerHTML;
    }

    // fonction pour mettre a jour l'affichage du tableau et du total
    function majAffichage() {
        // vider le tableau
        tbody.innerHTML = '';

        // afficher les plats
        for (let i = 0; i < platsCommande.length; i++) {
            const p = platsCommande[i];
            const tr = document.createElement('tr');
            tr.innerHTML = `<td style="padding: 10px;">${escapeHtml(p.nom)}</td>
                <td>${p.quantite}</td>
                <td>${p.sous_total.toFixed(2)} ₹</td>
                <td><button class="btn-edit btn-retirer-plat" data-index="${i}" style="color: #ff4444; border-color: #ff4444;">Retirer</button></td>`;
            tbody.appendChild(tr);
        }

        // afficher les menus
        for (let j = 0; j < menusCommande.length; j++) {
            const m = menusCommande[j];
            const tr2 = document.createElement('tr');
            tr2.innerHTML = `<td style="padding: 10px;">${escapeHtml(m.nom)} (Menu)</td>
                <td>${m.quantite}</td>
                <td>${m.sous_total.toFixed(2)} ₹</td>
                <td><button class="btn-edit btn-retirer-menu" data-index="${j}" style="color: #ff4444; border-color: #ff4444;">Retirer</button></td>`;
            tbody.appendChild(tr2);
        }

        // message si commande vide
        if (platsCommande.length === 0 && menusCommande.length === 0) {
            msgVide.style.display = 'block';
        } else {
            msgVide.style.display = 'none';
        }

        // maj du prix total a chaque clic
        const nouveauTotal = calculerTotal();
        txtTotal.innerHTML = `Nouveau Total : <strong>${nouveauTotal.toFixed(2)} ₹</strong>`;

        // maj de la difference
        const diff = Math.round((nouveauTotal - totalInitial) * 100) / 100;
        const divCredits = document.getElementById('div-utiliser-credits');

        if (diff > 0) {
            if (divCredits) divCredits.style.display = 'flex';
            majTexteBouton(diff);
        } else {
            if (divCredits) divCredits.style.display = 'none';
            if (diff < 0) {
                txtDiff.style.color = '#00ff88';
                txtDiff.textContent = `Bon de réduction de ${Math.abs(diff).toFixed(2)} ₹ généré.`;
                btnValider.textContent = 'Valider les modifications';
            } else {
                txtDiff.style.color = 'rgba(255,255,255,0.5)';
                txtDiff.textContent = 'Aucune différence de prix.';
                btnValider.textContent = 'Valider les modifications';
            }
        }

        // ecouteurs sur les boutons retirer (plats)
        const btnsRetirerPlat = document.querySelectorAll('.btn-retirer-plat');
        for (let k = 0; k < btnsRetirerPlat.length; k++) {
            btnsRetirerPlat[k].addEventListener('click', function () {
                const idx = parseInt(this.getAttribute('data-index'));
                retirerPlat(idx);
            });
        }

        // ecouteurs sur les boutons retirer (menus)
        const btnsRetirerMenu = document.querySelectorAll('.btn-retirer-menu');
        for (let l = 0; l < btnsRetirerMenu.length; l++) {
            btnsRetirerMenu[l].addEventListener('click', function () {
                const idx = parseInt(this.getAttribute('data-index'));
                retirerMenu(idx);
            });
        }
    }

    function majTexteBouton(diff) {
        let reste = diff;
        const cb = document.getElementById('utiliser_credits');
        if (cb && cb.checked) {
            reste = Math.max(0, diff - creditsDispo);
        }
        if (reste > 0) {
            txtDiff.style.color = '#ffaa00';
            txtDiff.textContent = `Différence de +${reste.toFixed(2)} ₹ à payer par carte.`;
            btnValider.textContent = '🔒 Payer le complément par carte';
        } else {
            txtDiff.style.color = '#00ff88';
            txtDiff.textContent = 'Différence couverte par vos crédits.';
            btnValider.textContent = 'Valider avec les crédits';
        }
    }

    const cbCreditsListener = document.getElementById('utiliser_credits');
    if (cbCreditsListener) {
        cbCreditsListener.addEventListener('change', () => {
            const nouveauTotal = calculerTotal();
            const diff = Math.round((nouveauTotal - totalInitial) * 100) / 100;
            if (diff > 0) {
                majTexteBouton(diff);
            }
        });
    }

    // retirer un plat de la commande
    function retirerPlat(index) {
        if (platsCommande[index].quantite > 1) {
            platsCommande[index].quantite -= 1;
            platsCommande[index].sous_total = platsCommande[index].quantite * platsCommande[index].prix_unitaire;
        } else {
            platsCommande.splice(index, 1);
        }
        majAffichage();
    }

    // retirer un menu de la commande
    function retirerMenu(index) {
        if (menusCommande[index].quantite > 1) {
            menusCommande[index].quantite -= 1;
            menusCommande[index].sous_total = menusCommande[index].quantite * menusCommande[index].prix_unitaire;
        } else {
            menusCommande.splice(index, 1);
        }
        majAffichage();
    }

    // ajouter un plat depuis le select
    const btnAjouterPlat = document.getElementById('btn-ajouter-plat');
    if (btnAjouterPlat) {
        btnAjouterPlat.addEventListener('click', () => {
            const select = document.getElementById('select-plat');
            const option = select.options[select.selectedIndex];
            const idPlat = parseInt(select.value);
            const nomPlat = option.getAttribute('data-nom');
            const prixPlat = parseFloat(option.getAttribute('data-prix'));

            let found = false;
            for (let i = 0; i < platsCommande.length; i++) {
                if (platsCommande[i].id_plat == idPlat) {
                    platsCommande[i].quantite += 1;
                    platsCommande[i].sous_total = platsCommande[i].quantite * platsCommande[i].prix_unitaire;
                    found = true;
                    break;
                }
            }

            if (!found) {
                platsCommande.push({
                    id_plat: idPlat,
                    nom: nomPlat,
                    quantite: 1,
                    prix_unitaire: prixPlat,
                    sous_total: prixPlat
                });
            }
            majAffichage();
        });
    }

    // ajouter un menu depuis le select
    const btnAjouterMenu = document.getElementById('btn-ajouter-menu');
    if (btnAjouterMenu) {
        btnAjouterMenu.addEventListener('click', () => {
            const select = document.getElementById('select-menu');
            const option = select.options[select.selectedIndex];
            const idMenu = parseInt(select.value);
            const nomMenu = option.getAttribute('data-nom');
            const prixMenu = parseFloat(option.getAttribute('data-prix'));

            let found = false;
            for (let i = 0; i < menusCommande.length; i++) {
                if (menusCommande[i].id_menu == idMenu) {
                    menusCommande[i].quantite += 1;
                    menusCommande[i].sous_total = menusCommande[i].quantite * menusCommande[i].prix_unitaire;
                    found = true;
                    break;
                }
            }

            if (!found) {
                menusCommande.push({
                    id_menu: idMenu,
                    nom: nomMenu,
                    quantite: 1,
                    prix_unitaire: prixMenu,
                    sous_total: prixMenu
                });
            }
            majAffichage();
        });
    }

    // validation finale au clic sur le bouton
    if (btnValider) {
        btnValider.addEventListener('click', function () {
            this.disabled = true;
            this.textContent = 'Traitement en cours...';

            const nouveauTotal = calculerTotal();
            const diff = Math.round((nouveauTotal - totalInitial) * 100) / 100;

            if (platsCommande.length === 0 && menusCommande.length === 0) {
                alert('La commande ne peut pas être vide.');
                this.disabled = false;
                this.textContent = 'Valider les modifications';
                return;
            }

            if (diff > 0) {
                const cb = document.getElementById('utiliser_credits');
                const hiddenCredits = document.getElementById('hidden-utiliser-credits');
                if (hiddenCredits) {
                    hiddenCredits.value = (cb && cb.checked) ? '1' : '0';
                }

                document.getElementById('hidden-plats').value = JSON.stringify(platsCommande);
                document.getElementById('hidden-menus').value = JSON.stringify(menusCommande);
                document.getElementById('hidden-nouveau-total').value = nouveauTotal.toFixed(2);
                document.getElementById('form-cybank-modif').submit();
                return;
            }

            envoyerModification();
        });
    }

    // envoi de la modification au serveur via fetch
    function envoyerModification() {
        const nouveauTotal = calculerTotal();

        fetch('../api/modifier_commande.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({
                id_commande: idCommande,
                plats: platsCommande,
                menus: menusCommande,
                nouveau_total: nouveauTotal,
                total_initial: totalInitial
            })
        })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    window.location.href = 'profil.php';
                } else {
                    alert('Erreur: ' + data.message);
                    btnValider.disabled = false;
                    btnValider.textContent = 'Valider les modifications';
                }
            })
            .catch(err => {
                alert('Erreur de connexion réseau.');
                console.error(err);
                btnValider.disabled = false;
                btnValider.textContent = 'Valider les modifications';
            });
    }

    // affichage initial
    majAffichage();
});
