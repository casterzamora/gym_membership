# Setup & Getting Started Guide

## System Requirements

### Development Environment
- PHP 8.2+ with these extensions:
  - PDO MySQL
  - OpenSSL
  - Mbstring
  - JSON
  - Tokenizer
  - XML
  - Curl
  - Fileinfo

- MySQL 8.0 or PostgreSQL 14+
- Node.js 18+ (for frontend)
- Git
- Composer 2.0+
- Docker & Docker Compose (optional but recommended)

### Minimum Hardware
- 4 GB RAM
- 2 GB free disk space
- Multi-core processor

---

## Installation Steps

### 1. Clone & Setup Laravel Backend

```bash
# Clone the repository
git clone https://github.com/your-org/gym-management-system.git
cd gym-management-system

# Copy environment file
cp .env.example .env

# Install PHP dependencies
composer install

# Generate application key
php artisan key:generate

# Generate JWT secret (if using JWT)
php artisan jwt:secret
```

### 2. Database Setup

```bash
# Create database
mysql -u root -p -e "CREATE DATABASE gym_attendance;"

# Or using Laravel
php artisan migrate:reset  # If database already exists
php artisan migrate

# Seed initial data (optional)
php artisan db:seed
```

### 3. Environment Configuration

Edit `.env` file:

```env
# App Settings
APP_NAME="Gym Management System"
APP_ENV=local
APP_DEBUG=true
APP_URL=http://localhost:8000

# Database
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=gym_attendance
DB_USERNAME=root
DB_PASSWORD=

# Mail Configuration (optional)
MAIL_MAILER=smtp
MAIL_HOST=smtp.mailtrap.io
MAIL_PORT=465
MAIL_USERNAME=your_email
MAIL_PASSWORD=your_password
MAIL_FROM_ADDRESS=noreply@gym.local

# Redis Cache (optional)
CACHE_DRIVER=redis
QUEUE_CONNECTION=redis
REDIS_HOST=127.0.0.1
REDIS_PASSWORD=null
REDIS_PORT=6379

# File Storage
FILESYSTEM_DISK=local

# API Configuration
SANCTUM_STATEFUL_DOMAINS=localhost:3000,localhost:8000
SESSION_DOMAIN=localhost
```

### 4. Generate Storage Link

```bash
php artisan storage:link
```

This creates a symbolic link from `storage/app/public` to `public/storage` for file access.

### 5. Frontend Setup (React/Vue)

```bash
cd frontend

# Install dependencies
npm install

# Or using yarn
yarn install

# Create environment file
cp .env.example .env.local

# Update with backend URL
# VITE_API_URL=http://localhost:8000/api
```

### 6. Configure CORS

Update `config/cors.php` for frontend domain:

```php
'allowed_origins' => [
    'localhost:3000',
    'localhost:5173', // Vite dev server
],

'allowed_origins_patterns' => [],

'allowed_methods' => ['*'],

'allowed_headers' => ['*'],
```

---

## Running the Application

### Option 1: Traditional Development (Recommended for Learning)

**Terminal 1 - Laravel Backend:**
```bash
php artisan serve
# App runs at http://localhost:8000
```

**Terminal 2 - Frontend:**
```bash
cd frontend
npm run dev
# Frontend runs at http://localhost:5173
```

**Terminal 3 - Redis (if using cache/queue):**
```bash
redis-server
```

### Option 2: Docker Compose

```bash
# Start all services
docker-compose up -d

# View logs
docker-compose logs -f

# Stop services
docker-compose down
```

**Services available:**
- Laravel API: http://localhost:8000
- Frontend: http://localhost:3000
- MySQL: localhost:3306
- Redis: localhost:6379
- Nginx: http://localhost:80

---

## Initial Data Setup

### Create Admin User

```bash
php artisan tinker
```

```php
# In Tinker shell
$user = \App\Models\User::create([
    'name' => 'Admin',
    'email' => 'admin@gym.local',
    'phone' => '+1-555-0000',
    'password' => Hash::make('password'),
    'role' => 'admin'
]);

$user->tokens()->create([
    'name' => 'admin-token',
    'abilities' => ['*'],
]);
```

### Create Sample Data

```bash
# Using seeders
php artisan db:seed --class=MembershipPlanSeeder
php artisan db:seed --class=AreaSeeder
php artisan db:seed --class=TrainerSeeder
```

---

## Testing

### Run All Tests

```bash
# Run PHPUnit tests
php artisan test

# Run with verbose output
php artisan test --verbose

# Run specific test file
php artisan test tests/Feature/AuthTest.php

# Run with coverage
php artisan test --coverage
```

### Test Database

Create separate test database:

```bash
mysql -u root -p -e "CREATE DATABASE gym_attendance_test;"

# Or use SQLite for tests (configured in phpunit.xml)
```

---

## API Testing

### Using Postman

1. Import `/documentation/postman-collection.json`
2. Set environment variables:
   - `{{ base_url }}` = http://localhost:8000/api
   - `{{ token }}` = Your Bearer token

### Using Laravel Tinker

```bash
php artisan tinker

# Test API call
\Http::post(config('app.url') . '/api/auth/login', [
    'email' => 'user@example.com',
    'password' => 'password'
])->json();
```

---

## Debugging

### Enable Debug Mode

