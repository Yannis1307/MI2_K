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

        <div class="filters-container" style="display: flex; gap: 10px; flex-wrap: wrap; justify-content: center; margin-bottom: 20px;">
            <select id="filter-category" class="filter-select" style="padding: 10px; border-radius: 8px; background: rgba(255,255,255,0.1); color: inherit; border: 1px solid rgba(255,255,255,0.2);">
                <option value="tous" style="color: #000;">Toutes Catégories</option>
                <option value="boissons" style="color: #000;">Boissons</option>
                <option value="plats" style="color: #000;">Plats</option>
                <option value="snacks" style="color: #000;">Snacks</option>
                <option value="specialites" style="color: #000;">Spécialités</option>
            </select>

            <select id="filter-diet" class="filter-select" style="padding: 10px; border-radius: 8px; background: rgba(255,255,255,0.1); color: inherit; border: 1px solid rgba(255,255,255,0.2);">
                <option value="" style="color: #000;">Tous Régimes/Goûts</option>
                <option value="vege" style="color: #000;">🌱 Végétarien</option>
                <option value="gluten-free" style="color: #000;">🌾 Sans Gluten</option>
                <option value="piquant" style="color: #000;">🌶️ Piquant</option>
            </select>

            <select id="filter-sort" class="filter-select" style="padding: 10px; border-radius: 8px; background: rgba(255,255,255,0.1); color: inherit; border: 1px solid rgba(255,255,255,0.2);">
                <option value="" style="color: #000;">Tri par défaut</option>
                <option value="price_asc" style="color: #000;">Prix croissant</option>
                <option value="price_desc" style="color: #000;">Prix décroissant</option>
            </select>
        </div>

        <!-- loader -->
        <div id="products-loader" style="display: none; text-align: center; margin: 20px 0;">
            <span style="font-size: 2em; animation: spin 1s linear infinite; display: inline-block;">⚙️</span>
        </div>

        <!-- grille de produits -->
        <div class="products-grid" id="products-grid">

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
                            <form method="POST" action="ajouter_panier.php" style="display:inline;" onsubmit="this.querySelector('.add-btn').disabled=true;">
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
                            <form method="POST" action="ajouter_panier.php" style="display:inline;" onsubmit="this.querySelector('.add-btn').disabled=true;">
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

    <!-- Script de filtrage et tri DOM (côté client) -->
    <script>
    document.addEventListener('DOMContentLoaded', function() {
        var catSelect = document.getElementById('filter-category');
        var dietSelect = document.getElementById('filter-diet');
        var sortSelect = document.getElementById('filter-sort');
        var searchInput = document.getElementById('product-search');
        var grid = document.getElementById('products-grid');
        
        // on recupere l'ordre initial des cartes au chargement
        var initialCards = Array.from(grid.querySelectorAll('.product-card'));
        for (var i = 0; i < initialCards.length; i++) {
            initialCards[i].setAttribute('data-index', i);
        }
        
        function formatPrice(price) {
            return price.toFixed(2).replace('.', ',') + ' ₹';
        }
        
        function escapeHtml(text) {
            if (!text) return '';
            var div = document.createElement('div');
            div.textContent = text;
            return div.innerHTML;
        }

        // fonction pour trier le DOM existant
        function applyLocalSort() {
            var sortValue = sortSelect.value;
            var currentCards = Array.from(grid.querySelectorAll('.product-card'));
            
            if (sortValue !== '') {
                currentCards.sort(function(a, b) {
                    var priceTextA = a.querySelector('.price').textContent;
                    var priceTextB = b.querySelector('.price').textContent;
                    var priceA = parseFloat(priceTextA.replace(' ₹', '').replace(',', '.'));
                    var priceB = parseFloat(priceTextB.replace(' ₹', '').replace(',', '.'));
                    
                    if (sortValue === 'price_asc') {
                        return priceA - priceB;
                    } else if (sortValue === 'price_desc') {
                        return priceB - priceA;
                    }
                    return 0;
                });
            } else {
                // Retour a l'ordre de l'index s'il existe
                currentCards.sort(function(a, b) {
                    var idxA = a.getAttribute('data-index') ? parseInt(a.getAttribute('data-index')) : 0;
                    var idxB = b.getAttribute('data-index') ? parseInt(b.getAttribute('data-index')) : 0;
                    return idxA - idxB;
                });
            }
            
            // Reorganisation dans le DOM
            for (var k = 0; k < currentCards.length; k++) {
                grid.appendChild(currentCards[k]);
            }
        }

        // filtrage asynchrone via fetch
        function applyAsyncFilter() {
            var catValue = catSelect.value;
            var dietValue = dietSelect.value;
            var searchValue = searchInput.value.toLowerCase().trim();
            
            var loader = document.getElementById('products-loader');
            loader.style.display = 'block';
            grid.style.opacity = '0.5';

            // construction de l'url de l'api
            var url = '../api/get_products.php?category=' + encodeURIComponent(catValue) +
                      '&diet=' + encodeURIComponent(dietValue) +
                      '&search=' + encodeURIComponent(searchValue);
            
            fetch(url)
                .then(function(response) { return response.json(); })
                .then(function(data) {
                    loader.style.display = 'none';
                    grid.style.opacity = '1';
                    
                    if (data.success) {
                        // on vide la grille
                        grid.innerHTML = '';
                        
                        var plats = data.plats;
                        if (plats.length === 0) {
                            var msg = document.createElement('div');
                            msg.id = 'no-product-msg';
                            msg.style.cssText = 'width: 100%; text-align: center; color: rgba(255,255,255,0.5); padding: 40px; grid-column: 1 / -1;';
                            msg.innerHTML = '<p>Aucun produit ne correspond à votre recherche.</p>';
                            grid.appendChild(msg);
                            return;
                        }
                        
                        // on regenere les cartes
                        for (var j = 0; j < plats.length; j++) {
                            var plat = plats[j];
                            var card = document.createElement('div');
                            card.className = 'product-card';
                            card.setAttribute('data-category', escapeHtml(plat.categorie));
                            card.setAttribute('data-index', j); // nouvel index
                            
                            var innerHTML = '<div class="card-glow"></div><div class="card-inner">';
                            
                            if (plat.is_piquant) {
                                innerHTML += '<span class="holo-badge badge-hot">PIQUANT</span>';
                            } else if (plat.is_vege) {
                                innerHTML += '<span class="holo-badge badge-nouveau">VÉGÉ</span>';
                            }
                            
                            innerHTML += '<img src="../' + escapeHtml(plat.image) + '" alt="' + escapeHtml(plat.nom) + '">';
                            innerHTML += '<div class="card-content">';
                            innerHTML += '<h3 class="product-name">' + escapeHtml(plat.nom) + '</h3>';
                            innerHTML += '<p class="product-desc">' + escapeHtml(plat.description) + '</p>';
                            
                            // details
                            innerHTML += '<details class="product-details"><summary class="details-btn">En savoir plus [+]</summary><div class="details-content">';
                            if (plat.lore) {
                                innerHTML += '<p class="lore"><strong>Histoire Galactique :</strong> ' + escapeHtml(plat.lore) + '</p>';
                            }
                            if (plat.ingredients) {
                                innerHTML += '<p class="ingredients"><strong>Ingrédients :</strong> ' + escapeHtml(plat.ingredients) + '</p>';
                            }
                            if (plat.allergenes && plat.allergenes.length > 0) {
                                innerHTML += '<p class="allergens"><strong>Allergènes :</strong> ' + escapeHtml(plat.allergenes.join(', ')) + '</p>';
                            } else {
                                innerHTML += '<p class="allergens"><strong>Allergènes :</strong> Aucun connu</p>';
                            }
                            innerHTML += '</div></details>';
                            
                            // prix et panier
                            innerHTML += '<div class="price-section">';
                            innerHTML += '<span class="price">' + formatPrice(plat.prix) + '</span>';
                            innerHTML += '<form method="POST" action="ajouter_panier.php" style="display:inline;" onsubmit="this.querySelector(\'.add-btn\').disabled=true;">';
                            innerHTML += '<input type="hidden" name="id_plat" value="' + escapeHtml(plat.id) + '">';
                            innerHTML += '<button type="submit" class="add-btn">+</button>';
                            innerHTML += '</form></div></div></div>';
                            
                            card.innerHTML = innerHTML;
                            grid.appendChild(card);
                        }
                        
                        // apres avoir reconstruit le dom, on applique le tri local
                        applyLocalSort();
                    }
                })
                .catch(function(error) {
                    loader.style.display = 'none';
                    grid.style.opacity = '1';
                    console.error('Erreur API:', error);
                });
        }

        // Ajout des events
        catSelect.addEventListener('change', applyAsyncFilter);
        dietSelect.addEventListener('change', applyAsyncFilter);
        
        // pour le tri on ne fait PAS d'appel API, on re-trie juste localement
        sortSelect.addEventListener('change', applyLocalSort);
        
        var timeout = null;
        searchInput.addEventListener('input', function() {
            clearTimeout(timeout);
            timeout = setTimeout(applyAsyncFilter, 300); // debounce 300ms
        });
    });
    </script>

<?php require_once 'includes/footer.php'; ?>