<?php
// fonctions utilitaires pour le projet

// lit un fichier json depuis le dossier data/ et le retourne en tableau php
function read_json($filename)
{
    $path = __DIR__ . '/../../data/' . $filename;
    $json = file_get_contents($path);
    return json_decode($json, true);
}
