<?php
// pages/modifier_commande.php
$page_title = 'Modifier la Commande';
$page_css = 'panier.css'; // on reutilise le design du panier
$page_id = 'modifier_commande';

require_once 'includes/functions.php';

// controle d'acces
if (!isset($_SESSION['user'])) {
    header('Location: connexion.php');
    exit;
}

if (!isset($_GET['id'])) {
    header('Location: profil.php');
    exit;
}

$id_cmd = $_GET['id'];
$commandes = read_json('commandes.json');
$commande_index = -1;
$commande = null;

foreach ($commandes as $i => $cmd) {
    if ($cmd['id'] === $id_cmd && $cmd['id_client'] == $_SESSION['user']['id']) {
        $commande_index = $i;
        $commande = $cmd;
        break;
    }
}

// securite : on ne modifie que les commandes en attente
if (!$commande || $commande['statut'] !== 'en attente') {
    $_SESSION['flash_error'] = "Impossible de modifier cette commande.";
    header('Location: profil.php');
    exit;
}

// securite : on bloque si une modification est deja en cours de traitement
if (isset($_SESSION['modif_en_cours']) && $_SESSION['modif_en_cours']['id_commande'] === $id_cmd) {
    $_SESSION['flash_error'] = "Une modification est déjà en cours pour cette commande.";
    header('Location: profil.php');
    exit;
}

// on charge les plats et menus disponibles
$plats = read_json('plats.json');
$menus = read_json('menus.json');

// total initial = le prix paye a l'origine (avant toute modif)
$total_initial = isset($commande['total_initial']) ? $commande['total_initial'] : $commande['total'];

require_once 'includes/header.php';
?>

