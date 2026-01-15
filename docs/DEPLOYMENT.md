# Production Deployment Guide

This guide covers the complete process for deploying Girasole to production environments, including server setup, security configuration, and maintenance procedures.

## Server Requirements

### System Requirements

**Minimum Specifications:**
- **CPU**: 2 cores (4 cores recommended)
- **RAM**: 4GB (8GB recommended for larger deployments)
- **Storage**: 50GB SSD (100GB+ recommended)
- **Network**: Stable internet connection with SSL certificate
- **OS**: Ubuntu 20.04 LTS or higher

**Required Software:**
- Docker & Docker Compose (latest stable versions)
- Git with SSH key access to GitHub
- SSL certificate for HTTPS
- Domain name with DNS configuration

### Security Prerequisites

**Firewall Configuration:**
```bash
# Basic firewall setup
sudo ufw default deny incoming
sudo ufw default allow outgoing
sudo ufw allow ssh
sudo ufw allow 80/tcp
sudo ufw allow 443/tcp
sudo ufw enable
```

**SSH Hardening:**
```bash
# Edit SSH configuration
sudo nano /etc/ssh/sshd_config

# Recommended settings:
# Port 22 (or custom port)
# PermitRootLogin no
# PasswordAuthentication no
# PubkeyAuthentication yes
# AllowUsers your-deploy-user

sudo systemctl restart ssh
```

## GitHub SSH Key Setup

### Generate SSH Key for Deployment

```bash
# Generate SSH key pair on server
ssh-keygen -t ed25519 -C "deployment@your-domain.com" -f ~/.ssh/github_deploy

# Display public key for GitHub
cat ~/.ssh/github_deploy.pub
```

### Configure GitHub Access

1. **Add Deploy Key to GitHub**:
   - Go to repository Settings → Deploy keys
   - Add new deploy key with read access
   - Paste the public key content

2. **Configure SSH Client**:
   ```bash
   # Create SSH config
   nano ~/.ssh/config
   
   # Add configuration
   Host github.com
       HostName github.com
       User git
       IdentityFile ~/.ssh/github_deploy
       IdentitiesOnly yes
   ```

3. **Test Connection**:
   ```bash
   ssh -T git@github.com
   # Should return: "Hi SoipoServices/girasole! You've successfully authenticated..."
   ```

## GitHub Actions Setup

### Required Repository Secrets

Configure these secrets in GitHub repository settings:

```bash
# Server connection details
HOST=your-server-ip-or-domain
USERNAME=deploy-user
KEY=-----BEGIN OPENSSH PRIVATE KEY-----
    (your private SSH key content)
    -----END OPENSSH PRIVATE KEY-----
PORT=22

# Application secrets
APP_KEY=base64:your-laravel-app-key
DB_PASSWORD=your-secure-database-password
ENCRYPTION_KEY=base64:your-encryption-key
```

### Deployment Workflow

The GitHub Actions workflow (`.github/workflows/deploy.yml`) handles:

1. **Code Quality Checks**
   - Run test suite
   - Code style validation
   - Security scanning

2. **Build Process**
   - Install dependencies
   - Compile frontend assets
   - Optimize for production

3. **Deployment Steps**
   - Deploy to server via SSH
   - Run database migrations
   - Clear and rebuild caches
   - Restart services

## Initial Server Setup

### 1. Clone Repository

```bash
# Create application directory
sudo mkdir -p /var/www/girasole
sudo chown $USER:$USER /var/www/girasole

# Clone repository
cd /var/www
git clone git@github.com:SoipoServices/girasole.git
cd girasole
```

### 2. Environment Configuration

```bash
# Decrypt production environment
php artisan env:decrypt --key=base64:YOUR_ENCRYPTION_KEY --env=production

# Or create environment manually
cp .env.example .env.production
```

