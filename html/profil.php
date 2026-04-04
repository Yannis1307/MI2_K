<?php
// variables de configuration pour le header
$page_title = 'Mon Profil';
$page_css = 'profil.css';
$page_id = 'profil';

// on charge les fonctions utilitaires json
require_once 'includes/functions.php';

// === CONTROLE D'ACCES : connexion obligatoire ===
if (!isset($_SESSION['user'])) {
    header('Location: connexion.php');
    exit;
}

// on charge les données completes de l'utilisateur connecte depuis users.json
$users_all = read_json('users.json');
$user_data = null;
foreach ($users_all as $u) {
    if ($u['id'] == $_SESSION['user']['id']) {
        $user_data = $u;
        break;
    }
}

// securite : si l'utilisateur n'est pas retrouve dans le json, on deconnecte
if (!$user_data) {
    session_destroy();
    header('Location: connexion.php');
    exit;
}

// calcul du niveau de fidelite selon les points
$points_fidelite = isset($user_data['points_fidelite']) ? intval($user_data['points_fidelite']) : 0;

if ($points_fidelite < 500) {
    $niveau_fidelite = 'Padawan';
    $points_prec = 0;
    $points_prochain = 500;
    $niveau_suivant = 'Chevalier Jedi';
} elseif ($points_fidelite < 1000) {
    $niveau_fidelite = 'Chevalier Jedi';
    $points_prec = 500;
    $points_prochain = 1000;
    $niveau_suivant = 'Maître Jedi';
} elseif ($points_fidelite < 2000) {
    $niveau_fidelite = 'Maître Jedi';
    $points_prec = 1000;
    $points_prochain = 2000;
    $niveau_suivant = 'Grand Maître';
} else {
    $niveau_fidelite = 'Grand Maître';
    $points_prec = 2000;
    $points_prochain = 2000;
}

// calcul du pourcentage de progression vers le niveau suivant
$pourcentage_niveau = ($points_prochain > $points_prec)
    ? min(100, round(($points_fidelite - $points_prec) / ($points_prochain - $points_prec) * 100))
    : 100;

// on inclut le header commun
require_once 'includes/header.php';
?>

