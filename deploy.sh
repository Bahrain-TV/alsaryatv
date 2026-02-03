#!/bin/bash

# Configuration

if [ -z "$APP_ENV" ]; then
    APP_ENV="staging"
else
    APP_ENV="production"
fi

# Configuration variables
SERVER="root@h6.doy.tech"
APP_DIR="/home/alsarya.tv/public_html"
ART_CMD="php artisan"
APP_USER="alsar4210"
DISCORD_WEBHOOK="https://discord.com/api/webhooks/1248966065417883659/hAnbGrEOLw9fWF6UCObAuuXHzW6ZM5I1babbBC4rBAdbUAB6YcfHqHhxZXEU4LYIyZp2"
SUDO_PREFIX="sudo -u $APP_USER"

# Version management
VERSION_FILE="$APP_DIR/version.txt"
if [ -f "$VERSION_FILE" ]; then
    CURRENT_VERSION=$(cat "$VERSION_FILE")
else
    CURRENT_VERSION="1.0.0"
fi

# Function to increment version
increment_version() {
    local version=$1
    local major minor patch

    IFS='.' read -r major minor patch <<< "$version"

    # Increment patch version
    patch=$((patch + 1))

    # Reset patch and increment minor if patch >= 100 (optional convention)
    if [ $patch -ge 100 ]; then
        patch=0
        minor=$((minor + 1))

        # Reset minor and increment major if minor >= 100
        if [ $minor -ge 100 ]; then
            minor=0
            major=$((major + 1))
        fi
    fi

    echo "${major}.${minor}.${patch}"
}

# Function to send a message to Discord
send_discord_message() {
    local message="$1"
    curl -s -H "Content-Type: application/json" -X POST -d "{\"content\": \"$message\"}" "$DISCORD_WEBHOOK"
}

# Function to check .env file exists
check_env_file() {
    local env_file="$APP_DIR/.env"

    if [ ! -f "$env_file" ]; then
        echo "ERROR: .env file not found at $env_file"
        send_discord_message "❌ Deployment failed: .env file is missing at $env_file"
        exit 1
    fi

    echo "✓ .env file exists"
    return 0
}

# Function to check database exists and is accessible
check_database() {
    # Load environment variables from .env
    set -a
    source "$APP_DIR/.env"
    set +a

    local db_connection="${DB_CONNECTION:-sqlite}"

    if [ "$db_connection" = "sqlite" ]; then
        # For SQLite, check if database file exists or can be created
        local db_path="${APP_DIR}/database/${DB_DATABASE:-database.sqlite}"

        if [ ! -f "$db_path" ]; then
            echo "⚠ Database file does not exist: $db_path (will be created during migration)"
        else
            echo "✓ SQLite database file exists at $db_path"
        fi
    else
        # For other database connections, verify connection can be established
        echo "→ Checking database connectivity for $db_connection..."

        # Run a simple Laravel artisan command to test database connection
        if ! $SUDO_PREFIX $ART_CMD db:show > /dev/null 2>&1; then
            echo "ERROR: Cannot connect to $db_connection database"
            send_discord_message "❌ Deployment failed: Cannot connect to $db_connection database"
            exit 1
        fi

        echo "✓ Database connection successful"
    fi

    return 0
}

# Function to check and run migrations
check_migrations() {
    echo "→ Checking migration status..."

    # Check if there are pending migrations
    local pending_migrations=$($SUDO_PREFIX $ART_CMD migrate:status 2>&1 | grep "Pending" || true)

    if [ -n "$pending_migrations" ]; then
        echo "⚠ Pending migrations detected. They will be run during deployment."
    else
        echo "✓ All migrations are up to date (or no migrations present)"
    fi

    return 0
}

# check if the folder exists
if [ ! -d "$APP_DIR" ]; then
    echo "ERROR: The folder $APP_DIR does not exist."
    send_discord_message "❌ Deployment failed: Application directory not found at $APP_DIR"
    exit 1
else
    cd "$APP_DIR" || exit 1
fi

# Run pre-deployment checks
echo ""
echo "=========================================="
echo "Running pre-deployment checks..."
echo "=========================================="
check_env_file
check_database
check_migrations
echo "=========================================="
echo "All pre-deployment checks passed!"
echo "=========================================="
echo ""


