<?php
// demarrage de la session
session_start();

// on detruit la session pour deconnecter l'utilisateur
session_unset();
session_destroy();

// on redirige vers la page d'accueil
header('Location: accueil.php');
exit;
