# Yii2 MVC Template

A minimal, production-ready Yii2 application template built from scratch. This template provides a clean foundation for building MVC applications with Yii2, without the bloat of official templates.

## ✨ Features

- **Minimal Setup**: Only essential files and configurations
- **MVC Structure**: Ready-to-use Controllers, Models, and Views
- **Environment Configuration**: `.env` file support for configuration
- **Database Ready**: Pre-configured database connection with MySQL
- **Console Commands**: Full support for migrations and console commands
- **Pretty URLs**: Clean URLs with `.htaccess` support
- **Error Handling**: Built-in error handling and logging
- **Flexible Deployment**: Run with Docker, native PHP, or command line
- **Security**: CSRF protection, cookie validation, and secure sessions

## 📋 System Requirements

### For Native PHP Setup:
- **PHP**: 8.0 or higher
- **Composer**: 2.x
- **Web Server**: Apache 2.4+ or Nginx (with PHP-FPM)
- **Database**: MySQL 5.7+ or MariaDB 10.3+
- **Required PHP Extensions**:
  - `pdo_mysql`
  - `mbstring`
  - `intl` (recommended)
  - `openssl`
  - `json`

### For Docker Setup:
- **Docker**: 20.10+
- **Docker Compose**: 2.0+

## 🚀 Setup Guide

> **Quick Start?** See [QUICK_START.md](QUICK_START.md) for condensed instructions.  
> **Detailed Guide?** See [SETUP.md](SETUP.md) for step-by-step instructions.

Choose the setup method that works best for your environment:

### Option 1: Full Docker Setup (Recommended for Beginners)

**Best for**: Quick setup, isolated environment, consistent across team

This runs both web server (Apache + PHP) and MySQL in Docker containers.

#### Steps:

1. **Clone the repository**
   ```bash
   git clone <repository-url>
   cd PROJECT
   ```

2. **Install dependencies**
   ```bash
   composer install
   ```

3. **Create environment file**
   ```bash
   # Windows
   copy .env.example .env
   
   # Linux/Mac
   cp .env.example .env
   ```
   
   **Note**: If `.env.example` doesn't exist, create `.env` manually. See the configuration section below for the template.

4. **Configure `.env` for Docker**
   ```env
   APP_ENV=local
   APP_DEBUG=1
   
   APP_ID=my-app
   APP_NAME="My Yii2 Application"
   
   # For Docker: use 'mysql' as host (service name)
   DB_DRIVER=mysql
   DB_HOST=mysql
   DB_PORT=3306
   DB_NAME=yii_zero
   DB_USER=root
   DB_PASSWORD=secret
   
   # Generate with: php -r "echo bin2hex(random_bytes(32));"
   COOKIE_VALIDATION_KEY=your-secret-key-here
   ```

5. **Start Docker containers**
   ```bash
   docker-compose up -d
   ```

6. **Run migrations (inside container)**
   ```bash
   # Option 1: Run from host (if PHP is installed locally)
   php yii migrate
   
   # Option 2: Run inside Docker container
   docker-compose exec web php yii migrate
   ```

7. **Access application**
   ```
   http://localhost
   ```

8. **Stop containers**
   ```bash
   docker-compose down
   ```

---

### Option 2: Docker Web Only + External MySQL

**Best for**: Using Docker for web server but connecting to existing/local MySQL

This runs only the web server in Docker and connects to MySQL running on your host or remote server.

#### Steps:

1. **Follow steps 1-3 from Option 1**

2. **Configure `.env` for external MySQL**
   ```env
   APP_ENV=local
   APP_DEBUG=1
   
   APP_ID=my-app
   APP_NAME="My Yii2 Application"
   
   # For external MySQL: use host.docker.internal (Windows/Mac) or host IP
   # On Linux, you may need to use your actual host IP (e.g., 172.17.0.1)
   DB_DRIVER=mysql
   DB_HOST=host.docker.internal  # or 127.0.0.1 on Linux
   DB_PORT=3306
   DB_NAME=my_database
   DB_USER=root
   DB_PASSWORD=your_local_password
   
   COOKIE_VALIDATION_KEY=your-secret-key-here
   ```

