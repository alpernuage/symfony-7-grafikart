# CHANGELOG

Ce fichier récapitule clairement les modifications apportées pendant la session en cours afin que vous puissiez « voir les modifs » rapidement.

Date: 2025-11-03

## 1) Sécurité: choix explicite de l'entry_point du firewall
Fichier: config/packages/security.yaml
- Ajout de `entry_point: App\Security\AppAuthenticator` dans le firewall `main` pour résoudre le conflit entre `form_login` et l’authenticator personnalisé.

Pourquoi: Symfony exige de choisir un entry_point quand plusieurs authenticators sont présents. Sans cela, accès protégé → erreur demandant de choisir l’entry_point.


## 2) Repository utilisateur: éviter l’exception NoResultException
Fichier: src/Repository/UserRepository.php
- Dans `findUserByEmailOrUsername()`, remplacement de `getSingleResult()` par `getOneOrNullResult()`.

Pourquoi: Quand aucun utilisateur ne correspond, `getSingleResult()` lançait une exception et provoquait une erreur 500. Avec `getOneOrNullResult()`, on retourne `null` et le flot d’authentification gère proprement « identifiants invalides ».


## 3) Authenticator: alignement des noms de champs du formulaire de login
Fichier: src/Security/AppAuthenticator.php
- Lecture des champs de formulaire en acceptant les conventions Symfony par défaut (`_username`, `_password`) ET les noms simples (`username`, `password`).
- Le CSRF et le remember-me restent inchangés.

Pourquoi: Le template de login envoie `_username` et `_password`. L’authenticator lisait auparavant `username` et `password`, recevant des valeurs vides → échec systématique.


## 4) Rappel sur le template de login (pour vérification)
Fichier: templates/security/login.html.twig
- Le formulaire utilise: `name="_username"`, `name="_password"`, `name="_csrf_token"`, et une case `name="_remember_me"`.
- Aucun changement nécessaire ici pendant cette session, mais c’est la référence côté vue.


## 5) Note sur la migration (information)
- Lors d’une tentative de migration, une erreur a été observée: colonne `is_verified` NOT NULL contenant des valeurs NULL dans la table `user`.
- Cette erreur ne vient pas des changements ci-dessus, mais de l’état des données/migration. Pour la corriger, vous pouvez soit:
  - Mettre une valeur par défaut non nulle (ex: `false`) et migrer en 2 temps (mise à jour des NULL existants puis contrainte NOT NULL), ou
  - Écrire une migration qui met à jour toutes les lignes NULL → `false` avant d’ajouter/faire respecter NOT NULL.


---

# Comment voir les modifications localement

## Via Git (ligne de commande)
- Voir ce qui a changé: `git status`
- Voir le diff contre le dernier commit: `git diff`
- Historique des commits: `git log --oneline --decorate --graph --all`
- Voir le contenu d’un commit précis: `git show <commit_sha>`
- Blame (qui a modifié quelle ligne): `git blame <chemin/vers/fichier>`

## Via PhpStorm
- Git > Log: sélectionnez un commit et cliquez sur "Show Diff"
- Clic droit sur un fichier > Git > Show History
- Clic droit sur un fichier > Git > Annotate (blame)
- Local History: Clic droit > Local History > Show History


# Détails rapides par fichier (copier-coller des chemins)
- config/packages/security.yaml
- src/Repository/UserRepository.php
- src/Security/AppAuthenticator.php
- templates/security/login.html.twig (à titre indicatif)

Si vous souhaitez, je peux créer un commit dédié regroupant ces changements, ou ouvrir une branche/PR pour revue visuelle encore plus claire.
