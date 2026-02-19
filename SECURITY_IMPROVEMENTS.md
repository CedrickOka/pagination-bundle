# Security Improvements Guide

This document outlines the security improvements implemented in OkaPaginationBundle.

## Implemented Security Measures

### 1. Input Validation (PaginationManager.php)

The `sanitizeQuery()` method now includes:

- **Length validation**: Maximum query parameter length is validated
- **Character whitelist**: Only alphanumeric, underscore, hyphen, comma, brackets allowed
- **SQL injection prevention**: Invalid characters are rejected

```php
// Maximum length is derived from maxPageNumber configuration
if (strlen($sanitized) > $maxLength) {
    throw new \InvalidArgumentException(...);
}

// Whitelist validation
if (!preg_match('/^[\w\s,\-\[\]\(\):.]+$/u', $sanitized)) {
    throw new \InvalidArgumentException(...);
}
```

### 2. Filter Value Length Limits (Filter.php)

- Maximum filter value length: 200 characters (`Filter::MAX_VALUE_LENGTH`)
- Validation in `castTo()` method
- Type validation using static patterns

```php
if (is_string($value) && strlen($value) > self::MAX_VALUE_LENGTH) {
    throw new \InvalidArgumentException('Filter value too long');
}
```

### 3. Regex DoS Prevention (RegexpLikeFilterExpression.php)

- Maximum pattern length: 100 characters
- Complex pattern detection and rejection
- Prevents catastrophic backtracking

```php
if (strlen($pattern) > 100) {
    throw new BadFilterExpressionException('Regex pattern too long');
}

if (preg_match('/\(\?<!/', $pattern)) {
    throw new BadFilterExpressionException('Complex regex patterns not allowed');
}
```

### 4. Range Filter Validation (RangeORMFilterExpression.php)

- Maximum range value length: 100 characters
- Prevents abuse with extremely long range values

## Recommendations for Users

### 1. Always Validate User Input

```php
// In your controller
$request->query->get('name', '', FILTER_SANITIZE_STRING);
```

### 2. Configure Appropriate Limits

```yaml
# config/packages/oka_pagination.yaml
oka_pagination:
    max_page_number: 400  # Adjust based on your needs
    filters:
        name:
            cast_type: string
            # Add max_length validation in your entity
```

### 3. Use Parameterized Queries

The bundle uses Doctrine's parameterized queries, which provides SQL injection protection. Never bypass this.

### 4. Enable Symfony Security Checker

Regularly run:

```bash
composer audit
```

## Future Security Enhancements

- Rate limiting for pagination requests
- IP-based filtering
- Audit logging
- CSRF protection for filter manipulation
