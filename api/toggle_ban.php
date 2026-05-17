<?php
// api/toggle_ban.php
header('Content-Type: application/json');
require_once '../pages/includes/functions.php';

if (!isset($_SESSION['user']) || $_SESSION['user']['role'] !== 'admin') {
    echo json_encode(['success' => false, 'message' => 'Non autorisé.']);
    exit;
}

$json = file_get_contents('php://input');
$data = json_decode($json, true);

if (!$data || !isset($data['id_user'])) {
    echo json_encode(['success' => false, 'message' => 'Données invalides.']);
    exit;
}

$id_user = (int) $data['id_user'];
$users = read_json('../data/users.json');
$updated = false;
$new_status = '';

foreach ($users as &$u) {
    if ($u['id'] === $id_user) {
        if ($u['role'] === 'admin') {
            echo json_encode(['success' => false, 'message' => 'Impossible de bannir un administrateur.']);
            exit;
        }

        $current = isset($u['statut']) ? $u['statut'] : 'actif';
        if ($current === 'banni') {
            $u['statut'] = 'actif';
            $new_status = 'actif';
        } else {
            $u['statut'] = 'banni';
            $new_status = 'banni';
        }
        $updated = true;
        break;
    }
}

if ($updated) {
    write_json('../data/users.json', $users);
    echo json_encode(['success' => true, 'new_status' => $new_status]);
} else {
    echo json_encode(['success' => false, 'message' => 'Utilisateur introuvable.']);
}
