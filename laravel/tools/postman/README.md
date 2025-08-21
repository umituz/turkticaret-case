# TurkTicaret API Postman Collection

This directory contains Postman collections and environments for testing the TurkTicaret API.

## Overview

The TurkTicaret API provides a comprehensive set of endpoints for managing categories, users, authentication, and system health. This collection is organized to provide easy testing and integration capabilities.

## Collection Structure

### Main Collection
- **File**: `TurkTicaret API.postman_collection.json`
- **Description**: Complete API collection with all endpoints
- **Features**: Authentication, CRUD operations, filtering, pagination

### Modular Collections
Collections are organized by functional areas in the `collections/` directory:

#### Categories (`collections/categories/`)
- **categories.json**: Category management endpoints
- Full CRUD operations with soft deletes
- Advanced filtering and search capabilities
- UUID-based identification system

#### Health (`collections/health/`)
- **health.json**: System health check endpoints
- API status and monitoring

#### Profile (`collections/profile/`)
- **profile.json**: User profile management
- Profile CRUD operations and user settings

## Environment Configuration

### Available Environments

#### Local Development (`environments/local.json`)
- **Base URL**: `http://localhost:8080/api`
- **Purpose**: Local development and testing via Docker
- **Features**: Pre-configured test data and UUID variables

## Variables and Configuration

### Global Variables (`project-models.json`)
Key variables used across collections:
- `user_uuid`: Test user identifier (UUID format)
- `category_uuid`: Sample category UUID
- `per_page`: Pagination limit (default: 25)
- `page`: Current page number
- `search`: Search query parameter

### Headers (`project-headers.json`)
Standard headers applied to all requests:
- `Accept: application/json`
- `Content-Type: application/json`
- `Authorization: Bearer {{token}}`

### Filters (`project-filters.json`)
Common filtering parameters:
- Status filters (active/inactive)
- Date range filters
- Search and sorting options

## Getting Started

### 1. Import Collection
1. Open Postman
2. Click "Import" button
3. Select `TurkTicaret API.postman_collection.json`
4. Import `environments/local.json` for local development

### 2. Set Up Environment
1. Use `environments/local.json` for local Docker development
2. Base URL is set to `http://localhost:8080/api`
3. Set authentication token after login

### 3. Authentication Flow
1. **Register** a new user account
2. **Login** to obtain authentication token
3. Token will be automatically set in environment variables
4. Use authenticated endpoints with automatic token injection

## Usage Examples

### Basic Operations

#### Authentication
```http
POST {{base_url}}/register
POST {{base_url}}/login
POST {{base_url}}/logout
```

#### Categories
```http
GET {{base_url}}/categories                         # List all categories
POST {{base_url}}/categories                        # Create category
GET {{base_url}}/categories/{{category_uuid}}       # Get specific category
PUT {{base_url}}/categories/{{category_uuid}}       # Update category
DELETE {{base_url}}/categories/{{category_uuid}}    # Soft delete category
```

#### Soft Delete Operations
```http
POST {{base_url}}/categories/{{category_uuid}}/restore      # Restore category
DELETE {{base_url}}/categories/{{category_uuid}}/force-delete # Permanent delete
```

#### Advanced Features
```http
GET {{base_url}}/categories?search=electronics&status=active
GET {{base_url}}/categories?sort_by=name&sort_direction=asc
GET {{base_url}}/categories?per_page=10&page=2
```

### Filtering and Pagination

All list endpoints support:
- **Pagination**: `?per_page=25&page=1`
- **Search**: `?search=keyword`
- **Status Filter**: `?status=active|inactive`
- **Sorting**: `?sort_by=field&sort_direction=asc|desc`

## API Response Format

All API responses follow a consistent structure:

### Success Response (Single Item)
```json
{
    "success": true,
    "message": "Your execution has been completed successfully",
    "errors": [],
    "data": {
        "uuid": "0198cb99-a0bf-70b3-8f12-034f6a4dc6da",
        "name": "Electronics",
        "description": "Electronic devices and gadgets",
        "slug": "electronics",
        "created_at": "2025-08-21T07:48:19+00:00",
        "updated_at": "2025-08-21T07:48:19+00:00"
    }
}
```

### Success Response (Collection)
```json
{
    "success": true,
    "message": "Your execution has been completed successfully",
    "errors": [],
    "data": [
        // Array of items
    ],
    "meta": {
        "total": 5,
        "count": 5,
        "per_page": 15,
        "current_page": 1,
        "last_page": 1,
        "from": 1,
        "to": 5,
        "path": "http://localhost:8080/api/categories",
        "first_page_url": "http://localhost:8080/api/categories?page=1",
        "last_page_url": "http://localhost:8080/api/categories?page=1",
        "next_page_url": null,
        "prev_page_url": null,
        "links": [
            // Pagination links
        ]
    }
}
```

### Error Response
```json
{
    "success": false,
    "message": "Error message",
    "data": null,
    "errors": {
        "field": ["Validation error message"]
    }
}
```

## Docker Development

### Prerequisites
- Docker and Docker Compose installed
- TurkTicaret Laravel application running in containers

### Container Configuration
- **Application**: `turkticaret_laravel` container
- **Database**: `turkticaret_postgres` container
- **API Base URL**: `http://localhost:8080`

## UUID Standards

### Important Notes
- All entity identifiers use UUID format
- Variable names follow `{entity}_uuid` pattern
- Never use `_id` suffix, always use `_uuid`
- Route model binding automatically handles UUID lookup

## Testing Guidelines

### 1. Sequential Testing
Follow the logical flow:
1. Health check endpoints
2. Authentication (register/login)
3. Category CRUD operations
4. Advanced filtering and search
5. Soft delete and restore operations

### 2. Data Management
- Use UUID-based test data variables
- Test with valid UUID formats
- Respect soft delete patterns
- Clean up test data when necessary

### 3. Authentication
- Test role-based access (Admin required for mutations)
- Handle token expiration
- Verify unauthorized access scenarios

## Automation and CI/CD

### Newman Integration
Run collections via command line:
```bash
newman run "TurkTicaret API.postman_collection.json" \
  -e "environments/local.json" \
  --reporters cli,json
```

### Continuous Integration
- Collections integrate with CI/CD pipelines
- Automated API testing on deployments
- Docker-based test environment

## Maintenance

### Keeping Collections Updated
1. Maintain UUID naming standards
2. Update environment variables for Docker setup
3. Add new endpoints following established patterns
4. Keep variable naming consistent with `_uuid` suffix

### Version Control
- Collections are version controlled
- Track API endpoint changes
- Document UUID migration patterns

## Support and Troubleshooting

### Common Issues
1. **UUID Format Errors**: Ensure UUIDs are properly formatted
2. **Docker Connection Issues**: Verify containers are running
3. **Authentication Failures**: Check token validity and admin roles

### Getting Help
- Check Laravel logs in Docker containers
- Verify Docker environment is properly configured
- Review UUID format requirements

---

For more information about the TurkTicaret API, refer to the main project documentation.
