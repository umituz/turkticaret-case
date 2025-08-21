# TurkTicaret Case - E-Commerce API Project

## 📋 Project Overview

TurkTicaret Case is a comprehensive e-commerce API built with Laravel 12.x, featuring a complete product catalog, cart management, order processing system, and user authentication. The project follows modern Laravel development patterns with Docker containerization and comprehensive testing coverage.

## 🚀 Quick Start

### Prerequisites

- Docker & Docker Compose
- Git

### Installation

1. **Clone the repository**
```bash
git clone <repository-url>
cd turkticaret-case
```

2. **Start Docker environment**
```bash
./start.sh
```

3. **Set up the application**
```bash
# Enter Laravel container
docker exec -it turkticaret_laravel bash

# Install dependencies
composer install

# Run migrations and seed data
php artisan migrate:fresh --seed
```

4. **Generate API token for testing**
```bash
docker exec -it turkticaret_laravel php artisan api:token admin@turkticaret.test --name=api-testing
```

## 🏗️ Architecture

The project follows a modular, service-oriented architecture:

### Core Technologies
- **Laravel 12.x** - PHP Framework
- **PostgreSQL 16** - Primary Database
- **Redis 6.2** - Caching & Sessions
- **Docker** - Containerization
- **Laravel Sanctum** - API Authentication
- **PHPUnit/Pest** - Testing Framework

### Architectural Patterns
- **Repository Pattern** - Data access abstraction
- **Service Layer** - Business logic encapsulation
- **Observer Pattern** - Event-driven audit logging
- **DTO Pattern** - Data transfer objects
- **Policy Pattern** - Authorization logic

## 📚 API Endpoints

Base URL: `http://localhost:8080`

### Authentication
- `POST /api/register` - User registration
- `POST /api/login` - User login

### Categories
- `GET /api/categories` - List categories
- `POST /api/categories` - Create category
- `GET /api/categories/{category}` - Get category
- `PUT /api/categories/{category}` - Update category
- `DELETE /api/categories/{category}` - Delete category
- `DELETE /api/categories/{category}/force-delete` - Force delete category
- `POST /api/categories/{category}/restore` - Restore category

### Products
- `GET /api/products` - List products with filtering
- `POST /api/products` - Create product
- `GET /api/products/{product}` - Get product
- `PUT /api/products/{product}` - Update product
- `DELETE /api/products/{product}` - Delete product
- `DELETE /api/products/{product}/force-delete` - Force delete product
- `POST /api/products/{product}/restore` - Restore product

### Cart Management
- `GET /api/cart` - Get user cart
- `POST /api/cart/add` - Add item to cart
- `PUT /api/cart/update` - Update cart item
- `DELETE /api/cart/remove/{product_uuid}` - Remove item from cart
- `DELETE /api/cart/clear` - Clear cart

### Orders
- `GET /api/orders` - List user orders
- `POST /api/orders` - Create order from cart
- `GET /api/orders/{order}` - Get order details

### User Profile
- `GET /api/profile` - Get user profile
- `PUT /api/profile` - Update profile

### Health Check
- `GET /api/health` - Application health status

## 🐳 Docker Environment

### Container Services
- **turkticaret_laravel** - Laravel application (Port: 8080)
- **turkticaret_postgres** - PostgreSQL database (Port: 5433)
- **turkticaret_redis** - Redis cache
- **turkticaret_mailhog** - Email testing (Port: 8025)

### Development Commands
```bash
# Start services
./start.sh

# Stop services
./stop.sh

# Access Laravel container
docker exec -it turkticaret_laravel bash

# Database access
docker exec -it turkticaret_postgres psql -U postgres -d turkticaret_case

# View logs
docker logs turkticaret_laravel
```

## 🧪 Testing

The project maintains 100% test coverage with comprehensive unit and feature tests.

### Run Tests
```bash
# All tests with coverage
docker exec -it turkticaret_laravel php artisan test --coverage --min=100

# Unit tests only
docker exec -it turkticaret_laravel php artisan test tests/Unit

# Feature tests only
docker exec -it turkticaret_laravel php artisan test tests/Feature

# Specific test file
docker exec -it turkticaret_laravel php artisan test tests/Unit/Models/Product/ProductTest.php
```

### Test Structure
- **Unit Tests** - Models, Services, Repositories, Observers
- **Feature Tests** - API endpoints, authentication flows
- **Base Test Classes** - Shared testing utilities and traits

## 🛡️ Security Features

- **Laravel Sanctum** - Secure API token authentication
- **CORS Middleware** - Cross-origin request handling
- **Trusted Proxies** - Secure proxy configuration
- **Policy Authorization** - Resource-based access control
- **Input Validation** - Comprehensive request validation
- **SQL Injection Protection** - Eloquent ORM security

## 📊 Database Design

### Core Entities
- **Users** - Authentication and profiles
- **Categories** - Product categorization
- **Products** - Product catalog with inventory
- **Carts & Cart Items** - Shopping cart management
- **Orders & Order Items** - Order processing
- **Activity Logs** - Audit trail system

### Key Features
- UUID primary keys for enhanced security
- Soft deletes for data integrity
- Comprehensive indexing for performance
- Foreign key constraints for referential integrity

## 🎯 Development Standards

### Code Quality
- **PSR-12** coding standards
- **100% test coverage** requirement
- **Comprehensive documentation** in CLAUDE.md files
- **English-only** code and comments
- **Modular architecture** with clear separation of concerns


## 🔧 API Testing

### Postman Collection
The project includes a comprehensive Postman collection:
```bash
# Located at: laravel/tools/postman/
- TurkTicaret API.postman_collection.json
- environments/local.json
```

### Authentication
All protected endpoints require Bearer token authentication:
```
Authorization: Bearer {your-api-token}
```

## 📈 Performance

### Optimization Features
- **Database indexing** on frequently queried columns
- **Eager loading** to prevent N+1 queries
- **Redis caching** for session and cache management
- **Query optimization** with proper relationship loading
- **API resource transformation** for efficient data serialization

## 📝 License

This project is developed as a case study for TurkTicaret technical assessment.

---