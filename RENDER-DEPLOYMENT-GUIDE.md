# Render Docker Deployment Guide for Laravel

## 1. Project Structure Summary
Your Laravel project is now configured for Render Docker deployment with the following files:
- `.dockerignore` - Excludes unnecessary folders from Docker build
- `Dockerfile` - Uses `richarvey/nginx-php-fpm:3.1.6` base image
- `scripts/00-laravel-deploy.sh` - Deployment script for Laravel optimization
- `composer.json` - Already configured with Laravel

## 2. Dashboard Render Configuration

### Step-by-step setup:

1. **Create new Web Service on Render:**
   - Go to [dashboard.render.com](https://dashboard.render.com)
   - Click "New +" → "Web Service"
   - Select "Docker" as the environment

2. **Repository connection:**
   - Connect your GitHub/GitLab/Bitbucket repository
   - Branch: `main` or `master`

3. **Service settings:**
   - **Name**: `fingerprint-regist` (or your preferred name)
   - **Region**: Choose closest to your users (e.g., `us-east` or `eu`)
   - **Branch**: `main`
   - **Build Command**: (leave empty or use `docker build -t ${SERVICE_NAME}:${SERVICE_ID} .`)
   - **Start Command**: `php-fpm`
   - **Instance Type**: Free or Paid depending on your needs

4. **Environment Variables** (Critical - add these in the "Environment" section):

   **Required:**
   - `APP_ENV=production`
   - `APP_DEBUG=false`
   - `APP_KEY=<your-app-key>` - Generate using `php artisan key:generate` locally or in a one-off container
   - `DB_CONNECTION=mysql` (or your database type)
   - `DB_HOST=<render-postgres-or-mysql-host>`
   - `DB_PORT=3306` (or your database port)
   - `DB_DATABASE=<your-database-name>`
   - `DB_USERNAME=<your-database-user>`
   - `DB_PASSWORD=<your-database-password>`

   **Optional (if using):**
   - `CACHE_STORE=database`
   - `SESSION_DRIVER=database`
   - `QUEUE_CONNECTION=database`
   - `REDIS_HOST`
   - `REDIS_PASSWORD`
   - `REDIS_PORT=6379`

   **Google OAuth (if needed):**
   - `GOOGLE_CLIENT_ID`
   - `GOOGLE_CLIENT_SECRET`
   - `LINKEDIN_CLIENT_ID`
   - `LINKEDIN_CLIENT_SECRET`
   - `FACEBOOK_CLIENT_ID`
   - `FACEBOOK_CLIENT_SECRET`

5. **Domain (optional):**
   - Add custom domain in the "Domains" tab
   - SSL certificates are automatically provisioned by Render

## 3. OpenCode CLI Remote Connection

### How to connect your local terminal to the Render Web Service:

#### Prerequisites:
- OpenCode CLI installed globally: `npm install -g @opencode/cli`
- Render API key from your dashboard

#### Steps:

1. **Authenticate with Render:**
   ```bash
   opencode attach --help
   # Or login first:
   opencode render login
   ```

2. **Attach to your Render service:**
   ```bash
   # Method 1: Using service name
   opencode attach --render fingerprint-regist
   
   # Method 2: Using service ID
   opencode attach --render <service-id-from-dashboard>
   ```

3. **What happens:**
   - OpenCode will connect to your Render Web Service
   - You'll get a remote terminal inside the running container
   - You can run Artisan commands, check logs, debug issues
   - Changes are made remotely (not affecting your local state directly)

4. **Common usage examples:**
   ```bash
   # Check running processes
   opencode attach --render fingerprint-regist -- php-fpm -v
   
   # Run Artisan commands remotely
   opencode attach --render fingerprint-regist -- php artisan optimize
   
   # Tail logs
   opencode attach --render fingerprint-regist -- tail -f /var/log/laravel.log
   
   # Check environment
   opencode attach --render fingerprint-regist -- env | grep APP_
   ```

5. **Disconnect:**
   - Simply close the terminal or type `exit`
   - Or press `Ctrl+D`

### Troubleshooting:

- **Connection refused:** Ensure your service is running and the start command is `php-fpm`
- **Permission errors:** The Dockerfile sets proper ownership of `/var/www/html`
- **Database connection issues:** Verify the environment variables match your Render database
- **APP_KEY missing:** Generate it first using: `opencode attach --render <service> -- php artisan key:generate`

## 4. Deployment Flow

1. Push code to your Git repository
2. Render automatically detects the Dockerfile and builds the image
3. Render starts the container with `php-fpm`
4. On first deployment, the deployment script runs (if configured in build hook)
5. Subsequent deployments use cached layers for faster builds

## 5. Notes & Tips

- The `Dockerfile` already includes `composer install --no-dev` and cache commands
- For continuous deployment, ensure your GitHub branch is connected to Render
- Render will automatically redeploy when you push new commits
- Use the OpenCode CLI `opencode attach` for remote debugging instead of SSH
- The `.dockerignore` ensures fast builds by excluding `node_modules`, `vendor`, and large files