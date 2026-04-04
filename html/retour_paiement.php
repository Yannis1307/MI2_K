<?php
// =============================================
// retour_paiement.php
// Reçoit le retour de CYBank (en GET), vérifie
// le hash de contrôle, crée la commande si accepté
// =============================================

require_once 'includes/functions.php';
require_once 'includes/getapikey.php';

// on recupere les parametres retournes par CYBank en GET
$transaction    = isset($_GET['transaction']) ? $_GET['transaction'] : '';
$montant        = isset($_GET['montant'])     ? $_GET['montant']     : '';
$vendeur        = isset($_GET['vendeur'])     ? $_GET['vendeur']     : '';
$statut         = isset($_GET['status'])      ? $_GET['status']      : (isset($_GET['statut']) ? $_GET['statut'] : '');
$control_recu   = isset($_GET['control'])     ? $_GET['control']     : '';

// on verifie que les parametres essentiels sont presents
if (empty($transaction) || empty($statut) || empty($vendeur)) {
    header('Location: accueil.php');
    exit;
}

// on recupere la cle API pour recalculer le hash de controle
$api_key = getAPIKey($vendeur);

// hash attendu selon la regle de CYBank : md5(api_key#transaction#montant#vendeur#statut#)
$control_attendu = md5($api_key . '#' . $transaction . '#' . $montant . '#' . $vendeur . '#' . $statut . '#');

// on verifie que la commande en cours existe en session et correspond a cette transaction
if (!isset($_SESSION['commande_en_cours']) || $_SESSION['commande_en_cours']['transaction'] !== $transaction) {
    // session perdue ou transaction inconnue
    $_SESSION['flash_error'] = 'Session expirée ou transaction invalide. Veuillez recommencer votre commande.';
    header('Location: panier.php');
    exit;
}

// === PAIEMENT ACCEPTE ===
if ($control_recu === $control_attendu && $statut === 'accepted') {

    $commande_data = $_SESSION['commande_en_cours'];

    // construction de la nouvelle commande
    $nouvelle_commande = [
        'id'              => 'JDI-' . strtoupper(substr(uniqid(), -5)),
        'id_client'       => $commande_data['id_client'],
        'login_client'    => $commande_data['login_client'],
        'date'            => date('d/m/Y'),
        'heure'           => date('H:i'),
        'type'            => $commande_data['type'],
        'heure_livraison' => $commande_data['heure_livraison'],
        'adresse'         => $commande_data['adresse'],
        'plats'           => $commande_data['plats'],
        'total'           => $commande_data['total'],
        'statut'          => 'en attente',
        'statut_paiement' => 'accepte',
        'transaction_id'  => $transaction,
        'montant_paye'    => floatval($montant),
        'id_livreur'      => null,
        'note_livraison'  => null,
        'note_qualite'    => null,
        'commentaire'     => '',
        'code_interphone' => '',
        'etage'           => ''
    ];

    // lecture + ajout dans commandes.json
    $commandes   = read_json('commandes.json');
    $commandes[] = $nouvelle_commande;
    write_json('commandes.json', $commandes);

    // on vide le panier et la commande en cours de la session
    unset($_SESSION['panier']);
    unset($_SESSION['commande_en_cours']);

    // message de succes pour la page profil
    $_SESSION['flash_success'] = 'Commande #' . $nouvelle_commande['id'] . ' validée avec succès ! Paiement CYBank accepté.';

    // redirection vers le profil pour voir l'historique
    header('Location: profil.php');
    exit;

// === PAIEMENT REFUSE ou HASH INVALIDE ===
} else {

    // on annule la commande en cours
    unset($_SESSION['commande_en_cours']);

    if ($control_recu !== $control_attendu) {
        // hash incorrect : possible tentative de fraude
        $_SESSION['flash_error'] = 'Erreur de sécurité : réponse CYBank invalide. Contactez le support.';
    } else {
        // paiement refusé par la banque
        $_SESSION['flash_error'] = 'Paiement refusé par CYBank. Vérifiez vos informations bancaires et réessayez.';
    }

    header('Location: panier.php');
    exit;
}
