# Postman Module

This is a shared module for Postman collection management across multiple projects.

## Structure

- `combine-collections.js`: Main entry script that orchestrates the collection combining process
- `src/`: Directory containing modular components:
    - `ConfigLoader.js`: Handles loading all configuration files
    - `CollectionBuilder.js`: Responsible for building and processing collections
    - `CollectionValidator.js`: Validates collections for potential issues
    - `CollectionUtils.js`: Utility functions for collection manipulation
- `common-headers.json`: Common headers for all API requests
- `common-models.json`: Common model UUIDs used as collection variables
- `common-filters.json`: Standard filter parameters for listing endpoints
- `endpoint-validator.js`: Script to validate endpoints for potential issues

## How to Use

Each project should create its own setup in the `tools/postman` directory:

```
tools/postman/
├── collections/          # Project-specific endpoint collections
├── project-config.json   # Project configuration
├── project-models.json   # Project-specific model UUIDs
├── project-headers.json  # Project-specific headers
├── project-filters.json  # Project-specific filters
├── master-collection.json # Project-specific collection structure
├── combine-collections.js # Simple wrapper to call the module
└── README.md
```

### 1. Create a Project Config File

Create a `project-config.json` with basic settings:

```json
{
  "name": "Your API Name",
  "output_file": "Your-API.postman_collection.json",
  "description": "API Documentation for Your Project",
  "base_url": "http://your-api-domain.com",
  "token_var": "token",
  "collections_dir": "collections"
}
```

The `base_url` should match your API's base URL without any trailing slashes. The module doesn't assume any specific URL structure or prefixes.

### 2. Create a Master Collection Template

Create a `master-collection.json` file that defines the structure of your API:

```json
{
  "info": {
    "_postman_id": "your-api-collection",
    "name": "Your API Collection",
    "description": "Collection for Your API endpoints",
    "schema": "https://schema.getpostman.com/json/collection/v2.1.0/collection.json"
  },
  "item": [
    {
      "name": "Authentication",
      "item": [
        {
          "$ref": "collections/auth.json#/item/0"
        },
        {
          "$ref": "collections/auth.json#/item/1"
        }
      ]
    },
    {
      "name": "Resources",
      "item": [
        {
          "$ref": "collections/resources.json"
        }
      ]
    }
  ],
  "variable": [
    {
      "key": "base_url",
      "value": "http://your-api-domain.com",
      "type": "string"
    },
    {
      "key": "token",
      "value": "",
      "type": "string"
    }
  ]
}
```

### 3. Create a Combine Script

Create a `combine-collections.js` file that calls this module:

```javascript
/**
 * Script to combine individual collection files into a single Postman Collection
 * This script uses the shared code from Modules/Postman with project-specific configuration
 *
 * Usage: node combine-collections.js
 */

import fs from "fs";
import path from "path";
import { fileURLToPath } from "url";
import { execSync } from "child_process";

const __filename = fileURLToPath(import.meta.url);
const __dirname = path.dirname(__filename);
const projectRoot = path.resolve(__dirname, "../..");
const modulePath = path.join(projectRoot, "Modules", "Postman");

// Check if the Postman module exists
if (!fs.existsSync(modulePath)) {
  console.error(`❌ Error: Postman module not found at ${modulePath}`);
  console.log(
    "Please ensure the Postman module exists at the correct location."
  );
  process.exit(1);
}

// Execute the module script with the current project path
try {
  const command = `node ${path.join(
    modulePath,
    "combine-collections.js"
  )} ${__dirname}`;
  console.log(`🚀 Running: ${command}`);
  execSync(command, { stdio: "inherit" });
} catch (error) {
  console.error(`❌ Error executing Postman module script: ${error.message}`);
  process.exit(1);
}

console.log("✅ Collection generation completed successfully");
```

### 4. Add API Endpoint Collections

Create JSON files in the `collections/` directory for your API endpoints. For example, a generic authentication collection:

```json
{
  "info": {
    "name": "Authentication",
    "schema": "https://schema.getpostman.com/json/collection/v2.1.0/collection.json"
  },
  "item": [
    {
      "name": "Login",
      "request": {
        "method": "POST",
        "url": {
          "raw": "{{base_url}}/auth/login",
          "host": ["{{base_url}}"],
          "path": ["auth", "login"]
        },
        "body": {
          "mode": "raw",
          "raw": "{\n  \"email\": \"user@example.com\",\n  \"password\": \"password123\"\n}"
        }
      }
    },
    {
      "name": "Refresh Token",
      "request": {
        "method": "POST",
        "url": {
          "raw": "{{base_url}}/auth/refresh",
          "host": ["{{base_url}}"],
          "path": ["auth", "refresh"]
        }
      }
    }
  ]
}
```

### 5. Generate the Collection

Run the combine script to generate the collection:

```bash
cd tools/postman
node combine-collections.js
```

### 6. Environment Handling in Postman

Create different environments in Postman with their own `base_url` variable matching your project's environment URLs:

- Local: http://localhost:8000
- Development: https://dev.your-api-domain.com
- Staging: https://staging.your-api-domain.com
- Production: https://your-api-domain.com

Switch between environments using Postman's environment selector.

## Features

This module provides several key features:

1. **Collection Combining**: Merges multiple collection JSON files into a single Postman collection
2. **Common Headers**: Automatically injects common headers into all requests
3. **Reference Resolution**: Resolves `$ref` references to other collection files
4. **Request Normalization**: Ensures all requests have proper names and structure
5. **Standard Filters**: Automatically adds common filter parameters to listing endpoints
6. **Model Variables**: Adds common model UUIDs as collection variables
7. **Endpoint Validation**: Checks for potential issues in endpoints (like undefined URLs or server errors)

