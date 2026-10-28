# nosql-project
Dépôt projet du cours BigData-NoSQL

# readme en cours d'édition :)


# Terminal 1 pour vérifier dans PostgreSQL :
docker exec -it lego_postgres psql -U legouser -d legodb -c "SELECT * FROM comments;"

# Terminal 2 pour vérifier dans Redis :
docker exec -it lego_redis redis-cli LRANGE lego:comments 0 -1