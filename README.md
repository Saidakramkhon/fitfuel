🍽️ FitFuel – Secure Food Ordering Platform

Overview

FitFuel is a secure web-based food ordering platform developed using PHP, MySQL, JavaScript, HTML, and CSS. The system allows users to browse healthy meals, manage a shopping cart, place orders, and provides administrators with a complete product management interface.

This version extends the original web application with a Three-Factor Authentication (3FA) mechanism implemented as part of the System Security project.

⸻

Features

User Features

* User registration
* Secure login
* Three-Factor Authentication (Password + Email OTP + Security Phrase)
* Password reset via email
* Browse meal catalog
* Search and filter products
* Shopping cart
* Checkout
* Session management

Administrator Features

* Administrator authentication
* Create products
* Update products
* Delete products
* Manage product catalog

Security Features

* Password hashing using password_hash()
* Password verification using password_verify()
* Email One-Time Password (OTP)
* Security Phrase verification
* Password reset tokens
* OTP expiration
* One-time OTP usage
* Login attempt tracking
* Security event logging
* Session-based authentication

⸻

Technologies

* PHP 8
* MySQL
* HTML5
* CSS3
* JavaScript (ES6)
* PHPMailer
* Composer
* XAMPP

⸻

Authentication Workflow

Email + Password
        │
        ▼
Password Verification
        │
        ▼
Generate Email OTP
        │
        ▼
OTP Verification
        │
        ▼
Security Phrase Verification
        │
        ▼
Create Session
        │
        ▼
Redirect to Shop / Admin Panel

⸻

Project Structure

FitFuel
│
├── backend
│   ├── api
│   │   ├── auth
│   │   ├── admin
│   │   └── orders
│   │
│   └── config
│
├── frontend
│   ├── css
│   ├── js
│   ├── images
│   └── *.html
│
├── vendor
│
├── composer.json
└── README.md

⸻

Database Tables

* users
* products
* orders
* order_items
* otp_codes
* security_phrases
* password_reset_tokens
* security_logs
* login_attempts

⸻

Installation

1. Clone the repository.

git clone https://github.com/Saidakramkhon/fitfuel.git

2. Import the FitFuel MySQL database.
3. Install PHP dependencies.

composer install

4. Configure email credentials in:

backend/config/mail_secret.php

5. Start Apache and MySQL using XAMPP.
6. Open:

http://localhost/fitfuel/frontend/

or

http://localhost/fitfuel-3fa/frontend/

depending on your local project folder.

⸻

Security Architecture

The authentication system is divided into three independent verification stages:

1. Password verification
2. Email OTP verification
3. Security Phrase verification

Only after all three stages are successfully completed is a valid user session created.

⸻

Screenshots

* Login Page
* Registration Page
* OTP Verification
* Security Phrase Verification
* Password Reset
* Shop
* Shopping Cart
* Admin Dashboard
* Product Management

⸻

Future Improvements

* HTTPS deployment
* Google Authenticator (TOTP)
* WebAuthn / Passkeys
* Trusted devices
* Account recovery improvements
* IP-based rate limiting
* Email verification during registration

⸻

Author

Saidakramkhon Ismonov

University of Messina

Bachelor’s Degree in Data Analysis

System Security Project
