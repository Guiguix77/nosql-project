# FindBrick



#CREATION DE LA DATABASE ET DES TABLES

//Placez-vous dans le dossier /src/db/
cd src/db/

//Pour créer la database et les tables , il faut exécuter le fichier execute.sql :
sqlite3 sets.db < execute.sql

//Pour vérifier que les tables ont bien été créées :
sqlite3 sets.db ".tables"

//Maintenant il faut insérer les données des fichiers CSV dans les tables :
php import_csv_sql.php


#ALLEZ SUR LE SITE WEB

//Placez-vous désormais dans le dossier racine du projet
cd ../..

//Ouvrez un serveur PHP
php -S localhost:8000

//Ouvrez votre navigateur et taper dans la barre de recherche
localhost:8000