3. **Start only web container**
   ```bash
   docker-compose -f docker-compose.web-only.yaml up -d
   ```

4. **Run migrations (from host)**
   ```bash
   php yii migrate
   ```

5. **Access application**
   ```
   http://localhost
   ```

---

### Option 3: Native PHP + Apache/Nginx + MySQL

**Best for**: Production-like environment, full control, no Docker

This uses your local PHP, Apache/Nginx, and MySQL installation.

#### Prerequisites Check:

```bash
# Check PHP version (need 8.0+)
php -v

# Check required extensions
php -m | grep pdo_mysql
php -m | grep mbstring
php -m | grep intl

# Check Composer
composer -V
```

#### Steps:

1. **Clone and install dependencies**
   ```bash
   git clone <repository-url>
   cd PROJECT
   composer install
   ```

2. **Create environment file**
   ```bash
   cp .env.example .env
   ```

3. **Configure `.env`**
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
   
   COOKIE_VALIDATION_KEY=your-secret-key-here
   ```

4. **Configure Apache Virtual Host**

   Create a virtual host pointing to `public/` directory:
   
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

   Or for Nginx:
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
           fastcgi_pass 127.0.0.1:9000;
           fastcgi_index index.php;
           include fastcgi_params;
           fastcgi_param SCRIPT_FILENAME $document_root$fastcgi_script_name;
       }
   }
   ```

5. **Create database**
   ```sql
   CREATE DATABASE my_database CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
   ```

6. **Run migrations**
   ```bash
   php yii migrate
   ```

7. **Access application**
   ```
   http://myapp.local
   # or
   http://localhost
   ```

---

### Option 4: PHP Built-in Server (Development Only)

**Best for**: Quick testing, no web server configuration needed

**⚠️ Warning**: This is for development only. Not suitable for production.

#### Steps:

1. **Follow steps 1-3 from Option 3**

2. **Start PHP built-in server**
   ```bash
   php -S localhost:8000 -t public
   ```

3. **Run migrations** (in another terminal)
   ```bash
   php yii migrate
   ```

4. **Access application**
   ```
   http://localhost:8000
   ```

---

## 📁 Project Structure

```
PROJECT/
├── config/                      # Configuration files
│   ├── console.php             # Console application config
│   ├── env.php                 # Environment loader
│   └── web.php                 # Web application config
├── controllers/                # Controllers (MVC)
│   └── SiteController.php
├── docker/                     # Docker configuration
│   ├── apache/
│   │   └── default.conf        # Apache virtual host
│   └── php/
│       └── Dockerfile          # PHP + Apache image
├── migrations/                 # Database migrations
├── models/                     # Models (MVC)
│   └── User.php
├── public/                     # Web root (document root)
│   ├── .htaccess              # URL rewriting
│   └── index.php              # Application entry point
├── runtime/                    # Runtime files (cache, logs)
├── views/                      # Views (MVC)
│   ├── layouts/
│   │   └── main.php          # Main layout
│   └── site/
│       ├── index.php          # Home page
│       └── error.php          # Error page
├── vendor/                     # Composer dependencies
├── .env                        # Environment variables (not in git)
├── .env.example                # Environment template
├── .gitignore                  # Git ignore rules
├── composer.json               # Composer configuration
├── docker-compose.yaml         # Docker full stack
├── docker-compose.web-only.yaml # Docker web only
├── yii                         # Console entry point (Unix)
└── yii.bat                     # Console entry point (Windows)
```

## 🛠️ Common Tasks

### Database Migrations

```bash
# Create a new migration
php yii migrate/create create_products_table

# Apply migrations
php yii migrate

# Rollback last migration
php yii migrate/down

# Show migration history
php yii migrate/history
```

**Note**: If using Docker, you may need to run commands inside the container:
```bash
docker-compose exec web php yii migrate
```

### Creating Controllers

Create a new file in `controllers/`:

```php
<?php

namespace app\controllers;

use yii\web\Controller;

class MyController extends Controller
{
    public function actionIndex()
    {
        return $this->render('index');
    }
}
```

Create corresponding view in `views/my/index.php`.

