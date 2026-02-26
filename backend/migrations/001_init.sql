-- migrations/001_init.sql
-- Runs automatically on a fresh MySQL volume via /docker-entrypoint-initdb.d/

CREATE TABLE IF NOT EXISTS users (
  id          BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  email       VARCHAR(255) NOT NULL,
  name        VARCHAR(255) NULL,
  google_id   VARCHAR(255) NULL,
  created_at  DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (id),
  UNIQUE KEY uniq_email (email),
  UNIQUE KEY uniq_google_id (google_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS sparks (
  id             BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  user_id        BIGINT UNSIGNED NOT NULL,
  text           TEXT NOT NULL,
  state          ENUM('open','ignored','searched','finished') NOT NULL DEFAULT 'open',
  created_at     DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  updated_at     DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  completed_note TEXT NULL,
  PRIMARY KEY (id),
  KEY idx_user_created (user_id, created_at),
  KEY idx_user_state   (user_id, state),
  CONSTRAINT fk_sparks_user
    FOREIGN KEY (user_id) REFERENCES users(id)
    ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Seed dev user (id=1) — used until OAuth is wired up
INSERT IGNORE INTO users (id, email, name) VALUES (1, 'dev@localhost', 'Dev User');
