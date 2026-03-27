<?php
// variables de configuration pour le header
$page_title = 'La Carte';
$page_css = 'produits.css';
$page_id = 'produits';

// on charge les fonctions utilitaires json
require_once 'includes/functions.php';

// on inclut le header commun
require_once 'includes/header.php';
?>

<main>
    <!-- titre de la page -->
    <div class="menu-header">
        <h1 class="epic-title">Holo-Menu Galactique</h1>
        <p class="subtitle">Découvrez les saveurs de la galaxie lointaine, très lointaine...</p>
    </div>

    <!-- barre de recherche centree -->
    <div class="search-bar-container">
        <input type="text" id="product-search" class="product-search-input" placeholder="🔍 Rechercher un plat...">
    </div>

    <!-- boutons radio caches pour le filtrage css pur (sans javascript) -->
    <input type="radio" name="filter" id="filter-tous" checked hidden>
    <input type="radio" name="filter" id="filter-boissons" hidden>
    <input type="radio" name="filter" id="filter-plats" hidden>
    <input type="radio" name="filter" id="filter-snacks" hidden>
    <input type="radio" name="filter" id="filter-specialites" hidden>
    <!-- filtres de regime -->
    <input type="radio" name="filter" id="filter-vege" hidden>
    <input type="radio" name="filter" id="filter-gluten-free" hidden>
    <input type="radio" name="filter" id="filter-piquant" hidden>

    <!-- boutons de filtre visibles (labels relies aux radios) -->
    <div class="filters-container">
        <label for="filter-tous" class="filter-btn">Tous</label>
        <label for="filter-boissons" class="filter-btn">Boissons</label>
        <label for="filter-plats" class="filter-btn">Plats</label>
        <label for="filter-snacks" class="filter-btn">Snacks</label>
        <label for="filter-specialites" class="filter-btn">Spécialités</label>
        <label for="filter-vege" class="filter-btn filter-vege">🌱 Végétarien</label>
        <label for="filter-gluten-free" class="filter-btn filter-gluten">🌾 Sans Gluten</label>
        <label for="filter-piquant" class="filter-btn filter-spicy">🌶️ Piquant</label>
    </div>

    <!-- grille de produits -->
    <div class="products-grid">

        <!-- boissons -->
        <div class="product-card" data-category="boissons" data-diet="vege gluten-free">
            <div class="card-glow"></div>
            <div class="card-inner">
                <span class="holo-badge badge-bestseller">BEST-SELLER</span>
                <img src="../images/Boisson/Laitbleu.png" alt="Lait Bleu">
                <div class="card-content">
                    <h3 class="product-name">Lait Bleu</h3>
                    <p class="product-desc">Lait de bantha riche et crémeux.</p>
                    <details class="product-details">
                        <summary class="details-btn">En savoir plus [+]</summary>
                        <div class="details-content">
                            <p class="lore">🌌 <strong>Histoire Galactique :</strong> Récolté sur les femelles
                                Bantha de Tatooine, ce lait bleu est une boisson traditionnelle des chasseurs
                                Tuskens. Sa couleur unique provient de l'alimentation à base de plantes rares du
                                désert.</p>
                            <p class="ingredients"><strong>Ingrédients :</strong> Lait de Bantha, extrait de cactus
                                Hubba, cristaux de sel de Tatooine</p>
                            <p class="allergens"><strong>Allergènes :</strong> 🥛 Lactose</p>
                        </div>
                    </details>
                    <div class="price-section">
                        <span class="price">7 Crédits</span>
                        <button class="add-btn">+</button>
                    </div>
                </div>
            </div>
        </div>

        <div class="product-card" data-category="boissons" data-diet="vege gluten-free">
            <div class="card-glow"></div>
            <div class="card-inner">
                <img src="../images/Boisson/Laitvert.png" alt="Lait Vert">
                <div class="card-content">
                    <h3 class="product-name">Lait Vert</h3>
                    <p class="product-desc">Issu des sirènes de thala, goût iodé.</p>
                    <details class="product-details">
                        <summary class="details-btn">En savoir plus [+]</summary>
                        <div class="details-content">
                            <p class="lore">🌊 <strong>Histoire Galactique :</strong> Trait directement des
                                créatures marines Thala-sirènes sur la planète Ahch-To. Luke Skywalker lui-même a
                                survécu en buvant ce lait pendant son exil. Goût rafraîchissant et légèrement salé.
                            </p>
                            <p class="ingredients"><strong>Ingrédients :</strong> Lait de Thala-sirène, algues
                                d'Ahch-To, essence de varech spatial</p>
                            <p class="allergens"><strong>Allergènes :</strong> 🥛 Lactose, 🐟 Produits marins</p>
                        </div>
                    </details>
                    <div class="price-section">
                        <span class="price">8 Crédits</span>
                        <button class="add-btn">+</button>
                    </div>
                </div>
            </div>
        </div>

        <div class="product-card" data-category="boissons" data-diet="vege gluten-free">
            <div class="card-glow"></div>
            <div class="card-inner">
                <img src="../images/Boisson/jusdejabba.jpg" alt="Jus de Jabba">
                <div class="card-content">
                    <h3 class="product-name">Jus de Jabba</h3>
                    <p class="product-desc">Trouble et visqueux, saveur Hutt.</p>
                    <details class="product-details">
                        <summary class="details-btn">En savoir plus [+]</summary>
                        <div class="details-content">
                            <p class="lore">👑 <strong>Histoire Galactique :</strong> Boisson officielle du Palais
                                de Jabba le Hutt. Sa consistance épaisse et son goût unique en font une boisson
                                prisée des collectionneurs. Certains disent qu'elle contient des épices rares de
                                Kessel...</p>
                            <p class="ingredients"><strong>Ingrédients :</strong> Fruits Gorga, pulpe de cactus
                                géant, sucre de canne de Naboo, épices mystérieuses</p>
                            <p class="allergens"><strong>Allergènes :</strong> Aucun connu</p>
                        </div>
                    </details>
                    <div class="price-section">
                        <span class="price">5 Crédits</span>
                        <button class="add-btn">+</button>
                    </div>
                </div>
            </div>
        </div>

        <div class="product-card" data-category="boissons" data-diet="vege gluten-free" data-flavor="piquant">
            <div class="card-glow"></div>
            <div class="card-inner">
                <span class="holo-badge badge-hot">PIQUANT</span>
                <img src="../images/Boisson/SpotchkaSoda.jpg" alt="Soda de Spotchka">
                <div class="card-content">
                    <h3 class="product-name">Soda de Spotchka</h3>
                    <p class="product-desc">Boisson pétillante au krill de Nevarro.</p>
                    <details class="product-details">
                        <summary class="details-btn">En savoir plus [+]</summary>
                        <div class="details-content">
                            <p class="lore">🌋 <strong>Histoire Galactique :</strong> Fermentée avec du krill des
                                rivières de lave de Nevarro. Très appréciée par les chasseurs de primes pour son
                                côté énergisant. Attention : légèrement épicé et pétillant !</p>
                            <p class="ingredients"><strong>Ingrédients :</strong> Krill de Nevarro, eau gazéifiée,
                                piment des volcans, sirop de lave refroidie</p>
                            <p class="allergens"><strong>Allergènes :</strong> 🦐 Crustacés</p>
                        </div>
                    </details>
                    <div class="price-section">
                        <span class="price">6 Crédits</span>
                        <button class="add-btn">+</button>
                    </div>
                </div>
            </div>
        </div>

        <!-- plats -->
        <div class="product-card" data-category="plats" data-diet="gluten-free">
            <div class="card-glow"></div>
            <div class="card-inner">
                <img src="../images/plats/rotidenuna.jpg" alt="Rôti de Nuna">
                <div class="card-content">
                    <h3 class="product-name">Rôti de Nuna</h3>
                    <p class="product-desc">Volaille grillée de Naboo.</p>
                    <details class="product-details">
                        <summary class="details-btn">En savoir plus [+]</summary>
                        <div class="details-content">
                            <p class="lore">🏛️ <strong>Histoire Galactique :</strong> Plat traditionnel servi lors
                                des banquets royaux de Naboo. La Reine Amidala elle-même appréciait ce mets délicat.
                                Élevé dans les prairies verdoyantes de Naboo, le Nuna est connu pour sa chair
                                tendre.</p>
                            <p class="ingredients"><strong>Ingrédients :</strong> Volaille Nuna, herbes de Naboo,
                                beurre de Nerf, citron de Theed</p>
                            <p class="allergens"><strong>Allergènes :</strong> 🥛 Lactose (beurre)</p>
                        </div>
                    </details>
                    <div class="price-section">
                        <span class="price">15 Crédits</span>
                        <button class="add-btn">+</button>
                    </div>
                </div>
            </div>
        </div>

        <div class="product-card" data-category="plats" data-diet="vege gluten-free">
            <div class="card-glow"></div>
            <div class="card-inner">
                <img src="../images/plats/ragoutderacinedendor.jpg" alt="Ragoût de racine d'Endor">
                <div class="card-content">
                    <h3 class="product-name">Ragoût de racine d'Endor</h3>
                    <p class="product-desc">Recette secrète de Yoda.</p>
                    <details class="product-details">
                        <summary class="details-btn">En savoir plus [+]</summary>
                        <div class="details-content">
                            <p class="lore">🧙 <strong>Histoire Galactique :</strong> "Bon pour la Force, ce ragoût
                                est. Hmmmm." - Maître Yoda. Préparé selon une ancienne recette Jedi transmise de
                                génération en génération. Les racines d'Endor sont cultivées par les Ewoks
                                eux-mêmes.</p>
                            <p class="ingredients"><strong>Ingrédients :</strong> Racines d'Endor, champignons
                                lunaires, bouillon de légumes, herbes mystiques</p>
                            <p class="allergens"><strong>Allergènes :</strong> Aucun connu</p>
                        </div>
                    </details>
                    <div class="price-section">
                        <span class="price">12 Crédits</span>
                        <button class="add-btn">+</button>
                    </div>
                </div>
            </div>
        </div>

        <div class="product-card" data-category="plats" data-diet="gluten-free" data-flavor="piquant">
            <div class="card-glow"></div>
            <div class="card-inner">
                <span class="holo-badge badge-hot">PIQUANT</span>
                <img src="../images/plats/CotelettesdeDewback.jpg" alt="Côtelettes de Dewback">
                <div class="card-content">
                    <h3 class="product-name">Côtelettes de Dewback</h3>
                    <p class="product-desc">Viande de reptile marinée aux épices.</p>
                    <details class="product-details">
                        <summary class="details-btn">En savoir plus [+]</summary>
                        <div class="details-content">
                            <p class="lore">🦎 <strong>Histoire Galactique :</strong> Les Stormtroopers impériaux
                                utilisaient les Dewbacks comme montures sur Tatooine. Ce plat robuste nécessite une
                                marinade de 48h dans les épices les plus piquantes du désert. Attention : très épicé
                                !</p>
                            <p class="ingredients"><strong>Ingrédients :</strong> Viande de Dewback, poivre des
                                sables, piment rouge de Mos Eisley, huile d'olive galactique</p>
                            <p class="allergens"><strong>Allergènes :</strong> Aucun connu</p>
                        </div>
                    </details>
                    <div class="price-section">
                        <span class="price">20 Crédits</span>
                        <button class="add-btn">+</button>
                    </div>
                </div>
            </div>
        </div>

        <!-- snacks -->
        <div class="product-card" data-category="snacks">
            <div class="card-glow"></div>
            <div class="card-inner">
                <span class="holo-badge badge-nouveau">NOUVEAU</span>
                <img src="../images/Snacks et Sucreries/macaronsdenevarro.jpg" alt="Macarons de Nevarro">
                <div class="card-content">
                    <h3 class="product-name">Macarons de Nevarro</h3>
                    <p class="product-desc">Biscuits bleus croquants.</p>
                    <details class="product-details">
                        <summary class="details-btn">En savoir plus [+]</summary>
                        <div class="details-content">
                            <p class="lore">🍪 <strong>Histoire Galactique :</strong> Créés par les artisans
                                pâtissiers de Nevarro après la libération de la planète. Leur couleur bleue provient
                                d'un colorant naturel extrait des cristaux locaux. Croquants à l'extérieur, moelleux
                                à l'intérieur.</p>
                            <p class="ingredients"><strong>Ingrédients :</strong> Farine de blé galactique, sucre
                                cristallisé, amandes de Corellia, extrait de cristal bleu</p>
                            <p class="allergens"><strong>Allergènes :</strong> 🥜 Fruits à coque, 🌾 Gluten, 🥚 Œufs
                            </p>
                        </div>
                    </details>
                    <div class="price-section">
                        <span class="price">10 Crédits</span>
                        <button class="add-btn">+</button>
                    </div>
                </div>
            </div>
        </div>

        <div class="product-card" data-category="snacks" data-diet="vege">
            <div class="card-glow"></div>
            <div class="card-inner">
                <img src="../images/Snacks et Sucreries/gauffresdelespaces.jpg" alt="Gaufres de l'Espace">
                <div class="card-content">
                    <h3 class="product-name">Gaufres de l'Espace</h3>
                    <p class="product-desc">Petit-déjeuner des pilotes.</p>
                    <details class="product-details">
                        <summary class="details-btn">En savoir plus [+]</summary>
                        <div class="details-content">
                            <p class="lore">🚀 <strong>Histoire Galactique :</strong> Le carburant préféré des
                                pilotes de X-Wing avant les missions. Luke Skywalker en mangeait chaque matin à la
                                base Yavin IV. Servies chaudes avec du sirop de miel des Wookiees.</p>
                            <p class="ingredients"><strong>Ingrédients :</strong> Farine complète, œufs de Porg,
                                lait de Bantha, sirop de miel de Kashyyyk</p>
                            <p class="allergens"><strong>Allergènes :</strong> 🥛 Lactose, 🥚 Œufs, 🌾 Gluten</p>
                        </div>
                    </details>
                    <div class="price-section">
                        <span class="price">8 Crédits</span>
                        <button class="add-btn">+</button>
                    </div>
                </div>
            </div>
        </div>

        <div class="product-card" data-category="snacks" data-diet="vege gluten-free">
            <div class="card-glow"></div>
            <div class="card-inner">
                <img src="../images/Snacks et Sucreries/fruitsjogan.jpg" alt="Fruits Jogan">
                <div class="card-content">
                    <h3 class="product-name">Fruits Jogan</h3>
                    <p class="product-desc">Fruit rayé de la Bordure Extérieure.</p>
                    <details class="product-details">
                        <summary class="details-btn">En savoir plus [+]</summary>
                        <div class="details-content">
                            <p class="lore">🍎 <strong>Histoire Galactique :</strong> Cultivé dans les vergers
                                flottants de Lothal. Ezra Bridger en volait régulièrement au marché avant de
                                rejoindre les Rebelles. Goût sucré avec une pointe d'acidité. Riche en vitamines
                                galactiques.</p>
                            <p class="ingredients"><strong>Ingrédients :</strong> 100% Fruit Jogan naturel</p>
                            <p class="allergens"><strong>Allergènes :</strong> Aucun connu</p>
                        </div>
                    </details>
                    <div class="price-section">
                        <span class="price">5 Crédits</span>
                        <button class="add-btn">+</button>
                    </div>
                </div>
            </div>
        </div>

        <!-- specialites -->
        <div class="product-card" data-category="specialites" data-flavor="piquant">
            <div class="card-glow"></div>
            <div class="card-inner">
                <span class="holo-badge badge-bestseller">BEST-SELLER</span>
                <img src="../images/Spécialités Exotiques/rontowrap.jpg" alt="Ronto Wrap">
                <div class="card-content">
                    <h3 class="product-name">Ronto Wrap</h3>
                    <p class="product-desc">Saucisse grillée au podracer.</p>
                    <details class="product-details">
                        <summary class="details-btn">En savoir plus [+]</summary>
                        <div class="details-content">
                            <p class="lore">🏁 <strong>Histoire Galactique :</strong> Street-food emblématique de
                                Tatooine, grillée aux moteurs de podracers ! Les mécaniciens de Mos Espa ont
                                perfectionné cette technique de cuisson rapide qui donne un goût fumé unique.
                                Attention : très copieux !</p>
                            <p class="ingredients"><strong>Ingrédients :</strong> Saucisse de Ronto, pain pita
                                galactique, sauce épicée, oignons grillés, poivrons rouges</p>
                            <p class="allergens"><strong>Allergènes :</strong> 🌾 Gluten</p>
                        </div>
                    </details>
                    <div class="price-section">
                        <span class="price">14 Crédits</span>
                        <button class="add-btn">+</button>
                    </div>
                </div>
            </div>
        </div>

        <div class="product-card" data-category="specialites" data-diet="gluten-free">
            <div class="card-glow"></div>
            <div class="card-inner">
                <img src="../images/Spécialités Exotiques/tartaredeYipYip.jpg" alt="Tartare de Tip-Yip">
                <div class="card-content">
                    <h3 class="product-name">Tartare de Tip-Yip</h3>
                    <p class="product-desc">Poulet d'Endor assaisonné.</p>
                    <details class="product-details">
                        <summary class="details-btn">En savoir plus [+]</summary>
                        <div class="details-content">
                            <p class="lore">🌲 <strong>Histoire Galactique :</strong> Élevé en liberté dans les
                                forêts d'Endor sous la surveillance bienveillante des Ewoks. Préparation raffinée
                                selon les techniques culinaires de Coruscant. Viande tendre et parfumée aux herbes
                                forestières.</p>
                            <p class="ingredients"><strong>Ingrédients :</strong> Viande de Tip-Yip, huile d'olive
                                d'Alderaan, échalotes, câpres, herbes d'Endor, jaune d'œuf</p>
                            <p class="allergens"><strong>Allergènes :</strong> 🥚 Œufs</p>
                        </div>
                    </details>
                    <div class="price-section">
                        <span class="price">16 Crédits</span>
                        <button class="add-btn">+</button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</main>

<?php require_once 'includes/footer.php'; ?>