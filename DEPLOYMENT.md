# 🚀 EngineeringAgent — Deployment Guide

A Laravel 12 Engineering Agent with employee tracking, AI reports, leaderboards, and GitHub VCS integration.

---

## 📋 Table of Contents

- [Tech Stack](#tech-stack)
- [Environment Variables](#environment-variables)
- [Run with Docker Locally](#run-with-docker-locally)
- [Deploy to Render](#deploy-to-render)
- [Startup Flow](#startup-flow)
- [AI Module Note](#ai-module-note)

---

## Tech Stack

| Layer | Technology |
|-------|-----------|
| Backend | Laravel 12 / PHP 8.3 |
| Frontend | Blade + Tailwind CSS v4 + Alpine.js |
| Database | PostgreSQL (production) / MySQL (local XAMPP) |
| Build | Vite 7 |
| Container | Docker + Docker Compose |
| AI | Ollama (local only) with mock fallback |

---

## Environment Variables

Copy `.env.example` to `.env` and fill in every variable:

```bash
cp .env.example .env
```

### Required Variables

| Variable | Description | Example |
|----------|-------------|---------|
| `APP_KEY` | Laravel encryption key | `base64:...` (generate below) |
| `APP_URL` | Full public URL | `https://your-app.onrender.com` |
| `APP_ENV` | Environment | `production` |
| `APP_DEBUG` | Debug mode | `false` (always false in prod) |
| `DB_CONNECTION` | Database driver | `pgsql` |
| `DB_HOST` | DB host | from Render PostgreSQL |
| `DB_PORT` | DB port | `5432` |
| `DB_DATABASE` | DB name | from Render PostgreSQL |
| `DB_USERNAME` | DB user | from Render PostgreSQL |
| `DB_PASSWORD` | DB password | from Render PostgreSQL |
| `LOG_CHANNEL` | Log output | `stderr` (required for Render) |

### Optional Variables

| Variable | Description | Default |
|----------|-------------|---------|
| `GITHUB_TOKEN` | GitHub API token for real VCS data | empty = simulation mode |
| `OLLAMA_ENDPOINT` | Ollama server URL | `http://localhost:11434` |
| `OLLAMA_MODEL` | Model name | `llama3.2:1b` |
| `AI_PROVIDER` | AI backend | `ollama` |

### Generate APP_KEY

```bash
php artisan key:generate --show
```
Copy the output and set it as `APP_KEY`.

---

## Run with Docker Locally

### Prerequisites
- Docker Desktop installed and running
- `.env` file created from `.env.example`

### Step 1 — Create your local `.env`

```bash
cp .env.example .env
```

Edit `.env` and set at minimum:
```env
APP_KEY=base64:YOUR_GENERATED_KEY_HERE
APP_ENV=local
APP_DEBUG=true
APP_URL=http://localhost:8000
DB_CONNECTION=pgsql
DB_HOST=db
DB_PORT=5432
DB_DATABASE=engineering_agent
DB_USERNAME=agent_user
DB_PASSWORD=secret
LOG_CHANNEL=stderr
```

### Step 2 — Build and Start

```bash
docker compose up --build
```

The first start will:
1. Build the PHP + Node image
2. Start PostgreSQL
3. Run migrations automatically
4. Seed the database (10 employees, 100 tasks)
5. Start Laravel on http://localhost:8000

### Step 3 — Open the App

```
http://localhost:8000
```

### Useful Docker Commands

```bash
# Start in background
docker compose up -d

# View logs
docker compose logs -f app

# Run artisan commands
docker compose exec app php artisan migrate:status
docker compose exec app php artisan db:seed --force
docker compose exec app php artisan tinker

# Stop everything
docker compose down

# Stop and remove all volumes (wipes database!)
docker compose down -v
```

---

## Deploy to Render

### Step 1 — Push Code to GitHub

Make sure your repo has all these files committed:
```
Dockerfile
docker-compose.yml
.dockerignore
scripts/start.sh
.env.example          ← committed (no secrets)
.env                  ← NOT committed (in .gitignore)
```

```bash
git add .
git commit -m "chore: prepare for Render deployment"
git push origin main
```

### Step 2 — Create PostgreSQL Database on Render

1. Go to [render.com](https://render.com) → **New** → **PostgreSQL**
2. Name: `engineering-agent-db`
3. Plan: **Free**
4. Click **Create Database**
5. Save the **Internal Database URL** — you'll need it in Step 4

### Step 3 — Create Web Service on Render

1. Go to Render → **New** → **Web Service**
2. Connect your GitHub repository
3. Set the following:

| Setting | Value |
|---------|-------|
| **Name** | `engineering-agent` |
| **Region** | Your nearest |
| **Branch** | `main` |
| **Runtime** | **Docker** |
| **Dockerfile Path** | `./Dockerfile` |
| **Instance Type** | Free |

### Step 4 — Set Environment Variables on Render

In the Web Service → **Environment** tab, add each variable:

```
APP_NAME          = EngineeringAgent
APP_ENV           = production
APP_KEY           = base64:YOUR_GENERATED_KEY
APP_DEBUG         = false
APP_URL           = https://your-app-name.onrender.com

DB_CONNECTION     = pgsql
DB_HOST           = (from Render DB → Internal Hostname)
DB_PORT           = 5432
DB_DATABASE       = (from Render DB → Database)
DB_USERNAME       = (from Render DB → Username)
DB_PASSWORD       = (from Render DB → Password)

SESSION_DRIVER    = database
CACHE_STORE       = database
QUEUE_CONNECTION  = database

LOG_CHANNEL       = stderr
LOG_LEVEL         = error

AI_PROVIDER       = ollama
OLLAMA_ENDPOINT   = http://localhost:11434
OLLAMA_MODEL      = llama3.2:1b

GITHUB_TOKEN      = (optional — leave empty for simulation mode)
```

> **Note:** You can also paste the full `DATABASE_URL` from Render into `DB_URL` instead of setting individual DB_* variables.

### Step 5 — Deploy

Click **Deploy** (or push a new commit). Render will:
1. Build the Docker image
2. Run `scripts/start.sh` which handles migration + seeding automatically
3. Serve the app on your Render URL

---

## Startup Flow

Every time the container starts (deploy or restart), this happens automatically:

```
Container boots
    │
    ▼
php artisan config:cache     ← caches all env vars
    │
    ▼
php artisan route:cache      ← faster routing
    │
    ▼
php artisan view:cache       ← pre-compiled Blade templates
    │
    ▼
php artisan migrate --force  ← runs any pending migrations
    │
    ▼
php artisan db:seed --force  ← seeds data (idempotent — skips if already seeded)
    │
    ▼
php artisan serve --host=0.0.0.0 --port=$PORT
```

### Seeder Idempotency

All seeders are safe to run multiple times:

| Seeder | Behaviour |
|--------|-----------|
| `DatabaseSeeder` | Skips user creation if `users` table is not empty |
| `EmployeesTableSeeder` | Skips entirely if any employees exist |
| `TasksTableSeeder` | Skips entirely if any tasks exist |

---

## AI Module Note

The AI report generator uses **Ollama** running locally on your machine.

| Environment | Behaviour |
|------------|-----------|
| **Local (XAMPP)** | Real AI response from Ollama |
| **Local (Docker)** | Connects to `host.docker.internal:11434` if Ollama is running |
| **Render (production)** | Ollama unavailable → **mock report generated automatically** |

The mock fallback is built into `OllamaProvider.php` and produces a professional structured report based on the employee's actual metrics. The app works fully without Ollama.

---

## Common Issues

### Render — App crashes on start
- Check that `APP_KEY` is set in environment variables
- Ensure `DB_*` variables match the Render PostgreSQL credentials exactly
- View logs: Render Dashboard → Logs tab

### Migrations fail on Render
- Ensure `DB_CONNECTION=pgsql` (not mysql)
- Check database is accessible (Render PostgreSQL must be in the same region)

### Assets not loading (blank / unstyled page)
- Make sure `npm run build` runs during Docker build (it does in the Dockerfile)
- Ensure `public/hot` is NOT committed to git (it's in `.gitignore`)
