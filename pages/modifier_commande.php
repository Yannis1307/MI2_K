<?php
// pages/modifier_commande.php
$page_title = 'Modifier la Commande';
$page_css = 'panier.css'; // on reutilise le design du panier
$page_id = 'modifier_commande';

require_once 'includes/functions.php';

// controle d'acces
if (!isset($_SESSION['user'])) {
    header('Location: connexion.php');
    exit;
}

if (!isset($_GET['id'])) {
    header('Location: profil.php');
    exit;
}

$id_cmd = $_GET['id'];
$commandes = read_json('commandes.json');
$commande_index = -1;
$commande = null;

foreach ($commandes as $i => $cmd) {
    if ($cmd['id'] === $id_cmd && $cmd['id_client'] == $_SESSION['user']['id']) {
        $commande_index = $i;
        $commande = $cmd;
        break;
    }
}

// securite : on ne modifie que les commandes en attente
if (!$commande || $commande['statut'] !== 'en attente') {
    $_SESSION['flash_error'] = "Impossible de modifier cette commande.";
    header('Location: profil.php');
    exit;
}

// securite : on bloque si une modification est deja en cours de traitement
if (isset($_SESSION['modif_en_cours']) && $_SESSION['modif_en_cours']['id_commande'] === $id_cmd) {
    $_SESSION['flash_error'] = "Une modification est déjà en cours pour cette commande.";
    header('Location: profil.php');
    exit;
}

// on charge les plats et menus disponibles
$plats = read_json('plats.json');
$menus = read_json('menus.json');

// total initial = le prix paye a l'origine (avant toute modif)
$total_initial = isset($commande['total_initial']) ? $commande['total_initial'] : $commande['total'];

require_once 'includes/header.php';
?>

