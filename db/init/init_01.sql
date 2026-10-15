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

-- Table des commentaires
CREATE TABLE IF NOT EXISTS set_comments (
  id         INTEGER PRIMARY KEY AUTOINCREMENT,
  set_num    TEXT NOT NULL
    REFERENCES sets(set_num)
    ON DELETE CASCADE
    ON UPDATE CASCADE,
  comment       TEXT NOT NULL,
  created_at TEXT NOT NULL DEFAULT (strftime('%Y-%m-%d %H:%M:%S','now','localtime'))

);
