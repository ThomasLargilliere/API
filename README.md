# API
Projet BileMo API OpenClassrooms

## Installation
1. Clonez ou téléchargez le repository GitHub dans le dossier voulu :
```
    git clone https://github.com/ThomasLargilliere/API.git
```
2. Editez le fichier .env pour notamment mettre à jour DATABASE_URL. Vous pouvez aussi mettre APP_ENV=prod à la place de dev.

3. Installer les dépendances avec [Composer](https://getcomposer.org/download/) :
```
    composer install
```
4. Pour créer la base de donnée avec la commande suivante :
```
    php bin/console doctrine:database:create
```
5. Pour créer les tables taper la commande suivante :
```
    php bin/console doctrine:migrations:migrate
```
6. Pour lancer le serveur utiliser la commande suivante (N'oublier pas de lancer aussi votre base de donnée via XAMPP, MAMP ou WAMP) :
```
    symfony server:start
```
7. Accéder à la documentation de l'API via http://localhost:8000/api/doc

OPTIONNEL :

1. Utiliser un jeu de données crées :
```
    php bin/console doctrine:fixtures:load
```
2. Se connecter :
```
    email : spyoo@spyoo.fr
    password : 123
```
