# CLAUDE.md

This file provides guidance to Claude Code (claude.ai/code) when working with code in this repository.

# Project: Days Battery

A Symfony 7.4 application following Domain-Driven Design principles.

## Coding Standards

**CRITICAL: All PHP code must follow PER Coding Style 3.0**
- Reference: https://www.php-fig.org/per/coding-style/
- Strict types declaration required: `declare(strict_types=1);`
- PSR-12 compatible formatting
- Type hints mandatory for all method parameters and return types

## Symfony Specific

- Version: Symfony 7.4
- Use PHP attributes for routing, validation, and configuration (not annotations or YAML)
- Follow DDD directory structure
- Entities in `src/Entity/`, Repositories in `src/Repository/`
- Services organized by bounded context when applicable

## Quality Tools

- **PHP CS Fixer**: Configuration in `.php-cs-fixer.php`
- **PHPStan**: Level 8 static analysis
- **Pre-commit**: Run `composer check` before committing to verify code quality

## Common Commands

### Development
- `symfony serve` - Start development server
- `symfony console` - Run Symfony console commands
- `symfony console debug:router` - List all routes
- `symfony console debug:container` - List all services

### Testing
- `composer test` - Run full test suite
- `vendor/bin/phpunit` - Run all tests
- `vendor/bin/phpunit tests/Unit/Specific/TestFile.php` - Run specific test file
- `vendor/bin/phpunit --filter testMethodName` - Run specific test method

### Database
- `symfony console doctrine:migrations:migrate` - Run migrations
- `symfony console doctrine:migrations:generate` - Generate new migration
- `symfony console doctrine:fixtures:load` - Load fixtures (dev/test only)
- `symfony console doctrine:schema:validate` - Validate schema against entities

### Code Quality
- `composer fix` - Auto-fix code style with PHP CS Fixer
- `composer check` - Run PHPStan + PHP CS Fixer in dry-run mode
- `vendor/bin/phpstan analyse` - Run static analysis

### Cache
- `symfony console cache:clear` - Clear application cache
- `symfony console cache:warmup` - Warm up cache

## Architecture

### DDD Structure (Domain-Driven Design)

The project follows DDD principles with a single bounded context: **Battery Management**

```
src/Battery/
├── Domain/              # Business logic and rules
│   ├── Entity/          # User, Battery (with business methods)
│   ├── ValueObject/     # Username, PublicHash, BatteryCapacity
│   ├── Repository/      # Interfaces (UserRepositoryInterface, BatteryRepositoryInterface)
│   ├── Exception/       # Domain exceptions
│   └── Service/         # Domain services (if needed)
│
├── Application/         # Use cases and application logic
│   ├── Command/         # Write operations (ClaimUsername, StartSession, StopSession)
│   ├── Query/           # Read operations (GetBatteryStatus, GetBatteryByHash)
│   └── Service/         # Application orchestration services
│
├── Infrastructure/      # Technical implementations
│   └── Persistence/
│       └── Doctrine/    # DoctrineUserRepository, DoctrineBatteryRepository
│
└── Presentation/        # HTTP layer
    ├── Controller/
    │   └── Api/         # API endpoints
    └── Request/         # Request DTOs with validation

src/Shared/
└── Domain/             # Shared domain concepts (UserId)
```

### Key Domain Concepts

**User Entity:**
- Represents a user who claims a username
- Contains unique `publicHash` for sharing battery status
- Created via `User::create(Username)`

**Battery Entity (Aggregate Root):**
- One battery per user per day
- Tracks time via manual start/stop sessions
- Business methods: `startSession()`, `stopSession()`, `updateCapacity()`
- Calculates remaining time and percentage in real-time
- Properties: capacity (default 16h), totalUsedSeconds, isActive, currentSessionStartedAt

**Value Objects:**
- `Username`: 3-32 chars, alphanumeric + underscore/hyphen
- `PublicHash`: 32-char hex string for public URL sharing
- `BatteryCapacity`: 1-24 hours, stored as seconds
- `UserId`: UUID v4

### Database Schema

**users table:**
- id (UUID), username (unique), public_hash (unique), created_at

**batteries table:**
- id (UUID), user_id, date, capacity_seconds, is_active
- current_session_started_at, total_used_seconds
- created_at, updated_at
- Index: (user_id, date) for fast daily lookup

### Sessions

- Session-based authentication (no JWT)
- Sessions stored in Redis
- User "claims" username → session created
- No traditional registration/login

### Process
Verified Features

  - Session-based authentication working
  - Time accumulation working correctly (tested 17 seconds accumulated)
  - Capacity updates and percentage recalculation working
  - All validation rules enforced (username format, capacity range)
  - All error handling working (409 Conflict, 404 Not Found, 422 Validation, 401 Unauthorized)
  - Public sharing via hash working


The next step would be to create simple Twig UI templates for the MVP to provide a web interface for:
  - Claiming username
  - Starting/stopping battery sessions
  - Viewing battery status
  - Sharing public URL