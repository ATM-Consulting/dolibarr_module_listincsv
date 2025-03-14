#Change Log

## Unreleased



## release 1.9
- FIX : définition d'une valeur par défaut si aucun titre trouvé pour éviter une erreur sur la fonction trim - *14/03/2025* - 1.9.3
- FIX : Add compatibility with conf MAIN_CHECKBOX_LEFT_COLUMN - *20/01/2024* - 1.9.2
- FIX : COMPAT 21 - *10/12/2024* - 1.9.1
- FIX : Nettoyage et compatibilité Dolibarr 20 - *02/08/2024* - 1.9.1
- NEW : DA025083 - Création d'un hook listInCSVFooterContext permettant à des modules externes d'utiliser ListInCSV. - *11/06/2024* - 1.9.0

## release 1.8
- FIX : DA024994 - Problème de sélection sur la liste des demandes de congés (car elle contient des valeurs dans des inputs) - *21/05/2024* - 1.8.4
- FIX : object test  - *25/03/2024* - 1.8.3  
- FIX : Compat agefodd session onglet participant sur tableau stagiaire *20/03/2024* - 1.8.2
- FIX : Module logo Image *18/12/2023* - 1.8.1
- FIX : Compat v19 et php8.2 *07/10/2023* - 1.8.0

## release 1.7

- NEW : Ajout de la possibilité d'exporter la liste des prix clients *07/10/2023* - 1.7.0

## release 1.6

- NEW : Ajout icône listincsv sur objets référents produit et tiers *07/02/2023* - 1.6.0

## release 1.5

- NEW : Ajout de la class TechATM pour l'affichage de la page "A propos" *10/05/2022* 1.15.0

## release 1.4

- FIX: Family name - *02/06/2022* - 1.4.3
- FIX: Delete Trigger - *02/06/2022* - 1.4.2
- FIX: context detection - *15/03/2022* - 1.4.1
- NEW: Include Dolibarr V13 stock to date - *28/02/2022* - 1.4.0

## release 1.3

- FIX: Appel de `call_trigger()` sur un non-objet - *08/10/2021* - 1.3.1
- NEW: Déclenchement d'un trigger sur export d'un fichier avec listincsv - *19/05/2021* - 1.3.0

## release 1.2

- FIX: La liste ne s'exporte plus - *20/05/2021* - 1.2.4
- FIX: Les champs de type "case à cocher" ne sont pas exportés - *17/05/2021* - 1.2.3
- FIX: Suppression du dossier Box ainsi que tu fichier box *11/05/2021* - 1.2.2
- FIX: $_SESSION devient newToken() *11/05/2021* - 1.2.1
- NEW: Déplacement du code qui crée le boutton vert "CSV" pour utilisation dans des modules externes avec un contexte ajax *06/05/2021* - 1.2.0

## release 1.1

- NEW: Ajout d'une gestion de récupération des informations via un autre paramètre que l'action du formulaire le plus proche *06/05/2021* - 1.1.0
