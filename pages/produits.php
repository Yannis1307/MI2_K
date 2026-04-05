<?php
// configuration de la page
$page_title = 'La Carte';
$page_css = 'produits.css';
$page_id = 'produits';

// chargement des fonctions json
require_once 'includes/functions.php';

// recuperation des plats
$plats = read_json('plats.json');

// indexation des plats par id
$plats_by_id = [];
foreach ($plats as $p) {
    $plats_by_id[$p['id']] = $p;
}

// recuperation des menus
$menus = read_json('menus.json');

// inclusion du header
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
            // boucle sur chaque plat
            foreach ($plats as $plat):
                // recuperation des regimes
                $regimes = isset($plat['regimes']) ? $plat['regimes'] : [];
                
                // filtrage des regimes
                $regimes_filtres = [];
                foreach ($regimes as $r) {
                    if ($r !== 'piquant') {
                        $regimes_filtres[] = $r;
                    }
                }
                $diet = implode(' ', $regimes_filtres);
                $is_piquant = in_array('piquant', $regimes);
                $is_vege = in_array('vege', $regimes);
            ?>

            <div class="product-card" data-category="<?= htmlspecialchars($plat['categorie']) ?>" <?php if (!empty($diet)) : ?>data-diet="<?= htmlspecialchars($diet) ?>"<?php endif; ?> <?php if ($is_piquant) : ?>data-flavor="piquant"<?php endif; ?>>
                <div class="card-glow"></div>
                <div class="card-inner">

                    <?php // badge piquant ?>
                    <?php if ($is_piquant) : ?>
                    <span class="holo-badge badge-hot">PIQUANT</span>
                    <?php endif; ?>

                    <?php // badge vegetarien ?>
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
                                <?php // lore du plat ?>
                                <?php if (!empty($plat['lore'])) : ?>
                                <p class="lore"><strong>Histoire Galactique :</strong> <?= htmlspecialchars($plat['lore']) ?></p>
                                <?php endif; ?>

                                <?php // ingredients ?>
                                <?php if (!empty($plat['ingredients'])) : ?>
                                <p class="ingredients"><strong>Ingrédients :</strong> <?= htmlspecialchars($plat['ingredients']) ?></p>
                                <?php endif; ?>

                                <?php // allergenes ?>
                                <?php if (!empty($plat['allergenes'])) : ?>
                                <p class="allergens"><strong>Allergènes :</strong> <?= htmlspecialchars(implode(', ', $plat['allergenes'])) ?></p>
                                <?php else : ?>
                                <p class="allergens"><strong>Allergènes :</strong> Aucun connu</p>
                                <?php endif; ?>
                            </div>
                        </details>
                        <div class="price-section">
                            <span class="price"><?= number_format($plat['prix'], 2, ',', '') ?> ₹</span>
                            <!-- ajout panier plat -->
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

        <!-- section menus -->
        <div class="menus-section">
            <div class="menus-section-header">
                <h2 class="menus-section-title">🍽️ Nos Menus Galactiques</h2>
                <p class="menus-section-subtitle">Des formules complètes pour tous les guerriers de la galaxie.</p>
            </div>
            <div class="menus-grid">
                <?php foreach ($menus as $menu): ?>
                <div class="menu-card">
                    <div class="menu-card-header">
                        <span class="menu-icon">⚔️</span>
                        <h3 class="menu-nom"><?= htmlspecialchars($menu['nom']) ?></h3>
                    </div>
                    <p class="menu-description"><?= htmlspecialchars($menu['description']) ?></p>

                    <ul class="menu-plats-list">
                        <?php foreach ($menu['plats_inclus'] as $id_plat): ?>
                            <?php if (isset($plats_by_id[$id_plat])): ?>
                            <li class="menu-plat-item">
                                <span class="menu-plat-bullet">✦</span>
                                <?= htmlspecialchars($plats_by_id[$id_plat]['nom']) ?>
                                <span class="menu-plat-prix"><?= number_format($plats_by_id[$id_plat]['prix'], 2, ',', '') ?> ₹</span>
                            </li>
                            <?php endif; ?>
                        <?php endforeach; ?>
                    </ul>

                    <div class="menu-footer">
                        <span class="menu-prix-total"><?= number_format($menu['prix_total'], 2, ',', '') ?> ₹</span>
                        <div style="display: flex; align-items: center; gap: 10px;">
                            <span class="menu-economie">Le menu complet</span>
                            <!-- ajout panier menu -->
                            <form method="POST" action="ajouter_panier.php" style="display:inline;">
                                <input type="hidden" name="id_menu" value="<?= $menu['id'] ?>">
                                <button type="submit" class="add-btn" style="padding: 5px 10px; font-size: 1.2em;">+</button>
                            </form>
                        </div>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
        </div>
    </main>

<?php require_once 'includes/footer.php'; ?>