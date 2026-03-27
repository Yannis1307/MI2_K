<?php
// variables de configuration pour le header
$page_title = 'Connexion';
$page_css = 'connexion.css';
$page_id = 'connexion';

// on charge les fonctions utilitaires json
require_once 'includes/functions.php';

// on inclut le header commun
require_once 'includes/header.php';
?>

<main>
    <!-- formulaire de connexion -->
    <div class="form-container">
        <div class="form-panel">
            <h1 class="form-title">IDENTIFICATION</h1>
            <p class="form-subtitle">Accédez au réseau de la Résistance</p>

            <form class="auth-form">
                <div class="form-group">
                    <label for="identifiant">Identifiant</label>
                    <input type="text" id="identifiant" name="identifiant" placeholder="Entrez votre nom de code"
                        required>
                </div>

                <div class="form-group">
                    <label for="password">Mot de passe</label>
                    <input type="password" id="password" name="password" placeholder="Code d'accès sécurisé" required>
                </div>

                <button type="submit" class="btn-submit btn-yellow">OUVRIR LE SAS</button>
            </form>

            <!-- lien vers inscription -->
            <div class="form-footer">
                <p>Pas encore de compte ? <a href="inscription.php" class="link-highlight">S'enrôler</a></p>
            </div>
        </div>
    </div>
</main>

<?php require_once 'includes/footer.php'; ?>