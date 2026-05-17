<?php
// variables de configuration pour le header
$page_title = 'Donner mon avis';
$page_css = 'notation.css';
$page_id = 'notation';

// on charge les fonctions utilitaires json
require_once 'includes/functions.php';

// controle d'acces pour tous les profils connectes
if (!isset($_SESSION['user'])) {
    header('Location: connexion.php');
    exit;
}

// on recupere l'id de la commande passe en get (s'il existe)
$id_commande = isset($_GET['id']) ? trim($_GET['id']) : '';
$is_general_review = empty($id_commande);

$commande   = null;
$index_cmd  = null;
$commandes  = [];

if (!$is_general_review) {
    // Si c'est une notation de commande, il faut etre client
    if ($_SESSION['user']['role'] !== 'client') {
        $_SESSION['flash_error'] = 'Seuls les clients peuvent évaluer une commande spécifique.';
        header('Location: profil.php');
        exit;
    }

    $commandes = read_json('commandes.json');
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
}

// traitement du formulaire
$erreur_form = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if ($is_general_review) {
        $note_globale = intval(isset($_POST['global-rating']) ? $_POST['global-rating'] : 0);
        $commentaire  = isset($_POST['comment']) ? trim($_POST['comment']) : '';

        if ($note_globale < 1 || $note_globale > 5) {
            $erreur_form = 'Veuillez attribuer une note globale (1 à 5 étoiles).';
        } else {
            $avis = read_json('avis.json');
            if (!is_array($avis)) $avis = [];
            $avis[] = [
                'id_user'     => $_SESSION['user']['id'],
                'login_user'  => $_SESSION['user']['login'],
                'role_user'   => $_SESSION['user']['role'],
                'note'        => $note_globale,
                'commentaire' => $commentaire,
                'date'        => date('d/m/Y H:i')
            ];
            write_json('avis.json', $avis);

            $_SESSION['flash_success'] = 'Merci ! Votre avis général a bien été enregistré.';
            header('Location: profil.php');
            exit;
        }
    } else {
        $note_qualite   = intval(isset($_POST['food-rating'])     ? $_POST['food-rating']     : 0);
        $note_livraison = intval(isset($_POST['delivery-rating']) ? $_POST['delivery-rating'] : 0);
        $commentaire    = isset($_POST['comment']) ? trim($_POST['comment']) : '';

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
}

// on construit le résumé des plats pour l'affichage si c'est une commande
$detail_plats = [];
if (!$is_general_review && $commande) {
    foreach ($commande['plats'] as $p) {
        $detail_plats[] = $p['quantite'] . 'x ' . $p['nom'];
    }
}

// on inclut le header commun (apres tout le php)
require_once 'includes/header.php';
?>

