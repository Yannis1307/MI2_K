<?php
// on charge les fonctions pour la session
require_once 'includes/functions.php';

// === CONTROLE D'ACCES : connexion obligatoire pour modifier le panier ===
if (!isset($_SESSION['user'])) {
    header('Location: connexion.php');
    exit;
}

// on recupere l'id du plat a retirer
$id_plat = $_POST['id_plat'] ?? null;

if ($id_plat && isset($_SESSION['panier'][$id_plat])) {
    // on decremente la quantite
    $_SESSION['panier'][$id_plat]--;

    // si la quantite tombe a 0, on supprime l'entree
    if ($_SESSION['panier'][$id_plat] <= 0) {
        unset($_SESSION['panier'][$id_plat]);
    }
}

// on redirige vers le panier
header('Location: panier.php');
exit;
