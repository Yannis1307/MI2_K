document.addEventListener('DOMContentLoaded', () => {
    const catSelect = document.getElementById('filter-category');
    const dietSelect = document.getElementById('filter-diet');
    const sortSelect = document.getElementById('filter-sort');
    const searchInput = document.getElementById('product-search');
    const grid = document.getElementById('products-grid');

    if (!grid) return;

    // on recupere l'ordre initial des cartes au chargement
    const initialCards = Array.from(grid.querySelectorAll('.product-card'));
    for (let i = 0; i < initialCards.length; i++) {
        initialCards[i].setAttribute('data-index', i);
    }

    function formatPrice(price) {
        return price.toFixed(2).replace('.', ',') + ' ₹';
    }

    function escapeHtml(text) {
        if (!text) return '';
        const div = document.createElement('div');
        div.textContent = text;
        return div.innerHTML;
    }

    // fonction pour trier le dom existant
    function applyLocalSort() {
        const sortValue = sortSelect.value;
        const currentCards = Array.from(grid.querySelectorAll('.product-card'));

        if (sortValue !== '') {
            currentCards.sort((a, b) => {
                const priceTextA = a.querySelector('.price').textContent;
                const priceTextB = b.querySelector('.price').textContent;
                const priceA = parseFloat(priceTextA.replace(' ₹', '').replace(',', '.'));
                const priceB = parseFloat(priceTextB.replace(' ₹', '').replace(',', '.'));

                if (sortValue === 'price_asc') {
                    return priceA - priceB;
                } else if (sortValue === 'price_desc') {
                    return priceB - priceA;
                }
                return 0;
            });
        } else {
            // retour a l'ordre de l'index s'il existe
            currentCards.sort((a, b) => {
                const idxA = a.getAttribute('data-index') ? parseInt(a.getAttribute('data-index')) : 0;
                const idxB = b.getAttribute('data-index') ? parseInt(b.getAttribute('data-index')) : 0;
                return idxA - idxB;
            });
        }

        // reorganisation dans le dom
        for (let k = 0; k < currentCards.length; k++) {
            grid.appendChild(currentCards[k]);
        }
    }

    // filtrage asynchrone via fetch
    function applyAsyncFilter() {
        const catValue = catSelect.value;
        const dietValue = dietSelect.value;
        const searchValue = searchInput.value.toLowerCase().trim();

        const loader = document.getElementById('products-loader');
        loader.style.display = 'block';
        grid.style.opacity = '0.5';

        // construction de l'url de l'api
        const url = '../api/get_products.php?category=' + encodeURIComponent(catValue) +
            '&diet=' + encodeURIComponent(dietValue) +
            '&search=' + encodeURIComponent(searchValue);

        fetch(url)
            .then(response => response.json())
            .then(data => {
                loader.style.display = 'none';
                grid.style.opacity = '1';

                if (data.success) {
                    // on vide la grille
                    grid.innerHTML = '';

                    const plats = data.plats;
                    if (plats.length === 0) {
                        const msg = document.createElement('div');
                        msg.id = 'no-product-msg';
                        msg.style.cssText = 'width: 100%; text-align: center; color: rgba(255,255,255,0.5); padding: 40px; grid-column: 1 / -1;';
                        msg.innerHTML = '<p>Aucun produit ne correspond à votre recherche.</p>';
                        grid.appendChild(msg);
                        return;
                    }

                    // on regenere les cartes
                    for (let j = 0; j < plats.length; j++) {
                        const plat = plats[j];
                        const card = document.createElement('div');
                        card.className = 'product-card';
                        card.setAttribute('data-category', escapeHtml(plat.categorie));
                        card.setAttribute('data-index', j); // nouvel index

                        let innerHTML = '<div class="card-glow"></div><div class="card-inner">';

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
            .catch(error => {
                loader.style.display = 'none';
                grid.style.opacity = '1';
                console.error('Erreur API:', error);
            });
    }

    // ajout des events
    if (catSelect) catSelect.addEventListener('change', applyAsyncFilter);
    if (dietSelect) dietSelect.addEventListener('change', applyAsyncFilter);

    // pour le tri on ne fait pas d'appel api, on retrie juste localement
    if (sortSelect) sortSelect.addEventListener('change', applyLocalSort);

    let timeout = null;
    if (searchInput) {
        searchInput.addEventListener('input', () => {
            clearTimeout(timeout);
            timeout = setTimeout(applyAsyncFilter, 300); // debounce 300ms
        });
    }
});
