<?php
// variables de configuration pour le header
$page_title = 'Inscription';
$page_css = 'connexion.css';
$page_id = 'inscription';

// on charge les fonctions utilitaires json
require_once 'includes/functions.php';

// traitement du formulaire quand il est soumis en post
$erreur = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $login = isset($_POST['nomcode']) ? $_POST['nomcode'] : '';
    $password = isset($_POST['password']) ? $_POST['password'] : '';
    $email = isset($_POST['email']) ? $_POST['email'] : '';
    $planete = isset($_POST['planete']) ? $_POST['planete'] : '';
    $camp = isset($_POST['camp']) ? $_POST['camp'] : 'jedi';

    // on lit les utilisateurs existants
    $users = read_json('users.json');

    // on verifie si l'email existe deja
    $email_existe = false;
    foreach ($users as $u) {
        if (isset($u['email']) && $u['email'] === $email) {
            $email_existe = true;
            break;
        }
    }

    if ($email_existe) {
        $erreur = 'Cette adresse email est déjà enregistrée dans nos systèmes.';
    } else {
        // generation d'un ID unique numerique (max id + 1)
        $max_id = 0;
        foreach ($users as $u) {
            if (isset($u['id']) && (int)$u['id'] > $max_id) {
                $max_id = (int)$u['id'];
            }
        }
        $new_id = $max_id + 1;

        // creation du nouvel utilisateur
        $nouvel_utilisateur = [
            'id'                 => $new_id,
        'login'              => $login,
        'password'           => password_hash($password, PASSWORD_DEFAULT),
        'role'               => 'client',
        'nom'                => $login,
        'prenom'             => '',
        'email'              => $email,
        'telephone'          => '',
        'adresse'            => 'Planète ' . ucfirst($planete),
        'date_inscription'   => date('d/m/Y'), // ajout de la date du jour
        'derniere_connexion' => date('d/m/Y'),
        'points_fidelite'    => 0,
        'statut_premium'     => 'normal',
        'statut'             => 'actif',
        'camp'               => $camp,
        'avatar'             => 'han_avatar.png'
    ];

    // on ajoute le nouvel utilisateur a la liste
    $users[] = $nouvel_utilisateur;

        // on sauvegarde dans le fichier json
        write_json('users.json', $users);

        // on definit le message de succes en session
        $_SESSION['success_message'] = "Votre recrutement est confirmé. Vous pouvez maintenant vous identifier.";

        // on redirige vers la page de connexion
        header('Location: connexion.php');
        exit;
    }
}

