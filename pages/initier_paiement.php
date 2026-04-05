<?php
// initialisation du paiement
// recoit le formulaire, sauvegarde en session puis redirige vers la banque

require_once 'includes/functions.php';

// controle d'acces
if (!isset($_SESSION['user'])) {
    header('Location: connexion.php');
    exit;
}

// verification de la methode
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: panier.php');
    exit;
}

// verification du panier
if (empty($_SESSION['panier']) && empty($_SESSION['panier_menus'])) {
    header('Location: panier.php');
    exit;
}

// recuperation des donnees du formulaire
$type_commande = isset($_POST['type_commande']) ? $_POST['type_commande'] : 'immediate';
$heure_livraison = isset($_POST['heure_livraison']) ? $_POST['heure_livraison'] : '';
$mode_retrait = isset($_POST['mode_retrait']) ? $_POST['mode_retrait'] : 'livraison';
$adresse = isset($_POST['adresse']) ? trim($_POST['adresse']) : '';

// on recupere l'etage et l'interphone
$code_interphone = isset($_POST['code_interphone']) ? trim($_POST['code_interphone']) : '';
$etage = isset($_POST['etage']) ? trim($_POST['etage']) : '';

if ($mode_retrait === 'livraison' && empty($adresse)) {
    $_SESSION['flash_error'] = 'Veuillez renseigner une adresse de livraison.';
    header('Location: panier.php');
    exit;
}

// recuperation des plats
$plats = read_json('plats.json');
$plats_index = [];
foreach ($plats as $p) {
    $plats_index[$p['id']] = $p;
}

// recuperation des menus
$menus = read_json('menus.json');
$menus_index = [];
foreach ($menus as $m) {
    $menus_index[$m['id']] = $m;
}

$plats_commandes = [];
$menus_commandes = [];
$total = 0;

// traitement des plats
$panier = isset($_SESSION['panier']) ? $_SESSION['panier'] : [];
foreach ($panier as $id_plat => $quantite) {
    if (isset($plats_index[$id_plat])) {
        $p = $plats_index[$id_plat];
        $sous_total = $p['prix'] * $quantite;
        $total += $sous_total;
        $plats_commandes[] = [
            'id_plat' => $id_plat,
            'nom' => $p['nom'],
            'quantite' => $quantite,
            'prix_unitaire' => $p['prix'],
            'sous_total' => $sous_total
        ];
    }
}

// traitement des menus
$panier_menus = isset($_SESSION['panier_menus']) ? $_SESSION['panier_menus'] : [];
foreach ($panier_menus as $id_menu => $quantite) {
    if (isset($menus_index[$id_menu])) {
        $m = $menus_index[$id_menu];
        $sous_total = $m['prix_total'] * $quantite;
        $total += $sous_total;
        
        // recuperation des noms des plats inclus
        $plats_inclus_noms = [];
        foreach ($m['plats_inclus'] as $id_plat_inclus) {
            if (isset($plats_index[$id_plat_inclus])) {
                $plats_inclus_noms[] = $plats_index[$id_plat_inclus]['nom'];
            }
        }
        
        $menus_commandes[] = [
            'id_menu' => $id_menu,
            'nom' => $m['nom'],
            'quantite' => $quantite,
            'prix_unitaire' => $m['prix_total'],
            'sous_total' => $sous_total,
            'plats_details' => $plats_inclus_noms
        ];
    }
}

// securite sur le total
if ($total <= 0) {
    header('Location: panier.php');
    exit;
}

// generation d'un identifiant de transaction unique
$transaction_id = strtoupper(bin2hex(random_bytes(5)));

// sauvegarde en session de la commande
$_SESSION['commande_en_cours'] = [
    'transaction' => $transaction_id,
    'id_client' => $_SESSION['user']['id'],
    'login_client' => $_SESSION['user']['login'],
    'type' => $type_commande,
    'mode_retrait' => $mode_retrait,
    'heure_livraison' => ($type_commande === 'planifiee') ? $heure_livraison : null,
    'adresse' => $adresse,
    'code_interphone' => $code_interphone,
    'etage' => $etage,
    'plats' => $plats_commandes,
    'menus' => $menus_commandes,
    'total' => $total,
];

// parametres api bancaire
require_once 'includes/getapikey.php';

$vendeur = 'TEST';
$api_key = getAPIKey($vendeur);
$montant = number_format($total, 2, '.', '');

// url de retour apres paiement
$protocole = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on') ? 'https' : 'http';
$dossier = rtrim(dirname($_SERVER['SCRIPT_NAME']), '/\\');
$retour = $protocole . '://' . $_SERVER['HTTP_HOST'] . $dossier . '/retour_paiement.php';

// hash de controle
$control = md5($api_key . '#' . $transaction_id . '#' . $montant . '#' . $vendeur . '#' . $retour . '#');
?>
<!DOCTYPE html>
<html lang="fr">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>La Table des Jedi — Redirection vers CYBank</title>
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
    </style>
</head>

<body>
    <div class="redirect-box">
        <div class="redirect-icon">🔒</div>
        <div class="montant-info"><?= number_format($total, 2, ',', ' ') ?> ₹</div>
        <h1>Redirection sécurisée vers CYBank...</h1>
        <p>Vous allez être redirigé vers la plateforme de paiement sécurisée. Ne fermez pas cette page.</p>
        <div class="loader"></div>

        <!-- formulaire cache vers cybank -->
        <form id="cybank-form" action="https://www.plateforme-smc.fr/cybank/index.php" method="POST">
            <input type="hidden" name="transaction" value="<?= htmlspecialchars($transaction_id) ?>">
            <input type="hidden" name="montant" value="<?= htmlspecialchars($montant) ?>">
            <input type="hidden" name="vendeur" value="<?= htmlspecialchars($vendeur) ?>">
            <input type="hidden" name="retour" value="<?= htmlspecialchars($retour) ?>">
            <input type="hidden" name="control" value="<?= htmlspecialchars($control) ?>">
            <button type="submit" class="btn-manual">Cliquer ici si pas de redirection automatique</button>
        </form>
    </div>

    <script>
        // auto-soumission
        setTimeout(function () {
            document.getElementById('cybank-form').submit();
        }, 500);
    </script>
</body>

</html>