<?php
// configuration de la page
$page_title = 'Mon Panier';
$page_css = 'profil.css';
$page_id = 'panier';

// chargement des fonctions json
require_once 'includes/functions.php';

// controle d'acces
if (!isset($_SESSION['user'])) {
    header('Location: connexion.php');
    exit;
}

// recuperation des plats
$plats = read_json('plats.json');

// indexation des plats
$plats_index = [];
foreach ($plats as $plat) {
    $plats_index[$plat['id']] = $plat;
}

// recuperation des menus
$menus = read_json('menus.json');

// indexation des menus
$menus_index = [];
foreach ($menus as $menu) {
    $menus_index[$menu['id']] = $menu;
}

// lecture des paniers en session
$panier = isset($_SESSION['panier']) ? $_SESSION['panier'] : [];
$panier_menus = isset($_SESSION['panier_menus']) ? $_SESSION['panier_menus'] : [];

// calcul du total
$total = 0;
foreach ($panier as $id_plat => $quantite) {
    if (isset($plats_index[$id_plat])) {
        $total += $plats_index[$id_plat]['prix'] * $quantite;
    }
}
foreach ($panier_menus as $id_menu => $quantite) {
    if (isset($menus_index[$id_menu])) {
        $total += $menus_index[$id_menu]['prix_total'] * $quantite;
    }
}

// compteur d'articles
$nb_articles = array_sum($panier) + array_sum($panier_menus);

// inclusion du header
require_once 'includes/header.php';
?>

