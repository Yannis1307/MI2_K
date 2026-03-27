<?php
// variables de configuration pour le header
$page_title = 'La Carte';
$page_css = 'produits.css';
$page_id = 'produits';

// on charge les fonctions utilitaires json
require_once 'includes/functions.php';

// on recupere tous les plats du json
$plats = read_json('plats.json');

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

            <?php
            // boucle pour afficher chaque plat dynamiquement
            foreach ($plats as $plat):
                // on prepare les regimes pour les attributs data-diet et data-flavor
                $regimes = $plat['regimes'] ?? [];
                $diet = implode(' ', array_filter($regimes, function ($r) {
                    return $r !== 'piquant';
                }));
                $is_piquant = in_array('piquant', $regimes);
                $is_vege = in_array('vege', $regimes);
            ?>

            <div class="product-card" data-category="<?= htmlspecialchars($plat['categorie']) ?>" <?php if (!empty($diet)) : ?>data-diet="<?= htmlspecialchars($diet) ?>"<?php endif; ?> <?php if ($is_piquant) : ?>data-flavor="piquant"<?php endif; ?>>
                <div class="card-glow"></div>
                <div class="card-inner">

                    <?php // affichage conditionnel du badge piquant ?>
                    <?php if ($is_piquant) : ?>
                    <span class="holo-badge badge-hot">PIQUANT</span>
                    <?php endif; ?>

                    <?php // affichage conditionnel du badge vegetarien ?>
                    <?php if ($is_vege && !$is_piquant) : ?>
                    <span class="holo-badge badge-nouveau">VÉGÉ</span>
                    <?php endif; ?>

                    <img src="../<?= htmlspecialchars($plat['image']) ?>" alt="<?= htmlspecialchars($plat['nom']) ?>">
                    <div class="card-content">
                        <h3 class="product-name"><?= htmlspecialchars($plat['nom']) ?></h3>
                        <p class="product-desc"><?= htmlspecialchars($plat['description']) ?></p>
                        <details class="product-details">
                            <summary class="details-btn">En savoir plus [+]</summary>
                            <div class="details-content">
                                <?php // affichage du lore (histoire galactique) si disponible ?>
                                <?php if (!empty($plat['lore'])) : ?>
                                <p class="lore"><strong>Histoire Galactique :</strong> <?= htmlspecialchars($plat['lore']) ?></p>
                                <?php endif; ?>

                                <?php // affichage des ingredients si disponibles ?>
                                <?php if (!empty($plat['ingredients'])) : ?>
                                <p class="ingredients"><strong>Ingrédients :</strong> <?= htmlspecialchars($plat['ingredients']) ?></p>
                                <?php endif; ?>

                                <?php // affichage des allergenes seulement si le plat en a ?>
                                <?php if (!empty($plat['allergenes'])) : ?>
                                <p class="allergens"><strong>Allergènes :</strong> <?= htmlspecialchars(implode(', ', $plat['allergenes'])) ?></p>
                                <?php else : ?>
                                <p class="allergens"><strong>Allergènes :</strong> Aucun connu</p>
                                <?php endif; ?>
                            </div>
                        </details>
                        <div class="price-section">
                            <span class="price"><?= number_format($plat['prix'], 2, ',', '') ?> ₹</span>
                            <!-- formulaire d'ajout au panier -->
                            <form method="POST" action="ajouter_panier.php" style="display:inline;">
                                <input type="hidden" name="id_plat" value="<?= $plat['id'] ?>">
                                <button type="submit" class="add-btn">+</button>
                            </form>
                        </div>
                    </div>
                </div>
            </div>

            <?php endforeach; ?>

        </div>
    </main>

<?php require_once 'includes/footer.php'; ?>