<?php
// api/update_statut_commande.php
// changement de statut d'une commande en asynchrone (restaurateur ou livreur)

header('Content-Type: application/json');
require_once '../pages/includes/functions.php';

// verification de la session
if (!isset($_SESSION['user'])) {
    echo json_encode(['success' => false, 'message' => 'Non autorisé.']);
    exit;
}

$role = $_SESSION['user']['role'];

// seuls les restaurateurs et livreurs peuvent changer le statut
if ($role !== 'restaurateur' && $role !== 'livreur') {
    echo json_encode(['success' => false, 'message' => 'Accès refusé.']);
    exit;
}

// lecture du payload json
$json = file_get_contents('php://input');
$data = json_decode($json, true);

if (!$data || !isset($data['id_commande']) || !isset($data['action_statut'])) {
    echo json_encode(['success' => false, 'message' => 'Données invalides.']);
    exit;
}

$id_cmd = trim($data['id_commande']);
$action_statut = trim($data['action_statut']);
$id_livreur_manuel = isset($data['id_livreur_manuel']) ? trim($data['id_livreur_manuel']) : '';

// lecture des commandes
$commandes = read_json('../data/commandes.json');
$updated = false;
$new_statut = '';

foreach ($commandes as $index => $cmd) {
    if ($cmd['id'] === $id_cmd) {

        // workflow du restaurateur : en attente > en preparation > prete > en livraison
        if ($action_statut === 'preparation' && $cmd['statut'] === 'en attente') {
            $commandes[$index]['statut'] = 'en préparation';
            $new_statut = 'en préparation';

        } elseif ($action_statut === 'prete' && $cmd['statut'] === 'en préparation') {
            $commandes[$index]['statut'] = 'prête';
            $new_statut = 'prête';

        } elseif ($action_statut === 'livraison' && $cmd['statut'] === 'prête') {
            $commandes[$index]['statut'] = 'en livraison';
            $new_statut = 'en livraison';

            // assignation du livreur (manuel ou auto)
            if (!empty($id_livreur_manuel)) {
                $commandes[$index]['id_livreur'] = $id_livreur_manuel;
            } else {
                // on prend le premier livreur actif
                $users = read_json('../data/users.json');
                foreach ($users as $u) {
                    if ($u['role'] === 'livreur' && (isset($u['statut']) ? $u['statut'] : 'actif') === 'actif') {
                        $commandes[$index]['id_livreur'] = $u['id'];
                        break;
                    }
                }
            }

        } elseif ($action_statut === 'a_recuperer' && $cmd['statut'] === 'prête') {
            $commandes[$index]['statut'] = 'à récupérer';
            $new_statut = 'à récupérer';

        } elseif ($action_statut === 'livrer') {
            // verification pour le livreur : seul le livreur assigne peut valider
            if ($role === 'livreur') {
                if ($cmd['statut'] !== 'en livraison' || $cmd['id_livreur'] != $_SESSION['user']['id']) {
                    echo json_encode(['success' => false, 'message' => 'Action non autorisée pour ce livreur.']);
                    exit;
                }
            }
            $commandes[$index]['statut'] = 'livré';
            $new_statut = 'livré';

        } elseif ($action_statut === 'abandonner') {
            $commandes[$index]['statut'] = 'abandonné';
            $new_statut = 'abandonné';

        } else {
            echo json_encode(['success' => false, 'message' => 'Transition de statut invalide.']);
            exit;
        }

        $updated = true;
        write_json('../data/commandes.json', $commandes);
        break;
    }
}

if ($updated) {
    echo json_encode(['success' => true, 'new_statut' => $new_statut]);
} else {
    echo json_encode(['success' => false, 'message' => 'Commande introuvable.']);
}
