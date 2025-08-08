# Two-Factor Authentication (2FA) Implementation

This Laravel application now includes comprehensive Two-Factor Authentication (2FA) using Google Authenticator and compatible TOTP apps.

## Features

✅ **Enable/Disable 2FA**: Users can easily enable or disable 2FA from their profile settings  
✅ **QR Code Generation**: Automatic QR code generation for easy setup with authenticator apps  
✅ **TOTP Verification**: 6-digit time-based one-time password verification  
✅ **Secure Storage**: 2FA secrets are encrypted and securely stored in the database  
✅ **Login Flow Integration**: Seamless integration with existing login process  
✅ **Middleware Protection**: Automatic protection of sensitive routes  
✅ **Recovery Options**: Ability to reset/regenerate 2FA secrets  
✅ **Compatible Apps**: Works with Google Authenticator, Microsoft Authenticator, Authy, etc.

## Installation & Setup

### 1. Package Installation
The required packages are already installed:
```bash
composer require pragmarx/google2fa-laravel
```

### 2. Database Migration
The migration has been created and run:
```bash
php artisan migrate
```

This adds the following fields to the `users` table:
- `google2fa_secret` (nullable) - Stores the encrypted TOTP secret
- `google2fa_enabled` (boolean) - Whether 2FA is enabled for the user
- `google2fa_enabled_at` (timestamp) - When 2FA was enabled

### 3. Routes
The following routes have been added:

**Setup Routes (Authenticated)**:
- `GET /2fa/setup` - Show 2FA setup page
- `POST /2fa/enable` - Enable 2FA after verification
- `POST /2fa/disable` - Disable 2FA after verification
- `POST /2fa/reset` - Reset/regenerate 2FA secret

**Verification Routes (Login Process)**:
- `GET /2fa/verify` - Show 2FA verification page during login
- `POST /2fa/verify` - Verify 2FA code during login

### 4. Middleware
The `TwoFactorAuth` middleware has been applied to:
- All student dashboard routes
- All admin dashboard routes

Exception routes that bypass 2FA verification:
- 2FA setup/management routes
- Password change route
- Logout route

## User Guide

### For End Users

#### Enabling 2FA

1. **Access Settings**: Click on your name in the top navigation → "Authentification 2FA"

2. **Scan QR Code**: 
   - Open your authenticator app (Google Authenticator, Authy, etc.)
   - Scan the QR code displayed on the setup page
   - Alternatively, manually enter the secret key shown

3. **Verify Setup**:
   - Enter the 6-digit code from your authenticator app
   - Click "Enable 2FA"
   - You'll see a success message confirming activation

#### Using 2FA

1. **Login Process**:
   - Enter your email and password as usual
   - If 2FA is enabled, you'll be redirected to the verification page
   - Enter the current 6-digit code from your authenticator app
   - Click "Verify & Continue" to complete login

2. **Features**:
   - Auto-submit when 6 digits are entered
   - Automatic input formatting (digits only)
   - Copy/paste support for codes

#### Managing 2FA

**Reset Secret Key**:
- Generates a new secret and QR code
- Automatically disables 2FA (must be re-enabled with new code)
- Useful if you lose access to your authenticator app

**Disable 2FA**:
- Requires current 6-digit code for verification
- Completely removes 2FA protection from your account
- Can be re-enabled at any time

### For Developers

#### Controllers

**TwoFactorController**:
- `show()` - Display 2FA setup page with QR code
- `enable()` - Enable 2FA after code verification
- `disable()` - Disable 2FA after code verification
- `reset()` - Reset 2FA secret key
- `showVerify()` - Display verification page during login
- `verify()` - Verify code during login process

#### Middleware

**TwoFactorAuth**:
- Checks if user has 2FA enabled
- Redirects to verification if not verified in session
- Allows access to certain routes without verification
- Applied to protected dashboard routes

#### User Model Updates

New fields and methods:
```php
// Fillable fields
'google2fa_secret', 'google2fa_enabled', 'google2fa_enabled_at'

// Hidden fields (security)
'google2fa_secret'

// Casts
'google2fa_enabled' => 'boolean'
'google2fa_enabled_at' => 'datetime'
```

#### Session Management

- `2fa_verified` session key tracks verification status
- Set to true after successful 2FA verification
- Cleared on logout and when 2FA is disabled
- Required for accessing protected routes

## Security Features

### 1. Secret Protection
- TOTP secrets are stored encrypted in database
- Secrets are hidden from API responses and serialization
- New secrets generated when reset

### 2. Verification Process
- Time-based verification (30-second windows)
- Prevents replay attacks through TOTP algorithm
- Requires current password for sensitive operations

### 3. Session Security
- 2FA verification tied to session
- Must re-verify after logout
- Session invalidation clears verification status

### 4. Route Protection
- Middleware automatically protects sensitive routes
- Granular control over which routes require 2FA
- Bypass routes for 2FA management

## Compatible Authenticator Apps

✅ **Google Authenticator** (iOS/Android)  
✅ **Microsoft Authenticator** (iOS/Android)  
✅ **Authy** (iOS/Android/Desktop)  
✅ **1Password** (Built-in TOTP)  
✅ **Bitwarden** (Built-in TOTP)  
✅ **Any RFC 6238 compliant TOTP app**

## Troubleshooting

### Common Issues

**"Invalid verification code"**:
- Ensure device clock is synchronized
- Try waiting for next code (30-second refresh)
- Verify you're using the correct account in authenticator

**Can't access 2FA setup**:
- Check if you're logged in
- Ensure middleware isn't blocking the route
- Clear browser cache and cookies

**Lost access to authenticator**:
- Contact administrator for manual 2FA reset
- Use account recovery procedures
- Admin can disable 2FA from user management panel

### Development Tips

**Testing 2FA**:
- Use the pragmarx/google2fa package directly for testing
- Verify TOTP codes in PHP: `$google2fa->verifyKey($secret, $code)`
- Test time synchronization issues

**QR Code Generation**:
- Uses Google Charts API for QR codes (no external dependencies)
- Format: `otpauth://totp/LABEL?secret=SECRET&issuer=ISSUER`
- Can be customized for different QR code providers

## Admin Features

### User Management
Administrators can:
- View 2FA status for all users
- Force disable 2FA for users who lost access
- Monitor 2FA adoption rates
- Reset user 2FA settings

### Security Monitoring
- Track 2FA enablement timestamps
- Monitor authentication patterns
- Identify security anomalies

## Configuration

### Environment Variables
No additional environment variables required. Uses existing Laravel session and database configuration.

### Customization Options
- QR code size and styling
- TOTP window tolerance
- Session timeout settings
- Allowed bypass routes

## API Integration

The 2FA system integrates seamlessly with:
- Laravel Sanctum (API authentication)
- Custom authentication guards
- Multi-tenant applications
- SSO implementations

## Conclusion

This 2FA implementation provides enterprise-grade security while maintaining excellent user experience. It follows Laravel best practices and integrates seamlessly with existing authentication flows.

For questions or support, refer to the Laravel documentation or the pragmarx/google2fa-laravel package documentation.
