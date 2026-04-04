<?php
// demarrage de la session
session_start();
// on charge les fonctions json
require_once 'includes/functions.php';

// === CONTROLE D'ACCES : livreur uniquement ===
if (!isset($_SESSION['user']) || $_SESSION['user']['role'] !== 'livreur') {
    header('Location: connexion.php');
    exit;
}

// === TRAITEMENT POST : confirmer la livraison ===
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['id_commande'])) {
    $id_cmd = trim($_POST['id_commande']);
    $commandes = read_json('commandes.json');
    $id_livreur = $_SESSION['user']['id'];

    foreach ($commandes as $index => $cmd) {
        if ($cmd['id'] === $id_cmd && $cmd['statut'] === 'en livraison' && $cmd['id_livreur'] == $id_livreur) {
            // on passe la commande en livré
            $commandes[$index]['statut'] = 'livré';
            write_json('commandes.json', $commandes);
            break;
        }
    }

    // redirect pour eviter le double POST
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
    <!-- interface autonome mobile : pas de common.css -->
    <link rel="stylesheet" href="../css/livraison.css">
</head>

<body>

    <!-- header mobile -->
    <header class="mobile-header">
        <img src="../images/logo.png" alt="Logo" class="mobile-logo">
        <div class="header-status">
            <span class="status-online"><span class="online-dot"></span> EN LIGNE</span>
        </div>
        <a href="accueil.php" class="btn-header-action">↩</a>
    </header>

    <!-- contenu principal mobile -->
    <main class="mobile-main">
        <div class="mobile-container">

            <!-- titre mission -->
            <div class="mission-banner">
                <span class="mission-icon">📡</span>
                <h1>MISSION EN COURS</h1>
            </div>

            <!-- carte de la mission -->
            <section class="mission-card">

                <?php
                // on recupere les commandes en livraison assignees a ce livreur
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
                <!-- client -->
                <div class="mission-section client-section">
                    <div class="client-info">
                        <img src="../images/lando_avatar.png" alt="Avatar Client" class="client-avatar">
                        <div class="client-details">
                            <span class="client-name"><?= htmlspecialchars($mission['login_client']) ?></span>
                            <span class="client-tag">📦 Commande #<?= htmlspecialchars($mission['id']) ?></span>
                        </div>
                    </div>
                </div>

                <!-- adresse + bouton gps -->
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

                <!-- details d'acces -->
                <div class="mission-section access-section">
                    <h2 class="section-label">🔑 Détails d'Accès</h2>
                    <div class="access-grid">
                        <div class="access-bubble">
                            <span class="access-icon">🔢</span>
                            <span class="access-label">Digicode</span>
                            <span class="access-value">—</span>
                        </div>
                        <div class="access-bubble">
                            <span class="access-icon">🏢</span>
                            <span class="access-label">Étage</span>
                            <span class="access-value">—</span>
                        </div>
                        <div class="access-bubble access-phone">
                            <span class="access-icon">📞</span>
                            <span class="access-label">Téléphone</span>
                            <span class="access-value">—</span>
                        </div>
                    </div>
                </div>

                <!-- detail de la commande -->
                <div class="mission-section order-section">
                    <h2 class="section-label">📦 Commande #<?= htmlspecialchars($mission['id']) ?></h2>
                    <ul class="order-list">
                        <?php foreach ($mission['plats'] as $p) : ?>
                        <li><span class="qty"><?= $p['quantite'] ?>x</span> <?= htmlspecialchars($p['nom']) ?></li>
                        <?php endforeach; ?>
                    </ul>
                    <div class="order-total">
                        <span>Total</span>
                        <span class="total-price"><?= number_format($mission['total'], 2, ',', '') ?> ₹</span>
                    </div>
                </div>

                <?php else : ?>
                <!-- aucune mission en cours -->
                <div class="mission-section" style="text-align: center; padding: 60px 20px;">
                    <p style="font-size: 1.3em; color: rgba(255,255,255,0.5); margin-bottom: 10px;">📡 Aucune mission en cours</p>
                    <p style="color: rgba(255,255,255,0.3); font-size: 0.9em;">En attente d'une nouvelle livraison...</p>
                </div>
                <?php endif; ?>

            </section>

            <!-- confirmation de livraison -->
            <?php if ($mission) : ?>
            <div class="confirm-zone">
                <form method="POST" action="livraison.php">
                    <input type="hidden" name="id_commande" value="<?= htmlspecialchars($mission['id']) ?>">
                    <button type="submit" class="btn-confirm-delivery">✅ CONFIRMER LA LIVRAISON</button>
                </form>
            </div>
            <?php endif; ?>

            <!-- bouton de secours -->
            <div class="help-zone">
                <button class="btn-help">⚠️ SIGNALER UN PROBLÈME</button>
            </div>

        </div>
    </main>

    <!-- footer mobile -->
    <footer class="mobile-footer">
        <p>&copy; 2026 La Table des Jedi — Interface Livreur · Projet Creative-Yumland (Phase #1)</p>
    </footer>

</body>

</html>