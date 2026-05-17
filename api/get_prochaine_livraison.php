<?php
// recuperation de la prochaine livraison (asynchrone)
require_once '../pages/includes/functions.php';

header('Content-Type: application/json');

// controle d'acces
if (!isset($_SESSION['user']) || $_SESSION['user']['role'] !== 'livreur') {
    echo json_encode(['success' => false, 'message' => 'Accès refusé.']);
    exit;
}

// lecture des commandes
$commandes = read_json('../data/commandes.json');
$id_livreur = $_SESSION['user']['id'];
$mission = null;

// on cherche la prochaine commande a livrer pour ce livreur
foreach ($commandes as $cmd) {
    if ($cmd['statut'] === 'en livraison' && isset($cmd['id_livreur']) && $cmd['id_livreur'] == $id_livreur) {
        $mission = $cmd;
        break; // on s'arrete a la premiere trouvee
    }
}

if ($mission) {
    echo json_encode(['success' => true, 'mission' => $mission]);
} else {
    echo json_encode(['success' => false, 'message' => 'Aucune mission en cours.']);
}