## How It Works

1. The project's `combine-collections.js` script calls the module
2. The `ConfigLoader` loads all configuration files from both common and project locations
3. The `CollectionBuilder` processes all collections and builds the full Postman collection
4. The `CollectionValidator` validates the collection for potential issues
5. The combined collection is written to the output file specified in the project config

The modular architecture makes it easier to maintain and extend the codebase with new features, while keeping the internal implementation details hidden from users of the module.

## Configuration Files

The module uses several configuration files that can be customized for each project:

### Common Files (Module Level)

These files are shared across all projects and provide default behaviors:

- `common-headers.json`: Basic headers for all API requests
- `common-models.json`: Common model UUIDs as collection variables
- `common-filters.json`: Standard filter parameters for listing endpoints

### Project Files (Project Level)

These files override or extend the common files for project-specific needs:

- `project-config.json`: Project configuration
- `project-headers.json`: Project-specific headers
- `project-models.json`: Project-specific model UUIDs
- `project-filters.json`: Project-specific filters

The module will first load the common files, then merge them with project-specific files, with project files taking precedence.

## Project Models

The module supports two types of model UUID variables:

1. **Common Models**: Basic models shared across all projects (defined in `Modules/Postman/common-models.json`)
2. **Project Models**: Project-specific models defined in each project (in `tools/postman/project-models.json`)

For common models that apply to all projects, the module provides:

```json
{
  "variables": [
    {
      "key": "user_uuid",
      "value": "00000000-0000-0000-0000-000000000001",
      "type": "string",
      "description": "UUID for a User entity"
    }
  ]
}
```

For project-specific models, create a `project-models.json` file in your project's `tools/postman` directory:

```json
{
  "variables": [
    {
      "key": "product_uuid",
      "value": "11111111-1111-1111-1111-111111111111",
      "type": "string",
      "description": "UUID for a Product entity"
    },
    {
      "key": "order_uuid",
      "value": "22222222-2222-2222-2222-222222222222",
      "type": "string",
      "description": "UUID for an Order entity"
    }
  ]
}
```

The module will combine both common and project-specific models while avoiding duplicates.

## Project Filters

Similar to model variables, the module supports two types of filters:

1. **Common Filters**: Standard filters common across all projects (defined in `Modules/Postman/common-filters.json`)
2. **Project Filters**: Project-specific filters defined in each project (in `tools/postman/project-filters.json`)

The common filters include standard pagination, sorting, searching, and date range parameters:

```json
{
  "standard_filters": {
    "pagination": [
      {
        "key": "per_page",
        "value": "{{per_page}}",
        "description": "Number of items per page (default: 15)",
        "example": "15",
        "required": false
      },
      {
        "key": "page",
        "value": "{{page}}",
        "description": "Page number",
        "example": "1",
        "required": false
      }
    ],
    "sorting": [
      {
        "key": "orderBy",
        "value": "{{orderBy}}",
        "description": "Field to sort by",
        "example": "created_at",
        "required": false
      },
      {
        "key": "order",
        "value": "{{order}}",
        "description": "Sort direction",
        "example": "desc",
        "required": false
      }
    ]
  }
}
```

For project-specific filters, create a `project-filters.json` file with filters that are specific to your project's domain:

```json
{
  "project_filters": {
    "price_range": [
      {
        "key": "min_price",
        "value": "{{min_price}}",
        "description": "Minimum price",
        "example": "10.00",
        "required": false
      },
      {
        "key": "max_price",
        "value": "{{max_price}}",
        "description": "Maximum price",
        "example": "100.00",
        "required": false
      }
    ],
    "status_filters": [
      {
        "key": "status",
        "value": "{{status}}",
        "description": "Filter by status",
        "example": "active",
        "required": false
      }
    ]
  }
}
```

## Project Headers

The module supports two types of headers:

1. **Common Headers**: Basic headers present in all requests across all projects (defined in `Modules/Postman/common-headers.json`)
2. **Project Headers**: Project-specific headers defined in each project (in `tools/postman/project-headers.json`)

The common headers typically include:

```json
{
  "headers": [
    {
      "key": "Accept",
      "value": "application/json",
      "type": "text"
    },
    {
      "key": "Content-Type",
      "value": "application/json",
      "type": "text"
    }
  ]
}
```

For project-specific headers, create a `project-headers.json` file with headers specific to your project:

```json
{
  "headers": [
    {
      "key": "Authorization",
      "value": "Bearer {{token}}",
      "type": "text"
    },
    {
      "key": "X-API-Version",
      "value": "1.0",
      "type": "text"
    },
    {
      "key": "X-Client-Type",
      "value": "web",
      "type": "text"
    }
  ]
}
```

The module will merge both header sets, with project headers taking precedence in case of duplicates.

## Endpoint Validation

The module includes an integrated endpoint validator that automatically checks your API collection for potential issues during the combining process:

- Validates all URLs for problems (like `undefined` or `null` values)
- Checks for missing or undefined variables in request URLs
- Verifies that all referenced variables exist in the collection

If any validation issues are found, the combine process will fail with an error message listing all detected problems. This prevents generating collections with obvious issues.

```bash
# Validation happens automatically when running combine-collections.js
node tools/postman/combine-collections.js
```

Example output with validation error:

```
❌ Error: Collection has validation issues:
- Authentication > Login: URL contains undefined variable: invalid_var
- Users > Get User: URL contains undefined or null values

Please fix these issues before proceeding.
```

This ensures that only valid collections without obvious issues are generated, helping you catch and fix problems early.
