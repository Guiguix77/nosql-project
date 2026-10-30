# nosql-project
Dépôt projet du cours BigData-NoSQL

# readme en cours d'édition :)


# Terminal 1 pour vérifier dans PostgreSQL :
docker exec -it lego_postgres psql -U legouser -d legodb -c "SELECT * FROM comments;"

# Terminal 2 pour vérifier dans Redis :
docker exec -it lego_redis redis-cli LRANGE lego:comments 0 -1



# A FAIRE :
- changer le 2025 copyright en 2026
- modifier la page des crédits en mettant toute l'équipe
- regarder pour mettre en gras quand on est sur une page dans le menu nav
- rajouter la version jour/nuit et anglais/français dans la page Mon Compte
- regarder pour mettre à jour la nouvelle table (des sets) avec le fichier csv rebrickable à aller récup sur le web
