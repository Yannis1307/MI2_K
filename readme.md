# 🌌 La Table des Jedi - Projet Creative-Yumland

Bienvenue sur le dépôt du projet **La Table des Jedi** (Phase 2 - PHP). 
Ce projet a été réalisé dans le cadre de la filière préING2 (2025-2026).

## 🎯 Description  
Application web dynamique simulant une plateforme de restauration dans l'univers Star Wars.  
L'interface s'adapte à 4 profils distincts : **Client, Restaurateur, Livreur et Administrateur**.

### 🌟 Fonctionnalités (Phase 2)
* **Système d'authentification** via sessions PHP.
* **Base de données simulée** via la manipulation de fichiers internes `.json`.
* **Tunnel d'achat dynamique** avec ajout de plats et menus au panier, saisie d'adresse et choix du retrait.
* **Interaction API Bancaire** (redirection de paiement MD5 vers *CYBank*).
* **Tableaux de bord dédiés** : Suivi des commandes, missions de livraison et profil client.
* **Code au style "étudiant"** : Norme pédagogique restrictive et simplification globale (ni opérateurs complexes, ni syntaxe de commentaires décoratifs).

## 🚀 Comment lancer le projet ?
Cette Phase requiert un environnement serveur local supportant PHP (ex: **XAMPP**, **WAMP**).

1. Placez ce projet dans le répertoire de votre serveur local (ex: `C:\xampp\htdocs\MI2_K-main`).
2. Démarrez le serveur Web (ex: **Apache**) depuis votre interface XAMPP/WAMP.
3. Ouvrez un navigateur sur : `http://localhost/MI2_K-main/pages/accueil.php`
4. Connectez-vous (voir le fichier `data/users.json` pour obtenir la liste des identifiants existants).
