<?php
// api/update_profil.php
// point d'entrée asynchrone pour mettre à jour les données du profil client

header('Content-Type: application/json');

// on remonte d'un dossier car functions.php est dans pages/includes/
require_once '../pages/includes/functions.php';

// verification de la session
if (!isset($_SESSION['user'])) {
    echo json_encode(['success' => false, 'message' => 'Non autorisé.']);
    exit;
}

// lecture du payload json
$json = file_get_contents('php://input');
$data = json_decode($json, true);

if (!$data) {
    echo json_encode(['success' => false, 'message' => 'Données invalides.']);
    exit;
}

$champ = isset($data['champ']) ? $data['champ'] : '';
$valeur = isset($data['valeur']) ? trim($data['valeur']) : '';

$champs_autorises = ['login', 'prenom', 'nom', 'email', 'telephone', 'adresse'];

if (!in_array($champ, $champs_autorises)) {
    echo json_encode(['success' => false, 'message' => 'Champ non modifiable.']);
    exit;
}

// lecture des utilisateurs
$users = read_json('../data/users.json');
$user_id = $_SESSION['user']['id'];
$updated = false;

// verification unicité (email et login)
if ($champ === 'email' || $champ === 'login') {
    foreach ($users as $u) {
        if ($u['id'] !== $user_id && isset($u[$champ]) && strtolower($u[$champ]) === strtolower($valeur)) {
            echo json_encode(['success' => false, 'message' => 'Cette valeur est déjà utilisée.']);
            exit;
        }
    }
}

// mise à jour
foreach ($users as &$u) {
    if ($u['id'] === $user_id) {
        $u[$champ] = $valeur;
        $updated = true;
        // met à jour la session aussi si c'est un champ stocké en session
        if (isset($_SESSION['user'][$champ])) {
            $_SESSION['user'][$champ] = $valeur;
        }
        break;
    }
}

if ($updated) {
    write_json('../data/users.json', $users);
    echo json_encode(['success' => true, 'message' => 'Profil mis à jour.', 'valeur' => htmlspecialchars($valeur)]);
} else {
    echo json_encode(['success' => false, 'message' => 'Erreur lors de la mise à jour.']);
}
