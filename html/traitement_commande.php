<?php
// on charge les fonctions pour la session et le json
require_once 'includes/functions.php';

// verification que l'utilisateur est connecte
if (!isset($_SESSION['user'])) {
    header('Location: connexion.php');
    exit;
}

// verification que le panier n'est pas vide
if (empty($_SESSION['panier'])) {
    header('Location: panier.php');
    exit;
}

// on recupere les plats pour calculer le total
$plats = read_json('plats.json');
$plats_index = [];
foreach ($plats as $plat) {
    $plats_index[$plat['id']] = $plat;
}

// on recupere les infos du formulaire
$type_commande = isset($_POST['type_commande']) ? $_POST['type_commande'] : 'immediate';
$heure_livraison = isset($_POST['heure_livraison']) ? $_POST['heure_livraison'] : '';
$adresse = isset($_POST['adresse']) ? $_POST['adresse'] : '';
$mode_retrait = isset($_POST['mode_retrait']) ? $_POST['mode_retrait'] : 'livraison';

// simulation api cybank (paiement accepte par defaut)
$paiement_ok = true;

if ($paiement_ok) {

    // construction de la liste des plats commandes avec les details
    $plats_commandes = [];
    $total = 0;

    foreach ($_SESSION['panier'] as $id_plat => $quantite) {
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

    // lecture du fichier commandes existant (ou tableau vide)
    $fichier_commandes = '../data/commandes.json';
    if (file_exists($fichier_commandes)) {
        $decoded = json_decode(file_get_contents($fichier_commandes), true);
        $commandes = isset($decoded) ? $decoded : [];
    } else {
        $commandes = [];
    }

    // creation de la nouvelle commande
    $nouvelle_commande = [
        'id' => 'JDI-' . strtoupper(substr(uniqid(), -5)),
        'id_client' => $_SESSION['user']['id'],
        'login_client' => $_SESSION['user']['login'],
        'date' => date('d/m/Y'),
        'heure' => date('H:i'),
        'type' => $type_commande,
        'mode_retrait' => $mode_retrait,
        'heure_livraison' => ($type_commande === 'planifiee') ? $heure_livraison : null,
        'adresse' => $adresse,
        'plats' => $plats_commandes,
        'total' => $total,
        'statut' => 'en attente'
    ];

    // on ajoute la commande a la liste
    $commandes[] = $nouvelle_commande;

    // sauvegarde dans le fichier json
    write_json('commandes.json', $commandes);

    // on vide le panier
    unset($_SESSION['panier']);

    // message flash de succes
    $_SESSION['flash_success'] = 'Commande #' . $nouvelle_commande['id'] . ' validée ! Paiement CYBank accepté.';

    // redirection vers le profil
    header('Location: profil.php');
    exit;

} else {
    // en cas d'echec du paiement (pas utilise pour le moment)
    $_SESSION['flash_error'] = 'Le paiement a été refusé par CYBank.';
    header('Location: panier.php');
    exit;
}