<main>
    <!-- message flash apres une commande validee -->
    <?php if (isset($_SESSION['flash_success'])): ?>
        <div
            style="background: rgba(0,255,100,0.15); border: 1px solid rgba(0,255,100,0.4); padding: 15px; border-radius: 10px; text-align: center; color: #7fff7f; max-width: 900px; margin: 20px auto 0;">
            ✅ <?= htmlspecialchars($_SESSION['flash_success']) ?>
        </div>
        <?php unset($_SESSION['flash_success']); ?>
    <?php endif; ?>

    <!-- titre du dashboard -->
    <section class="dashboard-header">
        <h1>🛡️ Tableau de Bord du Jedi</h1>
        <p class="subtitle">
            Bienvenue, <?= htmlspecialchars($user_data['prenom'] ?: $user_data['login']) ?>. Votre statut dans la
            galaxie.
        </p>
    </section>

    <div class="dashboard-grid">

        <!-- panneau 1 : carte d'identite -->
        <section class="panel panel-identity" id="identity-card">
            <div class="panel-header">
                <h2>📋 Carte d'Identité Galactique</h2>
            </div>
            <div class="panel-body">

                <!-- avatar du joueur -->
                <div class="avatar-section">
                    <div class="avatar-frame">
                        <img src="../images/<?= htmlspecialchars($user_data['avatar'] ?? 'han_avatar.png') ?>"
                            alt="Avatar de <?= htmlspecialchars($user_data['login']) ?>" class="profile-avatar">
                        <button class="btn-edit-avatar" title="Modifier l'avatar">✏️</button>
                    </div>
                    <span class="rank-badge"><?= htmlspecialchars($niveau_fidelite) ?></span>
                </div>

                <!-- champs editables (edition effective en phase 3) -->
                <div class="identity-fields">

                    <div class="field-row">
                        <span class="field-label">Pseudo</span>
                        <span class="field-value"><?= htmlspecialchars($user_data['login'] ?? '—') ?></span>
                        <button class="btn-edit" title="Modifier">✏️</button>
                    </div>

                    <div class="field-row">
                        <span class="field-label">Prénom</span>
                        <span class="field-value"><?= htmlspecialchars($user_data['prenom'] ?: '—') ?></span>
                        <button class="btn-edit" title="Modifier">✏️</button>
                    </div>

                    <div class="field-row">
                        <span class="field-label">Nom</span>
                        <span class="field-value"><?= htmlspecialchars($user_data['nom'] ?? '—') ?></span>
                        <button class="btn-edit" title="Modifier">✏️</button>
                    </div>

                    <div class="field-row">
                        <span class="field-label">Email</span>
                        <span class="field-value"><?= htmlspecialchars($user_data['email'] ?? 'Non renseigné') ?></span>
                        <button class="btn-edit" title="Modifier">✏️</button>
                    </div>

                    <div class="field-row">
                        <span class="field-label">Téléphone</span>
                        <span
                            class="field-value"><?= htmlspecialchars($user_data['telephone'] ?: 'Non renseigné') ?></span>
                        <button class="btn-edit" title="Modifier">✏️</button>
                    </div>

                    <div class="field-row">
                        <span class="field-label">Adresse</span>
                        <span class="field-value"><?= htmlspecialchars($user_data['adresse'] ?? '—') ?></span>
                        <button class="btn-edit" title="Modifier">✏️</button>
                    </div>

                    <div class="field-row">
                        <span class="field-label">Membre depuis</span>
                        <span class="field-value"><?= htmlspecialchars($user_data['date_inscription'] ?? '—') ?></span>
                    </div>

                    <div class="field-row">
                        <span class="field-label">Dernière connexion</span>
                        <span
                            class="field-value"><?= htmlspecialchars($user_data['derniere_connexion'] ?? '—') ?></span>
                    </div>

                    <div class="field-row">
                        <span class="field-label">Statut</span>
                        <span
                            class="field-value"><?= htmlspecialchars(ucfirst($user_data['statut_premium'] ?? 'normal')) ?></span>
                    </div>

                </div>

                <!-- bouton deconnexion lie a deconnexion.php -->
                <a href="deconnexion.php" class="btn-logout">⚠️ Déconnexion</a>

            </div>
        </section>

        <!-- panneau 2 : fidelite et recompenses -->
        <section class="panel panel-loyalty" id="loyalty-program">
            <div class="panel-header">
                <h2>⭐ Programme Fidélité</h2>
            </div>
            <div class="panel-body">

                <!-- solde de points -->
                <div class="points-display">
                    <div class="points-icon">💎</div>
                    <div class="points-info">
                        <span class="points-number"><?= number_format($points_fidelite, 0, ',', ' ') ?></span>
                        <span class="points-label">Crédits Républicains</span>
                    </div>
                </div>

                <!-- barre de progression vers le prochain niveau -->
                <div class="level-progress">
                    <div class="level-header">
                        <span class="level-name">Niveau : <?= htmlspecialchars($niveau_fidelite) ?></span>
                        <span class="level-percent"><?= $pourcentage_niveau ?>%</span>
                    </div>
                    <div class="progress-bar-track">
                        <div class="progress-bar-fill" style="width: <?= $pourcentage_niveau ?>%;"></div>
                        <div class="progress-bar-glow"></div>
                    </div>
                    <div class="level-footer">
                        <span><?= number_format($points_fidelite, 0, ',', ' ') ?> pts</span>
                        <?php if ($niveau_fidelite !== 'Grand Maître'): ?>
                            <span>Prochain : <?= htmlspecialchars($niveau_suivant) ?> —
                                <?= number_format($points_prochain, 0, ',', ' ') ?> pts</span>
                        <?php else: ?>
                            <span>🏆 Niveau maximum atteint !</span>
                        <?php endif; ?>
                    </div>
                </div>

                <!-- avantages debloques selon les points -->
                <div class="rewards-section">
                    <h3 class="rewards-title">🎁 Mes Avantages Débloqués</h3>
                    <div class="rewards-grid">

                        <?php if ($points_fidelite >= 500): ?>
                            <div class="reward-coupon reward-active">
                                <div class="coupon-scanline"></div>
                                <div class="coupon-icon">🎫</div>
                                <div class="coupon-content">
                                    <span class="coupon-title">Dessert Offert</span>
                                    <span class="coupon-detail">Macarons de Nevarro</span>
                                </div>
                                <span class="coupon-status status-unlocked">✅ Actif</span>
                            </div>
                        <?php else: ?>
                            <div class="reward-coupon reward-locked">
                                <div class="coupon-icon">🔒</div>
                                <div class="coupon-content">
                                    <span class="coupon-title">Dessert Offert</span>
                                    <span class="coupon-detail">Macarons de Nevarro</span>
                                </div>
                                <span class="coupon-status status-locked">Nécessite 500 pts</span>
                            </div>
                        <?php endif; ?>

                        <?php if ($points_fidelite >= 1000): ?>
                            <div class="reward-coupon reward-active">
                                <div class="coupon-scanline"></div>
                                <div class="coupon-icon">🏷️</div>
                                <div class="coupon-content">
                                    <span class="coupon-title">-10% sur la commande</span>
                                    <span class="coupon-detail">Applicable sur tout le menu</span>
                                </div>
                                <span class="coupon-status status-unlocked">✅ Actif</span>
                            </div>
                        <?php endif; ?>

                    </div>
                </div>

                <!-- prochain objectif -->
                <?php if ($niveau_fidelite !== 'Grand Maître'): ?>
                    <div class="rewards-section">
                        <h3 class="rewards-title">🎯 Prochain Objectif</h3>
                        <div class="rewards-grid">
                            <div class="reward-coupon reward-locked">
                                <div class="coupon-icon">🔒</div>
                                <div class="coupon-content">
                                    <?php if ($points_prochain <= 1000): ?>
                                        <span class="coupon-title">-10% sur la commande</span>
                                        <span class="coupon-detail">Applicable sur tout le menu</span>
                                    <?php else: ?>
                                        <span class="coupon-title">Livraison Gratuite</span>
                                        <span class="coupon-detail">Par Faucon Millenium</span>
                                    <?php endif; ?>
                                </div>
                                <span class="coupon-status status-locked">Nécessite
                                    <?= number_format($points_prochain, 0, ',', ' ') ?> pts</span>
                            </div>
                        </div>
                    </div>
                <?php endif; ?>

            </div>
        </section>

        <!-- panneau 3 : historique des commandes -->
        <section class="panel panel-orders" id="order-history">
            <div class="panel-header">
                <h2>📦 Historique des Commandes</h2>
            </div>
            <div class="panel-body">
                <div class="table-wrapper">
                    <table class="orders-table">
                        <thead>
                            <tr>
                                <th>Date</th>
                                <th>N° Commande</th>
                                <th>Détail</th>
                                <th>Prix</th>
                                <th>Statut</th>
                                <th>Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                            // on recupere les commandes du client connecte
                            $commandes = read_json('commandes.json');
                            $id_client = isset($_SESSION['user']['id']) ? $_SESSION['user']['id'] : null;
                            $mes_commandes = [];
                            foreach ($commandes as $cmd) {
                                if ($cmd['id_client'] == $id_client) {
                                    $mes_commandes[] = $cmd;
                                }
                            }
                            // on inverse pour avoir les plus recentes en premier
                            $mes_commandes = array_reverse($mes_commandes);
                            ?>
                            <?php if (empty($mes_commandes)): ?>
                                <tr>
                                    <td colspan="6"
                                        style="text-align: center; color: rgba(255,255,255,0.4); padding: 30px;">
                                        Aucune commande pour le moment.
                                    </td>
                                </tr>
                            <?php else: ?>
                                <?php foreach ($mes_commandes as $cmd): ?>
                                    <?php
                                    // detail des plats
                                    $detail = [];
                                    foreach ($cmd['plats'] as $p) {
                                        $detail[] = $p['quantite'] . 'x ' . $p['nom'];
                                    }
                                    $detail_str = implode(', ', $detail);

                                    // badge de statut
                                    $statut_cmd = $cmd['statut'];
                                    if ($statut_cmd === 'livré' || $statut_cmd === 'livre') {
                                        $badge_class = 'status-delivered';
                                        $badge_text = 'Livré';
                                        $peut_noter = true;
                                    } elseif ($statut_cmd === 'en livraison') {
                                        $badge_class = 'status-prep';
                                        $badge_text = 'En livraison';
                                        $peut_noter = false;
                                    } elseif ($statut_cmd === 'abandonné') {
                                        $badge_class = 'status-prep" style="background: rgba(255, 50, 50, 0.2); color: #ff5555; border-color: rgba(255, 50, 50, 0.5);';
                                        $badge_text = 'Abandonnée';
                                        $peut_noter = false;
                                    } else {
                                        $badge_class = 'status-prep';
                                        $badge_text = 'En attente';
                                        $peut_noter = false;
                                    }
                                    ?>
                                    <tr>
                                        <td><?= htmlspecialchars($cmd['date']) ?></td>
                                        <td class="order-id">#<?= htmlspecialchars($cmd['id']) ?></td>
                                        <td><?= htmlspecialchars($detail_str) ?></td>
                                        <td class="order-price"><?= number_format($cmd['total'], 2, ',', '') ?> ₹</td>
                                        <td><span class="status-badge <?= $badge_class ?>"><?= $badge_text ?></span></td>
                                        <td>
                                            <?php if ($peut_noter): ?>
                                                <a href="notation.php?id=<?= urlencode($cmd['id']) ?>" class="btn-edit"
                                                    title="Noter cette commande"
                                                    style="text-decoration:none; display:inline-block;">⭐ Noter</a>
                                            <?php else: ?>
                                                <span style="color:rgba(255,255,255,0.3);">—</span>
                                            <?php endif; ?>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </section>

    </div>
</main>

<?php require_once 'includes/footer.php'; ?>