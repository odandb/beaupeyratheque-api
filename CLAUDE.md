# CLAUDE.md

This file provides guidance to Claude Code (claude.ai/code) when working with code in this repository.

## Project Overview

Beaupeyratheque API is a modern Symfony 7.3 library management system built with API Platform 4.1. It provides a REST API for managing books, authors, reviews, and users with JWT authentication and file upload capabilities.

## Development Environment

### Starting the Application
```bash
# Start all services with Docker Compose
docker compose up --build

# Access the application
# - API: https://localhost/api
# - API Documentation: https://localhost/api/docs
```

### Available Commands
```bash
# Install dependencies
composer install

# Database operations
bin/console doctrine:migrations:migrate
bin/console app:user:create <email> <password> [roles]

# Cache operations
bin/console cache:clear
bin/console cache:warmup

# Development server (if not using Docker)
symfony server:start
```

## Architecture

### Core Entities and Relationships
- **User**: Authentication entity with roles (ROLE_USER, ROLE_ADMIN)
- **Author**: Book authors with nationality filtering
- **Book**: Central entity with title, ISBN, publication date, cover image
- **Review**: User reviews for books (rating 1-5, comment)
- **MediaObject**: File uploads for book covers using VichUploader

### Entity Relationships
- Author ↔ Book (ManyToMany bidirectional)
- Book ↔ Review (OneToMany)
- User ↔ Review (OneToMany)
- Book ↔ MediaObject (OneToOne)

### API Platform Customizations

#### Custom Filters
- **PublicationYearFilter** (`src/Filter/PublicationYearFilter.php`): Supports decade filtering (e.g., "1990s") and year ranges
- **NationalityFilter** (`src/Filter/NationalityFilter.php`): Wildcard and exclusion support for author nationality

#### Custom Extensions
- **BookPublishedExtension** (`src/Extension/BookPublishedExtension.php`): Automatically filters published books for non-admin users

#### Custom Providers
- **AppInfoProvider** (`src/State/AppInfoProvider.php`): Provides application metadata at `/api/app/info`

#### Custom Operations
- **AppInfo** (`src/ApiResource/AppInfo.php`): Read-only resource for application information

### Authentication & Security
- JWT-based authentication via LexikJWTAuthenticationBundle
- Public read access for books, authors, reviews
- Authenticated access required for creating reviews
- Admin-only access for user management
- File upload security through VichUploader integration

### File Upload System
- VichUploader configuration for book cover images
- Files stored with SmartUniqueNamer
- MediaObject entity handles file metadata
- Flysystem integration for flexible storage backends

### Custom Serialization
- **MultipartDecoder** (`src/Serializer/Decoder/MultipartDecoder.php`): Handles multipart form data
- **UploadedFileDenormalizer** (`src/Serializer/Denormalizer/UploadedFileDenormalizer.php`): Processes file uploads
- **MediaObjectNormalizer** (`src/Serializer/Normalizer/MediaObjectNormalizer.php`): Serializes file URLs

## Docker Configuration

### Services
- **php**: FrankenPHP with Caddy for high-performance PHP execution
- **database**: PostgreSQL 16 for data persistence

### Key Features
- Automatic HTTPS with self-signed certificates
- Mercure hub for real-time features
- Xdebug support for development
- Volume mounting for live code reloading

### Environment Variables
- `SERVER_NAME`: Server hostname (default: localhost)
- `DATABASE_URL`: PostgreSQL connection string
- `CORS_ALLOW_ORIGIN`: CORS policy configuration
- `JWT_SECRET_KEY`/`JWT_PUBLIC_KEY`: JWT key paths
- `XDEBUG_MODE`: Xdebug configuration

## Testing and Quality

### Running Tests
```bash
# PHPUnit tests (check for existing configuration)
bin/phpunit

# API Platform test utilities available
# Check for existing test suite in tests/ directory
```

### Code Quality
```bash
# Check composer.json for available scripts:
# - php-cs-fixer (code style)
# - phpstan (static analysis)
# - rector (automated refactoring)
composer run-script [script-name]
```

## API Usage Patterns

### Filtering Examples
```
# Books from the 1990s
GET /api/books?publicationYear=1990s

# Books from 2000-2010
GET /api/books?publicationYear=2000..2010

# Authors not from France
GET /api/authors?nationality[not]=France

# Authors from countries starting with "Fr"
GET /api/authors?nationality=Fr*
```

### Authentication Flow
1. POST `/api/auth` with credentials to get JWT token
2. Include `Authorization: Bearer <token>` header in requests
3. Access user-specific endpoints with valid token

## Key Configuration Files

- `config/packages/api_platform.yaml`: API Platform settings and defaults
- `config/packages/security.yaml`: Authentication and authorization rules
- `config/packages/vich_uploader.yaml`: File upload configuration
- `config/packages/doctrine.yaml`: Database and ORM settings
- `compose.yaml`: Production Docker configuration
- `compose.override.yaml`: Development Docker overrides

## Development Tips

- Use `bin/console debug:router` to see all available routes
- API documentation is auto-generated and available at `/api/docs`
- Check entity validation constraints in entity annotations
- Custom API Platform operations are defined as PHP attributes on entities
- File uploads require multipart/form-data content type