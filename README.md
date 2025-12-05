# StockHUB

Application de gestion de stock multi-magasins.

<img width="2145" height="1482" alt="Capture d’écran 2025-12-05 à 12 55 46" src="https://github.com/user-attachments/assets/02f46868-a86f-46c2-9073-302e4cb7c598" />
<img width="2145" height="1482" alt="Capture d’écran 2025-12-05 à 12 56 05" src="https://github.com/user-attachments/assets/76e7f1d1-2bfd-4bfc-8632-f87d402c7717" />
<img width="2145" height="1482" alt="Capture d’écran 2025-12-05 à 12 56 19" src="https://github.com/user-attachments/assets/aee3525e-3f02-44bf-bd43-1bcc06bb22dd" />
<img width="2145" height="1482" alt="Capture d’écran 2025-12-05 à 12 56 26" src="https://github.com/user-attachments/assets/de15ad92-8144-422e-ab52-9d1d315e641c" />
<img width="2145" height="1482" alt="Capture d’écran 2025-12-05 à 12 56 35" src="https://github.com/user-attachments/assets/d62d9fdd-6db7-41e3-b6be-52ccc740381b" />
<img width="2145" height="1482" alt="Capture d’écran 2025-12-05 à 12 56 42" src="https://github.com/user-attachments/assets/111616f9-08ce-4168-b392-8d4672490610" />
<img width="2145" height="1482" alt="Capture d’écran 2025-12-05 à 12 56 47" src="https://github.com/user-attachments/assets/357f6528-be0f-4c79-9786-3443230154ef" />
<img width="2145" height="1482" alt="Capture d’écran 2025-12-05 à 12 56 52" src="https://github.com/user-attachments/assets/cbaaa67c-39a2-4c56-af21-0496c20ef228" />
<img width="2145" height="1482" alt="Capture d’écran 2025-12-05 à 12 57 05" src="https://github.com/user-attachments/assets/3b90161b-6891-4c93-af6e-304436fd3e4b" />
<img width="2145" height="1482" alt="Capture d’écran 2025-12-05 à 12 57 09" src="https://github.com/user-attachments/assets/9f3962a3-2b31-4766-9052-3c522da2c777" />
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
