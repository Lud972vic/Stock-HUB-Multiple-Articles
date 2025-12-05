# StockHUB

Application de gestion de stock multi-magasins.

## Fonctionnalités

*   Gestion des fournisseurs, matériels, magasins et mouvements de stock.
*   Suivi en temps réel des stocks par magasin.
*   Recherche universelle multi-colonnes sur toutes les listes.
*   Filtres avancés pour affiner les résultats.

## Recherche et Filtres

L'application dispose d'un système de recherche et de filtrage complet sur toutes les pages de liste :

*   Utilisez le champ de recherche en haut de chaque liste pour filtrer les résultats.
*   La recherche est **insensible à la casse**.
*   Elle s'applique à **plusieurs colonnes** simultanément (ex: Code, Nom, Ville, Fournisseur, etc.).
*   Tapez simplement une partie du mot recherché (recherche partielle type `%valeur%`).

## Installation

1.  Cloner le dépôt.
2.  Configurer la base de données dans `.env`.
3.  Lancer `composer install`.
4.  Exécuter les migrations : `php bin/console doctrine:migrations:migrate`.
5.  Démarrer le serveur : `symfony server`.
