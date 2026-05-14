<?php
// demarrage automatique de la session
session_start();

// Verification du statut de l'utilisateur (bannissement en temps reel)
if (isset($_SESSION['user'])) {
    $current_id = $_SESSION['user']['id'];
    $path = __DIR__ . '/../../data/users.json';
    if (file_exists($path)) {
        $users = json_decode(file_get_contents($path), true);
        if ($users) {
            foreach ($users as $u) {
                if ($u['id'] == $current_id) {
                    if (isset($u['statut']) && $u['statut'] === 'banni') {
                        // Utilisateur banni : on detruit la session
                        session_destroy();
                        // On redirige vers la connexion
                        $currentPage = basename($_SERVER['PHP_SELF']);
                        if ($currentPage !== 'connexion.php') {
                            header('Location: connexion.php');
                            exit;
                        }
                    }
                    break;
                }
            }
        }
    }
}

// fonctions utilitaires pour le projet

// lit un fichier json depuis le dossier data/ et le retourne en tableau php
function read_json($filename)
{
    $path = '../data/' . $filename;
    if (!file_exists($path)) {
        return [];
    }
    $json = file_get_contents($path);
    return json_decode($json, true);
}

// ecrit un tableau php dans un fichier json du dossier data/
function write_json($filename, $data)
{
    $path = '../data/' . $filename;
    $json = json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
    file_put_contents($path, $json);
}
