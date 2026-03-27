<?php
// demarrage de la session (si pas deja fait par une page metier)
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// fonctions utilitaires pour le projet

// lit un fichier json depuis le dossier data/ et le retourne en tableau php
function read_json($filename)
{
    $path = __DIR__ . '/../../data/' . $filename;
    $json = file_get_contents($path);
    return json_decode($json, true);
}

// ecrit un tableau php dans un fichier json du dossier data/
function write_json($filename, $data)
{
    $path = __DIR__ . '/../../data/' . $filename;
    $json = json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
    file_put_contents($path, $json);
}
