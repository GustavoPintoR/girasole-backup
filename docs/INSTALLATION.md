# Installation & Setup Guide

This guide will help you set up the Girasole development environment from scratch.

## Prerequisites

- **Docker & Docker Compose**: Container management
- **Node.js 18+**: For frontend build tools
- **Git**: Version control

## Initial Setup

### 1. Clone the Repository

```bash
git clone git@github.com:SoipoServices/girasole.git
cd girasole
```

### 2. Install Dependencies

**PHP Dependencies:**
```bash
composer install
```

**JavaScript Dependencies:**
```bash
npm install
```

### 3. Environment Configuration

**Option A: Decrypt Existing Environment (Recommended)**

Obtain the decryption key from your team lead and run:
```bash
php artisan env:decrypt --key=YOUR_DECRYPTION_KEY
```

**Option B: Manual Configuration**

If you don't have access to the encrypted environment:
```bash
cp .env.example .env
php artisan key:generate
```

Then configure your `.env` file with the appropriate database and service credentials.

### 4. Start Development Environment

```bash
docker-compose up -d
```

This will start:
- PostgreSQL database with PostGIS extension
- Redis for caching and queues
- Other required services

### 5. Database Setup

**Run Migrations and Seeders:**
```bash
php artisan migrate --seed
```

This creates the database structure and adds:
- Default user accounts (test@example.com / admin@example.com)
- Initial system data
- Sample geographic data

### 6. Import Geographic Data

Import Italian geographic data (run these commands sequentially):

```bash
php artisan import:regions         # Import Italian regions
php artisan import:provinces       # Import Italian provinces  
php artisan import:cities          # Import Italian cities
php artisan import:postal-codes    # Import postal code data
```

**Alternative: Import All at Once**
```bash
php artisan import:all             # Import all geographic data sequentially
```

### 7. Start Development Services

**Option A: Use Composer Script (Recommended)**
```bash
composer run dev
```

This starts all development services concurrently:
- PHP development server (http://localhost:8000)
- Queue worker for background jobs
- Log monitoring (Pail)
- Vite development server with HMR

**Option B: Start Services Individually**
```bash
# Terminal 1: Start web server
php artisan serve

# Terminal 2: Start queue worker
php artisan queue:listen

# Terminal 3: Start frontend build
npm run dev

# Terminal 4: Monitor logs
php artisan pail
```

## Environment Configuration

### Updating Environment Files

If you modify the `.env` file, encrypt it for version control:

```bash
php artisan env:encrypt --force --key=YOUR_ENCRYPTION_KEY
```

**Important**: Always encrypt environment changes before committing to maintain security.

### Environment Variables

Key environment variables to configure:

```env
# Application
APP_ENV=local
APP_DEBUG=true
APP_URL=http://localhost:8000

# Database
DB_CONNECTION=pgsql
DB_HOST=localhost
DB_PORT=5432
DB_DATABASE=girasole
DB_USERNAME=your_username
DB_PASSWORD=your_password

# Email (Resend)
MAIL_MAILER=resend
RESEND_KEY=your_resend_api_key

# Stripe (for subscriptions)
STRIPE_KEY=your_stripe_key
STRIPE_SECRET=your_stripe_secret
STRIPE_WEBHOOK_SECRET= stripe_webhook_secret

# Mapbox (for mapping)
MAPBOX_ACCESS_TOKEN=your_mapbox_token
```

## Verification

### Test Your Installation

1. **Check Web Server**: Visit `http://localhost:8000`
2. **Test Database Connection**: 
   ```bash
   php artisan db:show
   ```
3. **Run Test Suite**:
   ```bash
   php artisan test
   ```
4. **Check Email Configuration**:
   ```bash
   php artisan girasole:mailer:test your-email@example.com
   ```

### Default Accounts

**Standard User:**
- Email: `test@example.com`
- Password: `password`

**Administrator:**
- Email: `admin@example.com`
- Password: `password`

⚠️ **Security Note**: Change these passwords immediately in production environments.

## Troubleshooting

### Common Issues

**Port Already in Use:**
```bash
# Check what's using port 8000
lsof -i :8000

# Use different port
php artisan serve --port=8001
```

**Database Connection Issues:**
```bash
# Check database status
docker-compose ps

# Restart database
docker-compose restart db
```

**Permission Issues:**
```bash
# Fix storage permissions
chmod -R 775 storage bootstrap/cache
```

**Frontend Build Issues:**
```bash
# Clear node modules and reinstall
rm -rf node_modules package-lock.json
npm install
```

### Reset Development Environment

If you need to start fresh:

```bash
# Stop all services
docker-compose down

# Clear all data
docker-compose down -v

# Remove generated files
rm -rf vendor node_modules

# Reinstall everything
composer install
npm install
docker-compose up -d
php artisan migrate:fresh --seed
```

## Next Steps

- Read the [Commands Reference](COMMANDS.md) for available development commands
- Check the [Features Overview](FEATURES.md) to understand application capabilities
- Review [Development Guidelines](DEVELOPMENT.md) for coding standards
- See [Deployment Guide](DEPLOYMENT.md) for production setup