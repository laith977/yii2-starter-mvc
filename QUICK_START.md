# Quick Start Guide

Choose your setup method and follow the steps:

## 🐳 Option 1: Docker Full Stack (Easiest)

```bash
# 1. Clone and install
git clone <repo-url>
cd PROJECT
composer install

# 2. Create .env file
cp .env.example .env
# Edit .env: Set DB_HOST=mysql, DB_PASSWORD=secret

# 3. Start Docker
docker-compose up -d

# 4. Run migrations
docker-compose exec web php yii migrate

# 5. Access
# http://localhost
```

## 🐳 Option 2: Docker Web Only

```bash
# 1-2. Same as above

# 3. Edit .env: Set DB_HOST=host.docker.internal (or your host IP)

# 4. Start web only
docker-compose -f docker-compose.web-only.yaml up -d

# 5. Run migrations (from host)
php yii migrate

# 6. Access
# http://localhost
```

## 💻 Option 3: Native PHP

```bash
# 1-2. Same as above

# 3. Edit .env: Set DB_HOST=127.0.0.1

# 4. Create database
mysql -u root -p
CREATE DATABASE my_database;

# 5. Configure Apache/Nginx (see SETUP.md)

# 6. Run migrations
php yii migrate

# 7. Access
# http://myapp.local or http://localhost
```

## 🚀 Option 4: PHP Built-in Server (Dev Only)

```bash
# 1-3. Same as Option 3

# 4. Start server
php -S localhost:8000 -t public

# 5. Run migrations (another terminal)
php yii migrate

# 6. Access
# http://localhost:8000
```

---

**Need more details?** See [SETUP.md](SETUP.md) for comprehensive instructions.

**Having issues?** Check the [Troubleshooting](SETUP.md#troubleshooting) section.

