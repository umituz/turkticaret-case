#!/bin/bash
# start.sh - TurkTicaret Docker Environment

set -e

# Color codes for output
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
BLUE='\033[0;34m'
NC='\033[0m' # No Color

# Function to print colored output
print_status() {
    echo -e "${BLUE}[INFO]${NC} $1"
}

print_success() {
    echo -e "${GREEN}[SUCCESS]${NC} $1"
}

print_warning() {
    echo -e "${YELLOW}[WARNING]${NC} $1"
}

print_error() {
    echo -e "${RED}[ERROR]${NC} $1"
}

# Function to create .env from .env.example
create_env_from_example() {
    local dir=$1
    local name=$2
    
    if [ ! -f "$dir/.env" ]; then
        if [ -f "$dir/.env.example" ]; then
            print_warning "$name .env file not found, creating from .env.example..."
            cp "$dir/.env.example" "$dir/.env"
            
            # Generate APP_KEY for Laravel if it's empty
            if [ "$name" = "Laravel" ] && [ -f "$dir/.env" ]; then
                if grep -q "^APP_KEY=$" "$dir/.env"; then
                    print_status "Generating Laravel APP_KEY..."
                    # Generate a base64 encoded random key
                    KEY=$(openssl rand -base64 32)
                    # Use sed compatible with macOS
                    sed -i '' "s/^APP_KEY=$/APP_KEY=base64:$KEY/" "$dir/.env"
                    print_success "APP_KEY generated successfully"
                fi
            fi
            
            print_success "$name .env file created from .env.example"
        else
            print_error "$name .env.example file not found in $dir!"
            print_error "Please create $dir/.env.example first"
            exit 1
        fi
    else
        print_status "$name .env file already exists"
    fi
}

# Check and create .env files
create_env_from_example "laravel" "Laravel"
create_env_from_example "next" "Next.js"

# Load environment variables from laravel directory
if [ -f laravel/.env ]; then
    source laravel/.env
else
    print_error "Failed to create or load laravel/.env"
    exit 1
fi

# Basic environment check
if [ -z "$HOST_UID" ]; then
    print_warning "HOST_UID not set, using default 501"
fi

print_status "Starting Turkticaret development environment..."

# Function to check and create Docker network
create_network() {
    local network_name="turkticaret_network"
    
    print_status "Checking Docker network: $network_name"
    
    if docker network inspect $network_name >/dev/null 2>&1; then
        print_status "Network $network_name already exists"
    else
        print_status "Creating Docker network: $network_name"
        if docker network create $network_name >/dev/null 2>&1; then
            print_success "Network $network_name created successfully"
        else
            print_error "Failed to create network $network_name"
            exit 1
        fi
    fi
}

# Create network before starting services
create_network

# Build base image first
print_status "Building base image first..."
if docker build -t turkticaret-base:latest -f docker/base.Dockerfile .; then
    print_success "Base image built successfully"
else
    print_error "Failed to build base image"
    exit 1
fi

# Build and start services
print_status "Building and starting Docker containers..."
docker compose up -d --build

# Check if services are running
print_status "Checking service status..."
sleep 5

if docker ps | grep -q turkticaret_laravel; then
    print_success "Turkticaret API is running!"
    print_status "API available at: http://localhost:8080"
else
    print_error "Turkticaret API failed to start"
    docker compose logs turkticaret-api
    exit 1
fi

if docker ps | grep -q turkticaret_postgres; then
    print_success "PostgreSQL is running!"
    
    # Run migrations and seeders
    print_status "Running Laravel migrations and seeders..."
    if docker exec turkticaret_laravel php artisan migrate:fresh --seed; then
        print_success "Database migrations and seeders completed successfully!"
    else
        print_error "Failed to run migrations and seeders"
        exit 1
    fi
else
    print_error "PostgreSQL failed to start"
    docker compose logs postgres
    exit 1
fi

if docker ps | grep -q turkticaret_redis; then
    print_success "Redis is running!"
else
    print_error "Redis failed to start"
    docker compose logs redis
fi

if docker ps | grep -q turkticaret_mailhog; then
    print_success "MailHog is running!"
    print_status "MailHog Web UI available at: http://localhost:8025"
else
    print_warning "MailHog may not be running properly"
fi

print_success "All services started successfully!"
print_status ""
print_status "🚀 Development environment is ready!"
print_status "📡 API: http://localhost:8080"
print_status "📧 MailHog: http://localhost:8025"
print_status "🗄️  PostgreSQL: localhost:5433"
print_status ""
print_status "To view logs: docker compose logs -f"
print_status "To stop: docker compose down"