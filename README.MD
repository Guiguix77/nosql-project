# FindBrick — Projet BigData & NoSQL
## Site réalisé par **Guillaume MÉRIEUX**, **Gaël ODDOS-MARCEL** et **Chloé AUREAU** dans le cadre du projet BigData & NoSQL.
Application web de référencement de boîtes LEGO (28 627 sets) avec gestion des comptes utilisateurs et de commentaires.
Les données saisies par l'utilisateur via les formulaires web sont persistées simultanément dans **PostgreSQL** et **Redis**, orchestrés via **Docker Compose**.

- **Déploiement en ligne (Serveur hébergé) :** `https://oddosmarcelgael.fr`
---

## 1. Récupérer et lancer le projet (`git clone` & `docker compose up`)

```bash
git clone https://github.com/Guiguix77/nosql-project.git
cd nosql-project
docker compose up --build -d
```
## 2. Interagir avec l'interface Web

Rendez-vous sur **http://localhost:8080** :

1. **Formulaire de Compte** (`http://localhost:8080/account.php`) :

    - Connectez-vous avec le compte de démonstration (Pseudo : `test` / Mot de passe : `test`) ou créez un nouveau compte via le formulaire d'inscription.

    - Double persistance : L'utilisateur est enregistré dans la table `users` de PostgreSQL et son profil + compteur de connexions sont persistés dans un Hash Redis (`lego:user:<username>`).

2. **Formulaire de Commentaire** (`http://localhost:8080/research.php?set_num=10316-1`) :

    - Recherchez un set LEGO (ex: `10316-1`) et ajoutez un commentaire via le formulaire.

    - Double persistance : Le commentaire est inséré dans la table `set_comments` de **PostgreSQL** et ajouté en JSON dans la liste **Redis** (`lego:comments:recent`) ainsi que dans le classement des sets les plus commentés (`lego:sets:most_commented`).

3. **Se connecter aux deux systèmes de persistance** (Les 2 Terminaux)

Ouvrez deux terminaux pour vous connecter à chacun des systèmes de stockage et constater la persistance des données saisies :

**Terminal 1 : Connexion à PostgreSQL**
```bash
docker exec -it lego_postgres psql -U legouser -d legodb
```
Requêtes SQL pour constater la persistance :
```sql
SELECT * FROM set_comments;
SELECT id, username, email, created_at FROM users;
\q
```
(Ou en une seule ligne : `docker exec -it lego_postgres psql -U legouser -d legodb -c "SELECT * FROM set_comments;"`)

**Terminal 2 : Connexion à Redis**
```bash
docker exec -it lego_redis redis-cli
```
Commandes Redis pour constater la persistance :
```bash
LRANGE lego:comments:recent 0 -1
ZREVRANGE lego:sets:most_commented 0 -1 WITHSCORES
HGETALL lego:user:test
exit
```
(Ou en une seule ligne : `docker exec -it lego_redis redis-cli LRANGE lego:comments:recent 0 -1`)