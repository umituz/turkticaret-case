#!/bin/bash
# stop.sh - TurkTicaret Docker Environment Stop Script

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

print_status "Stopping Turkticaret development environment..."

# Stop and remove containers
print_status "Stopping Docker containers..."
docker compose down

# Check if containers are stopped
if ! docker ps | grep -q "turkticaret_"; then
    print_success "All Turkticaret containers have been stopped!"
else
    print_warning "Some containers might still be running"
    docker ps | grep "turkticaret_"
fi

print_success "Turkticaret development environment stopped successfully!"
print_status ""
print_status "To start again: ./start.sh"
print_status "To remove volumes: docker compose down -v"
print_status "To remove images: docker compose down --rmi all"