-- Table des thèmes
CREATE TABLE IF NOT EXISTS themes (
  id        INTEGER PRIMARY KEY,
  name      TEXT NOT NULL,
  parent_id INTEGER NULL
    REFERENCES themes(id)
    ON UPDATE CASCADE
    ON DELETE SET NULL
    DEFERRABLE INITIALLY DEFERRED
);
-- Table des sets
CREATE TABLE IF NOT EXISTS sets (
  set_num   TEXT PRIMARY KEY,
  name      TEXT NOT NULL,
  year      INTEGER,
  theme_id  INTEGER NULL
    REFERENCES themes(id)
    ON UPDATE CASCADE
    ON DELETE SET NULL
    DEFERRABLE INITIALLY DEFERRED,
  num_parts INTEGER,
  img_url   TEXT
);

-- Table des utilisateurs
CREATE TABLE IF NOT EXISTS users (
  id         SERIAL PRIMARY KEY,
  username   TEXT UNIQUE NOT NULL,
  email      TEXT UNIQUE NOT NULL,
  password   TEXT NOT NULL,
  created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP
);

-- Table des commentaires
CREATE TABLE IF NOT EXISTS set_comments (
  id         SERIAL PRIMARY KEY,
  set_num    TEXT NOT NULL
    REFERENCES sets(set_num)
    ON DELETE CASCADE
    ON UPDATE CASCADE,
  username   TEXT NOT NULL DEFAULT 'Anonyme',
  comment    TEXT NOT NULL,
  created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP
);

--Importation automatique des CSV au premier démarrage du conteneur
COPY themes(id, name, parent_id)
FROM '/csv/themes.csv'
DELIMITER ','
CSV HEADER;

COPY sets(set_num, name, year, theme_id, num_parts, img_url)
FROM '/csv/sets.csv'
DELIMITER ','
CSV HEADER;