**Production Environment Variables:**
```env
APP_ENV=production
APP_DEBUG=false
APP_URL=https://your-domain.com

DB_CONNECTION=pgsql
DB_HOST=db
DB_PORT=5432
DB_DATABASE=girasole_production
DB_USERNAME=girasole_user
DB_PASSWORD=your-secure-password

CACHE_DRIVER=redis
SESSION_DRIVER=redis
QUEUE_CONNECTION=redis

REDIS_HOST=redis
REDIS_PASSWORD=your-redis-password
REDIS_PORT=6379

MAIL_MAILER=resend
RESEND_KEY=your-resend-api-key

STRIPE_KEY=pk_live_your_stripe_key
STRIPE_SECRET=sk_live_your_stripe_secret
STRIPE_WEBHOOK_SECRET=whsec_your_webhook_secret

MAPBOX_ACCESS_TOKEN=your-mapbox-token

PULSE_ENABLED=true
PULSE_INGEST=redis
```

### 3. Docker Configuration

**Production Docker Compose (`docker-compose.prod.yml`):**
```yaml
version: '3.8'

services:
  app:
    build:
      context: .
      dockerfile: Dockerfile.prod
    container_name: girasole_app
    restart: unless-stopped
    volumes:
      - ./storage:/var/www/storage
      - ./bootstrap/cache:/var/www/bootstrap/cache
    environment:
      - APP_ENV=production
    networks:
      - girasole_network
    depends_on:
      - db
      - redis

  nginx:
    image: nginx:alpine
    container_name: girasole_nginx
    restart: unless-stopped
    ports:
      - "80:80"
      - "443:443"
    volumes:
      - ./docker/nginx/prod.conf:/etc/nginx/conf.d/default.conf
      - ./public:/var/www/public
      - ./storage/app/public:/var/www/storage/app/public
      - /etc/letsencrypt:/etc/letsencrypt:ro
    networks:
      - girasole_network
    depends_on:
      - app

  db:
    image: postgis/postgis:15-3.3
    container_name: girasole_db
    restart: unless-stopped
    environment:
      POSTGRES_DB: girasole_production
      POSTGRES_USER: girasole_user
      POSTGRES_PASSWORD: ${DB_PASSWORD}
      PGDATA: /var/lib/postgresql/data/pgdata
    volumes:
      - postgres_data:/var/lib/postgresql/data
      - ./database/backups:/backups
    networks:
      - girasole_network

  redis:
    image: redis:7-alpine
    container_name: girasole_redis
    restart: unless-stopped
    command: redis-server --requirepass ${REDIS_PASSWORD}
    volumes:
      - redis_data:/data
    networks:
      - girasole_network

volumes:
  postgres_data:
  redis_data:

networks:
  girasole_network:
    driver: bridge
```

### 4. SSL Certificate Setup

**Using Let's Encrypt (Certbot):**
```bash
# Install Certbot
sudo apt update
sudo apt install certbot python3-certbot-nginx

# Obtain certificate
sudo certbot --nginx -d your-domain.com -d www.your-domain.com

# Test auto-renewal
sudo certbot renew --dry-run
```

**Nginx SSL Configuration:**
```nginx
server {
    listen 80;
    server_name your-domain.com www.your-domain.com;
    return 301 https://$server_name$request_uri;
}

server {
    listen 443 ssl http2;
    server_name your-domain.com www.your-domain.com;
    
    ssl_certificate /etc/letsencrypt/live/your-domain.com/fullchain.pem;
    ssl_certificate_key /etc/letsencrypt/live/your-domain.com/privkey.pem;
    
    # SSL Security Headers
    ssl_protocols TLSv1.2 TLSv1.3;
    ssl_ciphers HIGH:!aNULL:!MD5;
    ssl_prefer_server_ciphers on;
    
    # Security Headers
    add_header X-Frame-Options "SAMEORIGIN" always;
    add_header X-XSS-Protection "1; mode=block" always;
    add_header X-Content-Type-Options "nosniff" always;
    add_header Strict-Transport-Security "max-age=63072000" always;
    
    root /var/www/public;
    index index.php index.html;
    
    location / {
        try_files $uri $uri/ /index.php?$query_string;
    }
    
    location ~ \.php$ {
        fastcgi_pass app:9000;
        fastcgi_index index.php;
        fastcgi_param SCRIPT_FILENAME $realpath_root$fastcgi_script_name;
        include fastcgi_params;
    }
    
    location ~ /\.(?!well-known).* {
        deny all;
    }
}
```

