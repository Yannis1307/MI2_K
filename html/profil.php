<?php
// variables de configuration pour le header
$page_title = 'Mon Profil';
$page_css = 'profil.css';
$page_id = 'profil';

// on charge les fonctions utilitaires json
require_once 'includes/functions.php';

// on inclut le header commun
require_once 'includes/header.php';
?>

<main>
    <!-- titre du dashboard -->
    <section class="dashboard-header">
        <h1>🛡️ Tableau de Bord du Jedi</h1>
        <p class="subtitle">Bienvenue, Chevalier. Votre statut dans la galaxie.</p>
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
                        <img src="../images/han_avatar.png" alt="Avatar du joueur" class="profile-avatar">
                        <button class="btn-edit-avatar" title="Modifier l'avatar">✏️</button>
                    </div>
                    <span class="rank-badge">Chevalier Jedi</span>
                </div>

                <!-- champs editables -->
                <div class="identity-fields">
                    <div class="field-row">
                        <span class="field-label">Pseudo</span>
                        <span class="field-value">Han_Solo_77</span>
                        <button class="btn-edit" title="Modifier">✏️</button>
                    </div>
                    <div class="field-row">
                        <span class="field-label">Email</span>
                        <span class="field-value">han.solo@holonet.gx</span>
                        <button class="btn-edit" title="Modifier">✏️</button>
                    </div>
                    <div class="field-row">
                        <span class="field-label">Planète</span>
                        <span class="field-value">Corellia</span>
                        <button class="btn-edit" title="Modifier">✏️</button>
                    </div>
                    <div class="field-row">
                        <span class="field-label">Membre depuis</span>
                        <span class="field-value">Cycle 3024</span>
                    </div>
                </div>

                <!-- bouton deconnexion -->
                <button class="btn-logout">⚠️ Déconnexion</button>
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
                        <span class="points-number">1 250</span>
                        <span class="points-label">Crédits Républicains</span>
                    </div>
                </div>

                <!-- barre de progression vers le prochain niveau -->
                <div class="level-progress">
                    <div class="level-header">
                        <span class="level-name">Niveau : Chevalier Jedi</span>
                        <span class="level-percent">75%</span>
                    </div>
                    <div class="progress-bar-track">
                        <div class="progress-bar-fill" style="width: 75%;"></div>
                        <div class="progress-bar-glow"></div>
                    </div>
                    <div class="level-footer">
                        <span>1 250 pts</span>
                        <span>Prochain : Maître Jedi — 2 000 pts</span>
                    </div>
                </div>

                <!-- avantages debloques -->
                <div class="rewards-section">
                    <h3 class="rewards-title">🎁 Mes Avantages Débloqués</h3>
                    <div class="rewards-grid">

                        <!-- recompense 1 : active -->
                        <div class="reward-coupon reward-active">
                            <div class="coupon-scanline"></div>
                            <div class="coupon-icon">🎫</div>
                            <div class="coupon-content">
                                <span class="coupon-title">Dessert Offert</span>
                                <span class="coupon-detail">Macarons de Nevarro</span>
                            </div>
                            <span class="coupon-status status-unlocked">✅ Actif</span>
                        </div>

                        <!-- recompense 2 : active -->
                        <div class="reward-coupon reward-active">
                            <div class="coupon-scanline"></div>
                            <div class="coupon-icon">🏷️</div>
                            <div class="coupon-content">
                                <span class="coupon-title">-10% sur la commande</span>
                                <span class="coupon-detail">Applicable sur tout le menu</span>
                            </div>
                            <span class="coupon-status status-unlocked">✅ Actif</span>
                        </div>

                    </div>
                </div>

                <!-- prochain objectif -->
                <div class="rewards-section">
                    <h3 class="rewards-title">🎯 Prochain Objectif</h3>
                    <div class="rewards-grid">

                        <!-- recompense verrouillee -->
                        <div class="reward-coupon reward-locked">
                            <div class="coupon-icon">🔒</div>
                            <div class="coupon-content">
                                <span class="coupon-title">Livraison Gratuite</span>
                                <span class="coupon-detail">Par Faucon Millenium</span>
                            </div>
                            <span class="coupon-status status-locked">Nécessite 2 000 pts</span>
                        </div>

                    </div>
                </div>

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
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                            // on recupere les commandes du client connecte
                            $commandes = read_json('commandes.json');
                            $id_client = $_SESSION['user']['id'] ?? null;
                            $mes_commandes = [];
                            foreach ($commandes as $cmd) {
                                if ($cmd['id_client'] == $id_client) {
                                    $mes_commandes[] = $cmd;
                                }
                            }
                            // on inverse pour avoir les plus recentes en premier
                            $mes_commandes = array_reverse($mes_commandes);
                            ?>
                            <?php if (empty($mes_commandes)) : ?>
                            <tr>
                                <td colspan="5" style="text-align: center; color: rgba(255,255,255,0.4); padding: 30px;">Aucune commande pour le moment.</td>
                            </tr>
                            <?php else : ?>
                            <?php foreach ($mes_commandes as $cmd) : ?>
                            <?php
                            // on construit le detail des plats commandes
                            $detail = [];
                            foreach ($cmd['plats'] as $p) {
                                $detail[] = $p['quantite'] . 'x ' . $p['nom'];
                            }
                            $detail_str = implode(', ', $detail);

                            // on determine la classe css du badge selon le statut
                            $statut = $cmd['statut'];
                            if ($statut === 'livre' || $statut === 'livré') {
                                $badge_class = 'status-delivered';
                                $badge_text = 'Livré';
                            } elseif ($statut === 'en livraison') {
                                $badge_class = 'status-prep';
                                $badge_text = 'En livraison';
                            } else {
                                $badge_class = 'status-prep';
                                $badge_text = 'En attente';
                            }
                            ?>
                            <tr>
                                <td><?= htmlspecialchars($cmd['date']) ?></td>
                                <td class="order-id">#<?= htmlspecialchars($cmd['id']) ?></td>
                                <td><?= htmlspecialchars($detail_str) ?></td>
                                <td class="order-price"><?= number_format($cmd['total'], 2, ',', '') ?> ₹</td>
                                <td><span class="status-badge <?= $badge_class ?>"><?= $badge_text ?></span></td>
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