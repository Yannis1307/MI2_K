<!DOCTYPE html>
<html lang="fr">

<head>
    <link rel="icon" href="../images/logo.png">
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <!-- titre dynamique -->
    <title>La Table des Jedi - <?php echo isset($page_title) ? $page_title : 'Accueil'; ?></title>
    <!-- styles globaux -->
    <link rel="stylesheet" href="../css/common.css">

    <!-- theme cookie check et chargement css -->
    <?php
    $theme = isset($_COOKIE['theme']) ? $_COOKIE['theme'] : 'dark';
    if ($theme !== 'light' && $theme !== 'dark') {
        $theme = 'dark'; // fallback
    }
    ?>
    <link rel="stylesheet" id="theme-link" href="<?php echo $theme === 'light' ? '../css/light-theme.css' : ''; ?>">
    <!-- css specifique -->
    <?php if (!empty($page_css)): ?>
        <link rel="stylesheet" href="../css/<?php echo $page_css; ?>">
    <?php endif; ?>

    <!-- scripts -->
    <script src="../js/theme.js" defer></script>
    <?php if (isset($page_id) && $page_id === 'panier'): ?>
        <script src="../js/panier.js" defer></script>
    <?php elseif (isset($page_id) && $page_id === 'produits'): ?>
        <script src="../js/produits.js" defer></script>
    <?php elseif (isset($page_id) && $page_id === 'modifier_commande'): ?>
        <script src="../js/modifier_commande.js" defer></script>
    <?php elseif (isset($page_id) && $page_id === 'notation'): ?>
        <script src="../js/notation.js" defer></script>
    <?php endif; ?>
</head>

<body>
    <!-- video de fond -->
    <video autoplay muted loop playsinline id="video-bg">
        <source src="../video/etoile.mp4" type="video/mp4">
        Votre navigateur ne supporte pas la vidéo HTML5.
    </video>

    <!-- filtre sombre -->
    <div class="overlay"></div>

    <!-- entete commun -->
    <header>
        <a href="accueil.php"> <img src="../images/logo.png" alt="Logo"></a>

        <!-- navigation -->
        <nav>
            <ul>
                <li><a href="accueil.php" <?php if ((isset($page_id) ? $page_id : '') === 'accueil')
                    echo 'class="active"'; ?>>Accueil</a></li>
                <li><a href="produits.php" <?php if ((isset($page_id) ? $page_id : '') === 'produits')
                    echo 'class="active"'; ?>>La Carte</a></li>
                <li><a href="profil.php" <?php if ((isset($page_id) ? $page_id : '') === 'profil')
                    echo 'class="active"'; ?>>Mon Profil</a></li>
                <li><a href="notation.php" <?php if ((isset($page_id) ? $page_id : '') === 'notation')
                    echo 'class="active"'; ?>>Donner mon avis</a></li>
            </ul>
        </nav>

        <!-- espace membre et recherche -->
        <div class="header-right">
            <!-- bouton theme -->
            <button id="theme-toggle" class="btn-member"
                style="background: rgba(255,255,255,0.1); border:1px solid rgba(255,255,255,0.4); border-radius:50%; width: 40px; height: 40px; display: flex; align-items: center; justify-content: center; cursor:pointer; margin-right: 15px;"
                title="Changer de thème">
                <?php echo $theme === 'light' ? '🌑' : '☀️'; ?>
            </button>

            <div class="member-space">
                <?php
                // calcul du nombre d'articles dans le panier
                $nb_plats = isset($_SESSION['panier']) ? array_sum($_SESSION['panier']) : 0;
                $nb_menus = isset($_SESSION['panier_menus']) ? array_sum($_SESSION['panier_menus']) : 0;
                $nb_panier = $nb_plats + $nb_menus;
                ?>
                <!-- bouton panier -->
                <a href="panier.php"
                    class="btn-member btn-login <?php if ((isset($page_id) ? $page_id : '') === 'panier')
                        echo 'active'; ?>">🛒<?php if ($nb_panier > 0): ?>
                        <span id="cart-count">(<?= $nb_panier ?>)</span><?php else: ?> <span id="cart-count"
                            style="display:none;"></span><?php endif; ?></a>

                <?php if (isset($_SESSION['user'])): ?>
                    <!-- boutons contextuels de navigation par rôle -->
                    <?php if ($_SESSION['user']['role'] === 'admin'): ?>
                        <a href="admin.php" class="btn-member"
                            style="background: rgba(100, 160, 255, 0.2); color: #6498ff; border: 1px solid rgba(100, 160, 255, 0.4); margin-right: 10px;">🛡️
                            Panneau Admin</a>
                    <?php elseif ($_SESSION['user']['role'] === 'restaurateur'): ?>
                        <a href="commandes.php" class="btn-member"
                            style="background: rgba(255, 170, 0, 0.2); color: #ffaa00; border: 1px solid rgba(255, 170, 0, 0.4); margin-right: 10px;">👨‍🍳
                            Poste Cuisine</a>
                    <?php elseif ($_SESSION['user']['role'] === 'livreur'): ?>
                        <a href="livraison.php" class="btn-member"
                            style="background: rgba(0, 255, 136, 0.2); color: #00ff88; border: 1px solid rgba(0, 255, 136, 0.4); margin-right: 10px;">🚀
                            Interface Livraison</a>
                    <?php endif; ?>
                    <!-- etat connecte -->
                    <a href="profil.php" class="btn-member btn-login">👤
                        <?php echo htmlspecialchars($_SESSION['user']['login']); ?></a>
                    <a href="deconnexion.php" class="btn-member btn-signup">Déconnexion</a>
                <?php else: ?>
                    <!-- etat deconnecte -->
                    <a href="connexion.php"
                        class="btn-member btn-login <?php if ((isset($page_id) ? $page_id : '') === 'connexion')
                            echo 'active'; ?>">Connexion</a>
                    <a href="inscription.php"
                        class="btn-member btn-signup <?php if ((isset($page_id) ? $page_id : '') === 'inscription')
                            echo 'active'; ?>">Inscription</a>
                <?php endif; ?>
            </div>

            <!-- barre de recherche -->
            <div class="search-box">
                <input type="text" class="search-input" placeholder="RECHERCHER...">
                <button class="search-btn">🔍</button>
            </div>
        </div>
    </header>