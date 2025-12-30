🏠 DreamStay - Backend API

The backend REST API for the DreamStay ecosystem. Built with Laravel, it powers the mobile application and admin dashboard by handling authentication, complex booking logic, property management, and real-time notifications via Firebase.

⚡ Key Capabilities

Secure Authentication: Token-based auth using Laravel Sanctum.

Booking Engine: Validates date ranges, prevents overlaps, and handles status transitions.

Notification System: Integrates with Google FCM HTTP v1 API for push notifications.

Owner/Admin Features: Earnings calculation, apartment management, and user moderation.

🛠 Prerequisites

PHP: 8.1 or higher.

Composer.

MySQL Database.

🚀 Installation Guide

Clone the Repository

git clone [https://github.com/yamenhawari/residential_booking_app_backend.git](https://github.com/yamenhawari/residential_booking_app_backend.git)
cd residential_booking_app_backend

Install Dependencies

composer install

Environment Setup

cp .env.example .env
php artisan key:generate

Database Configuration
Update .env with your DB_DATABASE, DB_USERNAME, and DB_PASSWORD.

Migrations & Storage

php artisan migrate
php artisan storage:link

⚠️ Firebase Configuration
Download your service account JSON from Firebase Console, rename it to firebase_credentials.json, and place it in:
storage/app/firebase_credentials.json

Run the Server

php artisan serve --host 0.0.0.0 --port 8000

📚 API Overview

POST /api/register - Create account.

POST /api/login - Auth with Sanctum.

GET /api/apartment - Search listings.

POST /api/bookings - Create booking request.
