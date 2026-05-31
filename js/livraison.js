document.addEventListener('DOMContentLoaded', () => {

    // on utilise la delegation d'evenements pour cibler les boutons actuels et futurs
    document.addEventListener('click', (e) => {
        // on verifie si on a clique sur un bouton d'action
        if (e.target && e.target.classList.contains('js-action-livraison')) {
            const btn = e.target;
            const idCmd = btn.getAttribute('data-cmd');
            const action = btn.getAttribute('data-action');

            // on desactive le bouton pendant la requete
            btn.disabled = true;
            btn.style.opacity = '0.5';

            fetch('../api/update_statut_commande.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({
                    id_commande: idCmd,
                    action_statut: action
                })
            })
                .then(r => r.json())
                .then(data => {
                    if (data.success) {
                        // suppression de la commande livree de l'ecran (message de succes temporaire)
                        const banner = document.querySelector('.mission-banner h1');
                        if (banner) {
                            banner.innerHTML = (action === 'livrer') ? 'MISSION ACCOMPLIE' : 'MISSION ANNULÉE';
                        }

                        const container = document.querySelector('.confirm-zone');
                        if (container) {
                            container.innerHTML = '<div style="text-align: center; padding: 20px; font-size: 1.2em; color: ' + (action === 'livrer' ? '#00ff88' : '#ff4444') + ';">' +
                                (action === 'livrer' ? '✅ Colis livré avec succès.' : '❌ Colis signalé comme abandonné.') + '</div>';
                        }

                        // passage a la commande suivante (apres une seconde pour laisser l'utilisateur lire)
                        setTimeout(() => {
                            chargerProchaineLivraison();
                        }, 1000);
                    } else {
                        alert('Erreur: ' + data.message);
                        btn.disabled = false;
                        btn.style.opacity = '1';
                    }
                })
                .catch(err => {
                    console.error(err);
                    alert('Erreur réseau.');
                    btn.disabled = false;
                    btn.style.opacity = '1';
                });
        }
    });

    // fonction pour recuperer la prochaine livraison et l'injecter dans le dom
    function chargerProchaineLivraison() {
        fetch('../api/get_prochaine_livraison.php')
            .then(r => r.json())
            .then(data => {
                const missionCard = document.querySelector('.mission-card');
                const confirmZone = document.querySelector('.confirm-zone');
                const banner = document.querySelector('.mission-banner h1');

                if (data.success && data.mission) {
                    const m = data.mission;

                    if (banner) banner.innerHTML = 'MISSION EN COURS';

                    // on reconstruit le html complet de la carte
                    let htmlCard = '';

                    // client
                    htmlCard += '<div class="mission-section client-section">';
                    htmlCard += '<div class="client-info">';
                    htmlCard += '<img src="../images/lando_avatar.png" alt="Avatar Client" class="client-avatar">';
                    htmlCard += '<div class="client-details">';
                    htmlCard += '<span class="client-name">' + echapperHtml(m.login_client) + '</span>';
                    htmlCard += '<span class="client-tag">📦 Commande #' + echapperHtml(m.id) + '</span>';
                    htmlCard += '</div></div></div>';

                    // adresse
                    const adresse = m.adresse ? m.adresse : 'Adresse non renseignée';
                    htmlCard += '<div class="mission-section address-section">';
                    htmlCard += '<h2 class="section-label">📍 Adresse de Livraison</h2>';
                    htmlCard += '<p class="address-text">' + echapperHtml(adresse) + '</p>';
                    htmlCard += '<a href="https://www.google.com/maps/search/?api=1&query=' + encodeURIComponent(adresse) + '" target="_blank" class="btn-gps">🌍 OUVRIR LA CARTE GALACTIQUE</a>';
                    htmlCard += '</div>';

                    // acces
                    htmlCard += '<div class="mission-section access-section">';
                    htmlCard += '<h2 class="section-label">🔑 Détails d\'Accès</h2>';
                    htmlCard += '<div class="access-grid">';

                    const code = (m.code_interphone && m.code_interphone !== '') ? m.code_interphone : 'Non renseigné';
                    const etage = (m.etage && m.etage !== '') ? m.etage : 'Non renseigné';
                    const tel = (m.telephone_client && m.telephone_client !== '') ? m.telephone_client : '—';

                    htmlCard += '<div class="access-bubble"><span class="access-icon">🔢</span><span class="access-label">Digicode</span><span class="access-value">' + echapperHtml(code) + '</span></div>';
                    htmlCard += '<div class="access-bubble"><span class="access-icon">🏢</span><span class="access-label">Étage</span><span class="access-value">' + echapperHtml(etage) + '</span></div>';
                    htmlCard += '<div class="access-bubble access-phone"><span class="access-icon">📞</span><span class="access-label">Téléphone</span><span class="access-value">' + echapperHtml(tel) + '</span></div>';
                    htmlCard += '</div></div>';

                    // commande
                    htmlCard += '<div class="mission-section order-section">';
                    htmlCard += '<h2 class="section-label">📦 Commande #' + echapperHtml(m.id) + '</h2>';
                    htmlCard += '<ul class="order-list">';

                    if (m.plats && m.plats.length > 0) {
                        for (let i = 0; i < m.plats.length; i++) {
                            htmlCard += '<li><span class="qty">' + m.plats[i].quantite + 'x</span> ' + echapperHtml(m.plats[i].nom) + '</li>';
                        }
                    }

                    if (m.menus && m.menus.length > 0) {
                        for (let j = 0; j < m.menus.length; j++) {
                            htmlCard += '<li><span class="qty">' + m.menus[j].quantite + 'x</span> ' + echapperHtml(m.menus[j].nom);
                            if (m.menus[j].plats_details && m.menus[j].plats_details.length > 0) {
                                htmlCard += '<ul style="list-style: none; padding-left: 20px; font-size: 0.85em; opacity: 0.8; margin-top: 4px;">';
                                for (let k = 0; k < m.menus[j].plats_details.length; k++) {
                                    htmlCard += '<li>- ' + echapperHtml(m.menus[j].plats_details[k]) + '</li>';
                                }
                                htmlCard += '</ul>';
                            }
                            htmlCard += '</li>';
                        }
                    }

                    htmlCard += '</ul>';
                    const totalForm = parseFloat(m.total).toFixed(2).replace('.', ',');
                    htmlCard += '<div class="order-total"><span>Total</span><span class="total-price">' + totalForm + ' ₹</span></div>';
                    htmlCard += '</div>';

                    // on injecte la nouvelle carte
                    if (missionCard) missionCard.innerHTML = htmlCard;

                    // on recree les boutons de validation avec la delegation d'evenement
                    let htmlBtns = '<div style="width: 100%;">';
                    htmlBtns += '<button type="button" data-cmd="' + echapperHtml(m.id) + '" data-action="livrer" class="btn-confirm-delivery js-action-livraison" style="width: 100%; margin-bottom: 10px;">✅ CONFIRMER LA LIVRAISON</button>';
                    htmlBtns += '<button type="button" data-cmd="' + echapperHtml(m.id) + '" data-action="abandonner" class="btn-help js-action-livraison" style="width: 100%; background: rgba(255, 50, 50, 0.1); border: 1px solid #ff4444; color: #ff4444;">❌ SIGNALER COMME ABANDONNÉE</button>';
                    htmlBtns += '</div>';

                    if (confirmZone) {
                        confirmZone.innerHTML = htmlBtns;
                    } else if (missionCard) {
                        // si la zone avait disparu, on la recree juste apres la carte
                        const div = document.createElement('div');
                        div.className = 'confirm-zone';
                        div.style.display = 'flex';
                        div.style.flexDirection = 'column';
                        div.style.gap = '10px';
                        div.innerHTML = htmlBtns;
                        missionCard.insertAdjacentElement('afterend', div);
                    }

                } else {
                    // plus de commande, on affiche le message d'attente
                    if (banner) banner.innerHTML = 'EN ATTENTE';
                    if (missionCard) {
                        missionCard.innerHTML = '<div class="mission-section" style="text-align: center; padding: 60px 20px;"><p style="font-size: 1.3em; color: rgba(255,255,255,0.5); margin-bottom: 10px;">📡 Aucune mission en cours</p><p style="color: rgba(255,255,255,0.3); font-size: 0.9em;">En attente d\'une nouvelle livraison...</p></div>';
                    }
                    if (confirmZone) {
                        confirmZone.innerHTML = ''; // on cache les boutons
                    }
                }
            })
            .catch(e => {
                console.error('Erreur chargement suivante', e);
            });
    }

    // protection contre les injections xss (car on insere via innerhtml)
    function echapperHtml(texte) {
        const map = { '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#039;' };
        return String(texte).replace(/[&<>"']/g, m => map[m]);
    }
});
