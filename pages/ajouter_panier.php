<?php
// on charge les fonctions pour avoir acces a la session
require_once 'includes/functions.php';

// === CONTROLE D'ACCES : connexion obligatoire pour ajouter au panier ===
if (!isset($_SESSION['user'])) {
    header('Location: connexion.php');
    exit;
}

// on recupere l'id du plat envoye par le formulaire
$id_plat = isset($_POST['id_plat']) ? $_POST['id_plat'] : null;

if ($id_plat) {
    // creation du panier si vide
    if (!isset($_SESSION['panier'])) {
        $_SESSION['panier'] = [];
    }

    // on verifie si le plat est deja dans le panier
    if (isset($_SESSION['panier'][$id_plat])) {
        // on incremente la quantite
        $_SESSION['panier'][$id_plat]++;
    } else {
        // on ajoute le plat avec une quantite de 1
        $_SESSION['panier'][$id_plat] = 1;
    }
}

// on redirige vers la carte
header('Location: produits.php');
exit;
