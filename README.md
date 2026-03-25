# Sparks

Capture distracting thoughts while working, so you can review them later without breaking focus

Live at **sparkfreeze.com**.

---

## What it does

Users sign in with Google and manage a personal list of sparks — ideas, links, or anything worth revisiting. Each spark has a state:

| State | Meaning |
|-------|---------|
| `open` | Just captured |
| `ignored` | Dismissed |
| `searched` | Looked into it |
| `finished` | Done / acted on |

---

## Stack

| Layer | Technology |
|-------|-----------|
| Frontend | React + TypeScript (Vite) |
| Backend | PHP 8.1 |
| Database | MySQL 8.0 |
| Auth | Google OAuth (ID token, nonce-verified) |
| Session | PHP sessions via `HttpOnly` cookie |
| Dev environment | Docker Compose |

---

## Auth

Login is **Google OAuth only** — there are no passwords. The flow:

1. Frontend requests a nonce from `GET /auth/nonce`
2. Google One Tap returns a signed ID token (nonce embedded)
3. Frontend posts the token to `POST /auth/google`
4. Backend verifies the token and nonce, then finds or creates the user
5. A session cookie is set; all subsequent requests use it

---

## Database schema

```sql
CREATE TABLE users (
  id         BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  email      VARCHAR(255) NOT NULL,
  name       VARCHAR(255) NULL,
  google_id  VARCHAR(255) NULL,
  created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (id),
  UNIQUE KEY uniq_email (email),
  UNIQUE KEY uniq_google_id (google_id)
);

CREATE TABLE sparks (
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
  FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);
```

The migration lives in `backend/migrations/001_init.sql` and runs automatically when the MySQL Docker container initialises a fresh volume.

---

## API

All endpoints require an active session cookie except `/auth/*`.

| Method | Path | Description |
|--------|------|-------------|
| `GET` | `/auth/nonce` | Issue a one-shot login nonce |
| `POST` | `/auth/google` | Verify Google ID token, start session |
| `GET` | `/auth/session` | Return current user or `null` |
| `POST` | `/auth/logout` | Destroy session |
| `GET` | `/sparks` | List sparks for the authenticated user |
| `POST` | `/sparks` | Create a spark |
| `PATCH` | `/sparks/{id}` | Update `state`, `text`, or `completed_note` |
| `DELETE` | `/sparks/{id}` | Delete a spark |

---

## Local development

**Prerequisites:** Docker + Docker Compose, Node 20.

```bash
# 1. Copy and fill in env
cp backend/.env.example backend/.env   # set DB creds, GOOGLE_CLIENT_ID, etc.

# 2. Start everything
docker compose up

# Services:
#   Frontend  → http://localhost:5174
#   Backend   → http://localhost:8080
#   phpMyAdmin → http://localhost:8081
```

The MySQL container auto-runs `backend/migrations/001_init.sql` on first start. To reset the database, remove the named volume:

```bash
docker compose down -v
docker compose up
```

### Frontend env

Create `frontend/.env.local`:

```
VITE_API_URL=http://localhost:8080
```

---

## Project structure

```
sparks-app/
├── backend/
│   ├── migrations/       # SQL run on DB init
│   ├── src/
│   │   ├── Auth/         # Google ID token verification
│   │   ├── AuthController.php
│   │   ├── SparkController.php
│   │   ├── SparkRepository.php
│   │   ├── Db.php
│   │   └── bootstrap.php
│   └── .env              # not committed
├── frontend/
│   └── src/
│       └── api.ts        # typed API client
├── deploy/               # production server config
└── docker-compose.yml
```