```env
APP_DEBUG=true
```

### View Logs

```bash
# Watch real-time logs
tail -f storage/logs/laravel.log

# Or in another terminal
php artisan logs
```

### Database Queries

Enable query logging in `config/database.php`:

```php
'connections' => [
    'mysql' => [
        // ...
        'options' => [
            PDO::MYSQL_ATTR_INIT_COMMAND => "SET sql_mode='STRICT_TRANS_TABLES'",
        ],
    ]
]
```

Then in your code:
```php
DB::listen(function($query) {
    \Log::info($query->sql, $query->bindings);
});
```

---

## Common Issues & Solutions

### 1. "Connection refused" to MySQL

**Solution:**
```bash
# Check MySQL status
mysql -u root -p

# Or start MySQL service
# On Windows: net start MySQL80
# On Mac: brew services start mysql
# On Linux: sudo systemctl start mysql
```

### 2. "SQLSTATE[28000]" Authentication Error

**Solution:**
- Verify DB credentials in `.env`
- Ensure MySQL user exists and has proper privileges:
```sql
GRANT ALL PRIVILEGES ON gym_attendance.* TO 'root'@'127.0.0.1' IDENTIFIED BY 'password';
FLUSH PRIVILEGES;
```

### 3. "No application encryption key has been specified"

**Solution:**
```bash
php artisan key:generate
```

### 4. CORS Errors

**Solution:**
- Verify frontend URL in `config/cors.php`
- Check `CORS_ALLOWED_ORIGINS` in `.env`
- Ensure API returns correct headers

### 5. Token Expired

**Solution:**
```bash
# Refresh token endpoint
POST /api/auth/refresh

# With Bearer token in Authorization header
```

### 6. File Upload Issues

**Solution:**
```bash
# Check storage permissions
chmod -R 775 storage

# Create storage link
php artisan storage:link
```

---

## Performance Optimization

### Enable Caching

```env
CACHE_DRIVER=redis
```

Clear cache when needed:
```bash
php artisan cache:clear
php artisan config:cache
php artisan route:cache
php artisan view:cache
```

### Database Optimization

```bash
# Create indexes
php artisan migrate

# Analyze slow queries
mysql -u root -p gym_attendance -e "ANALYZE TABLE members, classes, attendance;"
```

### Frontend Optimization

```bash
# Build for production
npm run build

# Check bundle size
npm run build -- --analyze
```

---

## Version Control Workflow

```bash
# Create feature branch
git checkout -b feature/member-management

# Make changes and commit
git add .
git commit -m "feat: add member management endpoints"

# Push to remote
git push origin feature/member-management

# Create pull request on GitHub/GitLab

# After merge, pull changes
git checkout main
git pull origin main
```

---

## Environment Files Reference

### .env (Production)
```env
APP_ENV=production
APP_DEBUG=false
APP_URL=https://gym.example.com

DB_HOST=db.example.com
DB_DATABASE=gym_prod
DB_USERNAME=gym_user

CACHE_DRIVER=redis
QUEUE_CONNECTION=redis

MAIL_MAILER=smtp
MAIL_HOST=smtp.gmail.com
MAIL_PORT=587
```

### .env.testing (Testing)
```env
APP_ENV=testing
APP_DEBUG=true

DB_CONNECTION=sqlite
DB_DATABASE=:memory:

CACHE_DRIVER=array
QUEUE_CONNECTION=sync
```

---

## Next Steps

1. **Understand the Architecture**
   - Read [ARCHITECTURE.md](ARCHITECTURE.md)
   - Review database schema in [DATABASE_SCHEMA.md](DATABASE_SCHEMA.md)

2. **Create Models**
   - Start with User and Member models
   - Define relationships
   - Add scopes and accessors

3. **Build Controllers**
   - Implement authentication controller
   - Create resource controllers for each entity

4. **Write Tests**
   - Unit tests for models
   - Feature tests for endpoints

5. **Build Frontend**
   - Create authentication pages
   - Build member dashboard
   - Implement class management UI

6. **Deploy**
   - Set up staging environment
   - Configure production deployment
   - Set up monitoring and alerts

---

## Additional Resources

- [Laravel Documentation](https://laravel.com/docs)
- [React Documentation](https://react.dev) or [Vue Documentation](https://vuejs.org)
- [MySQL Documentation](https://dev.mysql.com/doc)
- [RESTful API Best Practices](https://restfulapi.net)
- [API Security](https://owasp.org/www-project-api-security)

---

## Support Channels

- **Documentation**: See `/documentation` folder
- **Issues**: Create issue on GitHub/GitLab
- **Discussions**: Use Discussions tab for questions
- **Email**: support@gym.local

---

## Checklist for First Run

- [ ] PHP 8.2+ installed
- [ ] MySQL 8.0+ running
- [ ] Node.js 18+ installed
- [ ] Composer installed
- [ ] Repository cloned
- [ ] `.env` file created and configured
- [ ] `php artisan key:generate` executed
- [ ] Database migrations run
- [ ] Storage link created
- [ ] Frontend dependencies installed
- [ ] Backend server running (`php artisan serve`)
- [ ] Frontend dev server running (`npm run dev`)
- [ ] Can access http://localhost:5173
- [ ] Can access http://localhost:8000/api
- [ ] Sample data seeded (optional)

