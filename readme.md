# 🌌 La Table des Jedi — Projet Creative-Yumland

**Bienvenue sur le dépôt du projet La Table des Jedi** (Phase 3 — JavaScript & Requêtes Asynchrones).
Ce projet a été réalisé dans le cadre de la filière **préING2 (2025-2026)**.

---

## 🎯 Description

Application web dynamique simulant une plateforme de restauration dans l'univers Star Wars.
L'interface s'adapte à **4 profils distincts** : **Client, Restaurateur, Livreur et Administrateur**.

---

## ⭐ Nouveautés Phase 3

- **Changement de charte graphique** : bouton mode clair/sombre (sans rechargement de page), préférence sauvegardée en cookie.
- **Validation côté client** : vérification de tous les formulaires en JavaScript (email, téléphone, date, mot de passe…) avec messages d'erreur dynamiques et affichage/masquage du mot de passe.
- **Compteur de caractères** en temps réel sur les champs limités (pseudo, email, mot de passe…).
- **Modification du profil** en connexion asynchrone (fetch/AJAX) — sans rechargement de page.
- **Filtres et tris asynchrones** sur la page produits (catégorie, régime alimentaire, saveur, prix…).
- **Modification de commande** avant préparation : ajout/suppression d'articles avec mise à jour du total en temps réel et paiement complémentaire si nécessaire.
- **Gestion des statuts de commande** par le restaurateur : `payée → en préparation → prête → assignée à un livreur`.
- **Blocage/déblocage d'utilisateur** par l'admin en asynchrone (déconnexion immédiate de la session si bloqué).
- **Confirmation de livraison** par le livreur.
- **Notation des commandes** livrées (une seule fois par commande).

---

## 🚀 Comment lancer le projet ?

Cette phase requiert un environnement serveur local supportant PHP (ex : **XAMPP**, **WAMP**).

1. Placez ce projet dans le répertoire de votre serveur local (ex : `C:\xampp\htdocs\MI2_K-main`).
2. Démarrez le serveur Web (**Apache**) depuis votre interface XAMPP/WAMP.
3. Ouvrez un navigateur sur : `http://localhost/MI2_K-main/pages/accueil.php`
4. Connectez-vous avec l'un des comptes ci-dessous.

---

## 🔑 Comptes de test

| Rôle | Login | Mot de passe |
|---|---|---|
| 👤 Client | `test` | `1234` |
| 🍽️ Restaurateur | `Dex_Diner` | `1234` |
| 🛡️ Administrateur | `Emp.Palpatine` | `1234` |
| 🚚 Livreur | `L.Skywalker` | `1234` |

> La liste complète des identifiants est également disponible dans `data/users.json`.

---

## 🗂️ Structure du projet

```
MI2_K-main/
├── pages/          → Vues PHP (accueil, produits, profil, commandes…)
├── scripts/        → Logique PHP (authentification, traitement des données…)
├── libs/           → Bibliothèques PHP réutilisables
├── js/             → Scripts JavaScript (validation, fetch, UI dynamique)
├── css/            → Feuilles de style (charte principale + charte alternative)
├── data/           → Fichiers JSON (users, plats, menus, commandes…)
└── assets/         → Images et ressources statiques
```

---
*Que la Force soit avec votre code.* 🌟