## Deployment Process

### 1. Build and Start Services

```bash
# Build production images
docker-compose -f docker-compose.prod.yml build

# Start services
docker-compose -f docker-compose.prod.yml up -d

# Install dependencies (production optimized)
docker-compose -f docker-compose.prod.yml exec app composer install --no-dev --optimize-autoloader

# Generate application key
docker-compose -f docker-compose.prod.yml exec app php artisan key:generate --force
```

### 2. Database Setup

```bash
# Run migrations
docker-compose -f docker-compose.prod.yml exec app php artisan migrate --force

# Import geographic data
docker-compose -f docker-compose.prod.yml exec app php artisan import:all

# Create admin user (if needed)
docker-compose -f docker-compose.prod.yml exec app php artisan tinker
# User::create(['name' => 'Admin', 'email' => 'admin@your-domain.com', ...])
```

### 3. Frontend Assets

```bash
# Install Node dependencies
docker-compose -f docker-compose.prod.yml exec app npm ci

# Build production assets
docker-compose -f docker-compose.prod.yml exec app npm run build

# Create storage symlink
docker-compose -f docker-compose.prod.yml exec app php artisan storage:link
```

### 4. Optimization

```bash
# Cache configuration and routes
docker-compose -f docker-compose.prod.yml exec app php artisan config:cache
docker-compose -f docker-compose.prod.yml exec app php artisan route:cache
docker-compose -f docker-compose.prod.yml exec app php artisan view:cache

# Optimize composer autoloader
docker-compose -f docker-compose.prod.yml exec app composer dump-autoload --optimize
```

## Production Monitoring

### 1. Laravel Pulse Setup

```bash
# Start Pulse worker
docker-compose -f docker-compose.prod.yml exec -d app php artisan pulse:work

# Configure Pulse dashboard access
# Add to routes/web.php:
Route::get('/pulse', function () {
    return redirect('/admin/pulse');
})->middleware(['auth', 'can:view-pulse']);
```

### 2. Log Monitoring

```bash
# View application logs
docker-compose -f docker-compose.prod.yml logs -f app

# Monitor with Pail
docker-compose -f docker-compose.prod.yml exec app php artisan pail
```

### 3. Health Checks

```bash
# Check application status
curl -f https://your-domain.com/health || exit 1

# Database connection check
docker-compose -f docker-compose.prod.yml exec app php artisan db:show

# Queue status
docker-compose -f docker-compose.prod.yml exec app php artisan queue:monitor
```

## Backup & Recovery

### Database Backups

```bash
# Create backup script
cat > /var/www/girasole/backup.sh << 'EOF'
#!/bin/bash
BACKUP_DIR="/var/www/girasole/database/backups"
TIMESTAMP=$(date +%Y%m%d_%H%M%S)
BACKUP_FILE="girasole_${TIMESTAMP}.sql"

# Create backup
docker-compose -f docker-compose.prod.yml exec -T db pg_dump -U girasole_user girasole_production > "${BACKUP_DIR}/${BACKUP_FILE}"

# Compress backup
gzip "${BACKUP_DIR}/${BACKUP_FILE}"

# Keep only last 7 days of backups
find "${BACKUP_DIR}" -name "*.sql.gz" -mtime +7 -delete

echo "Backup completed: ${BACKUP_FILE}.gz"
EOF

chmod +x /var/www/girasole/backup.sh
```