# Function to deploy the application
deploy() {
    echo ""
    echo "=========================================="
    echo "Starting application deployment..."
    echo "=========================================="

    send_discord_message "🚀 Deploying the application to $APP_ENV (Version: $CURRENT_VERSION)..."

    # Put application into maintenance mode with custom down page
    echo "→ Putting application in maintenance mode..."
    $ART_CMD down --render=down || {
        send_discord_message "⚠ Warning: Could not put application into maintenance mode"
    }

    # Bump version
    echo "→ Bumping version..."
    NEW_VERSION=$(increment_version "$CURRENT_VERSION")
    echo "$NEW_VERSION" > "$VERSION_FILE"
    chown $APP_USER:$APP_USER "$VERSION_FILE"
    send_discord_message "📦 Version bumped from $CURRENT_VERSION to $NEW_VERSION"

    # Fix permissions
    echo "→ Fixing permissions..."
    chmod -R 755 $APP_DIR/storage
    chmod -R 755 $APP_DIR/bootstrap/cache
    chmod -R 755 $APP_DIR/public
    chown -R $APP_USER:$APP_USER $APP_DIR/storage
    chown -R $APP_USER:$APP_USER $APP_DIR/bootstrap/cache
    chown -R $APP_USER:$APP_USER $APP_DIR/public

    # Install PHP dependencies
    echo "→ Installing PHP dependencies..."
    $SUDO_PREFIX composer install --optimize-autoloader --no-interaction --no-ansi || {
        send_discord_message "❌ Deployment failed: Composer install failed"
        $ART_CMD up
        exit 1
    }

    echo "→ Dumping autoloader..."
    $SUDO_PREFIX composer dump-autoload --optimize --no-interaction --no-ansi

    # Install Node dependencies
    echo "→ Installing Node dependencies..."
    $SUDO_PREFIX npm install || {
        send_discord_message "❌ Deployment failed: npm install failed"
        $ART_CMD up
        exit 1
    }

    echo "→ Building frontend assets..."
    $SUDO_PREFIX npm run build || {
        send_discord_message "❌ Deployment failed: npm run build failed"
        $ART_CMD up
        exit 1
    }

    # Run migrations
    echo "→ Running database migrations..."
    $ART_CMD migrate --force || {
        send_discord_message "❌ Deployment failed: Database migrations failed"
        $ART_CMD up
        exit 1
    }

    # Create storage symlink
    echo "→ Creating storage symlink..."
    $ART_CMD storage:link || {
        send_discord_message "⚠ Warning: Could not create storage symlink"
    }

    # Clear caches and optimize
    echo "→ Clearing caches and optimizing..."
    $ART_CMD view:clear
    $ART_CMD cache:clear
    $ART_CMD config:cache
    $ART_CMD route:cache
    $ART_CMD optimize

    # Restart queue
    echo "→ Restarting queue workers..."
    $ART_CMD queue:restart || {
        send_discord_message "⚠ Warning: Could not restart queue workers"
    }

    # Ensure proper ownership
    echo "→ Finalizing permissions..."
    chown -R $APP_USER:$APP_USER $APP_DIR/storage
    chown -R $APP_USER:$APP_USER $APP_DIR/bootstrap/cache
    chown -R $APP_USER:$APP_USER $APP_DIR/public

    # Bring application back online
    echo "→ Bringing application back online..."
    $ART_CMD up || {
        send_discord_message "⚠ Warning: Could not bring application back online"
    }

    echo "=========================================="
    echo "✓ Deployment completed successfully!"
    echo "=========================================="
    send_discord_message "✅ The application has been deployed to $APP_ENV (New Version: $NEW_VERSION)."
}
# Restart the application
deploy

# Uncomment the following line to automatically view logs after deployment
# view_logs

# check 1st argument
if [ "$1" == "emails" ]; then
    $ART_CMD send:emails
    send_discord_message "Emails have been sent."
    exit 0
fi

if [ "$1" == "version" ]; then
    echo "Current version: $CURRENT_VERSION"
    exit 0
fi

send_discord_message "Deployment script has finished."