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

// on recupere l'id de la commande passé en GET
$id_commande = isset($_GET['id']) ? trim($_GET['id']) : '';

if (empty($id_commande)) {
    // pas d'id fourni : on renvoie vers le profil
    header('Location: profil.php');
    exit;
}

// on charge les commandes JSON et on cherche la commande concernée
$commandes  = read_json('commandes.json');
$commande   = null;
$index_cmd  = null;

foreach ($commandes as $index => $cmd) {
    if ($cmd['id'] === $id_commande) {
        $commande  = $cmd;
        $index_cmd = $index;
        break;
    }
}

// securite : la commande doit exister, appartenir au client connecte et être livrée
if (
    !$commande
    || $commande['id_client'] != $_SESSION['user']['id']
    || ($commande['statut'] !== 'livré' && $commande['statut'] !== 'livre')
) {
    $_SESSION['flash_error'] = 'Cette commande ne peut pas être notée (elle n\'existe pas, ne vous appartient pas, ou n\'est pas encore livrée).';
    header('Location: profil.php');
    exit;
}

// securite : si deja notee, on redirige avec message
if (!empty($commande['note_qualite']) || !empty($commande['note_livraison'])) {
    $_SESSION['flash_error'] = 'Vous avez déjà noté cette commande.';
    header('Location: profil.php');
    exit;
}

// =============================================
// TRAITEMENT DU FORMULAIRE (POST)
// =============================================
$erreur_form = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $note_qualite   = intval(isset($_POST['food-rating'])     ? $_POST['food-rating']     : 0);
    $note_livraison = intval(isset($_POST['delivery-rating']) ? $_POST['delivery-rating'] : 0);
    $commentaire    = isset($_POST['comment']) ? trim(htmlspecialchars($_POST['comment'])) : '';

    // validation des notes
    if ($note_qualite < 1 || $note_qualite > 5 || $note_livraison < 1 || $note_livraison > 5) {
        $erreur_form = 'Veuillez attribuer une note pour chaque critère (1 à 5 étoiles).';
    } else {
        // on enregistre la notation dans commandes.json
        $commandes[$index_cmd]['note_qualite']      = $note_qualite;
        $commandes[$index_cmd]['note_livraison']    = $note_livraison;
        $commandes[$index_cmd]['commentaire']       = $commentaire;
        $commandes[$index_cmd]['date_notation']     = date('d/m/Y');

        write_json('commandes.json', $commandes);

        $_SESSION['flash_success'] = 'Merci ! Votre évaluation de la commande #' . $id_commande . ' a bien été enregistrée.';
        header('Location: profil.php');
        exit;
    }
}

// on construit le résumé des plats pour l'affichage
$detail_plats = [];
foreach ($commande['plats'] as $p) {
    $detail_plats[] = $p['quantite'] . 'x ' . $p['nom'];
}

// on inclut le header commun (apres tout le PHP)
require_once 'includes/header.php';
?>

<main>
    <div class="form-container">
        <div class="form-panel">

            <!-- titre et sous-titre -->
            <h1 class="form-title">TRANSMISSION D'ÉVALUATION</h1>
            <p class="form-subtitle">Votre avis aide l'Alliance à améliorer ses rations.</p>

            <?php if (!empty($erreur_form)) : ?>
            <div style="background: rgba(255,50,50,0.15); border: 1px solid rgba(255,50,50,0.4); padding: 14px; border-radius: 10px; text-align: center; color: #ff8080; margin-bottom: 20px;">
                ❌ <?= $erreur_form ?>
            </div>
            <?php endif; ?>

            <!-- rappel dynamique de la commande concernée -->
            <div class="order-recall">
                <span class="recall-icon">📦</span>
                <div class="recall-info">
                    <span class="recall-label">Concerne la commande</span>
                    <span class="recall-id">#<?= htmlspecialchars($commande['id']) ?></span>
                    <span class="recall-status">📅 <?= htmlspecialchars($commande['date']) ?></span>
                    <span class="recall-status" style="font-size:0.8em; color: rgba(255,255,255,0.5);">
                        <?= htmlspecialchars(implode(', ', $detail_plats)) ?>
                    </span>
                    <span class="recall-status" style="color: #7fff7f;">✅ Livrée — <?= number_format($commande['total'], 2, ',', '') ?> ₹</span>
                </div>
            </div>

            <!-- formulaire de notation fonctionnel -->
            <form class="rating-form" action="notation.php?id=<?= urlencode($id_commande) ?>" method="POST">

                <!-- critere 1 : qualite des vivres -->
                <fieldset class="rating-fieldset">
                    <legend class="rating-legend">
                        <span class="legend-icon">🍽️</span> Qualité des Vivres
                    </legend>
                    <p class="rating-hint">Évaluez la qualité des rations reçues.</p>
                    <div class="star-rating" id="stars-food">
                        <input type="radio" id="food-5" name="food-rating" value="5" <?= (isset($_POST['food-rating']) && $_POST['food-rating'] == 5) ? 'checked' : '' ?> required>
                        <label for="food-5" title="5 étoiles — Exceptionnel !">★</label>

                        <input type="radio" id="food-4" name="food-rating" value="4" <?= (isset($_POST['food-rating']) && $_POST['food-rating'] == 4) ? 'checked' : '' ?>>
                        <label for="food-4" title="4 étoiles — Très bon">★</label>

                        <input type="radio" id="food-3" name="food-rating" value="3" <?= (isset($_POST['food-rating']) && $_POST['food-rating'] == 3) ? 'checked' : '' ?>>
                        <label for="food-3" title="3 étoiles — Correct">★</label>

                        <input type="radio" id="food-2" name="food-rating" value="2" <?= (isset($_POST['food-rating']) && $_POST['food-rating'] == 2) ? 'checked' : '' ?>>
                        <label for="food-2" title="2 étoiles — Moyen">★</label>

                        <input type="radio" id="food-1" name="food-rating" value="1" <?= (isset($_POST['food-rating']) && $_POST['food-rating'] == 1) ? 'checked' : '' ?>>
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
                        <input type="radio" id="delivery-5" name="delivery-rating" value="5" <?= (isset($_POST['delivery-rating']) && $_POST['delivery-rating'] == 5) ? 'checked' : '' ?> required>
                        <label for="delivery-5" title="5 étoiles — Exceptionnel !">★</label>

                        <input type="radio" id="delivery-4" name="delivery-rating" value="4" <?= (isset($_POST['delivery-rating']) && $_POST['delivery-rating'] == 4) ? 'checked' : '' ?>>
                        <label for="delivery-4" title="4 étoiles — Très bon">★</label>

                        <input type="radio" id="delivery-3" name="delivery-rating" value="3" <?= (isset($_POST['delivery-rating']) && $_POST['delivery-rating'] == 3) ? 'checked' : '' ?>>
                        <label for="delivery-3" title="3 étoiles — Correct">★</label>

                        <input type="radio" id="delivery-2" name="delivery-rating" value="2" <?= (isset($_POST['delivery-rating']) && $_POST['delivery-rating'] == 2) ? 'checked' : '' ?>>
                        <label for="delivery-2" title="2 étoiles — Moyen">★</label>

                        <input type="radio" id="delivery-1" name="delivery-rating" value="1" <?= (isset($_POST['delivery-rating']) && $_POST['delivery-rating'] == 1) ? 'checked' : '' ?>>
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
                        placeholder="Rapport d'incident ou félicitations..."><?= isset($_POST['comment']) ? htmlspecialchars($_POST['comment']) : '' ?></textarea>
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