**Automated Backups with Cron:**
```bash
# Add to crontab
crontab -e

# Daily backup at 2 AM
0 2 * * * /var/www/girasole/backup.sh >> /var/log/girasole_backup.log 2>&1
```

### Application Backups

```bash
# Backup storage directory
tar -czf storage_backup_$(date +%Y%m%d).tar.gz storage/

# Backup .env file (encrypted)
cp .env.encrypted backups/env_$(date +%Y%m%d).encrypted
```

## Maintenance Procedures

### Update Application

```bash
# Pull latest changes
cd /var/www/girasole
git pull origin main

# Update dependencies
docker-compose -f docker-compose.prod.yml exec app composer install --no-dev --optimize-autoloader
docker-compose -f docker-compose.prod.yml exec app npm ci
docker-compose -f docker-compose.prod.yml exec app npm run build

# Run migrations
docker-compose -f docker-compose.prod.yml exec app php artisan migrate --force

# Clear and rebuild caches
docker-compose -f docker-compose.prod.yml exec app php artisan optimize
docker-compose -f docker-compose.prod.yml exec app php artisan config:cache
docker-compose -f docker-compose.prod.yml exec app php artisan route:cache
docker-compose -f docker-compose.prod.yml exec app php artisan view:cache

# Restart services
docker-compose -f docker-compose.prod.yml restart app
```

### Performance Optimization

```bash
# Monitor resource usage
docker stats

# Optimize database
docker-compose -f docker-compose.prod.yml exec db psql -U girasole_user -d girasole_production -c "VACUUM ANALYZE;"

# Clear expired cache entries
docker-compose -f docker-compose.prod.yml exec app php artisan cache:prune-stale-tags

# Monitor queue performance
docker-compose -f docker-compose.prod.yml exec app php artisan queue:monitor
```

### Security Updates

```bash
# Update system packages
sudo apt update && sudo apt upgrade -y

# Update Docker images
docker-compose -f docker-compose.prod.yml pull
docker-compose -f docker-compose.prod.yml up -d

# Update application dependencies
docker-compose -f docker-compose.prod.yml exec app composer audit
docker-compose -f docker-compose.prod.yml exec app composer update
```

## Troubleshooting

### Common Issues

**Database Connection Issues:**
```bash
# Check database status
docker-compose -f docker-compose.prod.yml ps db

# View database logs
docker-compose -f docker-compose.prod.yml logs db

# Test connection
docker-compose -f docker-compose.prod.yml exec app php artisan db:show
```

**Queue Processing Issues:**
```bash
# Check queue worker status
docker-compose -f docker-compose.prod.yml exec app php artisan queue:failed

# Restart queue workers
docker-compose -f docker-compose.prod.yml exec app php artisan queue:restart

# Monitor queue performance
docker-compose -f docker-compose.prod.yml exec app php artisan queue:monitor
```

**Performance Issues:**
```bash
# Check resource usage
docker stats

# Monitor slow queries
docker-compose -f docker-compose.prod.yml exec app php artisan db:monitor

# Clear all caches
docker-compose -f docker-compose.prod.yml exec app php artisan optimize:clear
docker-compose -f docker-compose.prod.yml exec app php artisan optimize
```

### Emergency Procedures

**Rollback Deployment:**
```bash
# Revert to previous commit
git log --oneline -n 10  # Find previous commit
git checkout PREVIOUS_COMMIT_HASH

# Rebuild and restart
docker-compose -f docker-compose.prod.yml build
docker-compose -f docker-compose.prod.yml up -d
```

**Database Recovery:**
```bash
# Restore from backup
gunzip database/backups/girasole_YYYYMMDD_HHMMSS.sql.gz
docker-compose -f docker-compose.prod.yml exec -T db psql -U girasole_user girasole_production < database/backups/girasole_YYYYMMDD_HHMMSS.sql
```

This deployment guide ensures a secure, scalable, and maintainable production environment for Girasole.