<?php
// session
session_start();

// fonctions json
require_once 'includes/functions.php';

// controle d'acces
if (!isset($_SESSION['user']) || $_SESSION['user']['role'] !== 'livreur') {
    header('Location: connexion.php');
    exit;
}

// traitements des actions (confirmer livraison, abandon)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['id_commande'])) {
    $id_cmd = trim($_POST['id_commande']);
    $action = isset($_POST['action']) ? $_POST['action'] : 'livrer';
    $commandes = read_json('commandes.json');
    $id_livreur = $_SESSION['user']['id'];

    foreach ($commandes as $index => $cmd) {
        if ($cmd['id'] === $id_cmd && $cmd['statut'] === 'en livraison' && $cmd['id_livreur'] == $id_livreur) {
            if ($action === 'abandonner') {
                $commandes[$index]['statut'] = 'abandonné';
            } else {
                $commandes[$index]['statut'] = 'livré';
            }
            write_json('commandes.json', $commandes);
            break;
        }
    }

    // evite resoumission
    header('Location: livraison.php');
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
        <a href="deconnexion.php" class="btn-header-action" style="width: auto; padding: 0 14px; color: #ff4444; border-color: rgba(255, 68, 68, 0.3); background: rgba(255, 68, 68, 0.08); font-size: 0.75rem; font-weight: bold; text-transform: uppercase;">🚪 Déconnexion</a>
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
                <form method="POST" action="livraison.php" style="width: 100%;">
                    <input type="hidden" name="id_commande" value="<?= htmlspecialchars($mission['id']) ?>">
                    <button type="submit" name="action" value="livrer" class="btn-confirm-delivery" style="width: 100%; margin-bottom: 10px;">✅ CONFIRMER LA LIVRAISON</button>
                    <button type="submit" name="action" value="abandonner" class="btn-help" style="width: 100%; background: rgba(255, 50, 50, 0.1); border: 1px solid #ff4444; color: #ff4444;">❌ SIGNALER COMME ABANDONNÉE</button>
                </form>
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
        <p>&copy; 2026 La Table des Jedi — Interface Livreur · Projet Creative-Yumland (Phase #1)</p>
    </footer>

</body>

</html>