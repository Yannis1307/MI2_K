<?php
// pages/retour_paiement_modif.php
// retour de cybank apres paiement du complement de modification
// on applique la modification seulement si cybank confirme le paiement

require_once 'includes/functions.php';
require_once 'includes/getapikey.php';

// recuperation des parametres get de cybank
$transaction    = isset($_GET['transaction']) ? $_GET['transaction'] : '';
$montant        = isset($_GET['montant'])     ? $_GET['montant']     : '';
$vendeur        = isset($_GET['vendeur'])     ? $_GET['vendeur']     : '';
$statut         = isset($_GET['status'])      ? $_GET['status']      : (isset($_GET['statut']) ? $_GET['statut'] : '');
$control_recu   = isset($_GET['control'])     ? $_GET['control']     : '';

// verification des parametres de base
if (empty($transaction) || empty($statut) || empty($vendeur)) {
    header('Location: accueil.php');
    exit;
}

// recuperation de la cle api
$api_key = getAPIKey($vendeur);

// calcul du controle attendu
$control_attendu = md5($api_key . '#' . $transaction . '#' . $montant . '#' . $vendeur . '#' . $statut . '#');

// verification de la session de modification
if (!isset($_SESSION['modif_en_cours']) || $_SESSION['modif_en_cours']['transaction'] !== $transaction) {
    $_SESSION['flash_error'] = 'Session expirée ou transaction invalide. Veuillez recommencer la modification.';
    header('Location: profil.php');
    exit;
}

// traitement si paiement valide
if ($control_recu === $control_attendu && $statut === 'accepted') {

    $modif_data = $_SESSION['modif_en_cours'];

    // on relit les commandes pour avoir la version la plus recente
    $commandes = read_json('commandes.json');
    $user_id = $_SESSION['user']['id'];
    $commande_index = -1;

    // on cherche la commande du client
    foreach ($commandes as $i => $cmd) {
        if ($cmd['id'] === $modif_data['id_commande'] && $cmd['id_client'] == $user_id) {
            $commande_index = $i;
            break;
        }
    }

    // securite : double verification que la commande est toujours en attente
    if ($commande_index === -1) {
        unset($_SESSION['modif_en_cours']);
        $_SESSION['flash_error'] = 'Commande introuvable.';
        header('Location: profil.php');
        exit;
    }

    // securite : on bloque si la commande est passee en preparation entre temps
    if ($commandes[$commande_index]['statut'] !== 'en attente') {
        unset($_SESSION['modif_en_cours']);
        $_SESSION['flash_error'] = 'La commande est déjà en préparation et ne peut plus être modifiée. Le paiement sera remboursé.';
        header('Location: profil.php');
        exit;
    }


    // mise a jour de la commande avec les nouvelles donnees
    $commandes[$commande_index]['plats'] = $modif_data['plats'];
    $commandes[$commande_index]['menus'] = $modif_data['menus'];
    $commandes[$commande_index]['total'] = $modif_data['nouveau_total'];

    // on met a jour total_initial au nouveau prix paye
    // comme ca la prochaine modification comparera au bon montant
    $commandes[$commande_index]['total_initial'] = $modif_data['nouveau_total'];

    // sauvegarde dans commandes.json
    write_json('commandes.json', $commandes);

    // nettoyage de la session de modification
    unset($_SESSION['modif_en_cours']);

    // notification de succes avec le montant paye
    $diff = number_format($modif_data['difference'], 2);
    $_SESSION['flash_success'] = 'Commande #' . $modif_data['id_commande'] . ' modifiée avec succès ! Complément de ' . $diff . ' ₹ réglé via CYBank.';

    // on rafraichit la page pour voir la commande modifiee
    header('Location: profil.php');
    exit;

// traitement si echec ou fraude
} else {

    // nettoyage de la session de modification
    unset($_SESSION['modif_en_cours']);

    if ($control_recu !== $control_attendu) {
        // erreur securite
        $_SESSION['flash_error'] = 'Erreur de sécurité : réponse CYBank invalide. La modification a été annulée.';
    } else {
        // refus bancaire
        $_SESSION['flash_error'] = 'Paiement refusé par CYBank. La commande n\'a pas été modifiée.';
    }

    header('Location: profil.php');
    exit;
}
