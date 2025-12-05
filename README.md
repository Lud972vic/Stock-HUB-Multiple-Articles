# StockHUB

Application de gestion de stock multi-magasins.
<img width="2145" height="1482" alt="Capture d’écran 2025-12-05 à 12 57 09" src="https://github.com/user-attachments/assets/b42ade76-6048-4387-a187-79e350be6a87" />
<img width="2145" height="1482" alt="Capture d’écran 2025-12-05 à 12 57 05" src="https://github.com/user-attachments/assets/49ed6818-c65c-400f-8cd5-365f54190b6c" />
<img width="2145" height="1482" alt="Capture d’écran 2025-12-05 à 12 56 52" src="https://github.com/user-attachments/assets/82ffeed1-c42b-459c-8f4d-d5ecda087f5f" />
<img width="2145" height="1482" alt="Capture d’écran 2025-12-05 à 12 56 47" src="https://github.com/user-attachments/assets/313f6640-75b0-4d4f-bbf5-f5928c270bcb" />
<img width="2145" height="1482" alt="Capture d’écran 2025-12-05 à 12 56 42" src="https://github.com/user-attachments/assets/04913920-1c8a-471a-af10-04b43c5f1a68" />
<img width="2145" height="1482" alt="Capture d’écran 2025-12-05 à 12 56 35" src="https://github.com/user-attachments/assets/99d9586a-0ac2-4b8c-afd3-00ff6ef14c0c" />
<img width="2145" height="1482" alt="Capture d’écran 2025-12-05 à 12 56 26" src="https://github.com/user-attachments/assets/cd882a5a-ce4f-4aa7-b72d-cac19b8a8632" />
<img width="2145" height="1482" alt="Capture d’écran 2025-12-05 à 12 56 19" src="https://github.com/user-attachments/assets/cd605983-00b5-4d2d-a5e0-8c3c636b6924" />
<img width="2145" height="1482" alt="Capture d’écran 2025-12-05 à 12 56 05" src="https://github.com/user-attachments/assets/a028c046-ad77-4ee4-93ba-9f2f4f6e559c" />
<img width="2145" height="1482" alt="Capture d’écran 2025-12-05 à 12 55 46" src="https://github.com/user-attachments/assets/4218ba75-d607-4257-9209-46809b66f7a5" />

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
