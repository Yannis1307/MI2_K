<?php
// session
// (géré par functions.php)
// fonctions json
require_once 'includes/functions.php';

// controle d'acces
if (!isset($_SESSION['user']) || $_SESSION['user']['role'] !== 'livreur') {
    header('Location: connexion.php');
    exit;
}
?>
<!DOCTYPE html>
<html lang="fr">

<head>
    <link rel="icon" href="../images/logo.png">
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>La Table des Jedi - Livraison</title>
    <!-- styles specifiques -->
    <link rel="stylesheet" href="../css/livraison.css">
</head>

<body>

    <!-- entete -->
    <header class="mobile-header">
        <img src="../images/logo.png" alt="Logo" class="mobile-logo">
        <div class="header-status">
            <span class="status-online"><span class="online-dot"></span> EN LIGNE</span>
        </div>
        <div style="display: flex; gap: 10px;">
            <a href="accueil.php" class="btn-header-action" style="width: auto; padding: 0 14px; color: #6498ff; border-color: rgba(100, 160, 255, 0.3); background: rgba(100, 160, 255, 0.08); font-size: 0.75rem; font-weight: bold; text-transform: uppercase;">🏠 Vue Client</a>
            <a href="deconnexion.php" class="btn-header-action" style="width: auto; padding: 0 14px; color: #ff4444; border-color: rgba(255, 68, 68, 0.3); background: rgba(255, 68, 68, 0.08); font-size: 0.75rem; font-weight: bold; text-transform: uppercase;">🚪 Déconnexion</a>
        </div>
    </header>

    <!-- corps de page -->
    <main class="mobile-main">
        <div class="mobile-container">

            <!-- titre -->
            <div class="mission-banner">
                <span class="mission-icon">📡</span>
                <h1>MISSION EN COURS</h1>
            </div>

            <!-- infos de mission -->
            <section class="mission-card">

                <?php
                // recherche de la mission en cours
                $commandes = read_json('commandes.json');
                $id_livreur = $_SESSION['user']['id'];
                $mission = null;
                foreach ($commandes as $cmd) {
                    if ($cmd['statut'] === 'en livraison' && isset($cmd['id_livreur']) && $cmd['id_livreur'] == $id_livreur) {
                        $mission = $cmd;
                        break;
                    }
                }
                ?>

                <?php if ($mission) : ?>
                <!-- informations du client -->
                <div class="mission-section client-section">
                    <div class="client-info">
                        <img src="../images/lando_avatar.png" alt="Avatar Client" class="client-avatar">
                        <div class="client-details">
                            <span class="client-name"><?= htmlspecialchars($mission['login_client']) ?></span>
                            <span class="client-tag">📦 Commande #<?= htmlspecialchars($mission['id']) ?></span>
                        </div>
                    </div>
                </div>

                <!-- adresse de depot -->
                <div class="mission-section address-section">
                    <h2 class="section-label">📍 Adresse de Livraison</h2>
                    <p class="address-text">
                        <?= htmlspecialchars(isset($mission['adresse']) ? $mission['adresse'] : 'Adresse non renseignée') ?>
                    </p>
                    <a href="https://www.google.com/maps/search/?api=1&query=<?= urlencode(isset($mission['adresse']) ? $mission['adresse'] : '') ?>"
                        target="_blank" class="btn-gps">
                        🌍 OUVRIR LA CARTE GALACTIQUE
                    </a>
                </div>

                <!-- donnees d'acces -->
                <div class="mission-section access-section">
                    <h2 class="section-label">🔑 Détails d'Accès</h2>
                    <div class="access-grid">
                        <div class="access-bubble">
                            <span class="access-icon">🔢</span>
                            <span class="access-label">Digicode</span>
                            <span class="access-value"><?php echo isset($mission['code_interphone']) && $mission['code_interphone'] !== '' ? htmlspecialchars($mission['code_interphone']) : 'Non renseigné'; ?></span>
                        </div>
                        <div class="access-bubble">
                            <span class="access-icon">🏢</span>
                            <span class="access-label">Étage</span>
                            <span class="access-value"><?php echo isset($mission['etage']) && $mission['etage'] !== '' ? htmlspecialchars($mission['etage']) : 'Non renseigné'; ?></span>
                        </div>
                        <div class="access-bubble access-phone">
                            <span class="access-icon">📞</span>
                            <span class="access-label">Téléphone</span>
                            <span class="access-value"><?= htmlspecialchars(!empty($mission['telephone_client']) ? $mission['telephone_client'] : '—') ?></span>
                        </div>
                    </div>
                </div>

                <!-- detail du colis -->
                <div class="mission-section order-section">
                    <h2 class="section-label">📦 Commande #<?= htmlspecialchars($mission['id']) ?></h2>
                    <ul class="order-list">
                        <?php // plats simples ?>
                        <?php if(isset($mission['plats'])) : ?>
                            <?php foreach ($mission['plats'] as $p) : ?>
                            <li><span class="qty"><?= $p['quantite'] ?>x</span> <?= htmlspecialchars($p['nom']) ?></li>
                            <?php endforeach; ?>
                        <?php endif; ?>
                        
                        <?php // menus ?>
                        <?php if(isset($mission['menus'])) : ?>
                            <?php foreach ($mission['menus'] as $m) : ?>
                            <li>
                                <span class="qty"><?= $m['quantite'] ?>x</span> <?= htmlspecialchars($m['nom']) ?>
                                <?php if (isset($m['plats_details']) && !empty($m['plats_details'])) : ?>
                                    <ul style="list-style: none; padding-left: 20px; font-size: 0.85em; opacity: 0.8; margin-top: 4px;">
                                        <?php foreach ($m['plats_details'] as $nom_plat) : ?>
                                            <li>- <?= htmlspecialchars($nom_plat) ?></li>
                                        <?php endforeach; ?>
                                    </ul>
                                <?php endif; ?>
                            </li>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </ul>
                    <div class="order-total">
                        <span>Total</span>
                        <span class="total-price"><?= number_format($mission['total'], 2, ',', '') ?> ₹</span>
                    </div>
                </div>

                <?php else : ?>
                <!-- attente -->
                <div class="mission-section" style="text-align: center; padding: 60px 20px;">
                    <p style="font-size: 1.3em; color: rgba(255,255,255,0.5); margin-bottom: 10px;">📡 Aucune mission en cours</p>
                    <p style="color: rgba(255,255,255,0.3); font-size: 0.9em;">En attente d'une nouvelle livraison...</p>
                </div>
                <?php endif; ?>

            </section>

            <!-- actions -->
            <?php if ($mission) : ?>
            <div class="confirm-zone" style="display: flex; flex-direction: column; gap: 10px;">
                <div style="width: 100%;">
                    <button type="button" data-cmd="<?= htmlspecialchars($mission['id']) ?>" data-action="livrer" class="btn-confirm-delivery js-action-livraison" style="width: 100%; margin-bottom: 10px;">✅ CONFIRMER LA LIVRAISON</button>
                    <button type="button" data-cmd="<?= htmlspecialchars($mission['id']) ?>" data-action="abandonner" class="btn-help js-action-livraison" style="width: 100%; background: rgba(255, 50, 50, 0.1); border: 1px solid #ff4444; color: #ff4444;">❌ SIGNALER COMME ABANDONNÉE</button>
                </div>
            </div>
            <?php endif; ?>

            <!-- support -->
            <div class="help-zone">
                <button class="btn-help" onclick="alert('Fonction de signalement en cours de développement')">⚠️ SIGNALER UN PROBLÈME</button>
            </div>

        </div>
    </main>

    <!-- bas de page -->
    <footer class="mobile-footer">
        <p>&copy; 2026 La Table des Jedi — Interface Livreur · Projet Creative-Yumland (Phase #3)</p>
    </footer>

    <!-- script de changement de statut en asynchrone -->
    <script>
    document.addEventListener('DOMContentLoaded', function() {
        
        // on utilise la delegation d'evenements pour cibler les boutons actuels et futurs
        document.addEventListener('click', function(e) {
            // on verifie si on a clique sur un bouton d'action
            if (e.target && e.target.classList.contains('js-action-livraison')) {
                var btn = e.target;
                var idCmd = btn.getAttribute('data-cmd');
                var action = btn.getAttribute('data-action');

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
                .then(function(r) { return r.json(); })
                .then(function(data) {
                    if (data.success) {
                        // suppression de la commande livree de l'ecran (message de succes temporaire)
                        var banner = document.querySelector('.mission-banner h1');
                        if (banner) {
                            banner.innerHTML = (action === 'livrer') ? 'MISSION ACCOMPLIE' : 'MISSION ANNULÉE';
                        }
                        
                        var container = document.querySelector('.confirm-zone');
                        if (container) {
                            container.innerHTML = '<div style="text-align: center; padding: 20px; font-size: 1.2em; color: ' + (action === 'livrer' ? '#00ff88' : '#ff4444') + ';">' + 
                                                  (action === 'livrer' ? '✅ Colis livré avec succès.' : '❌ Colis signalé comme abandonné.') + '</div>';
                        }

                        // passage a la commande suivante (apres une seconde pour laisser l'utilisateur lire)
                        setTimeout(function() {
                            chargerProchaineLivraison();
                        }, 1000);
                    } else {
                        alert('Erreur: ' + data.message);
                        btn.disabled = false;
                        btn.style.opacity = '1';
                    }
                })
                .catch(function(err) {
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
            .then(function(r) { return r.json(); })
            .then(function(data) {
                var missionCard = document.querySelector('.mission-card');
                var confirmZone = document.querySelector('.confirm-zone');
                var banner = document.querySelector('.mission-banner h1');
                
                if (data.success && data.mission) {
                    var m = data.mission;
                    
                    if (banner) banner.innerHTML = 'MISSION EN COURS';
                    
                    // on reconstruit le html complet de la carte
                    var htmlCard = '';
                    
                    // client
                    htmlCard += '<div class="mission-section client-section">';
                    htmlCard += '<div class="client-info">';
                    htmlCard += '<img src="../images/lando_avatar.png" alt="Avatar Client" class="client-avatar">';
                    htmlCard += '<div class="client-details">';
                    htmlCard += '<span class="client-name">' + echapperHtml(m.login_client) + '</span>';
                    htmlCard += '<span class="client-tag">📦 Commande #' + echapperHtml(m.id) + '</span>';
                    htmlCard += '</div></div></div>';

                    // adresse
                    var adresse = m.adresse ? m.adresse : 'Adresse non renseignée';
                    htmlCard += '<div class="mission-section address-section">';
                    htmlCard += '<h2 class="section-label">📍 Adresse de Livraison</h2>';
                    htmlCard += '<p class="address-text">' + echapperHtml(adresse) + '</p>';
                    htmlCard += '<a href="https://www.google.com/maps/search/?api=1&query=' + encodeURIComponent(adresse) + '" target="_blank" class="btn-gps">🌍 OUVRIR LA CARTE GALACTIQUE</a>';
                    htmlCard += '</div>';

                    // acces
                    htmlCard += '<div class="mission-section access-section">';
                    htmlCard += '<h2 class="section-label">🔑 Détails d\'Accès</h2>';
                    htmlCard += '<div class="access-grid">';
                    
                    var code = (m.code_interphone && m.code_interphone !== '') ? m.code_interphone : 'Non renseigné';
                    var etage = (m.etage && m.etage !== '') ? m.etage : 'Non renseigné';
                    var tel = (m.telephone_client && m.telephone_client !== '') ? m.telephone_client : '—';
                    
                    htmlCard += '<div class="access-bubble"><span class="access-icon">🔢</span><span class="access-label">Digicode</span><span class="access-value">' + echapperHtml(code) + '</span></div>';
                    htmlCard += '<div class="access-bubble"><span class="access-icon">🏢</span><span class="access-label">Étage</span><span class="access-value">' + echapperHtml(etage) + '</span></div>';
                    htmlCard += '<div class="access-bubble access-phone"><span class="access-icon">📞</span><span class="access-label">Téléphone</span><span class="access-value">' + echapperHtml(tel) + '</span></div>';
                    htmlCard += '</div></div>';

                    // commande
                    htmlCard += '<div class="mission-section order-section">';
                    htmlCard += '<h2 class="section-label">📦 Commande #' + echapperHtml(m.id) + '</h2>';
                    htmlCard += '<ul class="order-list">';
                    
                    if (m.plats && m.plats.length > 0) {
                        for (var i = 0; i < m.plats.length; i++) {
                            htmlCard += '<li><span class="qty">' + m.plats[i].quantite + 'x</span> ' + echapperHtml(m.plats[i].nom) + '</li>';
                        }
                    }
                    
                    if (m.menus && m.menus.length > 0) {
                        for (var j = 0; j < m.menus.length; j++) {
                            htmlCard += '<li><span class="qty">' + m.menus[j].quantite + 'x</span> ' + echapperHtml(m.menus[j].nom);
                            if (m.menus[j].plats_details && m.menus[j].plats_details.length > 0) {
                                htmlCard += '<ul style="list-style: none; padding-left: 20px; font-size: 0.85em; opacity: 0.8; margin-top: 4px;">';
                                for (var k = 0; k < m.menus[j].plats_details.length; k++) {
                                    htmlCard += '<li>- ' + echapperHtml(m.menus[j].plats_details[k]) + '</li>';
                                }
                                htmlCard += '</ul>';
                            }
                            htmlCard += '</li>';
                        }
                    }
                    
                    htmlCard += '</ul>';
                    var totalForm = parseFloat(m.total).toFixed(2).replace('.', ',');
                    htmlCard += '<div class="order-total"><span>Total</span><span class="total-price">' + totalForm + ' ₹</span></div>';
                    htmlCard += '</div>';

                    // on injecte la nouvelle carte
                    if (missionCard) missionCard.innerHTML = htmlCard;

                    // on recree les boutons de validation avec la delegation d'evenement
                    var htmlBtns = '<div style="width: 100%;">';
                    htmlBtns += '<button type="button" data-cmd="' + echapperHtml(m.id) + '" data-action="livrer" class="btn-confirm-delivery js-action-livraison" style="width: 100%; margin-bottom: 10px;">✅ CONFIRMER LA LIVRAISON</button>';
                    htmlBtns += '<button type="button" data-cmd="' + echapperHtml(m.id) + '" data-action="abandonner" class="btn-help js-action-livraison" style="width: 100%; background: rgba(255, 50, 50, 0.1); border: 1px solid #ff4444; color: #ff4444;">❌ SIGNALER COMME ABANDONNÉE</button>';
                    htmlBtns += '</div>';

                    if (confirmZone) {
                        confirmZone.innerHTML = htmlBtns;
                    } else if (missionCard) {
                        // si la zone avait disparu, on la recree juste apres la carte
                        var div = document.createElement('div');
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
            .catch(function(e) {
                console.error('Erreur chargement suivante', e);
            });
        }

        // protection contre les injections xss (car on insere via innerHTML)
        function echapperHtml(texte) {
            var map = { '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#039;' };
            return String(texte).replace(/[&<>"']/g, function(m) { return map[m]; });
        }
    });
    </script>

</body>

</html>