<main>
    <!-- titre de la page -->
    <section class="dashboard-header">
        <h1>🛒 Mon Panier</h1>
        <p class="subtitle">Vérifiez votre commande avant de passer au paiement.</p>
    </section>

    <div style="display: flex; flex-direction: column; gap: 20px; max-width: 900px; margin: 0 auto;">

        <?php // gestion des messages flash ?>
        <?php if (isset($_SESSION['flash_success'])): ?>
            <div
                style="background: rgba(0, 255, 100, 0.15); border: 1px solid rgba(0, 255, 100, 0.4); padding: 15px; border-radius: 10px; text-align: center; color: #7fff7f;">
                ✅ <?= htmlspecialchars($_SESSION['flash_success']) ?>
            </div>
            <?php unset($_SESSION['flash_success']); ?>
        <?php endif; ?>

        <?php if (isset($_SESSION['flash_error'])): ?>
            <div
                style="background: rgba(255, 50, 50, 0.15); border: 1px solid rgba(255, 50, 50, 0.4); padding: 15px; border-radius: 10px; text-align: center; color: #ff8080;">
                ❌ <?= htmlspecialchars($_SESSION['flash_error']) ?>
            </div>
            <?php unset($_SESSION['flash_error']); ?>
        <?php endif; ?>

        <?php if (empty($panier) && empty($panier_menus)): ?>
            <!-- panier vide -->
            <section class="panel">
                <div class="panel-header">
                    <h2>🍽️ Votre panier est vide</h2>
                </div>
                <div class="panel-body" style="text-align: center; padding: 40px;">
                    <p style="font-size: 1.2em; margin-bottom: 20px; color: rgba(255,255,255,0.7);">Aucun article sélectionné
                        pour le moment.</p>
                    <a href="produits.php" class="btn-logout"
                        style="background: linear-gradient(135deg, #00b4d8, #0077b6); border: none; text-decoration: none; display: inline-block;">🍕
                        Voir la Carte</a>
                </div>
            </section>

        <?php else: ?>
            <!-- articles dans le panier -->
            <section class="panel">
                <div class="panel-header">
                    <h2>📦 Articles (<?= $nb_articles ?>)</h2>
                </div>
                <div class="panel-body">
                    <div class="table-wrapper">
                        <table class="orders-table">
                            <thead>
                                <tr>
                                    <th>Article</th>
                                    <th>Prix unitaire</th>
                                    <th>Quantité</th>
                                    <th>Sous-total</th>
                                    <th>Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php // affichage des plats ?>
                                <?php foreach ($panier as $id_plat => $quantite): ?>
                                    <?php if (isset($plats_index[$id_plat])):
                                        $p = $plats_index[$id_plat];
                                        $sous_total = $p['prix'] * $quantite;
                                        ?>
                                        <tr>
                                            <td><?= htmlspecialchars($p['nom']) ?></td>
                                            <td class="order-price"><?= number_format($p['prix'], 2, ',', '') ?> ₹</td>
                                            <td><?= $quantite ?></td>
                                            <td class="order-price"><?= number_format($sous_total, 2, ',', '') ?> ₹</td>
                                            <td>
                                                <!-- retirer plat -->
                                                <form method="POST" action="retirer_panier.php" style="display:inline;">
                                                    <input type="hidden" name="id_plat" value="<?= $id_plat ?>">
                                                    <button type="submit" class="btn-edit" title="Retirer"
                                                        style="cursor:pointer;">🗑️</button>
                                                </form>
                                            </td>
                                        </tr>
                                    <?php endif; ?>
                                <?php endforeach; ?>

                                <?php // affichage des menus ?>
                                <?php foreach ($panier_menus as $id_menu => $quantite): ?>
                                    <?php if (isset($menus_index[$id_menu])):
                                        $m = $menus_index[$id_menu];
                                        $sous_total = $m['prix_total'] * $quantite;
                                        ?>
                                        <tr>
                                            <td><?= htmlspecialchars($m['nom']) ?> <span style="font-size: 0.8em; color: rgba(255,255,255,0.5);">(Menu)</span></td>
                                            <td class="order-price"><?= number_format($m['prix_total'], 2, ',', '') ?> ₹</td>
                                            <td><?= $quantite ?></td>
                                            <td class="order-price"><?= number_format($sous_total, 2, ',', '') ?> ₹</td>
                                            <td>
                                                <!-- retirer menu -->
                                                <form method="POST" action="retirer_panier.php" style="display:inline;">
                                                    <input type="hidden" name="id_menu" value="<?= $id_menu ?>">
                                                    <button type="submit" class="btn-edit" title="Retirer"
                                                        style="cursor:pointer;">🗑️</button>
                                                </form>
                                            </td>
                                        </tr>
                                    <?php endif; ?>
                                <?php endforeach; ?>
                            </tbody>
                            <tfoot>
                                <tr>
                                    <td colspan="3" style="text-align: right; font-weight: bold;">TOTAL :</td>
                                    <td class="order-price" style="font-weight: bold; font-size: 1.2em; color: #ffd700;">
                                        <?= number_format($total, 2, ',', '') ?> ₹</td>
                                    <td></td>
                                </tr>
                            </tfoot>
                        </table>
                    </div>
                </div>
            </section>

            <!-- bloc de paiement -->
            <section class="panel">
                <div class="panel-header">
                    <h2>💳 Valider la Commande</h2>
                </div>
                <div class="panel-body">
                    <!-- redirection vers le paiement bancaire -->
                    <form method="POST" action="initier_paiement.php">

                        <!-- parametres de la commande -->
                        <div style="display: grid; grid-template-columns: 1fr 1fr 1fr; gap: 10px; margin-bottom: 10px;">

                            <!-- type -->
                            <div>
                                <label
                                    style="display: block; margin-bottom: 8px; color: rgba(255,255,255,0.8); font-weight: bold; font-size: 0.85em; text-transform: uppercase; letter-spacing: 1px;">Type
                                    :</label>
                                <select name="type_commande"
                                    style="width: 100%; padding: 12px; border-radius: 8px; background: rgba(255,255,255,0.08); color: white; border: 1px solid rgba(255,255,255,0.2); font-size: 0.95em;">
                                    <option value="immediate" style="background:#1a1a2e; color:white;">🚀 Immédiate</option>
                                    <option value="planifiee" style="background:#1a1a2e; color:white;">📅 Planifiée</option>
                                </select>
                            </div>

                            <!-- mode -->
                            <div>
                                <label
                                    style="display: block; margin-bottom: 8px; color: rgba(255,255,255,0.8); font-weight: bold; font-size: 0.85em; text-transform: uppercase; letter-spacing: 1px;">Mode
                                    :</label>
                                <select name="mode_retrait"
                                    style="width: 100%; padding: 12px; border-radius: 8px; background: rgba(255,255,255,0.08); color: white; border: 1px solid rgba(255,255,255,0.2); font-size: 0.95em;">
                                    <option value="livraison" style="background:#1a1a2e; color:white;">🛵 Livraison</option>
                                    <option value="emporter" style="background:#1a1a2e; color:white;">🥡 À emporter</option>
                                </select>
                            </div>

                            <!-- date -->
                            <div>
                                <label
                                    style="display: block; margin-bottom: 8px; color: rgba(255,255,255,0.8); font-weight: bold; font-size: 0.85em; text-transform: uppercase; letter-spacing: 1px;">Date & Heure
                                    (si planifiée) :</label>
                                <input type="datetime-local" name="heure_livraison" value="<?= date('Y-m-d\TH:i') ?>"
                                    style="width: 100%; padding: 12px; border-radius: 8px; background: rgba(255,255,255,0.08); color: white; border: 1px solid rgba(255,255,255,0.2); font-size: 0.95em;">
                            </div>
                        </div>

                        <!-- details de livraison -->
                        <div style="display: grid; grid-template-columns: 2fr 1fr 1fr; gap: 10px; margin-bottom: 20px;">
                            <!-- adresse -->
                            <div>
                                <label
                                    style="display: block; margin-bottom: 8px; color: rgba(255,255,255,0.8); font-weight: bold; font-size: 0.85em; text-transform: uppercase; letter-spacing: 1px;">Adresse
                                    (si livraison) :</label>
                                <input type="text" name="adresse" placeholder="Ex: 12 Allée des Sénateurs, Coruscant"
                                    style="width: 100%; padding: 12px; border-radius: 8px; background: rgba(255,255,255,0.08); color: white; border: 1px solid rgba(255,255,255,0.2); font-size: 0.95em;">
                            </div>

                            <!-- etage -->
                            <div>
                                <label
                                    style="display: block; margin-bottom: 8px; color: rgba(255,255,255,0.8); font-weight: bold; font-size: 0.85em; text-transform: uppercase; letter-spacing: 1px;">Étage
                                    :</label>
                                <input type="text" name="etage" placeholder="Ex: 3ème"
                                    style="width: 100%; padding: 12px; border-radius: 8px; background: rgba(255,255,255,0.08); color: white; border: 1px solid rgba(255,255,255,0.2); font-size: 0.95em;">
                            </div>

                            <!-- interphone -->
                            <div>
                                <label
                                    style="display: block; margin-bottom: 8px; color: rgba(255,255,255,0.8); font-weight: bold; font-size: 0.85em; text-transform: uppercase; letter-spacing: 1px;">Digicode
                                    :</label>
                                <input type="text" name="code_interphone" placeholder="Ex: B42"
                                    style="width: 100%; padding: 12px; border-radius: 8px; background: rgba(255,255,255,0.08); color: white; border: 1px solid rgba(255,255,255,0.2); font-size: 0.95em;">
                            </div>
                        </div>

                        <!-- bouton go -->
                        <button type="submit" class="btn-logout"
                            style="width: 100%; background: linear-gradient(135deg, #ffd700, #ff8c00); border: none; color: #1a1a2e; font-weight: bold; font-size: 1.1em; cursor: pointer; padding: 15px; border-radius: 10px; text-transform: uppercase; letter-spacing: 1px;">
                            💳 Payer avec CYBank — <?= number_format($total, 2, ',', '') ?> ₹
                        </button>
                    </form>
                </div>
            </section>
        <?php endif; ?>

    </div>
</main>

<?php require_once 'includes/footer.php'; ?>