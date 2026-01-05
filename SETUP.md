# Detailed Setup Guide

This guide provides step-by-step instructions for setting up the Yii2 MVC template in different environments.

## Table of Contents

1. [Prerequisites Check](#prerequisites-check)
2. [Initial Setup](#initial-setup)
3. [Docker Full Stack Setup](#docker-full-stack-setup)
4. [Docker Web Only Setup](#docker-web-only-setup)
5. [Native PHP Setup](#native-php-setup)
6. [PHP Built-in Server](#php-built-in-server)
7. [Troubleshooting](#troubleshooting)

---

## Prerequisites Check

Before starting, verify your system meets the requirements:

### For Native PHP Setup:

```bash
# Check PHP version (need 8.0+)
php -v

# Check required extensions
php -m | grep -E "pdo_mysql|mbstring|intl|openssl|json"

# Check Composer
composer -V

# Check MySQL (if using local)
mysql --version
```

### For Docker Setup:

```bash
# Check Docker
docker --version

# Check Docker Compose
docker-compose --version
```

---

## Initial Setup

These steps are common to all setup methods:

### 1. Clone Repository

```bash
git clone <repository-url>
cd PROJECT
```

### 2. Install Dependencies

```bash
composer install
```

### 3. Create Environment File

```bash
# Windows
copy .env.example .env

# Linux/Mac
cp .env.example .env
```

**Note**: If `.env.example` doesn't exist, create `.env` manually with this content:

```env
# Application Environment
APP_ENV=local
APP_DEBUG=1

# Application Identity
APP_ID=my-app
APP_NAME="My Yii2 Application"

# Database Configuration
DB_DRIVER=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_NAME=my_database
DB_USER=root
DB_PASSWORD=

# Security
# Generate with: php -r "echo bin2hex(random_bytes(32));"
COOKIE_VALIDATION_KEY=
```

### 4. Generate Security Key

```bash
# Generate a secure cookie validation key
php -r "echo bin2hex(random_bytes(32));"
```

Copy the output and paste it as the value for `COOKIE_VALIDATION_KEY` in your `.env` file.

---

## Docker Full Stack Setup

This setup runs both web server and MySQL in Docker containers.

### Step 1: Configure .env

Edit `.env` file:

```env
APP_ENV=local
APP_DEBUG=1

APP_ID=my-app
APP_NAME="My Yii2 Application"

# Important: Use 'mysql' as host (Docker service name)
DB_DRIVER=mysql
DB_HOST=mysql
DB_PORT=3306
DB_NAME=yii_zero
DB_USER=root
DB_PASSWORD=secret

COOKIE_VALIDATION_KEY=your-generated-key-here
```

### Step 2: Start Containers

```bash
docker-compose up -d
```

This will:
- Build PHP 8.2 + Apache image
- Start MySQL 8.0 container
- Create network between containers

### Step 3: Verify Containers

```bash
docker-compose ps
```

You should see both `yii2-web` and `yii2-mysql` running.

### Step 4: Run Migrations

```bash
# Option 1: If PHP is installed locally
php yii migrate

# Option 2: Run inside Docker container
docker-compose exec web php yii migrate
```

### Step 5: Access Application

Open browser: `http://localhost`

### Useful Commands

```bash
# View logs
docker-compose logs -f

# Stop containers
docker-compose down

# Stop and remove volumes (clean slate)
docker-compose down -v

# Rebuild containers
docker-compose up -d --build

# Execute command in container
docker-compose exec web php yii cache/flush
```

---

## Docker Web Only Setup

This setup runs only the web server in Docker and connects to external MySQL.

### Step 1: Ensure MySQL is Running

Your MySQL should be running on:
- Local machine (127.0.0.1)
- Remote server
- Another Docker container

### Step 2: Configure .env

Edit `.env` file:

```env
APP_ENV=local
APP_DEBUG=1

APP_ID=my-app
APP_NAME="My Yii2 Application"

# For Windows/Mac Docker Desktop
DB_DRIVER=mysql
DB_HOST=host.docker.internal
DB_PORT=3306
DB_NAME=my_database
DB_USER=root
DB_PASSWORD=your_local_password

# For Linux, use your host IP instead:
# DB_HOST=172.17.0.1  # or your actual host IP

COOKIE_VALIDATION_KEY=your-generated-key-here
```

**Note**: On Linux, you may need to:
1. Find your Docker host IP: `ip addr show docker0 | grep inet`
2. Use that IP instead of `host.docker.internal`
3. Or add `extra_hosts` to docker-compose file

### Step 3: Start Web Container

```bash
docker-compose -f docker-compose.web-only.yaml up -d
```

### Step 4: Run Migrations

```bash
# Run from host (requires local PHP)
php yii migrate
```

### Step 5: Access Application

Open browser: `http://localhost`

---

## Native PHP Setup

This setup uses your local PHP, Apache/Nginx, and MySQL.

### Step 1: Verify Prerequisites

```bash
# Check PHP version
php -v  # Should be 8.0+

# Check extensions
php -m | grep pdo_mysql
php -m | grep mbstring
```

### Step 2: Configure .env

Edit `.env` file:

```env
APP_ENV=local
APP_DEBUG=1

APP_ID=my-app
APP_NAME="My Yii2 Application"

DB_DRIVER=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_NAME=my_database
DB_USER=root
DB_PASSWORD=your_password

COOKIE_VALIDATION_KEY=your-generated-key-here
```

### Step 3: Create Database

```bash
mysql -u root -p
```

```sql
CREATE DATABASE my_database CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
EXIT;
```

### Step 4: Configure Web Server

#### Apache Configuration

1. Enable `mod_rewrite`:
   ```bash
   # Linux
   sudo a2enmod rewrite
   sudo systemctl restart apache2
   
   # Windows (XAMPP/WAMP)
   # Usually enabled by default
   ```

2. Create Virtual Host:

   **Linux/Mac** (`/etc/apache2/sites-available/myapp.conf`):
   ```apache
   <VirtualHost *:80>
       ServerName myapp.local
       DocumentRoot /path/to/PROJECT/public
       
       <Directory /path/to/PROJECT/public>
           AllowOverride All
           Require all granted
       </Directory>
       
       ErrorLog ${APACHE_LOG_DIR}/myapp_error.log
       CustomLog ${APACHE_LOG_DIR}/myapp_access.log combined
   </VirtualHost>
   ```

   Enable site:
   ```bash
   sudo a2ensite myapp.conf
   sudo systemctl reload apache2
   ```

   Add to `/etc/hosts`:
   ```
   127.0.0.1 myapp.local
   ```

   **Windows** (XAMPP - `C:\xampp\apache\conf\extra\httpd-vhosts.conf`):
   ```apache
   <VirtualHost *:80>
       ServerName myapp.local
       DocumentRoot "C:/path/to/PROJECT/public"
       
       <Directory "C:/path/to/PROJECT/public">
           AllowOverride All
           Require all granted
       </Directory>
   </VirtualHost>
   ```

   Add to `C:\Windows\System32\drivers\etc\hosts`:
   ```
   127.0.0.1 myapp.local
   ```

#### Nginx Configuration

Create `/etc/nginx/sites-available/myapp`:

```nginx
server {
    listen 80;
    server_name myapp.local;
    root /path/to/PROJECT/public;
    
    index index.php;
    
    location / {
        try_files $uri $uri/ /index.php?$args;
    }
    
    location ~ \.php$ {
        fastcgi_pass 127.0.0.1:9000;  # or unix:/var/run/php/php8.2-fpm.sock
        fastcgi_index index.php;
        include fastcgi_params;
        fastcgi_param SCRIPT_FILENAME $document_root$fastcgi_script_name;
    }
    
    location ~ /\. {
        deny all;
    }
}
```

Enable site:
```bash
sudo ln -s /etc/nginx/sites-available/myapp /etc/nginx/sites-enabled/
sudo nginx -t
sudo systemctl reload nginx
```

### Step 5: Set Permissions

```bash
# Make runtime writable
chmod -R 777 runtime

# Make assets writable (if using asset manager)
chmod -R 777 public/assets
```

### Step 6: Run Migrations

```bash
php yii migrate
```

### Step 7: Access Application

```
http://myapp.local
# or
http://localhost
```

---

## PHP Built-in Server

**⚠️ Development Only - Not for Production**

### Step 1: Configure .env

Same as Native PHP setup (use `127.0.0.1` for DB_HOST).

### Step 2: Start Server

```bash
php -S localhost:8000 -t public
```

### Step 3: Run Migrations

In another terminal:
```bash
php yii migrate
```

### Step 4: Access Application

```
http://localhost:8000
```

---

## Troubleshooting

### Database Connection Issues

**Problem**: "SQLSTATE[HY000] [2002] Connection refused"

**Solutions**:
- **Docker Full Stack**: Ensure `DB_HOST=mysql` in `.env`
- **Docker Web Only**: 
  - Windows/Mac: Use `host.docker.internal`
  - Linux: Use host IP or configure `extra_hosts`
- **Native**: Verify MySQL is running and credentials are correct

**Test Connection**:
```bash
# From host
mysql -u root -p -h 127.0.0.1

# From Docker container
docker-compose exec web mysql -u root -p -h mysql
```

### Permission Denied Errors

```bash
# Fix runtime permissions
chmod -R 777 runtime
chmod -R 777 public/assets
```

### Pretty URLs Not Working

**Apache**:
- Ensure `mod_rewrite` is enabled
- Check `.htaccess` exists in `public/` directory
- Verify `AllowOverride All` in Apache config

**Nginx**:
- Check `try_files` directive in config
- Verify PHP-FPM is running

**Docker**:
- Already configured in Dockerfile
- No additional setup needed

### Composer Issues

```bash
# Clear Composer cache
composer clear-cache

# Reinstall dependencies
rm -rf vendor
composer install
```

### Docker Container Issues

```bash
# View container logs
docker-compose logs web
docker-compose logs mysql

# Restart containers
docker-compose restart

# Rebuild from scratch
docker-compose down -v
docker-compose up -d --build
```

---

## Next Steps

After successful setup:

1. ✅ Verify database connection on home page
2. ✅ Run initial migrations
3. ✅ Create your first model
4. ✅ Build your application!

For more information, see the main [README.md](README.md).

