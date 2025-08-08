# Gmail SMTP Setup Instructions

To enable email verification with Gmail, you need to configure your `.env` file with the correct SMTP settings and generate an App Password for Gmail.

## Step 1: Enable 2-Factor Authentication on Gmail

1. Go to your Google Account settings: https://myaccount.google.com/
2. Click on "Security" in the left sidebar
3. Under "Signing in to Google", enable "2-Step Verification"
4. Follow the setup process to enable 2FA

## Step 2: Generate an App Password

1. After enabling 2FA, go back to the Security section
2. Under "Signing in to Google", click on "App passwords"
3. Select "Mail" as the app and "Other (Custom name)" as the device
4. Enter "MyFuture Platform" as the custom name
5. Click "Generate"
6. Copy the 16-character password (it will look like: `abcd efgh ijkl mnop`)

## Step 3: Update Your .env File

Update your `.env` file with these settings:

```env
MAIL_MAILER=smtp
MAIL_HOST=smtp.gmail.com
MAIL_PORT=587
MAIL_USERNAME=myfuture.plateform@gmail.com
MAIL_PASSWORD=your_16_character_app_password_here
MAIL_ENCRYPTION=tls
MAIL_FROM_ADDRESS="myfuture.plateform@gmail.com"
MAIL_FROM_NAME="MyFuture Platform"
```

Replace `your_16_character_app_password_here` with the app password you generated in Step 2.

## Step 4: Test Email Sending

You can test if emails are working by registering a new user. The system will:

1. Send a verification email from `myfuture.plateform@gmail.com`
2. User clicks the verification link
3. User's email is verified
4. User is redirected to pending approval page

## Important Notes

- Never use your regular Gmail password in the `.env` file
- Always use the 16-character App Password
- Make sure 2-Factor Authentication is enabled on the Gmail account
- The Gmail account `myfuture.plateform@gmail.com` must exist and have the above setup completed

## Email Flow

1. User registers → Custom email verification sent from `myfuture.plateform@gmail.com`
2. User clicks verification link → Email verified
3. User tries to login → Redirected to pending approval (admin must approve)
4. Admin approves user → User can access the system
5. User must complete profile → User can create applications

This ensures a secure multi-step process for user onboarding.
