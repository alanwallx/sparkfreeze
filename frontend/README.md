# Sparks App

A simple productivity app to capture distracting thoughts ("Sparks") while working, so you can review them later without breaking focus.

---

## 🚀 Project Stack

- **Frontend:** React + TypeScript (Vite)
- **Backend:** PHP 8.1 (No framework)
- **Containerised:** Docker & Docker Compose

---

## 🎯 Features

- Add "Sparks" — ideas or distractions you want to park for later.
- View saved Sparks.
- Mark Sparks as "Ignored" if you no longer care about them.
- Quick Google Search link for Sparks you're still interested in.

---

## 📂 Folder Structure

```
/sparks-app
  /frontend    → React + Vite + TypeScript app
  /backend     → PHP API
  docker-compose.yml
  .gitignore
  README.md
```

---

## 🐳 Docker Setup

### Start the app

```bash
cd sparks-app
docker-compose up --build
```

- Frontend: [http://localhost:5173](http://localhost:5173)
- Backend API: [http://localhost:8080](http://localhost:8080)

### Background Mode

To run in the background:

```bash
docker-compose up --build -d
```

To stop:

```bash
docker-compose down
```

---

## 🔐 API Overview

**GET /** → Returns all Sparks

**POST /** → Adds a new Spark

- JSON Body:
  ```json
  {
    "text": "Why are speakers bi-wired?"
  }
  ```

**DELETE /?id={id}** → Marks a Spark as ignored

**OPTIONS /** → Handles CORS preflight requests

---

## 📝 .gitignore Highlights

The following are excluded from version control:

- `node_modules`
- `dist/` build output
- `.vite/` cache
- `backend/sparks.json` (your saved sparks)
- Logs, system files, editor config files
- Docker build files

---

## ✅ Next Steps

- Build React frontend UI to add, view, ignore Sparks.
- Connect React frontend to PHP API.

