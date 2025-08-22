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

# Check if .env file exists in laravel directory
if [ ! -f laravel/.env ]; then
    print_error "laravel/.env file not found!"
    exit 1
fi

# Load environment variables from laravel directory
source laravel/.env

# Basic environment check
if [ -z "$HOST_UID" ]; then
    print_warning "HOST_UID not set, using default 501"
fi

print_status "Starting Turkticaret development environment..."

# Create network if it doesn't exist
docker network create turkticaret_network 2>/dev/null || print_warning "Network turkticaret_network already exists"

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
else
    print_error "PostgreSQL failed to start"
    docker compose logs postgres
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