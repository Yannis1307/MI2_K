<?php
// pages/modifier_commande.php
$page_title = 'Modifier la Commande';
$page_css = 'panier.css'; // on reutilise le design du panier
$page_id = 'modifier_commande';

require_once 'includes/functions.php';

if (!isset($_SESSION['user'])) {
    header('Location: connexion.php');
    exit;
}

if (!isset($_GET['id']) && !isset($_POST['id_commande'])) {
    header('Location: profil.php');
    exit;
}

$id_cmd = isset($_GET['id']) ? $_GET['id'] : $_POST['id_commande'];
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

if (!$commande || $commande['statut'] !== 'en attente') {
    $_SESSION['flash_error'] = "Impossible de modifier cette commande.";
    header('Location: profil.php');
    exit;
}

$plats = read_json('plats.json');
$plats_index = [];
foreach ($plats as $p) $plats_index[$p['id']] = $p;

$menus = read_json('menus.json');
$menus_index = [];
foreach ($menus as $m) $menus_index[$m['id']] = $m;

// Traitement formulaire d'edition
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'];
    
    if ($action === 'retirer_plat') {
        $idx_to_remove = $_POST['index'];
        array_splice($commande['plats'], $idx_to_remove, 1);
    } elseif ($action === 'retirer_menu') {
        $idx_to_remove = $_POST['index'];
        array_splice($commande['menus'], $idx_to_remove, 1);
    } elseif ($action === 'ajouter_plat') {
        $id_plat = $_POST['id_plat'];
        if (isset($plats_index[$id_plat])) {
            $p = $plats_index[$id_plat];
            // On cherche s'il y est deja
            $found = false;
            foreach ($commande['plats'] as &$cp) {
                if ($cp['id_plat'] == $id_plat) {
                    $cp['quantite'] += 1;
                    $cp['sous_total'] = $cp['quantite'] * $cp['prix_unitaire'];
                    $found = true;
                    break;
                }
            }
            if (!$found) {
                $commande['plats'][] = [
                    'id_plat' => $id_plat,
                    'nom' => $p['nom'],
                    'quantite' => 1,
                    'prix_unitaire' => $p['prix'],
                    'sous_total' => $p['prix']
                ];
            }
        }
    }
    
    // Recalcul du total de la commande modifiee (sans sauvegarder encore)
    $nouveau_total = 0;
    foreach ($commande['plats'] as $cp) $nouveau_total += $cp['sous_total'];
    foreach ($commande['menus'] as $cm) $nouveau_total += $cm['sous_total'];
    
    $commande['nouveau_total'] = $nouveau_total;
    
    // Si validation finale
    if ($action === 'valider') {
        $ancien_total = $commande['total'];
        
        // Nettoyage de la variable temporaire
        unset($commande['nouveau_total']);
        $commande['total'] = $nouveau_total;
        
        if ($nouveau_total > $ancien_total) {
            // Generer un nouveau paiement (ici on simule directement la maj)
            $difference = $nouveau_total - $ancien_total;
            $commandes[$commande_index] = $commande;
            write_json('commandes.json', $commandes);
            $_SESSION['flash_success'] = "Commande mise à jour. Un complément de " . number_format($difference, 2) . " ₹ a été prélevé sur CYBank.";
        } elseif ($nouveau_total < $ancien_total) {
            // Remboursement / Bon de reduction
            $difference = $ancien_total - $nouveau_total;
            $commandes[$commande_index] = $commande;
            write_json('commandes.json', $commandes);
            
            // Ajouter le crédit au profil de l'utilisateur
            $users = read_json('users.json');
            $id_client = $_SESSION['user']['id'];
            foreach ($users as &$u) {
                if ($u['id'] == $id_client) {
                    if (!isset($u['solde_credits'])) $u['solde_credits'] = 0;
                    $u['solde_credits'] += $difference;
                    $_SESSION['user']['solde_credits'] = $u['solde_credits'];
                    break;
                }
            }
            write_json('users.json', $users);

            $_SESSION['flash_success'] = "Commande modifiée. Un crédit de " . number_format($difference, 2) . " ₹ a été ajouté à votre profil.";
        } else {
            $commandes[$commande_index] = $commande;
            write_json('commandes.json', $commandes);
            $_SESSION['flash_success'] = "Commande modifiée sans changement de prix.";
        }
        
        header('Location: profil.php');
        exit;
    }
    
    // On met a jour temporairement dans le json ? Ou en session ?
    // Le plus simple c'est de stocker la modif dans le json avec un flag ou direct
    // Pour cet exercice, on sauvegarde direct l'etat temporaire si on n'a pas validé, 
    // ou on l'applique direct car c'est un prototype
    $commandes[$commande_index] = $commande;
    write_json('commandes.json', $commandes);
}

// Calcul des totaux actuels
$ancien_total = isset($commande['total_initial']) ? $commande['total_initial'] : $commande['total']; // on stocke le vrai total
if (!isset($commande['total_initial'])) {
    $commande['total_initial'] = $commande['total'];
    $commandes[$commande_index] = $commande;
    write_json('commandes.json', $commandes);
    $ancien_total = $commande['total'];
}

