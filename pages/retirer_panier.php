<?php
// chargement des fonctions pour la session
require_once 'includes/functions.php';

// controle d'accès : connexion requise
if (!isset($_SESSION['user'])) {
    header('Location: connexion.php');
    exit;
}

// recupere l'id voulu
$id_plat = isset($_POST['id_plat']) ? $_POST['id_plat'] : null;
$id_menu = isset($_POST['id_menu']) ? $_POST['id_menu'] : null;

// gestion des plats
if ($id_plat && isset($_SESSION['panier'][$id_plat])) {
    $_SESSION['panier'][$id_plat]--;
    if ($_SESSION['panier'][$id_plat] <= 0) {
        unset($_SESSION['panier'][$id_plat]);
    }
}

// gestion des menus
if ($id_menu && isset($_SESSION['panier_menus'][$id_menu])) {
    $_SESSION['panier_menus'][$id_menu]--;
    if ($_SESSION['panier_menus'][$id_menu] <= 0) {
        unset($_SESSION['panier_menus'][$id_menu]);
    }
}

// redirection vers le panier
header('Location: panier.php');
exit;
