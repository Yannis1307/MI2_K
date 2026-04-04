<?php
// demarrage de la session
session_start();
// on charge les fonctions json
require_once 'includes/functions.php';

// === CONTROLE D'ACCES : restaurateur uniquement ===
if (!isset($_SESSION['user']) || $_SESSION['user']['role'] !== 'restaurateur') {
    header('Location: connexion.php');
    exit;
}

// === TRAITEMENT POST : passer une commande en livraison ===
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['id_commande'])) {
    $id_cmd = trim($_POST['id_commande']);
    $commandes = read_json('commandes.json');

    foreach ($commandes as $index => $cmd) {
        if ($cmd['id'] === $id_cmd && $cmd['statut'] === 'en attente') {
            // on passe la commande en livraison
            $commandes[$index]['statut'] = 'en livraison';

            // on cherche un livreur disponible dans users.json
            $users = read_json('users.json');
            foreach ($users as $u) {
                if ($u['role'] === 'livreur' && $u['statut'] === 'actif') {
                    $commandes[$index]['id_livreur'] = $u['id'];
                    break;
                }
            }

            write_json('commandes.json', $commandes);
            break;
        }
    }

    // redirect pour eviter le double POST
    header('Location: commandes.php');
    exit;
}
?>
<!DOCTYPE html>
<html lang="fr">

<head>
    <link rel="icon" href="../images/logo.png">
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>La Table des Jedi - Poste de Préparation</title>
    <!-- interface autonome : pas de common.css, tout est dans commandes.css -->
    <link rel="stylesheet" href="../css/commandes.css">
</head>

<body>


    <!-- header simplifie de la cuisine -->
    <header class="kds-header">
        <div class="header-left">
            <img src="../images/logo.png" alt="Logo" class="kds-logo">
        </div>
        <div class="header-center">
            <h1 class="kds-title">POSTE DE PRÉPARATION — CANTINA 1</h1>
            <span class="kds-live"><span class="live-dot"></span> EN SERVICE</span>
        </div>
        <div class="header-right">
            <a href="accueil.php" class="btn-kds btn-back">↩ Accueil</a>
            <button class="btn-kds btn-close-service">🔴 Fermer le service</button>
        </div>
    </header>


    <!-- contenu principal -->

    <main class="kds-main">


        <!-- colonne gauche : commandes en attente -->

        <section class="zone zone-pending">
            <?php
            // on recupere les commandes en attente
            $commandes = read_json('commandes.json');
            $en_attente = [];
            $en_livraison = [];
            foreach ($commandes as $c) {
                if ($c['statut'] === 'en attente') {
                    $en_attente[] = $c;
                } elseif ($c['statut'] === 'en livraison') {
                    $en_livraison[] = $c;
                }
            }

            // On prepare la liste des livreurs pour l'attribution (Phase 3)
            $users_all = read_json('users.json');
            $liste_livreurs = [];
            foreach ($users_all as $u) {
                if ($u['role'] === 'livreur') {
                    $liste_livreurs[] = $u;
                }
            }
            ?>
            <div class="zone-header">
                <h2>🔥 COMMANDES EN ATTENTE</h2>
                <span class="zone-count"><?= count($en_attente) ?> ticket<?= count($en_attente) > 1 ? 's' : '' ?></span>
            </div>

            <div class="tickets-list">

                <?php if (empty($en_attente)) : ?>
                <!-- aucune commande en attente -->
                <div style="text-align: center; padding: 40px; color: rgba(255,255,255,0.4);">
                    <p>Aucune commande en attente.</p>
                </div>
                <?php else : ?>
                <?php foreach ($en_attente as $cmd) : ?>
                <!-- ticket dynamique -->
                <article class="ticket ticket-normal">
                    <div class="ticket-header">
                        <div class="ticket-id-group">
                            <span class="ticket-number">#<?= htmlspecialchars($cmd['id']) ?></span>
                            <span class="ticket-time"><?= htmlspecialchars($cmd['heure']) ?></span>
                        </div>
                    </div>
                    <div class="ticket-body">
                        <ul class="ticket-items">
                            <?php // affichage de chaque plat commande ?>
                            <?php foreach ($cmd['plats'] as $p) : ?>
                            <li><span class="item-qty"><?= $p['quantite'] ?>x</span> <?= htmlspecialchars($p['nom']) ?></li>
                            <?php endforeach; ?>
                        </ul>
                    </div>
                    <div class="ticket-footer">
                        <span class="ticket-client">👤 <?= htmlspecialchars($cmd['login_client']) ?></span>
                        <form method="POST" action="commandes.php" style="display:flex; gap:10px; align-items:center;">
                            <input type="hidden" name="id_commande" value="<?= htmlspecialchars($cmd['id']) ?>">
                            
                            <!-- Séléction du livreur (Phase 3) -->
                            <select name="id_livreur_manuel" style="padding: 6px; border-radius: 4px; background: rgba(0,0,0,0.4); color: #fff; border: 1px solid rgba(255,255,255,0.2); font-size: 0.9em;">
                                <option value="">Livreur auto (P3)</option>
                                <?php foreach ($liste_livreurs as $livreur) : ?>
                                    <option value="<?= $livreur['id'] ?>">👤 <?= htmlspecialchars($livreur['login']) ?></option>
                                <?php endforeach; ?>
                            </select>

                            <button type="submit" class="btn-ready">✅ PRÊT POUR LIVRAISON</button>
                        </form>
                    </div>
                </article>
                <?php endforeach; ?>
                <?php endif; ?>

            </div>
        </section>


        <!-- colonne droite : en cours de livraison -->

        <section class="zone zone-delivery">
            <div class="zone-header">
                <h2>🚀 EN COURS DE LIVRAISON</h2>
                <span class="zone-count"><?= count($en_livraison) ?> en transit</span>
            </div>

            <div class="delivery-list">

                <?php if (empty($en_livraison)) : ?>
                <!-- aucune livraison en cours -->
                <div style="text-align: center; padding: 40px; color: rgba(255,255,255,0.4);">
                    <p>Aucune livraison en cours.</p>
                </div>
                <?php else : ?>
                <?php foreach ($en_livraison as $cmd) : ?>
                <!-- livraison dynamique -->
                <div class="delivery-card">
                    <div class="delivery-info">
                        <span class="delivery-id">#<?= htmlspecialchars($cmd['id']) ?></span>
                        <span class="delivery-status">
                            <span class="pulse-dot"></span> En transit
                        </span>
                    </div>
                    <div class="delivery-details">
                        <span class="delivery-driver">👤 <?= htmlspecialchars($cmd['login_client']) ?></span>
                        <span class="delivery-dest">→ <?= htmlspecialchars(isset($cmd['adresse']) ? $cmd['adresse'] : 'Adresse inconnue') ?></span>
                    </div>
                </div>
                <?php endforeach; ?>
                <?php endif; ?>

            </div>
        </section>

    </main>

    <!-- footer cuisine -->
    <footer class="kds-footer">
        <p>&copy; 2026 La Table des Jedi — Poste de Préparation · Interface Cantina · Projet Creative-Yumland (Phase #1)
        </p>
    </footer>

</body>

</html>