<main>
    <section class="dashboard-header">
        <h1>✏️ Modifier la Commande #<?= htmlspecialchars($id_cmd) ?></h1>
        <p class="subtitle">Ajustez les produits avant la préparation en cuisine.</p>
    </section>

    <div style="max-width: 900px; margin: 0 auto; display: flex; flex-direction: column; gap: 20px;">

        <!-- contenu actuel de la commande -->
        <section class="panel" id="panel-contenu">
            <div class="panel-header">
                <h2>Contenu Actuel</h2>
            </div>
            <div class="panel-body">
                <table style="width: 100%; border-collapse: collapse; text-align: left;" id="table-commande">
                    <thead>
                        <tr style="border-bottom: 1px solid rgba(255,255,255,0.2);">
                            <th style="padding: 10px;">Produit</th>
                            <th>Qté</th>
                            <th>Prix</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody id="tbody-commande">
                        <!-- rempli dynamiquement par le js -->
                    </tbody>
                </table>
                <p id="msg-panier-vide"
                    style="text-align: center; color: rgba(255,255,255,0.4); padding: 20px; display: none;">
                    La commande est vide. Ajoutez des produits ci-dessous.
                </p>
            </div>
        </section>

        <!-- section pour ajouter un plat -->
        <section class="panel">
            <div class="panel-header">
                <h2>Ajouter un Produit</h2>
            </div>
            <div class="panel-body" style="display: flex; gap: 10px; align-items: center; flex-wrap: wrap;">
                <!-- correction des couleurs du menu pour la visibilite -->
                <select id="select-plat" class="select-modif">
                    <?php foreach ($plats as $p): ?>
                        <option value="<?= $p['id'] ?>" data-prix="<?= $p['prix'] ?>"
                            data-nom="<?= htmlspecialchars($p['nom']) ?>"><?= htmlspecialchars($p['nom']) ?>
                            (<?= $p['prix'] ?> ₹)</option>
                    <?php endforeach; ?>
                </select>
                <button type="button" id="btn-ajouter-plat" class="btn-submit btn-cyan"
                    style="width: auto; padding: 10px 20px;">+ Ajouter</button>
            </div>
        </section>

        <!-- section pour ajouter un menu -->
        <?php if (!empty($menus)): ?>
            <section class="panel">
                <div class="panel-header">
                    <h2>Ajouter un Menu</h2>
                </div>
                <div class="panel-body" style="display: flex; gap: 10px; align-items: center; flex-wrap: wrap;">
                    <!-- correction des couleurs du menu pour la visibilite -->
                    <select id="select-menu" class="select-modif">
                        <?php foreach ($menus as $m): ?>
                            <option value="<?= $m['id'] ?>" data-prix="<?= $m['prix_total'] ?>"
                                data-nom="<?= htmlspecialchars($m['nom']) ?>"><?= htmlspecialchars($m['nom']) ?>
                                (<?= $m['prix_total'] ?> ₹)</option>
                        <?php endforeach; ?>
                    </select>
                    <button type="button" id="btn-ajouter-menu" class="btn-submit btn-cyan"
                        style="width: auto; padding: 10px 20px;">+ Ajouter</button>
                </div>
            </section>
        <?php endif; ?>

        <!-- recapitulatif et validation -->
        <section class="panel" style="text-align: right;" id="panel-recap">
            <div class="panel-body">
                <p style="font-size: 1.2em; margin-bottom: 10px;">Total initial :
                    <strong><?= number_format($total_initial, 2) ?> ₹</strong></p>
                <p style="font-size: 1.4em; margin-bottom: 10px; color: #ffd700;" id="txt-nouveau-total">Nouveau Total :
                    <strong>0.00 ₹</strong></p>
                <p id="txt-difference" style="margin-bottom: 20px;"></p>

                <?php
                $solde = isset($_SESSION['user']['solde_credits']) ? $_SESSION['user']['solde_credits'] : 0;
                if ($solde > 0):
                    ?>
                    <div id="div-utiliser-credits"
                        style="background: rgba(0, 255, 136, 0.1); border: 1px solid rgba(0, 255, 136, 0.4); padding: 15px; border-radius: 8px; margin-bottom: 20px; display: none; align-items: center; justify-content: flex-end; gap: 10px;">
                        <input type="checkbox" id="utiliser_credits" value="1"
                            style="width: 20px; height: 20px; cursor: pointer;">
                        <label for="utiliser_credits"
                            style="cursor: pointer; font-size: 1.1em; color: #00ff88; font-weight: bold;">
                            Utiliser mes crédits disponibles (<?= number_format($solde, 2, ',', ' ') ?> ₹)
                        </label>
                    </div>
                <?php endif; ?>

                <button type="button" id="btn-valider" class="btn-submit btn-yellow" style="font-size: 1.2em;">Valider
                    les modifications</button>
            </div>
        </section>
    </div>

    <!-- formulaire cache pour la redirection cybank (paiement de la difference) -->
    <form id="form-cybank-modif" method="POST" action="initier_paiement_modif.php" style="display: none;">
        <input type="hidden" name="id_commande" value="<?= htmlspecialchars($id_cmd) ?>">
        <input type="hidden" name="plats" id="hidden-plats" value="">
        <input type="hidden" name="menus" id="hidden-menus" value="">
        <input type="hidden" name="nouveau_total" id="hidden-nouveau-total" value="">
        <input type="hidden" name="total_initial" id="hidden-total-initial" value="<?= $total_initial ?>">
        <input type="hidden" name="utiliser_credits" id="hidden-utiliser-credits" value="0">
    </form>

    <!-- styles pour les menus deroulants lisibles -->
    <style>
        /* correction des couleurs du menu pour la visibilite */
        .select-modif {
            flex: 1;
            min-width: 200px;
            padding: 10px 14px;
            border-radius: 8px;
            background: rgba(0, 15, 35, 0.9);
            color: #e8e8e8;
            border: 1px solid rgba(248, 224, 66, 0.3);
            font-family: 'Rajdhani', 'Segoe UI', sans-serif;
            font-size: 1em;
            font-weight: 500;
            cursor: pointer;
            transition: border-color 0.3s ease, box-shadow 0.3s ease;
            -webkit-appearance: none;
            -moz-appearance: none;
            appearance: none;
            background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='12' height='12' fill='%23f8e042' viewBox='0 0 16 16'%3E%3Cpath d='M8 11L3 6h10z'/%3E%3C/svg%3E");
            background-repeat: no-repeat;
            background-position: right 12px center;
            padding-right: 36px;
        }

        .select-modif:hover {
            border-color: var(--gold);
            box-shadow: 0 0 10px rgba(248, 224, 66, 0.2);
        }

        .select-modif:focus {
            outline: none;
            border-color: var(--gold);
            box-shadow: 0 0 15px rgba(248, 224, 66, 0.3);
        }

        /* les options du dropdown doivent etre lisibles */
        .select-modif option {
            background: #0a0f1e;
            color: #e8e8e8;
            padding: 8px;
            font-size: 0.95em;
        }

        .select-modif option:checked {
            background: rgba(248, 224, 66, 0.2);
            color: #f8e042;
        }
    </style>

    <div id="commande-data" style="display:none;"
        data-plats='<?= json_encode(isset($commande['plats']) ? $commande['plats'] : [], JSON_HEX_APOS | JSON_HEX_QUOT) ?>'
        data-menus='<?= json_encode(isset($commande['menus']) ? $commande['menus'] : [], JSON_HEX_APOS | JSON_HEX_QUOT) ?>'
        data-total="<?= $total_initial ?>" data-id="<?= htmlspecialchars($id_cmd) ?>"
        data-credits="<?= isset($_SESSION['user']['solde_credits']) ? number_format($_SESSION['user']['solde_credits'], 2, '.', '') : '0' ?>">
    </div>
</main>

<?php require_once 'includes/footer.php'; ?>