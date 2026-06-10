<?php
// demarrage automatique de la session
session_start();

// verification du statut banni a chaque chargement de page
// on utilise __dir__ pour resoudre le chemin de maniere absolue,
// peu importe si le fichier est inclus depuis pages/ ou depuis api/
if (isset($_SESSION['user'])) {
    $chemin_users_abs = __DIR__ . '/../../data/users.json';
    if (file_exists($chemin_users_abs)) {
        $liste_users = json_decode(file_get_contents($chemin_users_abs), true);
        if ($liste_users) {
            foreach ($liste_users as $u) {
                if ($u['id'] == $_SESSION['user']['id']) {
                    $statut_u = isset($u['statut']) ? $u['statut'] : 'actif';
                    if ($statut_u === 'banni') {
                        // on detruit la session immediatement
                        session_unset();
                        session_destroy();

                        // si on est dans un contexte api (endpoint json), on
                        // retourne une erreur json plutot qu'une redirection html
                        $page_actuelle = basename($_SERVER['PHP_SELF']);
                        $dossier_actuel = basename(dirname($_SERVER['PHP_SELF']));
                        if ($dossier_actuel === 'api') {
                            echo json_encode(['success' => false, 'message' => 'Compte banni. Session terminée.']);
                            exit;
                        }

                        // sinon redirection vers la page de connexion
                        if ($page_actuelle !== 'connexion.php') {
                            header('Location: connexion.php?banni=1');
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
    // Chemin absolu vers le dossier data basé sur la position de ce fichier (pour Linux/Windows)
    $path = __DIR__ . '/../../data/' . $filename;
    if (!file_exists($path)) {
        return [];
    }
    $json = file_get_contents($path);
    return json_decode($json, true);
}

// ecrit un tableau php dans un fichier json du dossier data/
function write_json($filename, $data)
{
    // Chemin absolu vers le dossier data basé sur la position de ce fichier
    $path = __DIR__ . '/../../data/' . $filename;
    $json = json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
    file_put_contents($path, $json);
}
