document.addEventListener('DOMContentLoaded', () => {
    const dataContainer = document.getElementById('cart-data');
    if (!dataContainer) return;

    let totalInitial = parseFloat(dataContainer.getAttribute('data-total')) || 0;
    const creditsDispo = parseFloat(dataContainer.getAttribute('data-credits')) || 0;

    const cbCredits = document.getElementById('utiliser_credits');
    const txtBtnTotal = document.getElementById('txt-btn-total');
    const cartTotal = document.getElementById('cart-total');
    const cartCount = document.getElementById('cart-count');

    // macros elements
    const macroCal = document.getElementById('macro-cal-val');
    const macroPro = document.getElementById('macro-pro-val');
    const macroGlu = document.getElementById('macro-glu-val');
    const macroLip = document.getElementById('macro-lip-val');

    const updateTotalDisplay = () => {
        let totalFinal = totalInitial;
        if (cbCredits && cbCredits.checked) {
            totalFinal = Math.max(0, totalFinal - creditsDispo);
        }
        if (txtBtnTotal) {
            txtBtnTotal.textContent = totalFinal.toFixed(2).replace('.', ',');
        }
        if (cartTotal) {
            cartTotal.textContent = totalInitial.toFixed(2).replace('.', ',');
        }
    };

    if (cbCredits) {
        cbCredits.addEventListener('change', updateTotalDisplay);
    }

    const updateQuantity = async (id, type, newQuantity, rowElement) => {
        try {
            const response = await fetch('../api/update_panier_ajax.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify({
                    id: id,
                    type: type,
                    quantite: newQuantity
                })
            });

            const data = await response.json();
            if (data.success) {
                if (newQuantity <= 0) {
                    rowElement.remove();
                    // if table body is empty, we might want to reload to show empty state
                    const tbody = document.querySelector('.orders-table tbody');
                    if (tbody && tbody.children.length === 0) {
                        window.location.reload();
                        return;
                    }
                } else {
                    const qtySpan = document.getElementById(`qty-${type}-${id}`);
                    if (qtySpan) qtySpan.textContent = newQuantity;

                    // update subtotal
                    const subSpan = document.getElementById(`sub-${type}-${id}`);
                    if (subSpan) {
                        // we need the original unit price, which we didn't pass directly,
                        // but we can calculate it or simply let the backend return the individual subtotal
                        // actually, wait, it's easier to reload the page or return the individual item subtotal
                        // let's just calculate subtotal by reading the unit price from the table
                        const tr = rowElement;
                        const priceText = tr.querySelectorAll('.order-price')[0].textContent;
                        const price = parseFloat(priceText.replace(' ₹', '').replace(',', '.'));
                        const newSub = price * newQuantity;
                        subSpan.textContent = newSub.toFixed(2).replace('.', ',');
                    }
                }

                totalInitial = data.total;
                updateTotalDisplay();

                // update macros
                if (data.macros) {
                    if (macroCal) macroCal.textContent = data.macros.calories;
                    if (macroPro) macroPro.textContent = data.macros.proteines;
                    if (macroGlu) macroGlu.textContent = data.macros.glucides;
                    if (macroLip) macroLip.textContent = data.macros.lipides;
                }

                // update cart count
                if (cartCount) {
                    if (data.nb_articles > 0) {
                        cartCount.textContent = `(${data.nb_articles})`;
                        cartCount.style.display = 'inline';
                    } else {
                        cartCount.style.display = 'none';
                        window.location.reload();
                    }
                }

            } else {
                alert('Erreur lors de la mise à jour : ' + data.message);
            }
        } catch (error) {
            console.error('Erreur:', error);
            alert('Une erreur est survenue.');
        }
    };

    // attach events for buttons
    document.querySelectorAll('.btn-qty-plus').forEach(btn => {
        btn.addEventListener('click', function () {
            const id = this.getAttribute('data-id');
            const type = this.getAttribute('data-type');
            const qtySpan = document.getElementById(`qty-${type}-${id}`);
            const currentQty = parseInt(qtySpan.textContent);
            const rowElement = this.closest('tr');
            updateQuantity(id, type, currentQty + 1, rowElement);
        });
    });

    document.querySelectorAll('.btn-qty-minus').forEach(btn => {
        btn.addEventListener('click', function () {
            const id = this.getAttribute('data-id');
            const type = this.getAttribute('data-type');
            const qtySpan = document.getElementById(`qty-${type}-${id}`);
            const currentQty = parseInt(qtySpan.textContent);
            const rowElement = this.closest('tr');
            if (currentQty > 0) {
                updateQuantity(id, type, currentQty - 1, rowElement);
            }
        });
    });

    document.querySelectorAll('.btn-remove-item').forEach(btn => {
        btn.addEventListener('click', function () {
            const id = this.getAttribute('data-id');
            const type = this.getAttribute('data-type');
            const rowElement = this.closest('tr');
            updateQuantity(id, type, 0, rowElement);
        });
    });
});
