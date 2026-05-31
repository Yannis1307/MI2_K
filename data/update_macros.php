<?php
$file = 'c:/xampp/htdocs/MI2_K-main/data/plats.json';
$data = json_decode(file_get_contents($file), true);

foreach ($data as &$plat) {
    if ($plat['categorie'] === 'boissons') {
        $plat['calories'] = rand(100, 250);
        $plat['proteines'] = rand(0, 5);
        $plat['glucides'] = rand(20, 50);
        $plat['lipides'] = rand(0, 5);
    } elseif ($plat['categorie'] === 'plats' || $plat['categorie'] === 'specialites') {
        $plat['calories'] = rand(400, 800);
        $plat['proteines'] = rand(20, 50);
        $plat['glucides'] = rand(30, 80);
        $plat['lipides'] = rand(10, 30);
    } else {
        $plat['calories'] = rand(200, 400);
        $plat['proteines'] = rand(2, 10);
        $plat['glucides'] = rand(30, 60);
        $plat['lipides'] = rand(5, 20);
    }
}

file_put_contents($file, json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
echo "Macros updated!";
