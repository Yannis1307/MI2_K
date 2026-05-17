<?php
require_once 'includes/functions.php';

if (isset($_GET['role'])) {
    $role_cible = $_GET['role'];
    $users = read_json('users.json');
    $user_trouve = null;

    // On cherche un utilisateur avec le rôle demandé pour s'y connecter
    foreach ($users as $user) {
        if ($user['role'] === $role_cible && $user['statut'] !== 'banni') {
            $user_trouve = $user;
            break;
        }
    }

    if ($user_trouve) {
        // on stocke les infos en session
        $_SESSION['user'] = [
            'id' => $user_trouve['id'],
            'login' => $user_trouve['login'],
            'role' => $user_trouve['role'],
            'nom' => $user_trouve['nom'],
            'prenom' => $user_trouve['prenom'],
            'solde_credits' => isset($user_trouve['solde_credits']) ? $user_trouve['solde_credits'] : 0
        ];

        // redirection selon le role
        if ($user_trouve['role'] === 'admin') {
            header('Location: admin.php');
            exit;
        } elseif ($user_trouve['role'] === 'restaurateur') {
            header('Location: commandes.php');
            exit;
        } elseif ($user_trouve['role'] === 'livreur') {
            header('Location: livraison.php');
            exit;
        } else {
            header('Location: accueil.php');
            exit;
        }
    } else {
        // Rôle non trouvé
        header('Location: accueil.php?error=role_not_found');
        exit;
    }
}

// Redirection par défaut si pas de rôle fourni
header('Location: accueil.php');
exit;
