<?php
// variables de configuration pour le header
$page_title = 'Donner mon avis';
$page_css = 'notation.css';
$page_id = 'notation';

// on charge les fonctions utilitaires json
require_once 'includes/functions.php';

// === CONTROLE D'ACCES : connexion obligatoire (client uniquement) ===
if (!isset($_SESSION['user']) || $_SESSION['user']['role'] !== 'client') {
    header('Location: connexion.php');
    exit;
}

// on inclut le header commun
require_once 'includes/header.php';
?>

<main>
    <div class="form-container">

        <!-- titre et sous-titre -->
        <div class="form-panel">
            <h1 class="form-title">TRANSMISSION D'ÉVALUATION</h1>
            <p class="form-subtitle">Votre avis aide l'Alliance à améliorer ses rations.</p>

            <!-- rappel de la commande concernee -->
            <div class="order-recall">
                <span class="recall-icon">📦</span>
                <div class="recall-info">
                    <span class="recall-label">Concerne la commande</span>
                    <span class="recall-id">#JDI-7742</span>
                    <span class="recall-status">✅ Livrée</span>
                </div>
            </div>

            <!-- formulaire de notation -->
            <form class="rating-form" action="#" method="post">

                <!-- critere 1 : qualite des vivres -->
                <fieldset class="rating-fieldset">
                    <legend class="rating-legend">
                        <span class="legend-icon">🍽️</span> Qualité des Vivres
                    </legend>
                    <p class="rating-hint">Évaluez la qualité des rations reçues.</p>
                    <div class="star-rating" id="stars-food">
                        <input type="radio" id="food-5" name="food-rating" value="5">
                        <label for="food-5" title="5 étoiles — Exceptionnel !">★</label>

                        <input type="radio" id="food-4" name="food-rating" value="4">
                        <label for="food-4" title="4 étoiles — Très bon">★</label>

                        <input type="radio" id="food-3" name="food-rating" value="3">
                        <label for="food-3" title="3 étoiles — Correct">★</label>

                        <input type="radio" id="food-2" name="food-rating" value="2">
                        <label for="food-2" title="2 étoiles — Moyen">★</label>

                        <input type="radio" id="food-1" name="food-rating" value="1">
                        <label for="food-1" title="1 étoile — Décevant">★</label>
                    </div>
                </fieldset>

                <!-- critere 2 : performance du droide livreur -->
                <fieldset class="rating-fieldset">
                    <legend class="rating-legend">
                        <span class="legend-icon">🤖</span> Performance du Droïde Livreur
                    </legend>
                    <p class="rating-hint">Évaluez la rapidité et l'efficacité de la livraison.</p>
                    <div class="star-rating" id="stars-delivery">
                        <input type="radio" id="delivery-5" name="delivery-rating" value="5">
                        <label for="delivery-5" title="5 étoiles — Exceptionnel !">★</label>

                        <input type="radio" id="delivery-4" name="delivery-rating" value="4">
                        <label for="delivery-4" title="4 étoiles — Très bon">★</label>

                        <input type="radio" id="delivery-3" name="delivery-rating" value="3">
                        <label for="delivery-3" title="3 étoiles — Correct">★</label>

                        <input type="radio" id="delivery-2" name="delivery-rating" value="2">
                        <label for="delivery-2" title="2 étoiles — Moyen">★</label>

                        <input type="radio" id="delivery-1" name="delivery-rating" value="1">
                        <label for="delivery-1" title="1 étoile — Décevant">★</label>
                    </div>
                </fieldset>

                <!-- critere 3 : commentaire libre -->
                <fieldset class="rating-fieldset">
                    <legend class="rating-legend">
                        <span class="legend-icon">📝</span> Commentaire de Transmission
                    </legend>
                    <p class="rating-hint">Ajoutez des détails à votre rapport.</p>
                    <textarea class="comment-area" name="comment" rows="6"
                        placeholder="Rapport d'incident ou félicitations..."></textarea>
                </fieldset>

                <!-- bouton d'envoi -->
                <button type="submit" class="btn-submit btn-neon">
                    <span class="btn-text">⚡ ENVOYER LE RAPPORT</span>
                </button>

            </form>
        </div>

    </div>
</main>

<?php require_once 'includes/footer.php'; ?>