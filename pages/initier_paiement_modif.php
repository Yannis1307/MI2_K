<?php
// pages/initier_paiement_modif.php
// redirige vers cybank pour payer la difference de prix lors d'une modification

require_once 'includes/functions.php';

// controle d'acces
if (!isset($_SESSION['user'])) {
    header('Location: connexion.php');
    exit;
}

// verification de la methode
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: profil.php');
    exit;
}

// chargement des donnees veritables
$vrais_plats = read_json('plats.json');
$vrais_menus = read_json('menus.json');

// indexation pour recherche rapide
$index_plats = [];
foreach ($vrais_plats as $vp) {
    $index_plats[$vp['id']] = $vp;
}

$index_menus = [];
foreach ($vrais_menus as $vm) {
    $index_menus[$vm['id']] = $vm;
}

// recuperation des donnees du formulaire
$id_commande = isset($_POST['id_commande']) ? $_POST['id_commande'] : '';
$plats_json = isset($_POST['plats']) ? $_POST['plats'] : '[]';
$menus_json = isset($_POST['menus']) ? $_POST['menus'] : '[]';
$nouveau_total = isset($_POST['nouveau_total']) ? floatval($_POST['nouveau_total']) : 0;
$total_initial = isset($_POST['total_initial']) ? floatval($_POST['total_initial']) : 0;

// decodage des plats et menus
$plats = json_decode($plats_json, true);
$menus = json_decode($menus_json, true);

if (!$plats)
    $plats = [];
if (!$menus)
    $menus = [];

// verification que la commande n'est pas vide
if (empty($plats) && empty($menus)) {
    $_SESSION['flash_error'] = 'La commande ne peut pas être vide.';
    header('Location: modifier_commande.php?id=' . urlencode($id_commande));
    exit;
}

// on recalcule le vrai total cote serveur pour securiser
$vrai_nouveau_total = 0;

// securisation plats
foreach ($plats as $k => $p) {
    $qte = intval($p['quantite']);
    if ($qte <= 0) {
        $_SESSION['flash_error'] = 'Quantité invalide détectée.';
        header('Location: profil.php');
        exit;
    }

    $id_plat = $p['id_plat'];
    if (isset($index_plats[$id_plat])) {
        $vrai_prix = floatval($index_plats[$id_plat]['prix']);
        $vrai_sous_total = $vrai_prix * $qte;

        $plats[$k]['quantite'] = $qte;
        $plats[$k]['prix_unitaire'] = $vrai_prix;
        $plats[$k]['sous_total'] = $vrai_sous_total;

        $vrai_nouveau_total += $vrai_sous_total;
    } else {
        $_SESSION['flash_error'] = 'Produit invalide détecté.';
        header('Location: profil.php');
        exit;
    }
}

// securisation menus
foreach ($menus as $k => $m) {
    $qte = intval($m['quantite']);
    if ($qte <= 0) {
        $_SESSION['flash_error'] = 'Quantité invalide détectée.';
        header('Location: profil.php');
        exit;
    }

    $id_menu = $m['id_menu'];
    if (isset($index_menus[$id_menu])) {
        $vrai_prix = floatval($index_menus[$id_menu]['prix_total']);
        $vrai_sous_total = $vrai_prix * $qte;

        $menus[$k]['quantite'] = $qte;
        $menus[$k]['prix_unitaire'] = $vrai_prix;
        $menus[$k]['sous_total'] = $vrai_sous_total;

        $vrai_nouveau_total += $vrai_sous_total;
    } else {
        $_SESSION['flash_error'] = 'Menu invalide détecté.';
        header('Location: profil.php');
        exit;
    }
}

$vrai_nouveau_total = round($vrai_nouveau_total, 2);

// verification que la commande existe et est toujours en attente
$commandes = read_json('commandes.json');
$commande_trouvee = null;
foreach ($commandes as $cmd) {
    if ($cmd['id'] === $id_commande && $cmd['id_client'] == $_SESSION['user']['id']) {
        $commande_trouvee = $cmd;
        break;
    }
}

// securite : on bloque si la commande n'est plus en attente
if (!$commande_trouvee || $commande_trouvee['statut'] !== 'en attente') {
    $_SESSION['flash_error'] = "Cette commande ne peut plus être modifiée.";
    header('Location: profil.php');
    exit;
}

// calcul de la difference a payer avec le vrai nouveau total
$difference = round($vrai_nouveau_total - $total_initial, 2);

// securite : si pas de surplus, pas besoin de cybank
if ($difference <= 0) {
    $_SESSION['flash_error'] = 'Aucun complément à payer ou erreur de calcul.';
    header('Location: modifier_commande.php?id=' . urlencode($id_commande));
    exit;
}

$credits_utilises = 0;
$reste_a_payer = $difference;

if (isset($_POST['utiliser_credits']) && $_POST['utiliser_credits'] == '1') {
    // verification obligatoire dans users.json
    $users_all = read_json('users.json');
    $solde = 0;
    foreach ($users_all as $u) {
        if ($u['id'] == $_SESSION['user']['id']) {
            $solde = isset($u['solde_credits']) ? floatval($u['solde_credits']) : 0;
            break;
        }
    }

    if ($solde > 0) {
        if ($solde >= $difference) {
            $credits_utilises = $difference;
            $reste_a_payer = 0;
        } else {
            $credits_utilises = $solde;
            $reste_a_payer -= $solde;
        }
    }
}

