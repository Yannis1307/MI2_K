<?php
// demarrage de la session
session_start();
// on charge les fonctions json
require_once 'includes/functions.php';

// === CONTROLE D'ACCES : admin uniquement ===
if (!isset($_SESSION['user']) || $_SESSION['user']['role'] !== 'admin') {
    header('Location: connexion.php');
    exit;
}

// on recupere tous les utilisateurs
$users = read_json('users.json');

// on calcule les stats dynamiquement
$nb_total = count($users);
$nb_clients = 0;
$nb_livreurs = 0;
$nb_restaurateurs = 0;
$nb_bannis = 0;

foreach ($users as $u) {
    if ($u['role'] === 'client') {
        $nb_clients++;
    }
    if ($u['role'] === 'livreur') {
        $nb_livreurs++;
    }
    if ($u['role'] === 'restaurateur') {
        $nb_restaurateurs++;
    }
    
    // on verifie le statut pour compter les bannis
    $statut_u = isset($u['statut']) ? $u['statut'] : 'actif';
    if ($statut_u === 'banni') {
        $nb_bannis++;
    }
}
?>
<!DOCTYPE html>
<html lang="fr">

<head>
    <link rel="icon" href="../images/logo.png">
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>La Table des Jedi - Administration</title>
    <!-- interface autonome : pas de common.css -->
    <link rel="stylesheet" href="../css/admin.css">
</head>

<body>
    <!-- header admin -->
    <header class="admin-header">
        <div class="header-left">
            <img src="../images/logo.png" alt="Logo" class="admin-logo">
            <div class="header-tag">
                <span class="tag-rank">ACCÈS NIVEAU 5</span>
                <span class="tag-sector">SECTEUR 7 — CLASSIFIÉ</span>
            </div>
        </div>
        <div class="header-actions">
            <a href="deconnexion.php" class="btn-admin" style="background: rgba(255, 68, 68, 0.08); color: #ff4444; border: 1px solid rgba(255, 68, 68, 0.3); padding: 8px 16px; font-family: 'Consolas', monospace; font-size: 0.7rem; font-weight: bold; text-transform: uppercase; letter-spacing: 1.5px; border-radius: 4px; text-decoration: none; cursor: crosshair;">⚠️ Déconnexion</a>
        </div>
    </header>

    <!-- contenu principal -->
    <main>
        <!-- titre de la console -->
        <section class="console-header">
            <h1>CONSOLE D'ADMINISTRATION — SECTEUR 7</h1>
            <p class="console-subtitle">
                <span class="blink">●</span> CONNEXION SÉCURISÉE ÉTABLIE — TERMINAL IMPÉRIAL #TK-421
            </p>
        </section>

        <!-- statistiques rapides -->
        <div class="stats-bar">
            <div class="stat-card">
                <span class="stat-value"><?= $nb_total ?></span>
                <span class="stat-label">Utilisateurs</span>
            </div>
            <div class="stat-card">
                <span class="stat-value"><?= $nb_clients ?></span>
                <span class="stat-label">Clients</span>
            </div>
            <div class="stat-card">
                <span class="stat-value"><?= $nb_livreurs ?></span>
                <span class="stat-label">Livreurs</span>
            </div>
            <div class="stat-card">
                <span class="stat-value"><?= $nb_restaurateurs ?></span>
                <span class="stat-label">Restaurateur</span>
            </div>
            <div class="stat-card stat-alert">
                <span class="stat-value"><?= $nb_bannis ?></span>
                <span class="stat-label">Banni(s)</span>
            </div>
        </div>

        <!-- barre d'outils et filtres -->
        <div class="toolbar">
            <div class="toolbar-group">
                <label class="toolbar-label" for="filter-role">FILTRER PAR RÔLE :</label>
                <select id="filter-role" class="toolbar-select">
                    <option value="tous">— Tous —</option>
                    <option value="client">Client</option>
                    <option value="livreur">Livreur</option>
                    <option value="restaurateur">Restaurateur</option>
                </select>
            </div>
            <div class="toolbar-group">
                <label class="toolbar-label" for="search-id">RECHERCHER UN MATRICULE :</label>
                <input type="text" id="search-id" class="toolbar-input" placeholder="#U-XXXX">
            </div>
            <div class="toolbar-group">
                <span class="toolbar-info">📋 <?= $nb_total ?> entrées trouvées</span>
            </div>
        </div>

        <!-- tableau des utilisateurs -->
        <div class="table-container">
            <table class="admin-table">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Pseudo</th>
                        <th>Email</th>
                        <th>Rôle</th>
                        <th>Inscription</th>
                        <th>Statut</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    // affichage des utilisateurs en bdd
                    foreach ($users as $user) :
                        $role = $user['role'];
                        $statut = isset($user['statut']) ? $user['statut'] : 'actif';
                        $is_banned = ($statut === 'banni');

                        // classe du badge de role
                        $role_class = 'role-client';
                        if ($role === 'livreur') $role_class = 'role-livreur';
                        elseif ($role === 'restaurateur') $role_class = 'role-restaurateur';
                        elseif ($role === 'admin') $role_class = 'role-admin';
                    ?>
                    <tr <?php if ($is_banned) echo 'class="row-banned"'; ?>>
                        <td class="cell-id">#U-<?= $user['id'] ?></td>
                        <td class="cell-pseudo"><?= htmlspecialchars($user['login']) ?></td>
                        <td class="cell-email"><?= htmlspecialchars(isset($user['email']) ? $user['email'] : '—') ?></td>
                        <td><span class="role-badge <?= $role_class ?>"><?= ucfirst(htmlspecialchars($role)) ?></span></td>
                        <td class="cell-date"><?= htmlspecialchars(isset($user['date_inscription']) ? $user['date_inscription'] : '—') ?></td>
                        <td><span class="status-badge <?= $is_banned ? 'status-banned' : 'status-active' ?>"><?= $is_banned ? 'Banni' : 'Actif' ?></span></td>
                        <td class="cell-actions">
                            <button class="action-btn action-view" title="Voir">👁️</button>
                            <button class="action-btn action-edit" title="Éditer">✏️</button>
                            <button class="action-btn action-ban" title="<?= $is_banned ? 'Réhabiliter' : 'Bannir' ?>"><?= $is_banned ? '♻️' : '🗑️' ?></button>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>

        <!-- pied de tableau -->
        <div class="table-footer">
            <span class="footer-info">Affichage 1–<?= $nb_total ?> sur <?= $nb_total ?> entrées</span>
            <span class="footer-timestamp">Dernière mise à jour : <?= date('d/m/Y — H:i') ?> HGS</span>
        </div>

    </main>

    <!-- footer admin -->
    <footer class="admin-footer">
        <p>&copy; 2026 La Table des Jedi — Console Impériale · Accès Restreint · Projet Creative-Yumland (Phase #2)</p>
    </footer>

</body>

</html>