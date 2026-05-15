<?php
// demarrage automatique de la session
session_start();

// verification du statut banni a chaque chargement de page
if (isset($_SESSION['user'])) {
    $chemin_users = '../data/users.json';
    if (file_exists($chemin_users)) {
        $liste_users = json_decode(file_get_contents($chemin_users), true);
        if ($liste_users) {
            foreach ($liste_users as $u) {
                if ($u['id'] == $_SESSION['user']['id']) {
                    $statut_u = isset($u['statut']) ? $u['statut'] : 'actif';
                    if ($statut_u === 'banni') {
                        // utilisateur banni : on detruit sa session immediatement
                        session_destroy();
                        $page_actuelle = basename($_SERVER['PHP_SELF']);
                        if ($page_actuelle !== 'connexion.php') {
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
