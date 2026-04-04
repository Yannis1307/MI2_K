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
        if ($user['login'] === $login && $user['password'] === $password) {
            $user_trouve = $user;
            break;
        }
    }

    if ($user_trouve) {
        // on verifie que l'utilisateur n'est pas banni
        if ($user_trouve['statut'] === 'banni') {
            $erreur = 'Votre compte a été banni de la galaxie.';
        } else {
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

                <form class="auth-form" method="POST" action="">
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