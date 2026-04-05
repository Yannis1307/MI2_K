<?php
// demarrage de la session (si pas deja fait par une page metier)
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// fonctions utilitaires pour le projet

// lit un fichier json depuis le dossier data/ et le retourne en tableau php
function read_json($filename)
{
    $path = '../data/' . $filename;
    $json = file_get_contents($path);
    return json_decode($json, true);
}

// ecrit un tableau php dans un fichier json du dossier data/
function write_json($filename, $data)
{
    $path = '../data/' . $filename;
    $json = json_encode($data, JSON_PRETTY_PRINT);
    file_put_contents($path, $json);
}
