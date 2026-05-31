<?php
// session
// (géré par functions.php)
// fonctions json
require_once 'includes/functions.php';

// controle d'acces
if (!isset($_SESSION['user']) || $_SESSION['user']['role'] !== 'restaurateur') {
    header('Location: connexion.php');
    exit;
}

// plus de traitement post ici, tout passe en asynchrone maintenant
?>
<!DOCTYPE html>
<html lang="fr">

<head>
    <link rel="icon" href="../images/logo.png">
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>La Table des Jedi - Poste de Préparation</title>
    <!-- styles specifiques -->
    <link rel="stylesheet" href="../css/commandes.css">
</head>

<body>


    <!-- entete du dashboard -->
    <header class="kds-header">
        <div class="header-left">
            <img src="../images/logo.png" alt="Logo" class="kds-logo">
        </div>
        <div class="header-center">
            <h1 class="kds-title">POSTE DE PRÉPARATION — CANTINA 1</h1>
            <span class="kds-live"><span class="live-dot"></span> EN SERVICE</span>
        </div>
        <div class="header-right">
            <a href="accueil.php" class="btn-kds btn-back" style="margin-right: 10px;">🏠 Vue Client</a>
            <a href="deconnexion.php" class="btn-kds btn-back">🚪 Déconnexion</a>
            <button class="btn-kds btn-close-service">🔴 Fermer le service</button>
        </div>
    </header>


    <main class="kds-main">


        <!-- commandes a preparer -->

        <section class="zone zone-pending">
            <?php
            $commandes = read_json('commandes.json');
            $en_attente = [];
            $en_livraison_all = [];
            $terminees_all = [];
            foreach ($commandes as $c) {
                if (in_array($c['statut'], ['en attente', 'en préparation', 'prête'])) {
                    $en_attente[] = $c;
                } elseif (in_array($c['statut'], ['en livraison', 'à récupérer'])) {
                    $en_livraison_all[] = $c;
                } elseif (in_array($c['statut'], ['livré', 'livre', 'abandonné'])) {
                    $terminees_all[] = $c;
                }
            }
            
            // on garde les 5 dernieres
            $terminees_5 = array_slice($terminees_all, -5);
            
            // listes consolidees
            $en_livraison = array_merge($en_livraison_all, $terminees_5);

            // liste des livreurs
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
                <!-- liste vide -->
                <div style="text-align: center; padding: 40px; color: rgba(255,255,255,0.4);">
                    <p>Aucune commande en attente.</p>
                </div>
                <?php else : ?>
                <?php foreach ($en_attente as $cmd) : ?>
                <?php 
                    $is_planifiee = (isset($cmd['type']) && $cmd['type'] === 'planifiee');
                    $ticket_class = $is_planifiee ? 'ticket-planned' : 'ticket-normal';
                ?>
                <!-- carte d'une commande -->
                <article class="ticket <?= $ticket_class ?>" data-cmd-id="<?= htmlspecialchars($cmd['id']) ?>">
                    <div class="ticket-header">
                        <div class="ticket-id-group">
                            <span class="ticket-number">#<?= htmlspecialchars($cmd['id']) ?></span>
                            <span class="ticket-time">Créée: <?= htmlspecialchars($cmd['heure']) ?></span>
                            <?php if ($is_planifiee && !empty($cmd['heure_livraison'])) : ?>
                                <?php 
                                    $hl = $cmd['heure_livraison'];
                                    if (strpos($hl, 'T') !== false) {
                                        $hl = date('d/m/Y H:i', strtotime($hl));
                                    }
                                ?>
                                <span class="ticket-timer" style="color: #00bfff; border-color: #00bfff; background: rgba(0, 191, 255, 0.08);">
                                    📅 Prévue: <?= htmlspecialchars($hl) ?>
                                </span>
                            <?php endif; ?>
                        </div>
                    </div>
                    <div class="ticket-body">
                        <ul class="ticket-items">
                            <?php // liste des plats ?>
                            <?php if (isset($cmd['plats'])) : ?>
                                <?php foreach ($cmd['plats'] as $p) : ?>
                                <li><span class="item-qty"><?= $p['quantite'] ?>x</span> <?= htmlspecialchars($p['nom']) ?></li>
                                <?php endforeach; ?>
                            <?php endif; ?>
                            
                            <?php // liste des menus avec le detail ?>
                            <?php if (isset($cmd['menus'])) : ?>
                                <?php foreach ($cmd['menus'] as $m) : ?>
                                <li>
                                    <span class="item-qty"><?= $m['quantite'] ?>x</span> <?= htmlspecialchars($m['nom']) ?> <span style="font-size: 0.7em; color:#aaa;">(Menu)</span>
                                    <?php if (isset($m['plats_details']) && !empty($m['plats_details'])) : ?>
                                        <ul style="list-style: none; padding-left: 20px; font-size: 0.9em; opacity: 0.8; margin-top: 4px;">
                                            <?php foreach ($m['plats_details'] as $nom_plat) : ?>
                                                <li>- <?= htmlspecialchars($nom_plat) ?></li>
                                            <?php endforeach; ?>
                                        </ul>
                                    <?php endif; ?>
                                </li>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </ul>
                    </div>
                    <div class="ticket-footer">
                        <span class="ticket-client">👤 <?= htmlspecialchars($cmd['login_client']) ?> (<span class="js-statut-label"><?= htmlspecialchars(strtoupper($cmd['statut'])) ?></span>)</span>
                        <?php $est_emporter = (isset($cmd['mode_retrait']) && $cmd['mode_retrait'] === 'emporter'); ?>
                        <?php 
                        // assignation du livreur en asynchrone : calcul des etats
                        $stat = $cmd['statut'];
                        $is_att = ($stat === 'en attente');
                        $is_prp = ($stat === 'en préparation');
                        $is_prt = ($stat === 'prête');
                        ?>
                        <div class="cmd-actions-container" data-cmd="<?= htmlspecialchars($cmd['id']) ?>" style="display:flex; gap:10px; align-items:center; flex-wrap:wrap; margin-top: 10px;">
                            
                            <!-- bouton preparer en asynchrone -->
                            <button type="button" class="btn-ready js-action-cmd" data-cmd="<?= htmlspecialchars($cmd['id']) ?>" data-action="preparation" style="background: rgba(255, 170, 0, 0.2); border-color: #ffaa00; color: #ffaa00; <?= $is_att ? '' : 'display:none;' ?>">👨‍🍳 PRÉPARER</button>
                            
                            <!-- bouton prete en asynchrone -->
                            <button type="button" class="btn-ready js-action-cmd" data-cmd="<?= htmlspecialchars($cmd['id']) ?>" data-action="prete" style="background: rgba(0, 191, 255, 0.2); border-color: #00bfff; color: #00bfff; <?= $is_prp ? '' : 'display:none;' ?>">🔔 PRÊTE</button>
                            
                            <?php if (!$est_emporter) : ?>
                                <div class="js-livraison-group" style="display: <?= $is_prt ? 'inline-flex' : 'none' ?>; gap:10px; align-items:center;">
                                    <!-- select livreur -->
                                    <select class="js-select-livreur" data-cmd="<?= htmlspecialchars($cmd['id']) ?>" style="padding: 6px; border-radius: 4px; background: rgba(0,0,0,0.4); color: #fff; border: 1px solid rgba(255,255,255,0.2); font-size: 0.9em; width: 100px;">
                                        <option value="">Livreur auto</option>
                                        <?php foreach ($liste_livreurs as $livreur) : ?>
                                            <option value="<?= $livreur['id'] ?>">👤 <?= htmlspecialchars($livreur['login']) ?></option>
                                        <?php endforeach; ?>
                                    </select>
                                    <!-- bouton en livraison en asynchrone -->
                                    <button type="button" class="btn-ready js-action-cmd" data-cmd="<?= htmlspecialchars($cmd['id']) ?>" data-action="livraison">🚀 EN LIVRAISON</button>
                                </div>
                            <?php else : ?>
                                <div class="js-emporter-group" style="display: <?= $is_prt ? 'inline-flex' : 'none' ?>; gap:10px; align-items:center;">
                                    <span style="padding: 6px 12px; border-radius: 4px; background: rgba(255, 140, 0, 0.2); color: #ff8c00; border: 1px solid rgba(255, 140, 0, 0.5); font-size: 0.85em; font-weight: bold;">🥡 À EMPORTER</span>
                                    <!-- bouton remise au client en asynchrone -->
                                    <button type="button" class="btn-ready js-action-cmd" data-cmd="<?= htmlspecialchars($cmd['id']) ?>" data-action="a_recuperer" style="background: rgba(0,255,136,0.1); border-color: #00ff88; color: #00ff88;">✅ À RÉCUPÉRER</button>
                                </div>
                            <?php endif; ?>

                            <!-- bouton abandonner en asynchrone -->
                            <button type="button" class="btn-ready js-action-cmd js-btn-abandonner" data-cmd="<?= htmlspecialchars($cmd['id']) ?>" data-action="abandonner" style="padding: 6px 12px; font-size: 0.8em; background: rgba(255,50,50,0.1); border-color: #ff4444; color: #ff4444;" title="Marquer comme Abandonnée">❌ ABANDONNÉE</button>
                        </div>
                    </div>
                </article>
                <?php endforeach; ?>
                <?php endif; ?>

            </div>
        </section>


        <!-- expéditions en cours ou terminées -->

        <section class="zone zone-delivery">
            <div class="zone-header">
                <h2>🚀 STATUT DE LIVRAISON</h2>
                <span class="zone-count"><?= count($en_livraison) ?> commandes</span>
            </div>

            <div class="delivery-list">

                <?php if (empty($en_livraison)) : ?>
                <!-- liste vide -->
                <div style="text-align: center; padding: 40px; color: rgba(255,255,255,0.4);">
                    <p>Aucune livraison en cours.</p>
                </div>
                <?php else : ?>
                <?php foreach ($en_livraison as $cmd) : ?>
                <?php $is_planifiee = (isset($cmd['type']) && $cmd['type'] === 'planifiee'); ?>
                <!-- affichage d'une livraison -->
                <div class="delivery-card" <?= $cmd['statut'] === 'abandonné' ? 'style="border-left: 4px solid #ff4444;"' : (in_array($cmd['statut'], ['livré', 'livre', 'à récupérer']) ? 'style="border-left: 4px solid #00ff88;"' : ($is_planifiee ? 'style="border-left: 4px solid #00bfff;"' : '')) ?>>
                    <div class="delivery-info">
                        <span class="delivery-id">#<?= htmlspecialchars($cmd['id']) ?></span>
                        <span class="delivery-status" <?= $cmd['statut'] === 'abandonné' ? 'style="color: #ff4444;"' : (in_array($cmd['statut'], ['livré', 'livre', 'à récupérer']) ? 'style="color: #00ff88;"' : '') ?>>
                            <?php if ($cmd['statut'] === 'abandonné') : ?>
                                ❌ Abandonnée
                            <?php elseif ($cmd['statut'] === 'livré' || $cmd['statut'] === 'livre') : ?>
                                ✅ Livrée
                            <?php elseif ($cmd['statut'] === 'à récupérer') : ?>
                                🥡 À récupérer au comptoir
                            <?php else : ?>
                                <span class="pulse-dot"></span> En transit
                            <?php endif; ?>
                        </span>
                    </div>
                    <div class="delivery-details">
                        <div style="display: flex; flex-direction: column; gap: 4px;">
                            <span class="delivery-driver">👤 <?= htmlspecialchars($cmd['login_client']) ?></span>
                            <?php if ($is_planifiee && !empty($cmd['heure_livraison'])) : ?>
                                <?php 
                                    $hl = $cmd['heure_livraison'];
                                    if (strpos($hl, 'T') !== false) {
                                        $hl = date('d/m/Y H:i', strtotime($hl));
                                    }
                                ?>
                                <span style="font-size: 0.75rem; color: #00bfff; font-weight: bold;">📅 <?= htmlspecialchars($hl) ?></span>
                            <?php endif; ?>
                        </div>
                        <span class="delivery-dest" style="text-align: right; max-width: 60%; word-break: break-word;">→ <?= htmlspecialchars(isset($cmd['adresse']) ? $cmd['adresse'] : 'Adresse inconnue') ?></span>
                    </div>
                    <?php if (in_array($cmd['statut'], ['en livraison', 'à récupérer'])) : ?>
                    <div style="display:flex; gap:10px; margin-top: 15px;">
                        <button type="button" class="btn-ready js-action-cmd" data-cmd="<?= htmlspecialchars($cmd['id']) ?>" data-action="livrer" style="padding: 6px 12px; font-size: 0.85em; background: rgba(0, 255, 136, 0.1); border: 1px solid #00ff88; color: #00ff88;">✅ Livrée</button>
                        <button type="button" class="btn-ready js-action-cmd" data-cmd="<?= htmlspecialchars($cmd['id']) ?>" data-action="abandonner" style="padding: 6px 12px; font-size: 0.85em; background: rgba(255, 50, 50, 0.1); border: 1px solid #ff4444; color: #ff4444;">❌ Abandonnée</button>
                    </div>
                    <?php endif; ?>
                </div>
                <?php endforeach; ?>
                <?php endif; ?>

            </div>
        </section>

    </main>

    <!-- bas de page -->
    <footer class="kds-footer">
        <p>&copy; 2026 La Table des Jedi — Poste de Préparation · Interface Cantina · Projet Creative-Yumland (Phase #3)
        </p>
    </footer>

    <script src="../js/commandes.js" defer></script>

</body>

</html>