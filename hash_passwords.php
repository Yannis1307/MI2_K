<?php
require_once 'pages/includes/functions.php';

$users = read_json('users.json');
$updated = false;

foreach ($users as &$user) {
    // Check if the password is NOT already a bcrypt hash (starts with $2y$)
    if (isset($user['password']) && strpos($user['password'], '$2y$') !== 0) {
        $user['password'] = password_hash($user['password'], PASSWORD_DEFAULT);
        $updated = true;
    }
}
unset($user);

if ($updated) {
    write_json('users.json', $users);
    echo "Les mots de passe ont ete haches avec succes.\n";
} else {
    echo "Aucun mot de passe a hacher.\n";
}
