<?php
// variables de configuration pour le header
$page_title = 'Mon Panier';
$page_css = 'profil.css';
$page_id = 'panier';

// on charge les fonctions utilitaires json
require_once 'includes/functions.php';

// === CONTROLE D'ACCES : connexion obligatoire ===
if (!isset($_SESSION['user'])) {
    header('Location: connexion.php');
    exit;
}

// on recupere les plats pour croiser avec le panier
$plats = read_json('plats.json');

// on cree un index des plats par id pour recherche rapide
$plats_index = [];
foreach ($plats as $plat) {
    $plats_index[$plat['id']] = $plat;
}

// on recupere le panier de la session
$panier = $_SESSION['panier'] ?? [];

// calcul du total
$total = 0;
foreach ($panier as $id_plat => $quantite) {
    if (isset($plats_index[$id_plat])) {
        $total += $plats_index[$id_plat]['prix'] * $quantite;
    }
}

// compteur d'articles
$nb_articles = array_sum($panier);

// on inclut le header commun
require_once 'includes/header.php';
?>

    <main>
        <!-- titre du panier -->
        <section class="dashboard-header">
            <h1>🛒 Mon Panier</h1>
            <p class="subtitle">Vérifiez votre commande avant de passer au paiement.</p>
        </section>

        <div style="display: flex; flex-direction: column; gap: 20px; max-width: 900px; margin: 0 auto;">

            <?php // message flash si la commande vient d'etre validee ?>
            <?php if (isset($_SESSION['flash_success'])) : ?>
            <div style="background: rgba(0, 255, 100, 0.15); border: 1px solid rgba(0, 255, 100, 0.4); padding: 15px; border-radius: 10px; text-align: center; color: #7fff7f;">
                ✅ <?= htmlspecialchars($_SESSION['flash_success']) ?>
            </div>
            <?php unset($_SESSION['flash_success']); ?>
            <?php endif; ?>

            <?php if (empty($panier)) : ?>
            <!-- panier vide -->
            <section class="panel">
                <div class="panel-header">
                    <h2>🍽️ Votre panier est vide</h2>
                </div>
                <div class="panel-body" style="text-align: center; padding: 40px;">
                    <p style="font-size: 1.2em; margin-bottom: 20px; color: rgba(255,255,255,0.7);">Aucun plat sélectionné pour le moment.</p>
                    <a href="produits.php" class="btn-logout" style="background: linear-gradient(135deg, #00b4d8, #0077b6); border: none; text-decoration: none; display: inline-block;">🍕 Voir la Carte</a>
                </div>
            </section>

            <?php else : ?>
            <!-- liste des articles du panier -->
            <section class="panel">
                <div class="panel-header">
                    <h2>📦 Articles (<?= $nb_articles ?>)</h2>
                </div>
                <div class="panel-body">
                    <div class="table-wrapper">
                        <table class="orders-table">
                            <thead>
                                <tr>
                                    <th>Plat</th>
                                    <th>Prix unitaire</th>
                                    <th>Quantité</th>
                                    <th>Sous-total</th>
                                    <th>Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php // boucle sur chaque article du panier ?>
                                <?php foreach ($panier as $id_plat => $quantite) : ?>
                                <?php if (isset($plats_index[$id_plat])) :
                                    $p = $plats_index[$id_plat];
                                    $sous_total = $p['prix'] * $quantite;
                                ?>
                                <tr>
                                    <td><?= htmlspecialchars($p['nom']) ?></td>
                                    <td class="order-price"><?= number_format($p['prix'], 2, ',', '') ?> ₹</td>
                                    <td><?= $quantite ?></td>
                                    <td class="order-price"><?= number_format($sous_total, 2, ',', '') ?> ₹</td>
                                    <td>
                                        <!-- bouton pour retirer un article -->
                                        <form method="POST" action="retirer_panier.php" style="display:inline;">
                                            <input type="hidden" name="id_plat" value="<?= $id_plat ?>">
                                            <button type="submit" class="btn-edit" title="Retirer" style="cursor:pointer;">🗑️</button>
                                        </form>
                                    </td>
                                </tr>
                                <?php endif; ?>
                                <?php endforeach; ?>
                            </tbody>
                            <tfoot>
                                <tr>
                                    <td colspan="3" style="text-align: right; font-weight: bold;">TOTAL :</td>
                                    <td class="order-price" style="font-weight: bold; font-size: 1.2em; color: #ffd700;"><?= number_format($total, 2, ',', '') ?> ₹</td>
                                    <td></td>
                                </tr>
                            </tfoot>
                        </table>
                    </div>
                </div>
            </section>

            <!-- formulaire de validation de la commande -->
            <section class="panel">
                <div class="panel-header">
                    <h2>💳 Valider la Commande</h2>
                </div>
                <div class="panel-body">
                    <form method="POST" action="traitement_commande.php">

                        <!-- ligne horizontale avec les 3 champs cote a cote -->
                        <div style="display: grid; grid-template-columns: 1fr 1fr 2fr; gap: 15px; margin-bottom: 20px;">

                            <!-- type de commande -->
                            <div>
                                <label style="display: block; margin-bottom: 8px; color: rgba(255,255,255,0.8); font-weight: bold; font-size: 0.85em; text-transform: uppercase; letter-spacing: 1px;">Type :</label>
                                <select name="type_commande" style="width: 100%; padding: 12px; border-radius: 8px; background: rgba(255,255,255,0.08); color: white; border: 1px solid rgba(255,255,255,0.2); font-size: 0.95em;">
                                    <option value="immediate">🚀 Immédiate</option>
                                    <option value="planifiee">📅 Planifiée</option>
                                </select>
                            </div>

                            <!-- heure de livraison -->
                            <div>
                                <label style="display: block; margin-bottom: 8px; color: rgba(255,255,255,0.8); font-weight: bold; font-size: 0.85em; text-transform: uppercase; letter-spacing: 1px;">Heure :</label>
                                <input type="time" name="heure_livraison" value="12:00" style="width: 100%; padding: 12px; border-radius: 8px; background: rgba(255,255,255,0.08); color: white; border: 1px solid rgba(255,255,255,0.2); font-size: 0.95em;">
                            </div>

                            <!-- adresse de livraison -->
                            <div>
                                <label style="display: block; margin-bottom: 8px; color: rgba(255,255,255,0.8); font-weight: bold; font-size: 0.85em; text-transform: uppercase; letter-spacing: 1px;">Adresse :</label>
                                <input type="text" name="adresse" placeholder="Ex: 12 Allée des Sénateurs, Coruscant" style="width: 100%; padding: 12px; border-radius: 8px; background: rgba(255,255,255,0.08); color: white; border: 1px solid rgba(255,255,255,0.2); font-size: 0.95em;" required>
                            </div>
                        </div>

                        <!-- bouton de paiement pleine largeur -->
                        <button type="submit" class="btn-logout" style="width: 100%; background: linear-gradient(135deg, #ffd700, #ff8c00); border: none; color: #1a1a2e; font-weight: bold; font-size: 1.1em; cursor: pointer; padding: 15px; border-radius: 10px; text-transform: uppercase; letter-spacing: 1px;">
                            💳 Payer avec CYBank — <?= number_format($total, 2, ',', '') ?> ₹
                        </button>
                    </form>
                </div>
            </section>
            <?php endif; ?>

        </div>
    </main>

<?php require_once 'includes/footer.php'; ?>