<main>
    <section class="dashboard-header">
        <h1>✏️ Modifier la Commande #<?= htmlspecialchars($id_cmd) ?></h1>
        <p class="subtitle">Ajustez les produits avant la préparation en cuisine.</p>
    </section>

    <div style="max-width: 900px; margin: 0 auto; display: flex; flex-direction: column; gap: 20px;">

        <!-- contenu actuel de la commande -->
        <section class="panel" id="panel-contenu">
            <div class="panel-header">
                <h2>Contenu Actuel</h2>
            </div>
            <div class="panel-body">
                <table style="width: 100%; border-collapse: collapse; text-align: left;" id="table-commande">
                    <thead>
                        <tr style="border-bottom: 1px solid rgba(255,255,255,0.2);">
                            <th style="padding: 10px;">Produit</th>
                            <th>Qté</th>
                            <th>Prix</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody id="tbody-commande">
                        <!-- rempli dynamiquement par le js -->
                    </tbody>
                </table>
                <p id="msg-panier-vide" style="text-align: center; color: rgba(255,255,255,0.4); padding: 20px; display: none;">
                    La commande est vide. Ajoutez des produits ci-dessous.
                </p>
            </div>
        </section>

        <!-- section pour ajouter un plat -->
        <section class="panel">
            <div class="panel-header">
                <h2>Ajouter un Produit</h2>
            </div>
            <div class="panel-body" style="display: flex; gap: 10px; align-items: center; flex-wrap: wrap;">
                <!-- correction des couleurs du menu pour la visibilite -->
                <select id="select-plat" class="select-modif">
                    <?php foreach ($plats as $p): ?>
                        <option value="<?= $p['id'] ?>" data-prix="<?= $p['prix'] ?>" data-nom="<?= htmlspecialchars($p['nom']) ?>"><?= htmlspecialchars($p['nom']) ?> (<?= $p['prix'] ?> ₹)</option>
                    <?php endforeach; ?>
                </select>
                <button type="button" id="btn-ajouter-plat" class="btn-submit btn-cyan" style="width: auto; padding: 10px 20px;">+ Ajouter</button>
            </div>
        </section>

        <!-- section pour ajouter un menu -->
        <?php if (!empty($menus)): ?>
        <section class="panel">
            <div class="panel-header">
                <h2>Ajouter un Menu</h2>
            </div>
            <div class="panel-body" style="display: flex; gap: 10px; align-items: center; flex-wrap: wrap;">
                <!-- correction des couleurs du menu pour la visibilite -->
                <select id="select-menu" class="select-modif">
                    <?php foreach ($menus as $m): ?>
                        <option value="<?= $m['id'] ?>" data-prix="<?= $m['prix_total'] ?>" data-nom="<?= htmlspecialchars($m['nom']) ?>"><?= htmlspecialchars($m['nom']) ?> (<?= $m['prix_total'] ?> ₹)</option>
                    <?php endforeach; ?>
                </select>
                <button type="button" id="btn-ajouter-menu" class="btn-submit btn-cyan" style="width: auto; padding: 10px 20px;">+ Ajouter</button>
            </div>
        </section>
        <?php endif; ?>

        <!-- recapitulatif et validation -->
        <section class="panel" style="text-align: right;" id="panel-recap">
            <div class="panel-body">
                <p style="font-size: 1.2em; margin-bottom: 10px;">Total initial : <strong><?= number_format($total_initial, 2) ?> ₹</strong></p>
                <p style="font-size: 1.4em; margin-bottom: 10px; color: #ffd700;" id="txt-nouveau-total">Nouveau Total : <strong>0.00 ₹</strong></p>
                <p id="txt-difference" style="margin-bottom: 20px;"></p>
                <button type="button" id="btn-valider" class="btn-submit btn-yellow" style="font-size: 1.2em;">Valider les modifications</button>
            </div>
        </section>
    </div>

    <!-- formulaire cache pour la redirection cybank (paiement de la difference) -->
    <form id="form-cybank-modif" method="POST" action="initier_paiement_modif.php" style="display: none;">
        <input type="hidden" name="id_commande" value="<?= htmlspecialchars($id_cmd) ?>">
        <input type="hidden" name="plats" id="hidden-plats" value="">
        <input type="hidden" name="menus" id="hidden-menus" value="">
        <input type="hidden" name="nouveau_total" id="hidden-nouveau-total" value="">
        <input type="hidden" name="total_initial" id="hidden-total-initial" value="<?= $total_initial ?>">
    </form>

    <!-- styles pour les menus deroulants lisibles -->
    <style>
        /* correction des couleurs du menu pour la visibilite */
        .select-modif {
            flex: 1;
            min-width: 200px;
            padding: 10px 14px;
            border-radius: 8px;
            background: rgba(0, 15, 35, 0.9);
            color: #e8e8e8;
            border: 1px solid rgba(248, 224, 66, 0.3);
            font-family: 'Rajdhani', 'Segoe UI', sans-serif;
            font-size: 1em;
            font-weight: 500;
            cursor: pointer;
            transition: border-color 0.3s ease, box-shadow 0.3s ease;
            -webkit-appearance: none;
            -moz-appearance: none;
            appearance: none;
            background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='12' height='12' fill='%23f8e042' viewBox='0 0 16 16'%3E%3Cpath d='M8 11L3 6h10z'/%3E%3C/svg%3E");
            background-repeat: no-repeat;
            background-position: right 12px center;
            padding-right: 36px;
        }

        .select-modif:hover {
            border-color: var(--gold);
            box-shadow: 0 0 10px rgba(248, 224, 66, 0.2);
        }

        .select-modif:focus {
            outline: none;
            border-color: var(--gold);
            box-shadow: 0 0 15px rgba(248, 224, 66, 0.3);
        }

        /* les options du dropdown doivent etre lisibles */
        .select-modif option {
            background: #0a0f1e;
            color: #e8e8e8;
            padding: 8px;
            font-size: 0.95em;
        }

        .select-modif option:checked {
            background: rgba(248, 224, 66, 0.2);
            color: #f8e042;
        }
    </style>

    <script>
    document.addEventListener('DOMContentLoaded', function() {

        // donnees de la commande chargees depuis le php
        var platsCommande = <?= json_encode(isset($commande['plats']) ? $commande['plats'] : []) ?>;
        var menusCommande = <?= json_encode(isset($commande['menus']) ? $commande['menus'] : []) ?>;
        var totalInitial = <?= $total_initial ?>;
        var idCommande = '<?= addslashes($id_cmd) ?>';

        // elements du dom
        var tbody = document.getElementById('tbody-commande');
        var txtTotal = document.getElementById('txt-nouveau-total');
        var txtDiff = document.getElementById('txt-difference');
        var btnValider = document.getElementById('btn-valider');
        var msgVide = document.getElementById('msg-panier-vide');

        // fonction pour calculer le total actuel
        function calculerTotal() {
            var total = 0;
            for (var i = 0; i < platsCommande.length; i++) {
                total += platsCommande[i].sous_total;
            }
            for (var j = 0; j < menusCommande.length; j++) {
                total += menusCommande[j].sous_total;
            }
            return Math.round(total * 100) / 100;
        }

        // fonction pour mettre a jour l'affichage du tableau et du total
        function majAffichage() {
            // vider le tableau
            tbody.innerHTML = '';

            // afficher les plats
            for (var i = 0; i < platsCommande.length; i++) {
                var p = platsCommande[i];
                var tr = document.createElement('tr');
                tr.innerHTML = '<td style="padding: 10px;">' + escapeHtml(p.nom) + '</td>' +
                    '<td>' + p.quantite + '</td>' +
                    '<td>' + p.sous_total.toFixed(2) + ' ₹</td>' +
                    '<td><button class="btn-edit btn-retirer-plat" data-index="' + i + '" style="color: #ff4444; border-color: #ff4444;">Retirer</button></td>';
                tbody.appendChild(tr);
            }

            // afficher les menus
            for (var j = 0; j < menusCommande.length; j++) {
                var m = menusCommande[j];
                var tr2 = document.createElement('tr');
                tr2.innerHTML = '<td style="padding: 10px;">' + escapeHtml(m.nom) + ' (Menu)</td>' +
                    '<td>' + m.quantite + '</td>' +
                    '<td>' + m.sous_total.toFixed(2) + ' ₹</td>' +
                    '<td><button class="btn-edit btn-retirer-menu" data-index="' + j + '" style="color: #ff4444; border-color: #ff4444;">Retirer</button></td>';
                tbody.appendChild(tr2);
            }

            // message si commande vide
            if (platsCommande.length === 0 && menusCommande.length === 0) {
                msgVide.style.display = 'block';
            } else {
                msgVide.style.display = 'none';
            }

            // maj du prix total a chaque clic
            var nouveauTotal = calculerTotal();
            txtTotal.innerHTML = 'Nouveau Total : <strong>' + nouveauTotal.toFixed(2) + ' ₹</strong>';

            // maj de la difference
            var diff = Math.round((nouveauTotal - totalInitial) * 100) / 100;
            if (diff > 0) {
                txtDiff.style.color = '#ffaa00';
                txtDiff.textContent = 'Différence de +' + diff.toFixed(2) + ' ₹ à payer via CYBank.';
                btnValider.textContent = '🔒 Payer le complément avec CYBank';
            } else if (diff < 0) {
                txtDiff.style.color = '#00ff88';
                txtDiff.textContent = 'Bon de réduction de ' + Math.abs(diff).toFixed(2) + ' ₹ généré.';
                btnValider.textContent = 'Valider les modifications';
            } else {
                txtDiff.style.color = 'rgba(255,255,255,0.5)';
                txtDiff.textContent = 'Aucune différence de prix.';
                btnValider.textContent = 'Valider les modifications';
            }

            // ecouteurs sur les boutons retirer (plats)
            var btnsRetirerPlat = document.querySelectorAll('.btn-retirer-plat');
            for (var k = 0; k < btnsRetirerPlat.length; k++) {
                btnsRetirerPlat[k].addEventListener('click', function() {
                    var idx = parseInt(this.getAttribute('data-index'));
                    retirerPlat(idx);
                });
            }

            // ecouteurs sur les boutons retirer (menus)
            var btnsRetirerMenu = document.querySelectorAll('.btn-retirer-menu');
            for (var l = 0; l < btnsRetirerMenu.length; l++) {
                btnsRetirerMenu[l].addEventListener('click', function() {
                    var idx = parseInt(this.getAttribute('data-index'));
                    retirerMenu(idx);
                });
            }
        }

        // fonction pour echapper le html
        function escapeHtml(text) {
            var div = document.createElement('div');
            div.textContent = text;
            return div.innerHTML;
        }

        // retirer un plat de la commande
        function retirerPlat(index) {
            if (platsCommande[index].quantite > 1) {
                // on decremente la quantite
                platsCommande[index].quantite -= 1;
                platsCommande[index].sous_total = platsCommande[index].quantite * platsCommande[index].prix_unitaire;
            } else {
                // on retire completement le plat
                platsCommande.splice(index, 1);
            }
            // maj de l'affichage apres retrait
            majAffichage();
        }

        // retirer un menu de la commande
        function retirerMenu(index) {
            if (menusCommande[index].quantite > 1) {
                menusCommande[index].quantite -= 1;
                menusCommande[index].sous_total = menusCommande[index].quantite * menusCommande[index].prix_unitaire;
            } else {
                menusCommande.splice(index, 1);
            }
            // maj de l'affichage apres retrait
            majAffichage();
        }

        // ajouter un plat depuis le select
        document.getElementById('btn-ajouter-plat').addEventListener('click', function() {
            var select = document.getElementById('select-plat');
            var option = select.options[select.selectedIndex];
            var idPlat = parseInt(select.value);
            var nomPlat = option.getAttribute('data-nom');
            var prixPlat = parseFloat(option.getAttribute('data-prix'));

            // on cherche si le plat est deja dans la commande
            var found = false;
            for (var i = 0; i < platsCommande.length; i++) {
                if (platsCommande[i].id_plat == idPlat) {
                    // on incremente la quantite
                    platsCommande[i].quantite += 1;
                    platsCommande[i].sous_total = platsCommande[i].quantite * platsCommande[i].prix_unitaire;
                    found = true;
                    break;
                }
            }

            // sinon on ajoute une nouvelle ligne
            if (!found) {
                platsCommande.push({
                    id_plat: idPlat,
                    nom: nomPlat,
                    quantite: 1,
                    prix_unitaire: prixPlat,
                    sous_total: prixPlat
                });
            }

            // maj de l'affichage apres ajout
            majAffichage();
        });

        // ajouter un menu depuis le select
        var btnAjouterMenu = document.getElementById('btn-ajouter-menu');
        if (btnAjouterMenu) {
            btnAjouterMenu.addEventListener('click', function() {
                var select = document.getElementById('select-menu');
                var option = select.options[select.selectedIndex];
                var idMenu = parseInt(select.value);
                var nomMenu = option.getAttribute('data-nom');
                var prixMenu = parseFloat(option.getAttribute('data-prix'));

                // on cherche si le menu est deja dans la commande
                var found = false;
                for (var i = 0; i < menusCommande.length; i++) {
                    if (menusCommande[i].id_menu == idMenu) {
                        menusCommande[i].quantite += 1;
                        menusCommande[i].sous_total = menusCommande[i].quantite * menusCommande[i].prix_unitaire;
                        found = true;
                        break;
                    }
                }

                if (!found) {
                    menusCommande.push({
                        id_menu: idMenu,
                        nom: nomMenu,
                        quantite: 1,
                        prix_unitaire: prixMenu,
                        sous_total: prixMenu
                    });
                }

                // maj de l'affichage apres ajout
                majAffichage();
            });
        }

        // validation finale au clic sur le bouton
        btnValider.addEventListener('click', function() {
            // on desactive le bouton pour eviter le spam
            this.disabled = true;
            this.textContent = 'Traitement en cours...';

            var nouveauTotal = calculerTotal();
            var diff = Math.round((nouveauTotal - totalInitial) * 100) / 100;

            // verification que la commande n'est pas vide
            if (platsCommande.length === 0 && menusCommande.length === 0) {
                alert('La commande ne peut pas être vide.');
                this.disabled = false;
                this.textContent = 'Valider les modifications';
                return;
            }

            // appel obligatoire a l'api cy bank pour le surplus
            if (diff > 0) {
                // on remplit le formulaire cache et on redirige vers cybank
                document.getElementById('hidden-plats').value = JSON.stringify(platsCommande);
                document.getElementById('hidden-menus').value = JSON.stringify(menusCommande);
                document.getElementById('hidden-nouveau-total').value = nouveauTotal.toFixed(2);
                document.getElementById('form-cybank-modif').submit();
                return;
            }

            // si prix egal ou inferieur on envoie directement en async
            envoyerModification();
        });

        // envoi de la modification au serveur via fetch (cas prix egal ou inferieur)
        function envoyerModification() {
            var nouveauTotal = calculerTotal();

            // on envoie les nouvelles donnees en async
            fetch('../api/modifier_commande.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({
                    id_commande: idCommande,
                    plats: platsCommande,
                    menus: menusCommande,
                    nouveau_total: nouveauTotal,
                    total_initial: totalInitial
                })
            })
            .then(function(response) { return response.json(); })
            .then(function(data) {
                if (data.success) {
                    // on rafraichit la page pour voir la commande modifiee
                    window.location.href = 'profil.php';
                } else {
                    alert('Erreur: ' + data.message);
                    btnValider.disabled = false;
                    btnValider.textContent = 'Valider les modifications';
                }
            })
            .catch(function(err) {
                alert('Erreur de connexion réseau.');
                console.error(err);
                btnValider.disabled = false;
                btnValider.textContent = 'Valider les modifications';
            });
        }

        // affichage initial
        majAffichage();
    });
    </script>
</main>

<?php require_once 'includes/footer.php'; ?>
