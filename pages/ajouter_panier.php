<?php
// chargement des fonctions pour la session
require_once 'includes/functions.php';

// controle d'accès : connexion requise
if (!isset($_SESSION['user'])) {
    header('Location: connexion.php');
    exit;
}

// on recupere l'id envoye par le formulaire
$id_plat = isset($_POST['id_plat']) ? $_POST['id_plat'] : null;
$id_menu = isset($_POST['id_menu']) ? $_POST['id_menu'] : null;

// gestion des plats dans le panier
if ($id_plat) {
    if (!isset($_SESSION['panier'])) {
        $_SESSION['panier'] = [];
    }
    if (isset($_SESSION['panier'][$id_plat])) {
        $_SESSION['panier'][$id_plat]++;
    } else {
        $_SESSION['panier'][$id_plat] = 1;
    }
}

// gestion des menus dans le panier
if ($id_menu) {
    if (!isset($_SESSION['panier_menus'])) {
        $_SESSION['panier_menus'] = [];
    }
    if (isset($_SESSION['panier_menus'][$id_menu])) {
        $_SESSION['panier_menus'][$id_menu]++;
    } else {
        $_SESSION['panier_menus'][$id_menu] = 1;
    }
}

// redirection vers la carte
header('Location: produits.php');
exit;