<main>
    <div class="form-container">
        <div class="form-panel">

            <!-- titre et sous-titre -->
            <h1 class="form-title"><?= $is_general_review ? "AVIS GÉNÉRAL SUR LE RESTAURANT" : "TRANSMISSION D'ÉVALUATION" ?></h1>
            <p class="form-subtitle">
                <?= $is_general_review 
                    ? "Partagez votre expérience globale à la Table des Jedi." 
                    : "Votre avis aide l'Alliance à améliorer ses rations." ?>
            </p>

            <?php if (!empty($erreur_form)) : ?>
            <div style="background: rgba(255,50,50,0.15); border: 1px solid rgba(255,50,50,0.4); padding: 14px; border-radius: 10px; text-align: center; color: #ff8080; margin-bottom: 20px;">
                ❌ <?= $erreur_form ?>
            </div>
            <?php endif; ?>

            <?php if (!$is_general_review) : ?>
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
            <?php endif; ?>

            <!-- formulaire de notation fonctionnel -->
            <form class="rating-form" action="notation.php<?= !$is_general_review ? '?id=' . urlencode($id_commande) : '' ?>" method="POST">

                <?php if ($is_general_review) : ?>
                <!-- critere unique : avis général -->
                <fieldset class="rating-fieldset">
                    <legend class="rating-legend">
                        <span class="legend-icon">⭐</span> Note Globale
                    </legend>
                    <p class="rating-hint">Évaluez votre expérience au sein du restaurant.</p>
                    <div class="star-rating" id="stars-global">
                        <input type="radio" id="global-5" name="global-rating" value="5" <?= (isset($_POST['global-rating']) && $_POST['global-rating'] == 5) ? 'checked' : '' ?> required>
                        <label for="global-5" title="5 étoiles — Exceptionnel !">★</label>

                        <input type="radio" id="global-4" name="global-rating" value="4" <?= (isset($_POST['global-rating']) && $_POST['global-rating'] == 4) ? 'checked' : '' ?>>
                        <label for="global-4" title="4 étoiles — Très bon">★</label>

                        <input type="radio" id="global-3" name="global-rating" value="3" <?= (isset($_POST['global-rating']) && $_POST['global-rating'] == 3) ? 'checked' : '' ?>>
                        <label for="global-3" title="3 étoiles — Correct">★</label>

                        <input type="radio" id="global-2" name="global-rating" value="2" <?= (isset($_POST['global-rating']) && $_POST['global-rating'] == 2) ? 'checked' : '' ?>>
                        <label for="global-2" title="2 étoiles — Moyen">★</label>

                        <input type="radio" id="global-1" name="global-rating" value="1" <?= (isset($_POST['global-rating']) && $_POST['global-rating'] == 1) ? 'checked' : '' ?>>
                        <label for="global-1" title="1 étoile — Décevant">★</label>
                    </div>
                </fieldset>
                <?php else : ?>
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
                <?php endif; ?>

                <!-- critere 3 : commentaire libre -->
                <fieldset class="rating-fieldset">
                    <legend class="rating-legend">
                        <span class="legend-icon">📝</span> <?= $is_general_review ? "Votre Avis" : "Commentaire de Transmission" ?>
                    </legend>
                    <p class="rating-hint">Ajoutez des détails à votre <?= $is_general_review ? "avis" : "rapport" ?>.</p>
                    <textarea class="comment-area" name="comment" rows="6"
                        placeholder="<?= $is_general_review ? 'Partagez vos impressions...' : 'Rapport d\'incident ou félicitations...' ?>"><?= isset($_POST['comment']) ? htmlspecialchars($_POST['comment']) : '' ?></textarea>
                </fieldset>

                <!-- zone d'erreur js (invisible par defaut) -->
                <div id="js-notation-error" style="display:none; background: rgba(255,50,50,0.15); border: 1px solid rgba(255,50,50,0.4); padding: 12px 16px; border-radius: 10px; color: #ff8080; text-align: center; margin-bottom: 10px;"></div>

                <!-- bouton d'envoi -->
                <button type="submit" class="btn-submit btn-neon">
                    <span class="btn-text">⚡ ENVOYER <?= $is_general_review ? "MON AVIS" : "LE RAPPORT" ?></span>
                </button>

            </form>
        </div>
    </div>
</main>

<!-- validation js du formulaire de notation cote client -->
<script>
document.addEventListener('DOMContentLoaded', function() {
    var form = document.querySelector('.rating-form');
    var errorBox = document.getElementById('js-notation-error');
    var isGeneralReview = <?= $is_general_review ? 'true' : 'false' ?>;

    if (form) {
        form.addEventListener('submit', function(e) {
            // on remet l'erreur a zero
            errorBox.style.display = 'none';
            errorBox.textContent = '';

            var message = '';

            if (isGeneralReview) {
                var noteGlobale = form.querySelector('input[name="global-rating"]:checked');
                if (!noteGlobale) {
                    message = '⚠️ Veuillez attribuer une note globale.';
                }
            } else {
                // verification que la note qualite est selectionnee
                var noteQualite = form.querySelector('input[name="food-rating"]:checked');
                // verification que la note livraison est selectionnee
                var noteLivraison = form.querySelector('input[name="delivery-rating"]:checked');

                if (!noteQualite && !noteLivraison) {
                    message = '⚠️ Veuillez attribuer une note pour la qualité des vivres et pour la livraison.';
                } else if (!noteQualite) {
                    message = '⚠️ Veuillez attribuer une note pour la qualité des vivres.';
                } else if (!noteLivraison) {
                    message = '⚠️ Veuillez attribuer une note pour la livraison.';
                }
            }

            // si une note manque, on bloque l'envoi et on affiche l'erreur
            if (message) {
                e.preventDefault();
                errorBox.textContent = message;
                errorBox.style.display = 'block';
                // on scroll vers le message d'erreur
                errorBox.scrollIntoView({ behavior: 'smooth', block: 'center' });
            }
        });
    }
});
</script>

<?php require_once 'includes/footer.php'; ?>