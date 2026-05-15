<?php
// api/update_user.php
// modification des infos d'un utilisateur par l'admin

header('Content-Type: application/json');
require_once '../pages/includes/functions.php';

// verification que c'est bien un admin
if (!isset($_SESSION['user']) || $_SESSION['user']['role'] !== 'admin') {
    echo json_encode(['success' => false, 'message' => 'Non autorisé.']);
    exit;
}

// lecture du payload json
$json = file_get_contents('php://input');
$data = json_decode($json, true);

if (!$data || !isset($data['id_user'])) {
    echo json_encode(['success' => false, 'message' => 'Données invalides.']);
    exit;
}

$id_user = (int)$data['id_user'];
$nouveau_role = isset($data['role']) ? trim($data['role']) : '';
$nouveau_premium = isset($data['statut_premium']) ? trim($data['statut_premium']) : '';

// verification des valeurs autorisees pour le role
$roles_autorises = ['client', 'livreur', 'restaurateur'];
if (!empty($nouveau_role) && !in_array($nouveau_role, $roles_autorises)) {
    echo json_encode(['success' => false, 'message' => 'Rôle invalide.']);
    exit;
}

// verification des valeurs autorisees pour le statut premium
$premium_autorises = ['normal', 'premium', 'vip'];
if (!empty($nouveau_premium) && !in_array($nouveau_premium, $premium_autorises)) {
    echo json_encode(['success' => false, 'message' => 'Statut premium invalide.']);
    exit;
}

// lecture des utilisateurs
$users = read_json('../data/users.json');
$updated = false;

foreach ($users as &$u) {
    if ($u['id'] === $id_user) {
        // on interdit de modifier un admin
        if ($u['role'] === 'admin') {
            echo json_encode(['success' => false, 'message' => 'Impossible de modifier un administrateur.']);
            exit;
        }

        // modification du role de l'utilisateur
        if (!empty($nouveau_role)) {
            $u['role'] = $nouveau_role;
        }

        // modification du statut premium
        if (!empty($nouveau_premium)) {
            $u['statut_premium'] = $nouveau_premium;
        }

        $updated = true;
        break;
    }
}

if ($updated) {
    write_json('../data/users.json', $users);
    echo json_encode([
        'success' => true,
        'role' => $nouveau_role,
        'statut_premium' => $nouveau_premium
    ]);
} else {
    echo json_encode(['success' => false, 'message' => 'Utilisateur introuvable.']);
}
