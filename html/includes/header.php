<?php session_start(); ?>
<!DOCTYPE html>
<html lang="fr">

<head>
    <link rel="icon" href="../images/logo.png">
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <!-- titre dynamique defini par chaque page via $page_title -->
    <title>La Table des Jedi - <?php echo $page_title ?? 'Accueil'; ?></title>
    <!-- common.css : styles globaux (header, footer, reset) -->
    <link rel="stylesheet" href="../css/common.css">
    <!-- css specifique a la page, defini via $page_css -->
    <?php if (!empty($page_css)) : ?>
    <link rel="stylesheet" href="../css/<?php echo $page_css; ?>">
    <?php endif; ?>
</head>

<body>
    <!-- video de fond qui tourne en boucle -->
    <video autoplay muted loop playsinline id="video-bg">
        <source src="../video/etoile.mp4" type="video/mp4">
        Votre navigateur ne supporte pas la vidéo HTML5.
    </video>

    <!-- filtre sombre par dessus la video -->
    <div class="overlay"></div>

    <!-- header commun a toutes les pages du site principal -->
    <header>
        <a href="accueil.php"> <img src="../images/logo.png" alt="Logo"></a>

        <!-- navigation principale -->
        <nav>
            <ul>
                <li><a href="accueil.php" <?php if (($page_id ?? '') === 'accueil') echo 'class="active"'; ?>>Accueil</a></li>
                <li><a href="produits.php" <?php if (($page_id ?? '') === 'produits') echo 'class="active"'; ?>>La Carte</a></li>
                <li><a href="profil.php" <?php if (($page_id ?? '') === 'profil') echo 'class="active"'; ?>>Mon Profil</a></li>
                <li><a href="notation.php" <?php if (($page_id ?? '') === 'notation') echo 'class="active"'; ?>>Donner mon avis</a></li>
            </ul>
        </nav>

        <!-- partie droite : boutons membre + barre de recherche -->
        <div class="header-right">
            <div class="member-space">
                <a href="connexion.php" class="btn-member btn-login <?php if (($page_id ?? '') === 'connexion') echo 'active'; ?>">Connexion</a>
                <a href="inscription.php" class="btn-member btn-signup <?php if (($page_id ?? '') === 'inscription') echo 'active'; ?>">Inscription</a>
            </div>

            <div class="search-box">
                <input type="text" class="search-input" placeholder="RECHERCHER...">
                <button class="search-btn">🔍</button>
            </div>
        </div>
    </header>
