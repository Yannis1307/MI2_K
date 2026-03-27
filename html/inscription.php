<?php
// variables de configuration pour le header
$page_title = 'Inscription';
$page_css = 'connexion.css';
$page_id = 'inscription';

// on charge les fonctions utilitaires json
require_once 'includes/functions.php';

// on inclut le header commun
require_once 'includes/header.php';
?>

<main>
    <!-- formulaire d'inscription -->
    <div class="form-container">
        <div class="form-panel">
            <h1 class="form-title">RECRUTEMENT</h1>
            <p class="form-subtitle">Rejoignez l'Alliance des Gourmets</p>

            <form class="auth-form">
                <div class="form-group">
                    <label for="nomcode">Nom de code</label>
                    <input type="text" id="nomcode" name="nomcode" placeholder="Votre identifiant galactique" required>
                </div>

                <div class="form-group">
                    <label for="email">Email</label>
                    <input type="email" id="email" name="email" placeholder="votre.adresse@holonet.gal" required>
                </div>

                <div class="form-group">
                    <label for="planete">Planète d'origine</label>
                    <select id="planete" name="planete" required>
                        <option value="">Sélectionnez votre monde</option>
                        <option value="tatooine">Tatooine</option>
                        <option value="coruscant">Coruscant</option>
                        <option value="naboo">Naboo</option>
                        <option value="hoth">Hoth</option>
                        <option value="endor">Endor</option>
                        <option value="mustafar">Mustafar</option>
                        <option value="kashyyyk">Kashyyyk</option>
                        <option value="dagobah">Dagobah</option>
                    </select>
                </div>

                <!-- choix du camp avec radio boutons css -->
                <div class="form-group">
                    <label>Allégeance</label>
                    <div class="radio-group">
                        <label class="radio-label">
                            <input type="radio" name="camp" value="jedi" checked>
                            <span class="radio-custom jedi">⚔️ Jedi</span>
                        </label>
                        <label class="radio-label">
                            <input type="radio" name="camp" value="sith">
                            <span class="radio-custom sith">🔴 Sith</span>
                        </label>
                    </div>
                </div>

                <div class="form-group">
                    <label for="password">Mot de passe</label>
                    <input type="password" id="password" name="password" placeholder="Code secret de sécurité" required>
                </div>

                <button type="submit" class="btn-submit btn-cyan">SIGNER L'ENGAGEMENT</button>
            </form>

            <!-- lien vers connexion -->
            <div class="form-footer">
                <p>Déjà membre ? <a href="connexion.php" class="link-highlight">S'identifier</a></p>
            </div>
        </div>
    </div>
</main>

<?php require_once 'includes/footer.php'; ?>