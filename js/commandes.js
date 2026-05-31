document.addEventListener('DOMContentLoaded', () => {

    // fonction appelee lors du clic sur un bouton d'action
    window.actionClickHandler = function () {
        const btn = this;
        const idCmd = btn.getAttribute('data-cmd');
        const action = btn.getAttribute('data-action');

        // recuperation du livreur manuel si c'est une mise en livraison
        let idLivreur = '';
        if (action === 'livraison') {
            const selectLivreur = document.querySelector('.js-select-livreur[data-cmd="' + idCmd + '"]');
            if (selectLivreur) {
                idLivreur = selectLivreur.value;
            }
        }

        // desactivation du bouton pour eviter le double clic
        btn.disabled = true;
        btn.style.opacity = '0.5';

        // envoi de la requete asynchrone
        fetch('../api/update_statut_commande.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({
                id_commande: idCmd,
                action_statut: action,
                id_livreur_manuel: idLivreur
            })
        })
            .then(r => r.json())
            .then(data => {
                if (data.success) {
                    // on met a jour le badge et le bouton sans recharger la page
                    const article = btn.closest('.ticket') || btn.closest('.delivery-card');
                    if (article) {
                        const statutLabel = article.querySelector('.js-statut-label') || article.querySelector('.delivery-status');
                        if (statutLabel) {
                            if (action === 'preparation') statutLabel.innerHTML = 'EN PRÉPARATION';
                            else if (action === 'prete') statutLabel.innerHTML = 'PRÊTE';
                            else if (action === 'livraison') statutLabel.innerHTML = '<span class="pulse-dot"></span> EN TRANSIT';
                            else if (action === 'a_recuperer') statutLabel.innerHTML = '🥡 À RÉCUPÉRER';
                            else if (action === 'livrer') statutLabel.innerHTML = '✅ LIVRÉE';
                            else if (action === 'abandonner') statutLabel.innerHTML = '❌ ABANDONNÉE';
                        }
                        // on cache le bouton utilise
                        btn.style.display = 'none';

                        // gestion de l'affichage progressif dans la colonne centrale
                        const actionsContainer = article.querySelector('.cmd-actions-container');
                        if (actionsContainer) {
                            if (action === 'preparation') {
                                const btnPrete = actionsContainer.querySelector('[data-action="prete"]');
                                if (btnPrete) btnPrete.style.display = 'inline-block';
                            } else if (action === 'prete') {
                                const livGroup = actionsContainer.querySelector('.js-livraison-group');
                                const empGroup = actionsContainer.querySelector('.js-emporter-group');
                                if (livGroup) livGroup.style.display = 'inline-flex';
                                if (empGroup) empGroup.style.display = 'inline-flex';
                            } else if (action === 'livraison' || action === 'a_recuperer' || action === 'abandonner') {
                                // on cache tout le conteneur
                                actionsContainer.style.display = 'none';
                            }
                        }

                        // verification du type de commande avant deplacement (si finalisation preparation)
                        if (action === 'livraison' || action === 'a_recuperer') {
                            const deliveryList = document.querySelector('.delivery-list');
                            if (deliveryList && article.classList.contains('ticket')) {
                                const clientName = article.querySelector('.ticket-client').textContent.replace('👤 ', '').split(' (')[0];
                                let nomLivreur = "À récupérer au comptoir";
                                const txtStatut = (action === 'livraison') ? '<span class="pulse-dot"></span> En transit' : '🥡 À récupérer au comptoir';
                                const colorBorder = (action === 'a_recuperer') ? '#00ff88' : '';

                                if (action === 'livraison') {
                                    const select = article.querySelector('.js-select-livreur');
                                    if (select && select.selectedIndex >= 0 && select.options[select.selectedIndex].value !== '') {
                                        nomLivreur = select.options[select.selectedIndex].text.replace('👤 ', '');
                                    } else {
                                        nomLivreur = "Livreur assigné";
                                    }
                                }

                                // on construit la nouvelle carte pour la colonne de droite
                                const newCard = document.createElement('div');
                                newCard.className = 'delivery-card';
                                if (colorBorder) newCard.style.borderLeft = '4px solid ' + colorBorder;

                                newCard.innerHTML = `
                                <div class="delivery-info">
                                    <span class="delivery-id">#` + idCmd + `</span>
                                    <span class="delivery-status" ` + (action === 'a_recuperer' ? 'style="color: #00ff88;"' : '') + `>` + txtStatut + `</span>
                                </div>
                                <div class="delivery-details">
                                    <div style="display: flex; flex-direction: column; gap: 4px;">
                                        <span class="delivery-driver">👤 ` + clientName + `</span>
                                    </div>
                                    <span class="delivery-dest" style="text-align: right; max-width: 60%; word-break: break-word;">→ ` + ((action === 'livraison') ? 'Livreur : ' + nomLivreur : 'Au comptoir') + `</span>
                                </div>
                                <div style="display:flex; gap:10px; margin-top: 15px;">
                                    <button type="button" class="btn-ready js-action-cmd" data-cmd="` + idCmd + `" data-action="livrer" style="padding: 6px 12px; font-size: 0.85em; background: rgba(0, 255, 136, 0.1); border: 1px solid #00ff88; color: #00ff88;">✅ Livrée</button>
                                    <button type="button" class="btn-ready js-action-cmd" data-cmd="` + idCmd + `" data-action="abandonner" style="padding: 6px 12px; font-size: 0.85em; background: rgba(255, 50, 50, 0.1); border: 1px solid #ff4444; color: #ff4444;">❌ Abandonnée</button>
                                </div>
                            `;

                                // on attache le gestionnaire d'event sur les nouveaux boutons
                                const newBtns = newCard.querySelectorAll('.js-action-cmd');
                                for (let k = 0; k < newBtns.length; k++) {
                                    newBtns[k].addEventListener('click', window.actionClickHandler);
                                }

                                // on deplace la carte dans la colonne de droite
                                const emptyMsg = deliveryList.querySelector('p');
                                if (emptyMsg && emptyMsg.textContent.indexOf('Aucune') !== -1) {
                                    deliveryList.innerHTML = '';
                                }
                                deliveryList.insertBefore(newCard, deliveryList.firstChild);

                                // suppression de l'ancienne carte de la colonne centrale
                                article.remove();

                                // mise a jour des compteurs
                                const zoneCountPrep = document.querySelector('.zone-pending .zone-count');
                                if (zoneCountPrep) {
                                    const currentCount = parseInt(zoneCountPrep.textContent) || 1;
                                    zoneCountPrep.textContent = Math.max(0, currentCount - 1) + ' ticket' + (currentCount - 1 > 1 ? 's' : '');
                                }
                                const zoneCountDel = document.querySelector('.zone-delivery .zone-count');
                                if (zoneCountDel) {
                                    const delCount = parseInt(zoneCountDel.textContent) || 0;
                                    zoneCountDel.textContent = (delCount + 1) + ' commandes';
                                }
                            }
                        }

                        // si c'est un bouton dans deliverycard (marquer comme livree ou abandonnee)
                        const parentBtns = btn.parentNode;
                        if (parentBtns && (action === 'livrer' || action === 'abandonner') && !actionsContainer) {
                            parentBtns.style.display = 'none';
                            if (article.classList.contains('delivery-card')) {
                                article.style.borderLeft = (action === 'livrer') ? '4px solid #00ff88' : '4px solid #ff4444';
                                if (statutLabel) statutLabel.style.color = (action === 'livrer') ? '#00ff88' : '#ff4444';
                            }
                        }
                    }
                } else {
                    alert('Erreur: ' + data.message);
                    btn.disabled = false;
                    btn.style.opacity = '1';
                }
            })
            .catch(e => {
                console.error(e);
                alert('Erreur réseau.');
                btn.disabled = false;
                btn.style.opacity = '1';
            });
    };

    // on attache l'ecouteur sur tous les boutons d'action initiaux
    const actionBtns = document.querySelectorAll('.js-action-cmd');
    for (let i = 0; i < actionBtns.length; i++) {
        actionBtns[i].addEventListener('click', window.actionClickHandler);
    }

});
