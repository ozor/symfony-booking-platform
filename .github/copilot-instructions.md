# Copilot instructions for this repository

This is a PHP/Symfony backend project.

## Stack
- PHP 8.x
- Symfony
- Doctrine ORM
- PostgreSQL
- PHPUnit
- PHPStan/Psalm
- Docker / Docker Compose
- REST API / OpenAPI

## Coding guidelines
- Prefer explicit, readable PHP code.
- Follow Symfony best practices.
- Use constructor injection.
- Keep controllers thin.
- Put business logic in services or domain/application layer.
- Do not introduce new dependencies without explaining why.
- Avoid magic behavior unless it is idiomatic Symfony.

## Testing guidelines
- Generate PHPUnit tests using Arrange-Act-Assert.
- Cover success cases, validation errors, edge cases, and repository exceptions.
- Do not test private methods directly.
- Prefer meaningful test names.

## Review guidelines
- Look for security issues, missing validation, race conditions, edge cases, and weak error handling.
- Suggest improvements before rewriting code.
- Explain trade-offs.
