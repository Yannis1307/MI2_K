<?php
// =============================================
// initier_paiement.php
// Reçoit le formulaire du panier, sauvegarde la
// commande en session, puis redirige vers CYBank
// =============================================

require_once 'includes/functions.php';

// === CONTROLE D'ACCES ===
if (!isset($_SESSION['user'])) {
    header('Location: connexion.php');
    exit;
}

// doit venir d'un POST depuis panier.php
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: panier.php');
    exit;
}

// panier ne doit pas etre vide
if (empty($_SESSION['panier'])) {
    header('Location: panier.php');
    exit;
}

// on recupere les donnees du formulaire panier
$type_commande   = isset($_POST['type_commande'])   ? $_POST['type_commande']   : 'immediate';
$heure_livraison = isset($_POST['heure_livraison']) ? $_POST['heure_livraison'] : '';
$adresse         = isset($_POST['adresse'])         ? trim($_POST['adresse'])   : '';

if (empty($adresse)) {
    $_SESSION['flash_error'] = 'Veuillez renseigner une adresse de livraison.';
    header('Location: panier.php');
    exit;
}

// on calcule le total depuis les plats JSON
$plats       = read_json('plats.json');
$plats_index = [];
foreach ($plats as $p) {
    $plats_index[$p['id']] = $p;
}

$plats_commandes = [];
$total = 0;
foreach ($_SESSION['panier'] as $id_plat => $quantite) {
    if (isset($plats_index[$id_plat])) {
        $p         = $plats_index[$id_plat];
        $sous_total = $p['prix'] * $quantite;
        $total     += $sous_total;
        $plats_commandes[] = [
            'id_plat'       => $id_plat,
            'nom'           => $p['nom'],
            'quantite'      => $quantite,
            'prix_unitaire' => $p['prix'],
            'sous_total'    => $sous_total
        ];
    }
}

if ($total <= 0) {
    header('Location: panier.php');
    exit;
}

// generation d'un identifiant de transaction unique (10 chars alphanumeriques)
$transaction_id = strtoupper(bin2hex(random_bytes(5)));

// on sauvegarde toutes les donnees de la commande EN SESSION
// (elles doivent survivre au voyage aller-retour sur CYBank)
$_SESSION['commande_en_cours'] = [
    'transaction'    => $transaction_id,
    'id_client'      => $_SESSION['user']['id'],
    'login_client'   => $_SESSION['user']['login'],
    'type'           => $type_commande,
    'heure_livraison'=> ($type_commande === 'planifiee') ? $heure_livraison : null,
    'adresse'        => $adresse,
    'plats'          => $plats_commandes,
    'total'          => $total,
];

// =============================================
// Paramètres de l'API CYBank
// =============================================
require_once 'includes/getapikey.php';

// Le fichier getapikey.php n'accepte que les codes de MI-2_A à MI-2_J donc on ne peut pas mettre notre code vendeur MI-2_K
// Alors on utilise TEST pour tester
$vendeur  = 'TEST';
$api_key  = getAPIKey($vendeur);
$montant  = number_format($total, 2, '.', '');

// URL de retour apres paiement (CYBank y ajoutera ses parametres GET)
$protocole = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on') ? 'https' : 'http';
$dossier   = rtrim(dirname($_SERVER['SCRIPT_NAME']), '/\\');
$retour    = $protocole . '://' . $_SERVER['HTTP_HOST'] . $dossier . '/retour_paiement.php';

// hash de controle : md5(api_key#transaction#montant#vendeur#retour#)
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
        * { margin: 0; padding: 0; box-sizing: border-box; }
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
            background: rgba(255,255,255,0.05);
            border: 1px solid rgba(255,215,0,0.3);
            border-radius: 16px;
            padding: 50px 40px;
            max-width: 420px;
        }
        .redirect-icon { font-size: 3em; margin-bottom: 20px; }
        h1 { font-size: 1.3em; margin-bottom: 12px; color: #ffd700; }
        p  { font-size: 0.9em; color: rgba(255,255,255,0.6); margin-bottom: 24px; }
        .loader {
            width: 40px; height: 40px;
            border: 3px solid rgba(255,215,0,0.2);
            border-top-color: #ffd700;
            border-radius: 50%;
            animation: spin 0.8s linear infinite;
            margin: 0 auto 20px;
        }
        @keyframes spin { to { transform: rotate(360deg); } }
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

        <!-- formulaire caché qui s'auto-soumet vers CYBank -->
        <form id="cybank-form" action="https://www.plateforme-smc.fr/cybank/index.php" method="POST">
            <input type="hidden" name="transaction" value="<?= htmlspecialchars($transaction_id) ?>">
            <input type="hidden" name="montant"     value="<?= htmlspecialchars($montant) ?>">
            <input type="hidden" name="vendeur"     value="<?= htmlspecialchars($vendeur) ?>">
            <input type="hidden" name="retour"      value="<?= htmlspecialchars($retour) ?>">
            <input type="hidden" name="control"     value="<?= htmlspecialchars($control) ?>">
            <button type="submit" class="btn-manual">Cliquer ici si pas de redirection automatique</button>
        </form>
    </div>

    <script>
        // auto-soumission du formulaire apres 500ms
        setTimeout(function() {
            document.getElementById('cybank-form').submit();
        }, 500);
    </script>
</body>
</html>
