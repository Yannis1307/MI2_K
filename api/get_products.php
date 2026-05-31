<?php
// api/get_products.php
// point d'entrée asynchrone pour récupérer les produits filtrés

header('Content-Type: application/json');
require_once '../pages/includes/functions.php';

// lecture de tous les plats
$plats = read_json('../data/plats.json');

// recuperation des filtres depuis la query string (get)
$category = isset($_GET['category']) ? $_GET['category'] : 'tous';
$diet = isset($_GET['diet']) ? $_GET['diet'] : '';
$sort = isset($_GET['sort']) ? $_GET['sort'] : '';
$search = isset($_GET['search']) ? strtolower(trim($_GET['search'])) : '';

$plats_filtres = [];

foreach ($plats as $plat) {
    // filtre de categorie
    if ($category !== 'tous' && $plat['categorie'] !== $category) {
        continue;
    }

    // filtre de regime / piquant
    if (!empty($diet)) {
        $plat_regimes = isset($plat['regimes']) ? $plat['regimes'] : [];
        if (!in_array($diet, $plat_regimes)) {
            continue;
        }
    }

    // filtre de recherche texte
    if (!empty($search)) {
        if (strpos(strtolower($plat['nom']), $search) === false && 
            strpos(strtolower($plat['description']), $search) === false) {
            continue;
        }
    }

    // formatage specifique des donnees pour simplifier l'affichage js
    $is_piquant = isset($plat['regimes']) && in_array('piquant', $plat['regimes']);
    $is_vege = isset($plat['regimes']) && in_array('vege', $plat['regimes']);
    
    // ajout au resultat
    $plat['is_piquant'] = $is_piquant;
    $plat['is_vege'] = $is_vege;
    $plats_filtres[] = $plat;
}

// tri des donnees en php (bien que l'enonce dise que le tri peut etre fait cote client sur les donnees deja recuperees,
// on peut aussi le faire ici ou laisser le client le gerer. faisons le au cas ou)
if ($sort === 'price_asc') {
    usort($plats_filtres, function($a, $b) {
        return $a['prix'] <=> $b['prix'];
    });
} elseif ($sort === 'price_desc') {
    usort($plats_filtres, function($a, $b) {
        return $b['prix'] <=> $a['prix'];
    });
}

// on retourne le json
echo json_encode(['success' => true, 'plats' => $plats_filtres]);
