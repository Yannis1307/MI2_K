<?php
// api/modifier_commande.php
// point d'entree asynchrone pour modifier une commande (cas prix egal ou inferieur)
// le cas prix superieur passe par cybank directement (initier_paiement_modif.php)

header('Content-Type: application/json');

// on remonte d'un dossier car functions.php est dans pages/includes/
require_once '../pages/includes/functions.php';

// verification de la session
if (!isset($_SESSION['user'])) {
    echo json_encode(['success' => false, 'message' => 'Non autorisé.']);
    exit;
}

// chargement des donnees veritables
$vrais_plats = read_json('../data/plats.json');
$vrais_menus = read_json('../data/menus.json');

// indexation pour recherche rapide
$index_plats = [];
foreach ($vrais_plats as $vp) {
    $index_plats[$vp['id']] = $vp;
}

$index_menus = [];
foreach ($vrais_menus as $vm) {
    $index_menus[$vm['id']] = $vm;
}

// lecture du payload json
$json = file_get_contents('php://input');
$data = json_decode($json, true);

if (!$data) {
    echo json_encode(['success' => false, 'message' => 'Données invalides.']);
    exit;
}

$id_commande = isset($data['id_commande']) ? $data['id_commande'] : '';
$plats = isset($data['plats']) ? $data['plats'] : [];
$menus = isset($data['menus']) ? $data['menus'] : [];
$nouveau_total = isset($data['nouveau_total']) ? floatval($data['nouveau_total']) : 0;
$total_initial = isset($data['total_initial']) ? floatval($data['total_initial']) : 0;

// verification que la commande n'est pas vide
if (empty($plats) && empty($menus)) {
    echo json_encode(['success' => false, 'message' => 'La commande ne peut pas être vide.']);
    exit;
}

// lecture des commandes
$commandes = read_json('../data/commandes.json');
$user_id = $_SESSION['user']['id'];
$commande_index = -1;

// on cherche la commande du client
foreach ($commandes as $i => $cmd) {
    if ($cmd['id'] === $id_commande && $cmd['id_client'] == $user_id) {
        $commande_index = $i;
        break;
    }
}

// verification que la commande existe
if ($commande_index === -1) {
    echo json_encode(['success' => false, 'message' => 'Commande introuvable.']);
    exit;
}

// securite : double verification que le statut est bien en attente
// si la commande est passee en preparation on bloque toute modification
$statut_actuel = $commandes[$commande_index]['statut'];
if ($statut_actuel !== 'en attente') {
    echo json_encode(['success' => false, 'message' => 'La commande est passée en "' . $statut_actuel . '". Modification impossible.']);
    exit;
}

// securite : on verifie qu'une modification n'est pas deja en cours
if (isset($_SESSION['modif_en_cours']) && $_SESSION['modif_en_cours']['id_commande'] === $id_commande) {
    echo json_encode(['success' => false, 'message' => 'Une modification est déjà en cours pour cette commande.']);
    exit;
}

// on recalcule le total cote serveur pour securiser avec les vrais prix
$total_recalcule = 0;

// securisation des plats
foreach ($plats as $k => $p) {
    $qte = intval($p['quantite']);
    if ($qte <= 0) {
        echo json_encode(['success' => false, 'message' => 'Quantité invalide détectée.']);
        exit;
    }
    
    $id_plat = $p['id_plat'];
    if (isset($index_plats[$id_plat])) {
        $vrai_prix = floatval($index_plats[$id_plat]['prix']);
        $vrai_sous_total = $vrai_prix * $qte;
        
        $plats[$k]['quantite'] = $qte;
        $plats[$k]['prix_unitaire'] = $vrai_prix;
        $plats[$k]['sous_total'] = $vrai_sous_total;
        
        $total_recalcule += $vrai_sous_total;
    } else {
        echo json_encode(['success' => false, 'message' => 'Produit invalide détecté.']);
        exit;
    }
}

// securisation des menus
foreach ($menus as $k => $m) {
    $qte = intval($m['quantite']);
    if ($qte <= 0) {
        echo json_encode(['success' => false, 'message' => 'Quantité invalide détectée.']);
        exit;
    }
    
    $id_menu = $m['id_menu'];
    if (isset($index_menus[$id_menu])) {
        $vrai_prix = floatval($index_menus[$id_menu]['prix_total']);
        $vrai_sous_total = $vrai_prix * $qte;
        
        $menus[$k]['quantite'] = $qte;
        $menus[$k]['prix_unitaire'] = $vrai_prix;
        $menus[$k]['sous_total'] = $vrai_sous_total;
        
        $total_recalcule += $vrai_sous_total;
    } else {
        echo json_encode(['success' => false, 'message' => 'Menu invalide détecté.']);
        exit;
    }
}

$total_recalcule = round($total_recalcule, 2);

// on recupere le total initial d'origine (depuis le json)
$ancien_total = isset($commandes[$commande_index]['total_initial'])
    ? floatval($commandes[$commande_index]['total_initial'])
    : floatval($commandes[$commande_index]['total']);

$difference = round($total_recalcule - $ancien_total, 2);

// securite : si le prix a augmente on refuse, il faut passer par cybank
if ($difference > 0) {
    echo json_encode(['success' => false, 'message' => 'Le prix a augmenté. Vous devez payer via CYBank.']);
    exit;
}


// mise a jour de la commande
$commandes[$commande_index]['plats'] = $plats;
$commandes[$commande_index]['menus'] = $menus;
$commandes[$commande_index]['total'] = $total_recalcule;

// on met a jour total_initial au nouveau prix
// comme ca la prochaine modification comparera au bon montant
$commandes[$commande_index]['total_initial'] = $total_recalcule;

// gestion de la difference de prix
if ($difference < 0) {
    // remboursement sous forme de credits
    $credits = abs($difference);

    $users = read_json('../data/users.json');
    foreach ($users as &$u) {
        if ($u['id'] == $user_id) {
            if (!isset($u['solde_credits'])) {
                $u['solde_credits'] = 0;
            }
            $u['solde_credits'] += $credits;
            $_SESSION['user']['solde_credits'] = $u['solde_credits'];
            break;
        }
    }
    write_json('../data/users.json', $users);

    $message = 'Commande modifiée. Un crédit de ' . number_format($credits, 2) . ' ₹ a été ajouté à votre profil.';
} else {
    // prix identique
    $message = 'Commande modifiée sans changement de prix.';
}

// sauvegarde de la commande
write_json('../data/commandes.json', $commandes);

// message flash pour le profil
$_SESSION['flash_success'] = $message;

echo json_encode(['success' => true, 'message' => $message]);
