<?php
// demarrage de la session
session_start();
// on charge les fonctions json
require_once 'includes/functions.php';
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
            <a href="accueil.php" class="btn-admin btn-back">↩ Retour Accueil</a>
            <button class="btn-admin btn-logout-admin">⚠️ Déconnexion Admin</button>
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
                <span class="stat-value">6</span>
                <span class="stat-label">Utilisateurs</span>
            </div>
            <div class="stat-card">
                <span class="stat-value">3</span>
                <span class="stat-label">Clients</span>
            </div>
            <div class="stat-card">
                <span class="stat-value">2</span>
                <span class="stat-label">Livreurs</span>
            </div>
            <div class="stat-card">
                <span class="stat-value">1</span>
                <span class="stat-label">Restaurateur</span>
            </div>
            <div class="stat-card stat-alert">
                <span class="stat-value">1</span>
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
                <span class="toolbar-info">📋 6 entrées trouvées</span>
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
                    <tr>
                        <td class="cell-id">#U-1138</td>
                        <td class="cell-pseudo">Han_Solo_77</td>
                        <td class="cell-email">han.solo@holonet.gx</td>
                        <td><span class="role-badge role-client">Client</span></td>
                        <td class="cell-date">12/01/3026</td>
                        <td><span class="status-badge status-active">Actif</span></td>
                        <td class="cell-actions">
                            <button class="action-btn action-view" title="Voir">👁️</button>
                            <button class="action-btn action-edit" title="Éditer">✏️</button>
                            <button class="action-btn action-ban" title="Bannir">🗑️</button>
                        </td>
                    </tr>
                    <tr>
                        <td class="cell-id">#U-1139</td>
                        <td class="cell-pseudo">Leia_Organa</td>
                        <td class="cell-email">leia.organa@alderaan.rp</td>
                        <td><span class="role-badge role-client">Client</span></td>
                        <td class="cell-date">15/01/3026</td>
                        <td><span class="status-badge status-active">Actif</span></td>
                        <td class="cell-actions">
                            <button class="action-btn action-view" title="Voir">👁️</button>
                            <button class="action-btn action-edit" title="Éditer">✏️</button>
                            <button class="action-btn action-ban" title="Bannir">🗑️</button>
                        </td>
                    </tr>
                    <tr>
                        <td class="cell-id">#U-1140</td>
                        <td class="cell-pseudo">Mando_Din</td>
                        <td class="cell-email">din.djarin@mandalore.gx</td>
                        <td><span class="role-badge role-livreur">Livreur</span></td>
                        <td class="cell-date">20/01/3026</td>
                        <td><span class="status-badge status-active">Actif</span></td>
                        <td class="cell-actions">
                            <button class="action-btn action-view" title="Voir">👁️</button>
                            <button class="action-btn action-edit" title="Éditer">✏️</button>
                            <button class="action-btn action-ban" title="Bannir">🗑️</button>
                        </td>
                    </tr>
                    <tr>
                        <td class="cell-id">#U-1141</td>
                        <td class="cell-pseudo">Chewie_Wookiee</td>
                        <td class="cell-email">chewbacca@kashyyyk.gx</td>
                        <td><span class="role-badge role-livreur">Livreur</span></td>
                        <td class="cell-date">22/01/3026</td>
                        <td><span class="status-badge status-active">Actif</span></td>
                        <td class="cell-actions">
                            <button class="action-btn action-view" title="Voir">👁️</button>
                            <button class="action-btn action-edit" title="Éditer">✏️</button>
                            <button class="action-btn action-ban" title="Bannir">🗑️</button>
                        </td>
                    </tr>
                    <tr>
                        <td class="cell-id">#U-1142</td>
                        <td class="cell-pseudo">Dex_Jettster</td>
                        <td class="cell-email">dex@cococity.co</td>
                        <td><span class="role-badge role-restaurateur">Restaurateur</span></td>
                        <td class="cell-date">05/01/3026</td>
                        <td><span class="status-badge status-active">Actif</span></td>
                        <td class="cell-actions">
                            <button class="action-btn action-view" title="Voir">👁️</button>
                            <button class="action-btn action-edit" title="Éditer">✏️</button>
                            <button class="action-btn action-ban" title="Bannir">🗑️</button>
                        </td>
                    </tr>
                    <tr class="row-banned">
                        <td class="cell-id">#U-1143</td>
                        <td class="cell-pseudo">Jar_Jar_B</td>
                        <td class="cell-email">jarjar@naboo.gx</td>
                        <td><span class="role-badge role-client">Client</span></td>
                        <td class="cell-date">28/01/3026</td>
                        <td><span class="status-badge status-banned">Banni</span></td>
                        <td class="cell-actions">
                            <button class="action-btn action-view" title="Voir">👁️</button>
                            <button class="action-btn action-edit" title="Éditer">✏️</button>
                            <button class="action-btn action-ban" title="Réhabiliter">♻️</button>
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>

        <!-- pied de tableau -->
        <div class="table-footer">
            <span class="footer-info">Affichage 1–6 sur 6 entrées</span>
            <span class="footer-timestamp">Dernière mise à jour : Cycle 3026.02.13 — 15:59 HGS</span>
        </div>

    </main>

    <!-- footer admin -->
    <footer class="admin-footer">
        <p>&copy; 2026 La Table des Jedi — Console Impériale · Accès Restreint · Projet Creative-Yumland (Phase #1)</p>
    </footer>

</body>

</html>