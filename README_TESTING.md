# 🧪 Testing Documentation for MyFuture Platform

## 📋 Overview

This document provides comprehensive information about the testing setup for the MyFuture platform, including unit tests, feature tests, and testing best practices.

## 🎯 Test Coverage

### ✅ Unit Tests (76 tests, 180 assertions)

#### **User Tests** (`tests/Unit/UserTest.php`)
- ✅ User creation and validation
- ✅ Profile completion checks
- ✅ Role-based functionality (admin/student)
- ✅ 2FA status management
- ✅ User approval/rejection workflows
- ✅ Relationship testing (applications, notifications)
- ✅ Field validation and security

#### **Application Tests** (`tests/Unit/ApplicationTest.php`)
- ✅ Application creation and management
- ✅ Status and priority updates
- ✅ Country and program filtering
- ✅ Urgent application identification
- ✅ Relationship testing (user, documents, steps)
- ✅ Statistical queries and counts

#### **Document Tests** (`tests/Unit/DocumentTest.php`)
- ✅ Document upload and management
- ✅ Status validation (pending, approved, rejected)
- ✅ Document type categorization
- ✅ Validation workflow testing
- ✅ Relationship testing (application, user, validator)
- ✅ File path and metadata management

#### **Message Tests** (`tests/Unit/MessageTest.php`)
- ✅ Message creation and management
- ✅ Read/unread status tracking
- ✅ Sender/receiver relationships
- ✅ Application-specific messaging
- ✅ Conversation management
- ✅ Message filtering and search

### 🔧 Test Factories

#### **UserFactory** (`database/factories/UserFactory.php`)
```php
// Create different user types
User::factory()->admin()->create();
User::factory()->student()->create();
User::factory()->approved()->create();
User::factory()->with2FA()->create();
```

#### **ApplicationFactory** (`database/factories/ApplicationFactory.php`)
```php
// Create applications with specific attributes
Application::factory()->pending()->create();
Application::factory()->highPriority()->create();
Application::factory()->usUniversity()->create();
Application::factory()->computerScience()->create();
```

#### **DocumentFactory** (`database/factories/DocumentFactory.php`)
```php
// Create documents with specific types and statuses
Document::factory()->transcript()->create();
Document::factory()->approved()->create();
Document::factory()->pdf()->create();
```

#### **MessageFactory** (`database/factories/MessageFactory.php`)
```php
// Create messages with specific attributes
Message::factory()->unread()->create();
Message::factory()->fromAdminToStudent()->create();
Message::factory()->applicationUpdate()->create();
```

## 🚀 Running Tests

### Prerequisites
```bash
# Install dependencies
composer install

# Set up testing environment
cp .env.example .env.testing
```

### Test Commands

#### Run All Tests
```bash
php artisan test
```

#### Run Specific Test Suites
```bash
# Unit tests only
php artisan test --testsuite=Unit

# Feature tests only
php artisan test --testsuite=Feature
```

#### Run Specific Test Files
```bash
# Run User tests
php artisan test tests/Unit/UserTest.php

# Run Application tests
php artisan test tests/Unit/ApplicationTest.php

# Run Document tests
php artisan test tests/Unit/DocumentTest.php

# Run Message tests
php artisan test tests/Unit/MessageTest.php
```

#### Run Specific Test Methods
```bash
# Run a specific test method
php artisan test --filter=it_can_create_a_user
```

#### Test with Coverage (if available)
```bash
# Run tests with coverage report
php artisan test --coverage
```

## 🛠️ Test Configuration

### PHPUnit Configuration (`phpunit.xml`)
```xml
<php>
    <env name="APP_ENV" value="testing"/>
    <env name="BCRYPT_ROUNDS" value="4"/>
    <env name="CACHE_DRIVER" value="array"/>
    <env name="DB_CONNECTION" value="sqlite"/>
    <env name="DB_DATABASE" value=":memory:"/>
    <env name="MAIL_MAILER" value="array"/>
    <env name="QUEUE_CONNECTION" value="sync"/>
    <env name="SESSION_DRIVER" value="array"/>
    <env name="TELESCOPE_ENABLED" value="false"/>
</php>
```

### Database Configuration
- **Testing Database**: SQLite in-memory database
- **Migrations**: Automatically run for each test
- **Factories**: Generate realistic test data
- **Cleanup**: Database is reset between tests

## 📊 Test Statistics

### Current Test Results
```
Tests:    76 passed (180 assertions)
Duration: 6.91s
```

### Test Categories
- **Model Tests**: 76 tests covering all core models
- **Relationship Tests**: 24 tests for model relationships
- **Business Logic Tests**: 32 tests for application logic
- **Validation Tests**: 20 tests for data validation

## 🔍 Testing Best Practices

### 1. Test Naming Convention
```php
/** @test */
public function it_can_create_a_user()
{
    // Test implementation
}
```

### 2. Database Testing
```php
use RefreshDatabase, WithFaker;

// Each test gets a fresh database
// Faker provides realistic test data
```

### 3. Factory Usage
```php
// Create single instance
$user = User::factory()->create();

// Create multiple instances
$users = User::factory()->count(5)->create();

// Create with specific attributes
$admin = User::factory()->admin()->approved()->create();
```

### 4. Relationship Testing
```php
// Test relationships exist
$this->assertInstanceOf(User::class, $application->user);

// Test relationship data
$this->assertEquals($user->id, $application->user->id);
```

### 5. Assertion Examples
```php
// Status assertions
$response->assertStatus(200);

// View assertions
$response->assertViewIs('admin.dashboard');

// Data assertions
$response->assertViewHas('total_applications', 10);

// Database assertions
$this->assertDatabaseHas('users', ['email' => 'test@example.com']);
```

## 🐛 Troubleshooting

### Common Issues

#### 1. Database Connection Errors
```bash
# Ensure SQLite is available
php artisan config:cache --env=testing
```

#### 2. Factory Errors
```bash
# Clear cache and regenerate autoload
composer dump-autoload
php artisan config:clear
```

#### 3. Test Timeout Issues
```bash
# Increase memory limit for tests
php -d memory_limit=512M artisan test
```

### Debugging Tests
```php
// Add debugging to tests
dd($user->toArray());

// Use Laravel's dump and die
dump($response->getContent());
```

## 📈 Continuous Integration

### GitHub Actions Example
```yaml
name: Tests
on: [push, pull_request]
jobs:
  test:
    runs-on: ubuntu-latest
    steps:
      - uses: actions/checkout@v2
      - name: Setup PHP
        uses: shivammathur/setup-php@v2
        with:
          php-version: '8.1'
      - name: Install dependencies
        run: composer install
      - name: Run tests
        run: php artisan test
```

## 🎯 Future Testing Improvements

### Planned Enhancements
1. **API Testing**: Add tests for REST API endpoints
2. **Browser Testing**: Add Dusk tests for frontend functionality
3. **Performance Testing**: Add tests for application performance
4. **Security Testing**: Add tests for security vulnerabilities
5. **Integration Testing**: Add tests for external service integrations

### Test Coverage Goals
- **Unit Tests**: 95% coverage
- **Feature Tests**: 80% coverage
- **Integration Tests**: 70% coverage

## 📚 Additional Resources

- [Laravel Testing Documentation](https://laravel.com/docs/testing)
- [PHPUnit Documentation](https://phpunit.de/documentation.html)
- [Faker Documentation](https://fakerphp.github.io/)

---

**Last Updated**: August 17, 2025  
**Test Suite Version**: 1.0.0  
**Maintained By**: MyFuture Development Team

