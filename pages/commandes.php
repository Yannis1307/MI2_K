<?php
// session
// (géré par functions.php)
// fonctions json
require_once 'includes/functions.php';

// controle d'acces
if (!isset($_SESSION['user']) || $_SESSION['user']['role'] !== 'restaurateur') {
    header('Location: connexion.php');
    exit;
}

// plus de traitement POST ici, tout passe en asynchrone maintenant
?>
<!DOCTYPE html>
<html lang="fr">

<head>
    <link rel="icon" href="../images/logo.png">
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>La Table des Jedi - Poste de Préparation</title>
    <!-- styles specifiques -->
    <link rel="stylesheet" href="../css/commandes.css">
</head>

<body>


    <!-- en-tete du dashboard -->
    <header class="kds-header">
        <div class="header-left">
            <img src="../images/logo.png" alt="Logo" class="kds-logo">
        </div>
        <div class="header-center">
            <h1 class="kds-title">POSTE DE PRÉPARATION — CANTINA 1</h1>
            <span class="kds-live"><span class="live-dot"></span> EN SERVICE</span>
        </div>
        <div class="header-right">
            <a href="accueil.php" class="btn-kds btn-back" style="margin-right: 10px;">🏠 Vue Client</a>
            <a href="deconnexion.php" class="btn-kds btn-back">🚪 Déconnexion</a>
            <button class="btn-kds btn-close-service">🔴 Fermer le service</button>
        </div>
    </header>


    <main class="kds-main">


        <!-- commandes a preparer -->

        <section class="zone zone-pending">
            <?php
            $commandes = read_json('commandes.json');
            $en_attente = [];
            $en_livraison_all = [];
            $terminees_all = [];
            foreach ($commandes as $c) {
                if (in_array($c['statut'], ['en attente', 'en préparation', 'prête'])) {
                    $en_attente[] = $c;
                } elseif (in_array($c['statut'], ['en livraison', 'à récupérer'])) {
                    $en_livraison_all[] = $c;
                } elseif (in_array($c['statut'], ['livré', 'livre', 'abandonné'])) {
                    $terminees_all[] = $c;
                }
            }
            
            // on garde les 5 dernieres
            $terminees_5 = array_slice($terminees_all, -5);
            
            // listes consolidees
            $en_livraison = array_merge($en_livraison_all, $terminees_5);

            // liste des livreurs
            $users_all = read_json('users.json');
            $liste_livreurs = [];
            foreach ($users_all as $u) {
                if ($u['role'] === 'livreur') {
                    $liste_livreurs[] = $u;
                }
            }
            ?>
            <div class="zone-header">
                <h2>🔥 COMMANDES EN ATTENTE</h2>
                <span class="zone-count"><?= count($en_attente) ?> ticket<?= count($en_attente) > 1 ? 's' : '' ?></span>
            </div>

            <div class="tickets-list">

                <?php if (empty($en_attente)) : ?>
                <!-- liste vide -->
                <div style="text-align: center; padding: 40px; color: rgba(255,255,255,0.4);">
                    <p>Aucune commande en attente.</p>
                </div>
                <?php else : ?>
                <?php foreach ($en_attente as $cmd) : ?>
                <?php 
                    $is_planifiee = (isset($cmd['type']) && $cmd['type'] === 'planifiee');
                    $ticket_class = $is_planifiee ? 'ticket-planned' : 'ticket-normal';
                ?>
                <!-- carte d'une commande -->
                <article class="ticket <?= $ticket_class ?>" data-cmd-id="<?= htmlspecialchars($cmd['id']) ?>">
                    <div class="ticket-header">
                        <div class="ticket-id-group">
                            <span class="ticket-number">#<?= htmlspecialchars($cmd['id']) ?></span>
                            <span class="ticket-time">Créée: <?= htmlspecialchars($cmd['heure']) ?></span>
                            <?php if ($is_planifiee && !empty($cmd['heure_livraison'])) : ?>
                                <?php 
                                    $hl = $cmd['heure_livraison'];
                                    if (strpos($hl, 'T') !== false) {
                                        $hl = date('d/m/Y H:i', strtotime($hl));
                                    }
                                ?>
                                <span class="ticket-timer" style="color: #00bfff; border-color: #00bfff; background: rgba(0, 191, 255, 0.08);">
                                    📅 Prévue: <?= htmlspecialchars($hl) ?>
                                </span>
                            <?php endif; ?>
                        </div>
                    </div>
                    <div class="ticket-body">
                        <ul class="ticket-items">
                            <?php // liste des plats ?>
                            <?php if (isset($cmd['plats'])) : ?>
                                <?php foreach ($cmd['plats'] as $p) : ?>
                                <li><span class="item-qty"><?= $p['quantite'] ?>x</span> <?= htmlspecialchars($p['nom']) ?></li>
                                <?php endforeach; ?>
                            <?php endif; ?>
                            
                            <?php // liste des menus avec le detail ?>
                            <?php if (isset($cmd['menus'])) : ?>
                                <?php foreach ($cmd['menus'] as $m) : ?>
                                <li>
                                    <span class="item-qty"><?= $m['quantite'] ?>x</span> <?= htmlspecialchars($m['nom']) ?> <span style="font-size: 0.7em; color:#aaa;">(Menu)</span>
                                    <?php if (isset($m['plats_details']) && !empty($m['plats_details'])) : ?>
                                        <ul style="list-style: none; padding-left: 20px; font-size: 0.9em; opacity: 0.8; margin-top: 4px;">
                                            <?php foreach ($m['plats_details'] as $nom_plat) : ?>
                                                <li>- <?= htmlspecialchars($nom_plat) ?></li>
                                            <?php endforeach; ?>
                                        </ul>
                                    <?php endif; ?>
                                </li>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </ul>
                    </div>
                    <div class="ticket-footer">
                        <span class="ticket-client">👤 <?= htmlspecialchars($cmd['login_client']) ?> (<span class="js-statut-label"><?= htmlspecialchars(strtoupper($cmd['statut'])) ?></span>)</span>
                        <?php $est_emporter = (isset($cmd['mode_retrait']) && $cmd['mode_retrait'] === 'emporter'); ?>
                        <?php 
                        // assignation du livreur en asynchrone : calcul des etats
                        $stat = $cmd['statut'];
                        $is_att = ($stat === 'en attente');
                        $is_prp = ($stat === 'en préparation');
                        $is_prt = ($stat === 'prête');
                        ?>
                        <div class="cmd-actions-container" data-cmd="<?= htmlspecialchars($cmd['id']) ?>" style="display:flex; gap:10px; align-items:center; flex-wrap:wrap; margin-top: 10px;">
                            
                            <!-- bouton preparer en asynchrone -->
                            <button type="button" class="btn-ready js-action-cmd" data-cmd="<?= htmlspecialchars($cmd['id']) ?>" data-action="preparation" style="background: rgba(255, 170, 0, 0.2); border-color: #ffaa00; color: #ffaa00; <?= $is_att ? '' : 'display:none;' ?>">👨‍🍳 PRÉPARER</button>
                            
                            <!-- bouton prete en asynchrone -->
                            <button type="button" class="btn-ready js-action-cmd" data-cmd="<?= htmlspecialchars($cmd['id']) ?>" data-action="prete" style="background: rgba(0, 191, 255, 0.2); border-color: #00bfff; color: #00bfff; <?= $is_prp ? '' : 'display:none;' ?>">🔔 PRÊTE</button>
                            
                            <?php if (!$est_emporter) : ?>
                                <div class="js-livraison-group" style="display: <?= $is_prt ? 'inline-flex' : 'none' ?>; gap:10px; align-items:center;">
                                    <!-- select livreur -->
                                    <select class="js-select-livreur" data-cmd="<?= htmlspecialchars($cmd['id']) ?>" style="padding: 6px; border-radius: 4px; background: rgba(0,0,0,0.4); color: #fff; border: 1px solid rgba(255,255,255,0.2); font-size: 0.9em; width: 100px;">
                                        <option value="">Livreur auto</option>
                                        <?php foreach ($liste_livreurs as $livreur) : ?>
                                            <option value="<?= $livreur['id'] ?>">👤 <?= htmlspecialchars($livreur['login']) ?></option>
                                        <?php endforeach; ?>
                                    </select>
                                    <!-- bouton en livraison en asynchrone -->
                                    <button type="button" class="btn-ready js-action-cmd" data-cmd="<?= htmlspecialchars($cmd['id']) ?>" data-action="livraison">🚀 EN LIVRAISON</button>
                                </div>
                            <?php else : ?>
                                <div class="js-emporter-group" style="display: <?= $is_prt ? 'inline-flex' : 'none' ?>; gap:10px; align-items:center;">
                                    <span style="padding: 6px 12px; border-radius: 4px; background: rgba(255, 140, 0, 0.2); color: #ff8c00; border: 1px solid rgba(255, 140, 0, 0.5); font-size: 0.85em; font-weight: bold;">🥡 À EMPORTER</span>
                                    <!-- bouton remise au client en asynchrone -->
                                    <button type="button" class="btn-ready js-action-cmd" data-cmd="<?= htmlspecialchars($cmd['id']) ?>" data-action="a_recuperer" style="background: rgba(0,255,136,0.1); border-color: #00ff88; color: #00ff88;">✅ À RÉCUPÉRER</button>
                                </div>
                            <?php endif; ?>

                            <!-- bouton abandonner en asynchrone -->
                            <button type="button" class="btn-ready js-action-cmd js-btn-abandonner" data-cmd="<?= htmlspecialchars($cmd['id']) ?>" data-action="abandonner" style="padding: 6px 12px; font-size: 0.8em; background: rgba(255,50,50,0.1); border-color: #ff4444; color: #ff4444;" title="Marquer comme Abandonnée">❌ ABANDONNÉE</button>
                        </div>
                    </div>
                </article>
                <?php endforeach; ?>
                <?php endif; ?>

            </div>
        </section>


        <!-- expéditions en cours ou terminées -->

        <section class="zone zone-delivery">
            <div class="zone-header">
                <h2>🚀 STATUT DE LIVRAISON</h2>
                <span class="zone-count"><?= count($en_livraison) ?> commandes</span>
            </div>

            <div class="delivery-list">

                <?php if (empty($en_livraison)) : ?>
                <!-- liste vide -->
                <div style="text-align: center; padding: 40px; color: rgba(255,255,255,0.4);">
                    <p>Aucune livraison en cours.</p>
                </div>
                <?php else : ?>
                <?php foreach ($en_livraison as $cmd) : ?>
                <?php $is_planifiee = (isset($cmd['type']) && $cmd['type'] === 'planifiee'); ?>
                <!-- affichage d'une livraison -->
                <div class="delivery-card" <?= $cmd['statut'] === 'abandonné' ? 'style="border-left: 4px solid #ff4444;"' : (in_array($cmd['statut'], ['livré', 'livre', 'à récupérer']) ? 'style="border-left: 4px solid #00ff88;"' : ($is_planifiee ? 'style="border-left: 4px solid #00bfff;"' : '')) ?>>
                    <div class="delivery-info">
                        <span class="delivery-id">#<?= htmlspecialchars($cmd['id']) ?></span>
                        <span class="delivery-status" <?= $cmd['statut'] === 'abandonné' ? 'style="color: #ff4444;"' : (in_array($cmd['statut'], ['livré', 'livre', 'à récupérer']) ? 'style="color: #00ff88;"' : '') ?>>
                            <?php if ($cmd['statut'] === 'abandonné') : ?>
                                ❌ Abandonnée
                            <?php elseif ($cmd['statut'] === 'livré' || $cmd['statut'] === 'livre') : ?>
                                ✅ Livrée
                            <?php elseif ($cmd['statut'] === 'à récupérer') : ?>
                                🥡 À récupérer au comptoir
                            <?php else : ?>
                                <span class="pulse-dot"></span> En transit
                            <?php endif; ?>
                        </span>
                    </div>
                    <div class="delivery-details">
                        <div style="display: flex; flex-direction: column; gap: 4px;">
                            <span class="delivery-driver">👤 <?= htmlspecialchars($cmd['login_client']) ?></span>
                            <?php if ($is_planifiee && !empty($cmd['heure_livraison'])) : ?>
                                <?php 
                                    $hl = $cmd['heure_livraison'];
                                    if (strpos($hl, 'T') !== false) {
                                        $hl = date('d/m/Y H:i', strtotime($hl));
                                    }
                                ?>
                                <span style="font-size: 0.75rem; color: #00bfff; font-weight: bold;">📅 <?= htmlspecialchars($hl) ?></span>
                            <?php endif; ?>
                        </div>
                        <span class="delivery-dest" style="text-align: right; max-width: 60%; word-break: break-word;">→ <?= htmlspecialchars(isset($cmd['adresse']) ? $cmd['adresse'] : 'Adresse inconnue') ?></span>
                    </div>
                    <?php if (in_array($cmd['statut'], ['en livraison', 'à récupérer'])) : ?>
                    <div style="display:flex; gap:10px; margin-top: 15px;">
                        <button type="button" class="btn-ready js-action-cmd" data-cmd="<?= htmlspecialchars($cmd['id']) ?>" data-action="livrer" style="padding: 6px 12px; font-size: 0.85em; background: rgba(0, 255, 136, 0.1); border: 1px solid #00ff88; color: #00ff88;">✅ Livrée</button>
                        <button type="button" class="btn-ready js-action-cmd" data-cmd="<?= htmlspecialchars($cmd['id']) ?>" data-action="abandonner" style="padding: 6px 12px; font-size: 0.85em; background: rgba(255, 50, 50, 0.1); border: 1px solid #ff4444; color: #ff4444;">❌ Abandonnée</button>
                    </div>
                    <?php endif; ?>
                </div>
                <?php endforeach; ?>
                <?php endif; ?>

            </div>
        </section>

    </main>

    <!-- bas de page -->
    <footer class="kds-footer">
        <p>&copy; 2026 La Table des Jedi — Poste de Préparation · Interface Cantina · Projet Creative-Yumland (Phase #3)
        </p>
    </footer>

    <!-- script de changement de statut en asynchrone -->
    <script>
    document.addEventListener('DOMContentLoaded', function() {

        // fonction appelee lors du clic sur un bouton d'action
        window.actionClickHandler = function() {
            var btn = this;
            var idCmd = btn.getAttribute('data-cmd');
            var action = btn.getAttribute('data-action');

            // recuperation du livreur manuel si c'est une mise en livraison
            var idLivreur = '';
            if (action === 'livraison') {
                var selectLivreur = document.querySelector('.js-select-livreur[data-cmd="' + idCmd + '"]');
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
            .then(function(r) { return r.json(); })
            .then(function(data) {
                if (data.success) {
                    // on met a jour le badge et le bouton sans recharger la page
                    var article = btn.closest('.ticket') || btn.closest('.delivery-card');
                    if (article) {
                        var statutLabel = article.querySelector('.js-statut-label') || article.querySelector('.delivery-status');
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
                        var actionsContainer = article.querySelector('.cmd-actions-container');
                        if (actionsContainer) {
                            if (action === 'preparation') {
                                var btnPrete = actionsContainer.querySelector('[data-action="prete"]');
                                if (btnPrete) btnPrete.style.display = 'inline-block';
                            } else if (action === 'prete') {
                                var livGroup = actionsContainer.querySelector('.js-livraison-group');
                                var empGroup = actionsContainer.querySelector('.js-emporter-group');
                                if (livGroup) livGroup.style.display = 'inline-flex';
                                if (empGroup) empGroup.style.display = 'inline-flex';
                            } else if (action === 'livraison' || action === 'a_recuperer' || action === 'abandonner') {
                                // on cache tout le conteneur
                                actionsContainer.style.display = 'none';
                            }
                        }
                        
                        // verification du type de commande avant deplacement (si finalisation preparation)
                        if (action === 'livraison' || action === 'a_recuperer') {
                            var deliveryList = document.querySelector('.delivery-list');
                            if (deliveryList && article.classList.contains('ticket')) {
                                var clientName = article.querySelector('.ticket-client').textContent.replace('👤 ', '').split(' (')[0];
                                var nomLivreur = "À récupérer au comptoir";
                                var txtStatut = (action === 'livraison') ? '<span class="pulse-dot"></span> En transit' : '🥡 À récupérer au comptoir';
                                var colorBorder = (action === 'a_recuperer') ? '#00ff88' : '';
                                
                                if (action === 'livraison') {
                                    var select = article.querySelector('.js-select-livreur');
                                    if (select && select.selectedIndex >= 0 && select.options[select.selectedIndex].value !== '') {
                                        nomLivreur = select.options[select.selectedIndex].text.replace('👤 ', '');
                                    } else {
                                        nomLivreur = "Livreur assigné";
                                    }
                                }

                                // on construit la nouvelle carte pour la colonne de droite
                                var newCard = document.createElement('div');
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
                                var newBtns = newCard.querySelectorAll('.js-action-cmd');
                                for(var k=0; k<newBtns.length; k++) {
                                    newBtns[k].addEventListener('click', window.actionClickHandler);
                                }
                                
                                // on deplace la carte dans la colonne de droite
                                var emptyMsg = deliveryList.querySelector('p');
                                if (emptyMsg && emptyMsg.textContent.indexOf('Aucune') !== -1) {
                                    deliveryList.innerHTML = '';
                                }
                                deliveryList.insertBefore(newCard, deliveryList.firstChild);
                                
                                // suppression de l'ancienne carte de la colonne centrale
                                article.remove();
                                
                                // mise a jour des compteurs
                                var zoneCountPrep = document.querySelector('.zone-pending .zone-count');
                                if (zoneCountPrep) {
                                    var currentCount = parseInt(zoneCountPrep.textContent) || 1;
                                    zoneCountPrep.textContent = Math.max(0, currentCount - 1) + ' ticket' + (currentCount - 1 > 1 ? 's' : '');
                                }
                                var zoneCountDel = document.querySelector('.zone-delivery .zone-count');
                                if (zoneCountDel) {
                                    var delCount = parseInt(zoneCountDel.textContent) || 0;
                                    zoneCountDel.textContent = (delCount + 1) + ' commandes';
                                }
                            }
                        }

                        // si c'est un bouton dans delivery-card (marquer comme livree ou abandonnee)
                        var parentBtns = btn.parentNode;
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
            .catch(function(e) {
                console.error(e);
                alert('Erreur réseau.');
                btn.disabled = false;
                btn.style.opacity = '1';
            });
        };

        // on attache l'ecouteur sur tous les boutons d'action initiaux
        var actionBtns = document.querySelectorAll('.js-action-cmd');
        for (var i = 0; i < actionBtns.length; i++) {
            actionBtns[i].addEventListener('click', window.actionClickHandler);
        }

    });
    </script>

</body>

</html>