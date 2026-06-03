# 🛒 Laravel E-commerce API

A fully-featured RESTful e-commerce API built with Laravel, covering email notifications, background queue processing, event-driven architecture, and database notifications.

---

## 📋 Table of Contents

- [Features](#features)
- [Tech Stack](#tech-stack)
- [Getting Started](#getting-started)
- [Environment Variables](#environment-variables)
- [Database Setup](#database-setup)
- [Running the App](#running-the-app)
- [API Endpoints](#api-endpoints)
- [Architecture Overview](#architecture-overview)
- [Testing the API](#testing-the-api)
- [Queue Monitoring](#queue-monitoring)
- [Project Structure](#project-structure)

---

## ✨ Features

- **Authentication** — Register, login, logout via Laravel Sanctum
- **Email Verification** — Verify account via email link
- **Password Reset** — Forgot/reset password via email token
- **Product Management** — CRUD with image upload, filtering, searching, and pagination
- **Order Management** — Place orders, track status, cancel with automatic stock restoration
- **Email Notifications** — Order confirmation and status update emails (HTML templates)
- **Queue System** — Non-blocking background job processing for emails and stock updates
- **Event-Driven Architecture** — `OrderPlaced` and `OrderStatusChanged` events with multiple listeners
- **Database Notifications** — In-app notifications with read/unread tracking

---

## 🛠 Tech Stack

- **Framework:** Laravel 12
- **Auth:** Laravel Sanctum
- **Queue:** Database driver
- **Mail:** Log driver (for local dev) / SMTP (for production)
- **Database:** PostgreSQL
- **Storage:** Local public disk (for product images)

---

## 🚀 Getting Started

### Prerequisites

- PHP >= 8.2
- Composer
- PostgreSQL
- Node.js (optional, for asset compilation)

### Installation

```bash
git clone https://github.com/[YOUR_GITHUB_USERNAME]/[YOUR_REPO_NAME].git
cd [YOUR_REPO_NAME]

composer install

cp .env.example .env
php artisan key:generate - for internal use such as sessions, signed URLs, password reset tokens.
```

---

## ⚙️ Environment Variables

Update your `.env` file with the following:

```env
APP_NAME="[YOUR_APP_NAME]"
APP_URL=http://localhost:8000

DB_CONNECTION=pgsql
DB_HOST=127.0.0.1
DB_PORT=5433
DB_DATABASE=[YOUR_DATABASE_NAME]
DB_USERNAME=[YOUR_DB_USERNAME]
DB_PASSWORD=[YOUR_DB_PASSWORD]

# Mail — use 'log' for local testing, configure SMTP for production
MAIL_MAILER=log
MAIL_FROM_ADDRESS=[YOUR_FROM_EMAIL]        # e.g. noreply@shop.com
MAIL_FROM_NAME="[YOUR_SHOP_NAME]"          # e.g. "My Shop"

# For production SMTP, also set:
# MAIL_HOST=smtp.mailtrap.io
# MAIL_PORT=2525
# MAIL_USERNAME=[YOUR_SMTP_USERNAME]
# MAIL_PASSWORD=[YOUR_SMTP_PASSWORD]
# MAIL_ENCRYPTION=tls

QUEUE_CONNECTION=database

FILESYSTEM_DISK=public
```

---

## 🗄️ Database Setup

```bash
# Create the database first
psql -U root -p 5433 -d postgres -c "CREATE DATABASE [YOUR_DATABASE_NAME];"

# Install Sanctum and run migrations
php artisan install:api
php artisan queue:table
php artisan notification:table
php artisan cache:table
php artisan migrate

# Create storage symlink for image uploads
php artisan storage:link

# Seed the database with sample products
php artisan db:seed
```

---

## ▶️ Running the App

You'll need **two terminal windows** running simultaneously:

**Terminal 1 — Laravel dev server:**
```bash
php artisan serve
```

**Terminal 2 — Queue worker (required for emails & background jobs):**
```bash
php artisan queue:work
```

> The queue worker must be running for emails, stock updates, and notifications to process.

---

## 📡 API Endpoints

Base URL: `http://localhost:8000/api/v1`

### Auth

| Method | Endpoint | Auth | Description |
|--------|----------|------|-------------|
| POST | `/register` | ❌ | Register new user |
| POST | `/login` | ❌ | Login |
| POST | `/logout` | ✅ | Logout |
| GET | `/me` | ✅ | Get current user |
| POST | `/forgot-password` | ❌ | Send password reset link |
| POST | `/reset-password` | ❌ | Reset password with token |
| POST | `/email/verification-notification` | ✅ | Resend verification email |
| GET | `/email/verify/{id}/{hash}` | ❌ | Verify email address |

### Products

| Method | Endpoint | Auth | Description |
|--------|----------|------|-------------|
| GET | `/products` | ❌ | List all products (with filters) |
| GET | `/products/{slug}` | ❌ | Get single product |
| POST | `/products` | ✅ | Create product (with image upload) |
| PUT | `/products/{id}` | ✅ | Update product |
| DELETE | `/products/{id}` | ✅ | Delete product |

**Available query filters:**
- `?search=keyboard` — search by name, description, or SKU
- `?in_stock=true` — only in-stock items
- `?on_sale=true` — only items with a compare price
- `?min_price=50&max_price=150` — price range
- `?sort_by=price&sort_order=asc` — sorting
- `?per_page=10` — pagination

### Orders

| Method | Endpoint | Auth | Description |
|--------|----------|------|-------------|
| GET | `/orders` | ✅ | List my orders |
| POST | `/orders` | ✅ | Place a new order |
| GET | `/orders/{id}` | ✅ | Get order details |
| PUT | `/orders/{id}/status` | ✅ | Update order status |
| POST | `/orders/{id}/cancel` | ✅ | Cancel an order |

### Notifications

| Method | Endpoint | Auth | Description |
|--------|----------|------|-------------|
| GET | `/notifications` | ✅ | Get all notifications |
| GET | `/notifications/unread` | ✅ | Get unread notifications |
| POST | `/notifications/{id}/read` | ✅ | Mark one as read |
| POST | `/notifications/read-all` | ✅ | Mark all as read |
| DELETE | `/notifications/{id}` | ✅ | Delete a notification |

> All protected routes require `Authorization: Bearer YOUR_TOKEN` header.

---

## 🏗 Architecture Overview

### Event Flow — Order Placed

```
POST /orders
    ↓
OrderController@store
    ↓ (DB transaction)
Order + OrderItems created
    ↓
OrderPlaced event fired
    ↓ (dispatched to queue)
├── SendOrderConfirmation   → Email to customer
├── UpdateProductStock      → Decrement stock quantities
└── CreateOrderNotification → Database notification for user
```

### Event Flow — Order Status Changed

```
PUT /orders/{id}/status
    ↓
OrderController@updateStatus
    ↓
OrderStatusChanged event fired
    ↓ (dispatched to queue)
└── SendOrderStatusNotification → Status update email to customer
```

### Why Queues?

All email sending and stock updates run in the background. The API responds immediately to the user, the queue worker handles the heavy lifting asynchronously. This means:

- **No request timeouts** due to slow email providers
- **Automatic retry** on failure
- **Scalable** - multiple queue workers can run in parallel

---

## 🧪 Testing the API

### Register & get token

```bash
curl -X POST http://localhost:8000/api/v1/register \
  -H "Content-Type: application/json" \
  -H "Accept: application/json" \
  -d '{
    "name": "John Doe",
    "email": "john@example.com",
    "password": "password123",
    "password_confirmation": "password123"
  }'
```

### Place an order (triggers full event chain)

```bash
curl -X POST http://localhost:8000/api/v1/orders \
  -H "Content-Type: application/json" \
  -H "Accept: application/json" \
  -H "Authorization: Bearer YOUR_TOKEN" \
  -d '{
    "items": [
      { "product_id": 1, "quantity": 2 }
    ],
    "shipping_name": "John Doe",
    "shipping_email": "john@example.com",
    "shipping_phone": "+1234567890",
    "shipping_address": "123 Main Street",
    "shipping_city": "New York",
    "shipping_state": "NY",
    "shipping_zip": "10001",
    "shipping_country": "USA"
  }'
```

### Check emails in log (local dev)

```bash
tail -f storage/logs/laravel.log
```

---

## 📊 Queue Monitoring

```bash
# Watch queue worker output in real time
php artisan queue:work

# View failed jobs
php artisan queue:failed

# Retry all failed jobs
php artisan queue:retry all

# Clear all failed jobs
php artisan queue:flush
```

---

## 📁 Project Structure

```
app/
├── Events/
│   ├── OrderPlaced.php
│   └── OrderStatusChanged.php
├── Listeners/
│   ├── SendOrderConfirmation.php
│   ├── SendOrderStatusNotification.php
│   ├── UpdateProductStock.php
│   └── CreateOrderNotification.php
├── Mail/
│   ├── OrderConfirmation.php
│   └── OrderStatusUpdate.php
├── Notifications/
│   └── OrderPlacedNotification.php
├── Http/
│   ├── Controllers/Api/
│   │   ├── AuthController.php
│   │   ├── ProductController.php
│   │   ├── OrderController.php
│   │   └── NotificationController.php
│   ├── Requests/
│   │   ├── StoreProductRequest.php
│   │   ├── UpdateProductRequest.php
│   │   └── PlaceOrderRequest.php
│   └── Resources/
│       ├── ProductResource.php
│       ├── OrderResource.php
│       ├── OrderItemResource.php
│       └── UserResource.php
├── Models/
│   ├── User.php
│   ├── Product.php
│   ├── Order.php
│   └── OrderItem.php
└── Providers/
    └── EventServiceProvider.php

resources/views/emails/
├── order-confirmation.blade.php
└── order-status-update.blade.php
```

---

## 📝 Notes

- This project uses `MAIL_MAILER=log` for local development. All emails are written to `storage/logs/laravel.log` instead of being sent.
- For production, swap the mail driver to SMTP (e.g. Mailtrap, Mailgun, SES) and configure the relevant `.env` values.
- The queue driver is set to `database`. For production, consider Redis for better performance.
- Admin-only product management is simplified in this project (any authenticated user can create/edit/delete products). Add a roles/permissions layer (e.g. Spatie) for a real production app.
