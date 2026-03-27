<?php
// variables de configuration pour le header
$page_title = 'Accueil';
$page_css = 'accueil.css';
$page_id = 'accueil';

// on charge les fonctions utilitaires json
require_once 'includes/functions.php';

// on inclut le header commun
require_once 'includes/header.php';
?>

<main>
    <!-- section d'accueil avec titre et bouton -->
    <div id="bienvenue">
        <h1>Bienvenue sur la Table des Jedi</h1>
        <div class="cta-container">
            <a href="produits.php" class="btn-carte">Voir la carte</a>
        </div>
    </div>
    <div id="presentation">
        <h1> Goûtez au meilleur plat de la galaxie !</h1>
    </div>

    <!-- slider des plats recommandes -->
    <section id="plat-du-jour">
        <h2>Ce que le Conseil des jedi vous recommande :</h2>
        <div id="plats-container">
            <!-- boutons radio caches pour controler les slides sans javascript -->
            <input type="radio" name="slider" id="auto" checked>
            <input type="radio" name="slider" id="slide1">
            <input type="radio" name="slider" id="slide2">
            <input type="radio" name="slider" id="slide3">
            <input type="radio" name="slider" id="slide4">

            <!-- fleches de navigation (labels relies aux radios) -->
            <div class="slider-controls">
                <!-- fleches precedent -->
                <label for="slide4" class="arrow prev slide1-control">❮</label>
                <label for="slide1" class="arrow prev slide2-control">❮</label>
                <label for="slide2" class="arrow prev slide3-control">❮</label>
                <label for="slide3" class="arrow prev slide4-control">❮</label>

                <!-- fleches suivant -->
                <label for="slide2" class="arrow next slide1-control">❯</label>
                <label for="slide3" class="arrow next slide2-control">❯</label>
                <label for="slide4" class="arrow next slide3-control">❯</label>
                <label for="slide1" class="arrow next slide4-control">❯</label>
            </div>

            <div id="slider">
                <a href="produits.php" class="slide">
                    <img src="../images/Boisson/jusdejabba.jpg" alt="Jus de Jabba">
                    <div class="slide-caption">
                        <h2>Boisson</h2>
                        <h3>Jus de Jabba</h3>
                    </div>
                </a>
                <a href="produits.php" class="slide">
                    <img src="../images/plats/CotelettesdeDewback.jpg" alt="Cotelettes de Dewback">
                    <div class="slide-caption">
                        <h2>Plats</h2>
                        <h3>Cotelettes de Dewback</h3>
                    </div>
                </a>
                <a href="produits.php" class="slide">
                    <img src="../images/Snacks et Sucreries/fruitsjogan.jpg" alt="Fruits Jogan">
                    <div class="slide-caption">
                        <h2>Snacks</h2>
                        <h3>Fruits Jogan</h3>
                    </div>
                </a>
                <a href="produits.php" class="slide">
                    <img src="../images/Spécialités Exotiques/tartaredeYipYip.jpg" alt="Tartare de Yip Yip">
                    <div class="slide-caption">
                        <h2>Spécialités</h2>
                        <h3>Tartare de Yip Yip</h3>
                    </div>
                </a>
            </div>
        </div>
    </section>

    <!-- section histoire du restaurant -->
    <section id="histoire-galactique">
        <div class="history-container">
            <div class="history-content">
                <h1>NOTRE HISTOIRE GALACTIQUE</h1>
                <p>
                    À LA TABLE DES JEDI, CHAQUE PLAT EST UN VOYAGE AU CŒUR DE LA GALAXIE. NOS INGRÉDIENTS
                    SONT SOURCÉS AUPRÈS DES MEILLEURS CULTIVATEURS SUR DES MONDES LOINTAINS COMME
                    TATOOINE, KASHYYYK ET CORUSCANT. GRÂCE À DES CONTREBANDIERS DE CONFIANCE, PILOTANT
                    DES CARGOS RAPIDES, NOUS VOUS ASSURONS UN TRANSPORT TRANSGALAXY DES PRODUITS POUR UNE
                    EXPÉRIENCE D'EXCELLENCE ET DES SAVEURS AUTHENTIQUES SUR VOTRE TABLE.
                    QUE LA FORCE (ET L'APPÉTIT) SOIT AVEC VOUS !
                </p>
                <img id="history-logo" src="../images/jedi_chef_logo.png" alt="Jedi Chef Logo">
            </div>
            <div class="history-visual">
                <img src="../images/Faucon.jpg" alt="Vaisseau Galactique">
            </div>
            <div class="star-corner">✦</div>
        </div>
    </section>

    <!-- section avis clients -->
    <section id="avis-clients">
        <h2 class="section-title">RADIO-COMMUNICATIONS REÇUES</h2>
        <div class="reviews-container">
            <!-- carte avis 1 -->
            <div class="review-card">
                <div class="card-glow"></div>
                <div class="card-content">
                    <div class="user-info">
                        <img src="../images/han_avatar.png" alt="Han S." class="avatar">
                        <h3>HAN S.</h3>
                    </div>
                    <p class="review-text">"LIVRÉ EN MOINS DE 12 SECONDES. LE RONTO WRAP ÉTAIT ENCORE CHAUD !"</p>
                </div>
            </div>

            <!-- carte avis 2 -->
            <div class="review-card">
                <div class="card-glow"></div>
                <div class="card-content">
                    <div class="user-info">
                        <img src="../images/chewie_avatar.png" alt="Chewie" class="avatar">
                        <h3>CHEWIE</h3>
                    </div>
                    <p class="review-text">"RRRAARRWHHGWWR !<br><span class="translation">(TRADUCTION : MEILLEURES
                            PORTIONS DE LA GALAXIE)</span>"</p>
                </div>
            </div>

            <!-- carte avis 3 -->
            <div class="review-card">
                <div class="card-glow"></div>
                <div class="card-content">
                    <div class="user-info">
                        <img src="../images/lando_avatar.png" alt="Lando C." class="avatar">
                        <h3>LANDO C.</h3>
                    </div>
                    <p class="review-text">"UN SERVICE D'UNE ÉLÉGANCE RARE, DIGNE DE LA CITÉ DES NUAGES."</p>
                </div>
            </div>
        </div>
    </section>


</main>

<?php require_once 'includes/footer.php'; ?>