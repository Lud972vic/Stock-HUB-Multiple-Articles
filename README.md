# StockHUB

Application de gestion de stock multi-magasins.

<img width="2145" height="1482" alt="Capture d’écran 2025-12-05 à 12 55 46" src="https://github.com/user-attachments/assets/817b7d19-871e-4d4f-a626-63483fc7f757" />
<img width="2145" height="1482" alt="Capture d’écran 2025-12-05 à 12 56 05" src="https://github.com/user-attachments/assets/69226eea-cd81-4bba-b4e6-a5ee9e28c741" />
<img width="2145" height="1482" alt="Capture d’écran 2025-12-05 à 12 56 19" src="https://github.com/user-attachments/assets/e353996b-9f92-44b9-ad9b-fa47ab24052b" />
<img width="2145" height="1482" alt="Capture d’écran 2025-12-05 à 12 56 26" src="https://github.com/user-attachments/assets/bb2887d3-768c-44b7-b0fe-afd4df935bc4" />
<img width="2145" height="1482" alt="Capture d’écran 2025-12-05 à 12 56 35" src="https://github.com/user-attachments/assets/e49a0fc0-011a-4ea6-ac11-5564cac7c0b3" />
<img width="2145" height="1482" alt="Capture d’écran 2025-12-05 à 12 56 42" src="https://github.com/user-attachments/assets/661362ff-3e0d-402a-9f7a-301537dc940f" />
<img width="2145" height="1482" alt="Capture d’écran 2025-12-05 à 12 56 47" src="https://github.com/user-attachments/assets/9b944e33-68ca-447d-a7ab-369fa265bd96" />
<img width="2145" height="1482" alt="Capture d’écran 2025-12-05 à 12 56 52" src="https://github.com/user-attachments/assets/367bf1c2-2977-4c2c-ae55-7056734cf5c3" />
<img width="2145" height="1482" alt="Capture d’écran 2025-12-05 à 12 57 05" src="https://github.com/user-attachments/assets/774c3b1e-0ae1-497e-ba30-f9ccfbde5d13" />
<img width="2145" height="1482" alt="Capture d’écran 2025-12-05 à 12 57 09" src="https://github.com/user-attachments/assets/bfb5bc51-fd39-4c90-9e48-33717d327dda" />

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