### Creating Models

```php
<?php

namespace app\models;

use yii\db\ActiveRecord;

class Product extends ActiveRecord
{
    public static function tableName()
    {
        return 'products';
    }
}
```

### Console Commands

```bash
# List all available commands
php yii

# Clear cache
php yii cache/flush

# Run specific command
php yii migrate
```

## 🔧 Configuration

### Environment Variables

All configuration is done through `.env` file. Copy `.env.example` to `.env` and customize:

- **Database settings**: Connection details for your MySQL server
- **Application settings**: App ID, name, debug mode
- **Security**: Cookie validation key (generate a secure random string)

### Web Application Config

Edit `config/web.php` to customize:
- Application components
- URL routing rules
- Cache configuration
- Log targets
- Custom parameters

### Console Application Config

Edit `config/console.php` for console-specific settings.

## 🐳 Docker Commands Reference

### Full Stack (docker-compose.yaml)

```bash
# Start all services
docker-compose up -d

# View logs
docker-compose logs -f

# Stop services
docker-compose down

# Rebuild containers
docker-compose up -d --build

# Execute command in web container
docker-compose exec web php yii migrate
```

### Web Only (docker-compose.web-only.yaml)

```bash
# Start web service only
docker-compose -f docker-compose.web-only.yaml up -d

# Stop web service
docker-compose -f docker-compose.web-only.yaml down
```

## 🔒 Security

- **CSRF Protection**: Enabled by default
- **Cookie Validation**: Secure cookie handling
- **Session Security**: HTTP-only cookies
- **Environment Variables**: Sensitive data in `.env` (not in git)

**⚠️ Important**: 
- Always change `COOKIE_VALIDATION_KEY` in production!
- Never commit `.env` file to version control
- Use strong passwords for database in production

## 🧪 Testing Database Connection

The home page (`/`) displays database connection status. You can also test programmatically:

```php
try {
    \Yii::$app->db->open();
    echo "Database connected!";
} catch (\Exception $e) {
    echo "Error: " . $e->getMessage();
}
```

## 📝 Code Standards

- PSR-4 autoloading
- PSR-2 coding style (4 spaces indentation)
- PHPDoc comments for classes and methods
- Type hints where applicable
- Strict types declaration

## 🐛 Troubleshooting

### Database Connection Issues

**Docker Full Stack:**
- Ensure `DB_HOST=mysql` in `.env`
- Check if MySQL container is running: `docker-compose ps`
- View MySQL logs: `docker-compose logs mysql`

**Docker Web Only:**
- On Windows/Mac: Use `DB_HOST=host.docker.internal`
- On Linux: Use your host IP or `172.17.0.1`
- Ensure MySQL allows connections from Docker network

**Native Setup:**
- Verify MySQL is running
- Check credentials in `.env`
- Test connection: `mysql -u root -p -h 127.0.0.1`

### Permission Issues

```bash
# Ensure runtime directory is writable
chmod -R 777 runtime
chmod -R 777 public/assets  # if using asset manager
```

### Pretty URLs Not Working

- **Apache**: Ensure `mod_rewrite` is enabled
- **Docker**: Already configured in Dockerfile
- **Nginx**: Check configuration (see Option 3)

## 📚 Learning Resources

- [Yii2 Official Guide](https://www.yiiframework.com/doc/guide/2.0/en)
- [Yii2 API Documentation](https://www.yiiframework.com/doc/api/2.0)
- [Yii2 Forum](https://forum.yiiframework.com/)

## 🤝 Contributing

This is a template project. Feel free to:
- Fork and customize for your needs
- Submit issues and suggestions
- Improve documentation

## 📄 License

MIT License - feel free to use this template for any project.

---

## 🎯 Quick Reference

| Task | Command |
|------|---------|
| Install dependencies | `composer install` |
| Start Docker (full) | `docker-compose up -d` |
| Start Docker (web only) | `docker-compose -f docker-compose.web-only.yaml up -d` |
| Run migrations | `php yii migrate` |
| Start PHP server | `php -S localhost:8000 -t public` |
| Clear cache | `php yii cache/flush` |

---

**Happy Coding!** 🎉