// on inclut le header commun
require_once 'includes/header.php';
?>

    <main>
        <!-- formulaire d'inscription -->
        <div class="form-container">
            <div class="form-panel">
                <h1 class="form-title">RECRUTEMENT</h1>
                <p class="form-subtitle">Rejoignez l'Alliance des Gourmets</p>

                <!-- message d'erreur si inscription echoue -->
                <?php if (!empty($erreur)) : ?>
                <p style="color: #ff4444; text-align: center; margin-bottom: 15px;"><?php echo $erreur; ?></p>
                <?php endif; ?>

                <form class="auth-form" method="POST" action="">
                    <div class="form-group" style="position: relative;">
                        <label for="nomcode">Nom de code</label>
                        <input type="text" id="nomcode" name="nomcode" placeholder="Votre identifiant galactique" maxlength="30" required>
                        <small id="nomcode-counter" style="position: absolute; right: 0; bottom: -20px; color: rgba(255,255,255,0.5); font-size: 0.8em;">30 caractères restants</small>
                    </div>

                    <div class="form-group" style="position: relative; margin-top: 10px;">
                        <label for="email">Email</label>
                        <input type="email" id="email" name="email" placeholder="votre.adresse@holonet.gal" maxlength="50" required>
                        <small id="email-counter" style="position: absolute; right: 0; bottom: -20px; color: rgba(255,255,255,0.5); font-size: 0.8em;">50 caractères restants</small>
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

                    <div class="form-group" style="position: relative; margin-top: 10px;">
                        <label for="password">Mot de passe</label>
                        <div style="position: relative; display: flex; align-items: center;">
                            <input type="password" id="password" name="password" placeholder="Code secret de sécurité" maxlength="50" style="width: 100%; padding-right: 40px;" required>
                            <span id="toggle-password" style="position: absolute; right: 10px; cursor: pointer; color: rgba(255,255,255,0.7); font-size: 1.2em;" title="Afficher/Masquer">👁️</span>
                        </div>
                        <small id="password-counter" style="position: absolute; right: 0; bottom: -20px; color: rgba(255,255,255,0.5); font-size: 0.8em;">50 caractères restants</small>
                        
                        <div id="password-rules" style="margin-top: 25px; font-size: 0.8em; color: rgba(255,255,255,0.6); display: flex; flex-direction: column; gap: 4px; padding: 10px; background: rgba(0,0,0,0.3); border-radius: 8px;">
                            <span id="rule-length">❌ 8 caractères minimum</span>
                            <span id="rule-upper">❌ Au moins une majuscule</span>
                            <span id="rule-number">❌ Au moins un chiffre</span>
                            <span id="rule-special">❌ Au moins un caractère spécial</span>
                        </div>
                    </div>

                    <button type="submit" class="btn-submit btn-cyan">SIGNER L'ENGAGEMENT</button>
                </form>

                <!-- lien vers connexion -->
                <div class="form-footer">
                    <p>Déjà membre ? <a href="connexion.php" class="link-highlight">S'identifier</a></p>
                </div>
            </div>
        </div>

        <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Toggle Password Visibility
            const togglePassword = document.getElementById('toggle-password');
            const passwordField = document.getElementById('password');
            
            togglePassword.addEventListener('click', function() {
                const type = passwordField.getAttribute('type') === 'password' ? 'text' : 'password';
                passwordField.setAttribute('type', type);
                this.innerHTML = type === 'password' ? '👁️' : '🙈';
            });

            // Character Counters
            function setupCounter(inputId, counterId, max) {
                const input = document.getElementById(inputId);
                const counter = document.getElementById(counterId);
                if(input && counter) {
                    input.addEventListener('input', function() {
                        const remaining = max - this.value.length;
                        counter.textContent = remaining + ' caractères restants';
                    });
                }
            }
            
            setupCounter('nomcode', 'nomcode-counter', 30);
            setupCounter('email', 'email-counter', 50);
            setupCounter('password', 'password-counter', 50);

            // Password rules validation
            const ruleLength = document.getElementById('rule-length');
            const ruleUpper = document.getElementById('rule-upper');
            const ruleNumber = document.getElementById('rule-number');
            const ruleSpecial = document.getElementById('rule-special');

            passwordField.addEventListener('input', function() {
                const val = this.value;
                
                if (val.length >= 8) { ruleLength.innerHTML = '✅ 8 caractères minimum'; ruleLength.style.color = '#7fff7f'; }
                else { ruleLength.innerHTML = '❌ 8 caractères minimum'; ruleLength.style.color = ''; }

                if (/[A-Z]/.test(val)) { ruleUpper.innerHTML = '✅ Au moins une majuscule'; ruleUpper.style.color = '#7fff7f'; }
                else { ruleUpper.innerHTML = '❌ Au moins une majuscule'; ruleUpper.style.color = ''; }

                if (/[0-9]/.test(val)) { ruleNumber.innerHTML = '✅ Au moins un chiffre'; ruleNumber.style.color = '#7fff7f'; }
                else { ruleNumber.innerHTML = '❌ Au moins un chiffre'; ruleNumber.style.color = ''; }

                if (/[^A-Za-z0-9]/.test(val)) { ruleSpecial.innerHTML = '✅ Au moins un caractère spécial'; ruleSpecial.style.color = '#7fff7f'; }
                else { ruleSpecial.innerHTML = '❌ Au moins un caractère spécial'; ruleSpecial.style.color = ''; }
            });

            // Client-side Validation
            const form = document.querySelector('.auth-form');
            form.addEventListener('submit', function(e) {
                // remove existing js errors
                const existingError = document.getElementById('js-error');
                if (existingError) existingError.remove();

                const nomcode = document.getElementById('nomcode').value.trim();
                const email = document.getElementById('email').value.trim();
                const password = passwordField.value;
                let errorMsg = '';

                // Simple email regex
                const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;

                if (nomcode.length < 3) {
                    errorMsg = 'Le nom de code doit faire au moins 3 caractères.';
                } else if (!emailRegex.test(email)) {
                    errorMsg = 'L\'adresse email n\'est pas valide.';
                } else if (password.length < 8 || !/[A-Z]/.test(password) || !/[0-9]/.test(password) || !/[^A-Za-z0-9]/.test(password)) {
                    errorMsg = 'Le mot de passe ne respecte pas les règles de sécurité.';
                }

                if (errorMsg) {
                    e.preventDefault(); // Stop HTTP request
                    const p = document.createElement('p');
                    p.id = 'js-error';
                    p.style.color = '#ff4444';
                    p.style.textAlign = 'center';
                    p.style.marginBottom = '15px';
                    p.textContent = errorMsg;
                    form.parentNode.insertBefore(p, form);
                }
            });
        });
        </script>
    </main>

<?php require_once 'includes/footer.php'; ?>