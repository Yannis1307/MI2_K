<?php
// session (géré par functions.php)
// fonctions json
require_once 'includes/functions.php';

// controle d'acces
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
            <a href="accueil.php" class="btn-admin btn-back">🏠 Vue Client</a>
            <a href="deconnexion.php" class="btn-admin btn-logout-admin">⚠️ Déconnexion</a>
        </div>
    </header>

    <!-- contenu principal -->
    <main>
        <!-- console admin -->
        <section class="console-header">
            <h1>CONSOLE D'ADMINISTRATION — SECTEUR 7</h1>
            <p class="console-subtitle">
                <span class="blink">●</span> CONNEXION SÉCURISÉE ÉTABLIE — TERMINAL IMPÉRIAL #TK-421
            </p>
        </section>

        <!-- statistiques -->
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
                    foreach ($users as $user):
                        $role = $user['role'];
                        $statut = isset($user['statut']) ? $user['statut'] : 'actif';
                        $is_banned = ($statut === 'banni');
                        $premium = isset($user['statut_premium']) ? $user['statut_premium'] : 'normal';

                        // classe du badge de role
                        $role_class = 'role-client';
                        if ($role === 'livreur')
                            $role_class = 'role-livreur';
                        elseif ($role === 'restaurateur')
                            $role_class = 'role-restaurateur';
                        elseif ($role === 'admin')
                            $role_class = 'role-admin';
                        ?>
                        <tr <?php if ($is_banned)
                            echo 'class="row-banned"'; ?> data-id="<?= $user['id'] ?>"
                            data-role="<?= htmlspecialchars($role) ?>" data-premium="<?= htmlspecialchars($premium) ?>"
                            data-login="<?= htmlspecialchars($user['login']) ?>">
                            <td class="cell-id">#U-<?= $user['id'] ?></td>
                            <td class="cell-pseudo"><?= htmlspecialchars($user['login']) ?></td>
                            <td class="cell-email"><?= htmlspecialchars(isset($user['email']) ? $user['email'] : '—') ?>
                            </td>
                            <td><span class="role-badge <?= $role_class ?>"><?= ucfirst(htmlspecialchars($role)) ?></span>
                            </td>
                            <td class="cell-date">
                                <?= htmlspecialchars(isset($user['date_inscription']) ? $user['date_inscription'] : '—') ?>
                            </td>
                            <td><span
                                    class="status-badge <?= $is_banned ? 'status-banned' : 'status-active' ?>"><?= $is_banned ? 'Banni' : 'Actif' ?></span>
                            </td>
                            <td class="cell-actions">
                                <button class="action-btn action-view" title="Voir">👁️</button>
                                <button class="action-btn action-edit js-edit-btn" data-id="<?= $user['id'] ?>"
                                    title="Éditer">✏️</button>
                                <button class="action-btn action-ban js-ban-btn" data-id="<?= $user['id'] ?>"
                                    title="<?= $is_banned ? 'Réhabiliter' : 'Bannir' ?>"><?= $is_banned ? '♻️' : '🗑️' ?></button>
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

    <!-- modale d'edition d'un utilisateur -->
    <div id="modale-edit"
        style="display: none; position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.85); z-index: 9999; justify-content: center; align-items: center;">
        <div class="admin-modal-box"
            style="background: #242526; border: 1px solid rgba(248, 224, 66, 0.3); border-radius: 16px; padding: 40px; max-width: 420px; width: 90%; position: relative;">
            <button id="btn-fermer-edit" class="admin-modal-close"
                style="position: absolute; top: 10px; right: 15px; background: none; border: none; color: #e4e6eb; font-size: 1.5em; cursor: pointer;">&times;</button>
            <h2 class="admin-modal-title"
                style="font-size: 1.2em; margin-bottom: 20px; color: #f8e042; text-align: center;">✏️ MODIFIER
                L'UTILISATEUR</h2>
            <p id="edit-user-label" class="admin-modal-label"
                style="text-align: center; color: #e4e6eb; margin-bottom: 20px; font-size: 0.9em;"></p>

            <!-- champ role -->
            <div style="margin-bottom: 16px;">
                <!-- correction contraste modale admin -->
                <label class="admin-modal-field"
                    style="display: block; font-size: 0.85em; color: #e4e6eb; margin-bottom: 6px;">Rôle</label>
                <select id="edit-role" class="admin-modal-input"
                    style="width: 100%; padding: 10px; border-radius: 8px; background: rgba(255,255,255,0.08); color: #e4e6eb; border: 1px solid rgba(255,255,255,0.2); font-size: 1em;">
                    <option value="client" style="background: #242526; color: #e4e6eb;">Client</option>
                    <option value="livreur" style="background: #242526; color: #e4e6eb;">Livreur</option>
                    <option value="restaurateur" style="background: #242526; color: #e4e6eb;">Restaurateur</option>
                </select>
            </div>

            <!-- champ statut premium -->
            <div style="margin-bottom: 24px;">
                <label class="admin-modal-field"
                    style="display: block; font-size: 0.85em; color: #e4e6eb; margin-bottom: 6px;">Statut
                    Premium</label>
                <select id="edit-premium" class="admin-modal-input"
                    style="width: 100%; padding: 10px; border-radius: 8px; background: rgba(255,255,255,0.08); color: #e4e6eb; border: 1px solid rgba(255,255,255,0.2); font-size: 1em;">
                    <option value="normal" style="background: #242526; color: #e4e6eb;">Normal</option>
                    <option value="premium" style="background: #242526; color: #e4e6eb;">Premium</option>
                    <option value="vip" style="background: #242526; color: #e4e6eb;">VIP</option>
                </select>
            </div>

            <p id="edit-erreur"
                style="color: #ff4444; font-size: 0.85em; margin-bottom: 10px; display: none; text-align: center;"></p>
            <button id="btn-valider-edit"
                style="width: 100%; padding: 12px; border-radius: 8px; border: none; background: linear-gradient(135deg, #f8e042, #ff8c00); color: #1a1a2e; font-weight: bold; font-size: 1.05em; cursor: pointer;">Valider
                les modifications</button>
        </div>
    </div>

    <!-- footer admin -->
    <footer class="admin-footer">
        <p>&copy; 2026 La Table des Jedi — Console Impériale · Accès Restreint · Projet Creative-Yumland (Phase #3)</p>
    </footer>

    <script src="../js/admin.js" defer></script>
</body>

</html>