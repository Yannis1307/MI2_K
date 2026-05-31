<?php
require_once '../pages/includes/functions.php';
header('Content-Type: application/json');

if (!isset($_SESSION['user'])) {
    echo json_encode(['success' => false, 'message' => 'Non autorisé']);
    exit;
}

$data = json_decode(file_get_contents('php://input'), true);

if (!$data) {
    echo json_encode(['success' => false, 'message' => 'Données invalides']);
    exit;
}

$id = isset($data['id']) ? (int)$data['id'] : 0;
$type = isset($data['type']) ? $data['type'] : '';
$quantite = isset($data['quantite']) ? (int)$data['quantite'] : 0;

if ($id <= 0 || !in_array($type, ['plat', 'menu'])) {
    echo json_encode(['success' => false, 'message' => 'Paramètres invalides']);
    exit;
}

// mise à jour de la quantité
if ($type === 'plat') {
    if (!isset($_SESSION['panier'])) {
        $_SESSION['panier'] = [];
    }
    if ($quantite > 0) {
        $_SESSION['panier'][$id] = $quantite;
    } else {
        unset($_SESSION['panier'][$id]);
    }
} else {
    if (!isset($_SESSION['panier_menus'])) {
        $_SESSION['panier_menus'] = [];
    }
    if ($quantite > 0) {
        $_SESSION['panier_menus'][$id] = $quantite;
    } else {
        unset($_SESSION['panier_menus'][$id]);
    }
}

// recalcul du total et des macros
$plats = read_json('plats.json');
$plats_index = [];
if ($plats) {
    foreach ($plats as $plat) {
        $plats_index[$plat['id']] = $plat;
    }
}

$menus = read_json('menus.json');
$menus_index = [];
if ($menus) {
    foreach ($menus as $menu) {
        $menus_index[$menu['id']] = $menu;
    }
}

$total = 0;
$total_calories = 0;
$total_proteines = 0;
$total_glucides = 0;
$total_lipides = 0;

$panier = isset($_SESSION['panier']) ? $_SESSION['panier'] : [];
$panier_menus = isset($_SESSION['panier_menus']) ? $_SESSION['panier_menus'] : [];

foreach ($panier as $id_plat => $qty) {
    if (isset($plats_index[$id_plat])) {
        $p = $plats_index[$id_plat];
        $total += $p['prix'] * $qty;
        
        // calcul des macros du panier
        $total_calories += (isset($p['calories']) ? $p['calories'] : 0) * $qty;
        $total_proteines += (isset($p['proteines']) ? $p['proteines'] : 0) * $qty;
        $total_glucides += (isset($p['glucides']) ? $p['glucides'] : 0) * $qty;
        $total_lipides += (isset($p['lipides']) ? $p['lipides'] : 0) * $qty;
    }
}

foreach ($panier_menus as $id_menu => $qty) {
    if (isset($menus_index[$id_menu])) {
        $total += $menus_index[$id_menu]['prix_total'] * $qty;
        
        // calcul des macros pour chaque menu
        foreach ($menus_index[$id_menu]['plats_inclus'] as $id_plat) {
            if (isset($plats_index[$id_plat])) {
                $p = $plats_index[$id_plat];
                $total_calories += (isset($p['calories']) ? $p['calories'] : 0) * $qty;
                $total_proteines += (isset($p['proteines']) ? $p['proteines'] : 0) * $qty;
                $total_glucides += (isset($p['glucides']) ? $p['glucides'] : 0) * $qty;
                $total_lipides += (isset($p['lipides']) ? $p['lipides'] : 0) * $qty;
            }
        }
    }
}

$nb_articles = array_sum($panier) + array_sum($panier_menus);

echo json_encode([
    'success' => true,
    'total' => $total,
    'nb_articles' => $nb_articles,
    'macros' => [
        'calories' => $total_calories,
        'proteines' => $total_proteines,
        'glucides' => $total_glucides,
        'lipides' => $total_lipides
    ]
]);