// cas ou tout est paye par credits
if ($reste_a_payer == 0 && $credits_utilises > 0) {
    // deduction des credits
    $users_update = read_json('users.json');
    foreach ($users_update as &$u) {
        if ($u['id'] == $_SESSION['user']['id']) {
            $u['solde_credits'] -= $credits_utilises;
            $_SESSION['user']['solde_credits'] = $u['solde_credits'];
            break;
        }
    }
    write_json('users.json', $users_update);

    // mise a jour de la commande
    $commandes_update = read_json('commandes.json');
    foreach ($commandes_update as &$cmd) {
        if ($cmd['id'] === $id_commande && $cmd['id_client'] == $_SESSION['user']['id']) {
            $cmd['plats'] = $plats;
            $cmd['menus'] = $menus;
            $cmd['total'] = $vrai_nouveau_total;
            $cmd['total_initial'] = $vrai_nouveau_total;

            // mise a jour de la repartition du paiement
            $anc_credits = isset($cmd['credits_utilises']) ? $cmd['credits_utilises'] : 0;
            $cmd['credits_utilises'] = $anc_credits + $credits_utilises;
            break;
        }
    }
    write_json('commandes.json', $commandes_update);

    $_SESSION['flash_success'] = 'Commande modifiée avec succès ! (Complément entièrement réglé avec vos crédits).';
    header('Location: profil.php');
    exit;
}

// generation d'un identifiant de transaction unique
$transaction_id = strtoupper(bin2hex(random_bytes(5)));

// on stocke les infos de modification en session pour le retour cybank
$_SESSION['modif_en_cours'] = [
    'transaction' => $transaction_id,
    'id_commande' => $id_commande,
    'plats' => $plats,
    'menus' => $menus,
    'nouveau_total' => $vrai_nouveau_total,
    'total_initial' => $total_initial,
    'difference' => $reste_a_payer,
    'credits_utilises' => $credits_utilises
];

// parametres api bancaire (meme vendeur que pour les commandes)
require_once 'includes/getapikey.php';

$vendeur = 'TEST';
$api_key = getAPIKey($vendeur);
$montant = number_format($reste_a_payer, 2, '.', '');

// url de retour apres paiement (page dediee a la modification)
$protocole = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on') ? 'https' : 'http';
$dossier = rtrim(dirname($_SERVER['SCRIPT_NAME']), '/\\');
$retour = $protocole . '://' . $_SERVER['HTTP_HOST'] . $dossier . '/retour_paiement_modif.php';

// hash de controle pour cybank
$control = md5($api_key . '#' . $transaction_id . '#' . $montant . '#' . $vendeur . '#' . $retour . '#');
?>
<!DOCTYPE html>
<html lang="fr">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>La Table des Jedi — Paiement du complément</title>
    <link rel="icon" href="../images/logo.png">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            background: #0a0a1a;
            color: white;
            font-family: 'Segoe UI', sans-serif;
            display: flex;
            justify-content: center;
            align-items: center;
            min-height: 100vh;
            text-align: center;
        }

        .redirect-box {
            background: rgba(255, 255, 255, 0.05);
            border: 1px solid rgba(255, 215, 0, 0.3);
            border-radius: 16px;
            padding: 50px 40px;
            max-width: 420px;
        }

        .redirect-icon {
            font-size: 3em;
            margin-bottom: 20px;
        }

        h1 {
            font-size: 1.3em;
            margin-bottom: 12px;
            color: #ffd700;
        }

        p {
            font-size: 0.9em;
            color: rgba(255, 255, 255, 0.6);
            margin-bottom: 24px;
        }

        .loader {
            width: 40px;
            height: 40px;
            border: 3px solid rgba(255, 215, 0, 0.2);
            border-top-color: #ffd700;
            border-radius: 50%;
            animation: spin 0.8s linear infinite;
            margin: 0 auto 20px;
        }

        @keyframes spin {
            to {
                transform: rotate(360deg);
            }
        }

        .btn-manual {
            background: linear-gradient(135deg, #ffd700, #ff8c00);
            border: none;
            color: #1a1a2e;
            font-weight: bold;
            padding: 12px 24px;
            border-radius: 8px;
            cursor: pointer;
            font-size: 0.95em;
        }

        .montant-info {
            font-size: 1.4em;
            font-weight: bold;
            color: #ffd700;
            margin-bottom: 20px;
        }

        .info-detail {
            font-size: 0.85em;
            color: rgba(255, 255, 255, 0.4);
            margin-bottom: 10px;
        }
    </style>
</head>

<body>
    <div class="redirect-box">
        <div class="redirect-icon">🔒</div>
        <p class="info-detail">Complément pour la commande #<?= htmlspecialchars($id_commande) ?></p>
        <div class="montant-info"><?= number_format($difference, 2, ',', ' ') ?> ₹</div>
        <h1>Redirection sécurisée vers CYBank...</h1>
        <p>Vous allez payer le complément de votre commande modifiée. Ne fermez pas cette page.</p>
        <div class="loader"></div>

        <!-- formulaire cache vers cybank (meme api que pour les commandes) -->
        <form id="cybank-form" action="https://www.plateforme-smc.fr/cybank/index.php" method="POST">
            <input type="hidden" name="transaction" value="<?= htmlspecialchars($transaction_id) ?>">
            <input type="hidden" name="montant" value="<?= htmlspecialchars($montant) ?>">
            <input type="hidden" name="vendeur" value="<?= htmlspecialchars($vendeur) ?>">
            <input type="hidden" name="retour" value="<?= htmlspecialchars($retour) ?>">
            <input type="hidden" name="control" value="<?= htmlspecialchars($control) ?>">
            <button type="submit" class="btn-manual">Cliquer ici si pas de redirection automatique</button>
        </form>
    </div>

    <script src="../js/initier_paiement_modif.js" defer></script>
</body>

</html>