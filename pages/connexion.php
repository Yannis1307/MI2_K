<?php
// variables de configuration pour le header
$page_title = 'Connexion';
$page_css = 'connexion.css';
$page_id = 'connexion';

// on charge les fonctions utilitaires json
require_once 'includes/functions.php';

// variable pour stocker le message d'erreur
$erreur = '';

// traitement du formulaire quand il est soumis en post
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $login = isset($_POST['identifiant']) ? $_POST['identifiant'] : '';
    $password = isset($_POST['password']) ? $_POST['password'] : '';

    // on lit la liste des utilisateurs
    $users = read_json('users.json');
    $user_trouve = null;

    // on cherche un utilisateur qui correspond
    foreach ($users as $user) {
        if ($user['login'] === $login && password_verify($password, $user['password'])) {
            $user_trouve = $user;
            break;
        }
    }

    if ($user_trouve) {
        // on verifie que l'utilisateur n'est pas banni
        if ($user_trouve['statut'] === 'banni') {
            $erreur = 'Votre compte a été banni de la galaxie.';
        } else {
            // on met a jour la date de derniere connexion dans users.json
            foreach ($users as &$u) {
                if ($u['id'] === $user_trouve['id']) {
                    $u['derniere_connexion'] = date('d/m/Y');
                    break;
                }
            }
            unset($u);
            write_json('users.json', $users);

            // on stocke les infos en session
            $_SESSION['user'] = [
                'id' => $user_trouve['id'],
                'login' => $user_trouve['login'],
                'role' => $user_trouve['role'],
                'nom' => $user_trouve['nom'],
                'prenom' => $user_trouve['prenom']
            ];

            // redirection selon le role
            if ($user_trouve['role'] === 'admin') {
                header('Location: admin.php');
                exit;
            } elseif ($user_trouve['role'] === 'restaurateur') {
                header('Location: commandes.php');
                exit;
            } elseif ($user_trouve['role'] === 'livreur') {
                header('Location: livraison.php');
                exit;
            } else {
                header('Location: accueil.php');
                exit;
            }
        }
    } else {
        $erreur = 'Identifiant ou mot de passe incorrect.';
    }
}

// on inclut le header commun
require_once 'includes/header.php';
?>

    <main>
        <!-- formulaire de connexion -->
        <div class="form-container">
            <div class="form-panel">
                <h1 class="form-title">IDENTIFICATION</h1>
                <p class="form-subtitle">Accédez au réseau de la Résistance</p>

                <!-- message d'erreur si identifiants incorrects -->
                <?php if (!empty($erreur)) : ?>
                <p style="color: #ff4444; text-align: center; margin-bottom: 15px;"><?php echo $erreur; ?></p>
                <?php endif; ?>

                <?php
                if (isset($_SESSION['success_message'])) {
                    echo '<p style="color: #00ff88; text-align: center; margin-bottom: 15px;">' . htmlspecialchars($_SESSION['success_message']) . '</p>';
                    unset($_SESSION['success_message']);
                }
                ?>

                <form class="auth-form" method="POST" action="">
                    <div class="form-group" style="position: relative;">
                        <label for="identifiant">Identifiant</label>
                        <input type="text" id="identifiant" name="identifiant" placeholder="Entrez votre nom de code" maxlength="30" required>
                        <small id="identifiant-counter" style="position: absolute; right: 0; bottom: -20px; color: rgba(255,255,255,0.5); font-size: 0.8em;">0/30</small>
                    </div>

                    <div class="form-group" style="position: relative;">
                        <label for="password">Mot de passe</label>
                        <div style="position: relative; display: flex; align-items: center;">
                            <input type="password" id="password" name="password" placeholder="Code d'accès sécurisé" style="width: 100%; padding-right: 40px;" required>
                            <span id="toggle-password" style="position: absolute; right: 10px; cursor: pointer; color: rgba(255,255,255,0.7); font-size: 1.2em;" title="Afficher/Masquer">👁️</span>
                        </div>
                    </div>

                    <button type="submit" class="btn-submit btn-yellow">OUVRIR LE SAS</button>
                </form>

                <!-- lien vers inscription -->
                <div class="form-footer">
                    <p>Pas encore de compte ? <a href="inscription.php" class="link-highlight">S'enrôler</a></p>
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

            // Character Counter
            const identifiantField = document.getElementById('identifiant');
            const identifiantCounter = document.getElementById('identifiant-counter');
            
            identifiantField.addEventListener('input', function() {
                identifiantCounter.textContent = this.value.length + '/30';
            });

            // Client-side Validation
            const form = document.querySelector('.auth-form');
            form.addEventListener('submit', function(e) {
                // remove existing js errors
                const existingError = document.getElementById('js-error');
                if (existingError) existingError.remove();

                const identifiant = identifiantField.value.trim();
                const password = passwordField.value;
                let errorMsg = '';

                if (identifiant.length < 3) {
                    errorMsg = 'L\'identifiant doit faire au moins 3 caractères.';
                } else if (password.length === 0) {
                    errorMsg = 'Veuillez saisir votre mot de passe.';
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