$nouveau_total = 0;
foreach ($commande['plats'] as $cp) $nouveau_total += $cp['sous_total'];
foreach ($commande['menus'] as $cm) $nouveau_total += $cm['sous_total'];

require_once 'includes/header.php';
?>

<main>
    <section class="dashboard-header">
        <h1>✏️ Modifier la Commande #<?= htmlspecialchars($id_cmd) ?></h1>
        <p class="subtitle">Ajustez les produits avant la préparation en cuisine.</p>
    </section>

    <div style="max-width: 900px; margin: 0 auto; display: flex; flex-direction: column; gap: 20px;">
        <section class="panel">
            <div class="panel-header">
                <h2>Contenu Actuel</h2>
            </div>
            <div class="panel-body">
                <table style="width: 100%; border-collapse: collapse; text-align: left;">
                    <thead>
                        <tr style="border-bottom: 1px solid rgba(255,255,255,0.2);">
                            <th style="padding: 10px;">Produit</th>
                            <th>Prix</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if(isset($commande['plats'])): foreach ($commande['plats'] as $idx => $p): ?>
                        <tr>
                            <td style="padding: 10px;"><?= $p['quantite'] ?>x <?= htmlspecialchars($p['nom']) ?></td>
                            <td><?= number_format($p['sous_total'], 2) ?> ₹</td>
                            <td>
                                <form method="POST" style="display:inline;">
                                    <input type="hidden" name="id_commande" value="<?= $id_cmd ?>">
                                    <input type="hidden" name="action" value="retirer_plat">
                                    <input type="hidden" name="index" value="<?= $idx ?>">
                                    <button class="btn-edit" style="color: #ff4444; border-color: #ff4444;">Retirer</button>
                                </form>
                            </td>
                        </tr>
                        <?php endforeach; endif; ?>
                        
                        <?php if(isset($commande['menus'])): foreach ($commande['menus'] as $idx => $m): ?>
                        <tr>
                            <td style="padding: 10px;"><?= $m['quantite'] ?>x <?= htmlspecialchars($m['nom']) ?> (Menu)</td>
                            <td><?= number_format($m['sous_total'], 2) ?> ₹</td>
                            <td>
                                <form method="POST" style="display:inline;">
                                    <input type="hidden" name="id_commande" value="<?= $id_cmd ?>">
                                    <input type="hidden" name="action" value="retirer_menu">
                                    <input type="hidden" name="index" value="<?= $idx ?>">
                                    <button class="btn-edit" style="color: #ff4444; border-color: #ff4444;">Retirer</button>
                                </form>
                            </td>
                        </tr>
                        <?php endforeach; endif; ?>
                    </tbody>
                </table>
            </div>
        </section>

        <section class="panel">
            <div class="panel-header">
                <h2>Ajouter un Produit</h2>
            </div>
            <div class="panel-body" style="display: flex; gap: 10px; align-items: center;">
                <form method="POST" style="display: flex; gap: 10px; width: 100%;">
                    <input type="hidden" name="id_commande" value="<?= $id_cmd ?>">
                    <input type="hidden" name="action" value="ajouter_plat">
                    <select name="id_plat" style="flex: 1; padding: 10px; border-radius: 8px; background: rgba(255,255,255,0.1); color: inherit; border: 1px solid rgba(255,255,255,0.2);">
                        <?php foreach ($plats as $p): ?>
                            <option value="<?= $p['id'] ?>" style="color: #000;"><?= htmlspecialchars($p['nom']) ?> (<?= $p['prix'] ?> ₹)</option>
                        <?php endforeach; ?>
                    </select>
                    <button type="submit" class="btn-submit btn-cyan" style="width: auto; padding: 10px 20px;">Ajouter</button>
                </form>
            </div>
        </section>

        <section class="panel" style="text-align: right;">
            <div class="panel-body">
                <p style="font-size: 1.2em; margin-bottom: 10px;">Total initial : <strong><?= number_format($ancien_total, 2) ?> ₹</strong></p>
                <p style="font-size: 1.4em; margin-bottom: 20px; color: #ffd700;">Nouveau Total : <strong><?= number_format($nouveau_total, 2) ?> ₹</strong></p>
                
                <?php
                $diff = $nouveau_total - $ancien_total;
                if ($diff > 0) {
                    echo '<p style="color: #ffaa00; margin-bottom: 20px;">Différence de +'.number_format($diff, 2).' ₹ à payer.</p>';
                } elseif ($diff < 0) {
                    echo '<p style="color: #00ff88; margin-bottom: 20px;">Bon de réduction de '.number_format(abs($diff), 2).' ₹ généré.</p>';
                }
                ?>
                
                <form method="POST">
                    <input type="hidden" name="id_commande" value="<?= $id_cmd ?>">
                    <input type="hidden" name="action" value="valider">
                    <button type="submit" class="btn-submit btn-yellow" style="font-size: 1.2em;">
                        <?php if ($diff > 0) echo "Payer le complément avec CYBank"; else echo "Valider les modifications"; ?>
                    </button>
                </form>
            </div>
        </section>
    </div>
</main>

<?php require_once 'includes/footer.php'; ?>
