<?php
// retour de cybank apres paiement
// verifie le statut et enregistre la commande

require_once 'includes/functions.php';
require_once 'includes/getapikey.php';

// recuperation des parametres get
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

// verification de la session
if (!isset($_SESSION['commande_en_cours']) || $_SESSION['commande_en_cours']['transaction'] !== $transaction) {
    $_SESSION['flash_error'] = 'Session expirée ou transaction invalide. Veuillez recommencer votre commande.';
    header('Location: panier.php');
    exit;
}

// traitement si paiement valide
if ($control_recu === $control_attendu && $statut === 'accepted') {

    $commande_data = $_SESSION['commande_en_cours'];

    // recuperation du telephone client
    $users_all = read_json('users.json');
    $telephone_client = '';
    foreach ($users_all as $u) {
        if ($u['id'] == $commande_data['id_client']) {
            $telephone_client = isset($u['telephone']) ? $u['telephone'] : '';
            break;
        }
    }

    // creation de la nouvelle commande
    $nouvelle_commande = [
        'id'               => 'JDI-' . strtoupper(substr(uniqid(), -5)),
        'id_client'        => $commande_data['id_client'],
        'login_client'     => $commande_data['login_client'],
        'date'             => date('d/m/Y'),
        'heure'            => date('H:i'),
        'type'             => $commande_data['type'],
        'mode_retrait'     => isset($commande_data['mode_retrait']) ? $commande_data['mode_retrait'] : 'livraison',
        'heure_livraison'  => $commande_data['heure_livraison'],
        'adresse'          => $commande_data['adresse'],
        'telephone_client' => $telephone_client,
        'plats'            => $commande_data['plats'],
        'menus'            => isset($commande_data['menus']) ? $commande_data['menus'] : [],
        'total'            => $commande_data['total'],
        'statut'           => 'en attente',
        'statut_paiement'  => 'accepte',
        'transaction_id'   => $transaction,
        'montant_paye'     => floatval($montant),
        'id_livreur'       => null,
        'note_livraison'   => null,
        'note_qualite'     => null,
        'commentaire'      => '',
        'code_interphone'  => isset($commande_data['code_interphone']) ? $commande_data['code_interphone'] : '',
        'etage'            => isset($commande_data['etage']) ? $commande_data['etage'] : ''
    ];

    // enregistrement dans commandes.json
    $commandes   = read_json('commandes.json');
    $commandes[] = $nouvelle_commande;
    write_json('commandes.json', $commandes);

    // nettoyage sessions panier
    unset($_SESSION['panier']);
    unset($_SESSION['panier_menus']);
    unset($_SESSION['commande_en_cours']);

    // notification de succes
    $_SESSION['flash_success'] = 'Commande #' . $nouvelle_commande['id'] . ' validée avec succès ! Paiement CYBank accepté.';

    // redirection vers profil
    header('Location: profil.php');
    exit;

// traitement si echec ou fraude
} else {

    // annulation
    unset($_SESSION['commande_en_cours']);

    if ($control_recu !== $control_attendu) {
        // erreur securite
        $_SESSION['flash_error'] = 'Erreur de sécurité : réponse CYBank invalide. Contactez le support.';
    } else {
        // refus bancaire
        $_SESSION['flash_error'] = 'Paiement refusé par CYBank. Vérifiez vos informations bancaires et réessayez.';
    }

    header('Location: panier.php');
    exit;
}
