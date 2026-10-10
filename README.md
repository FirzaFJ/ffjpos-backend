# ⚙️ FFJ Mart POS - Backend API (Laravel)

This is the core backend RESTful API for the **FFJ Mart Point of Sale (POS)** system. Built with **Laravel**, it manages business logic, database transactions, inventory tracking, user authentication, and API endpoints for consumption by the React frontend.

The entire backend and its MySQL database are hosted on **InfinityFree**.

---

## 🏗️ Architecture Overview

- **Architecture:** Decoupled / API-Driven Architecture
- **Backend Framework:** Laravel (PHP)
- **Database Engine:** MySQL (Hosted on InfinityFree Hosting Server)
- **Host Server:** InfinityFree Hosting (`https://ffjmart-backend.wuaze.com`)
- **Cross-Origin Security:** Configured via Laravel CORS middleware to allow requests from the Vercel-hosted frontend.

---

## 🛠️ Tech Stack & Requirements

- **PHP Version:** 8.1 / 8.2
- **Framework:** Laravel 10.x / 11.x
- **Database:** MySQL
- **Dependencies:** Composer

---

## 🗄️ Database Setup (InfinityFree MySQL)

The application uses an InfinityFree-hosted MySQL database. 

### Database Connection Configuration
In your `.env` file, configure your database details provided by your InfinityFree Control Panel (vPanel):

```env
DB_CONNECTION=mysql
DB_HOST=sqlxxx.infinityfree.com     # Replace with your MySQL Hostname from vPanel
DB_PORT=3306                         # Default MySQL port
DB_DATABASE=if0_xxxxx_ffjmart       # Replace with your InfinityFree DB Name
DB_USERNAME=if0_xxxxx               # Replace with your InfinityFree vPanel Username
DB_PASSWORD=your_vpanel_password    # Replace with your InfinityFree vPanel Password
```

---

## 🔒 CORS Configuration (`config/cors.php`)

To enable seamless communication between the Vercel-hosted frontend and the InfinityFree-hosted backend, Cross-Origin Resource Sharing (CORS) is enabled across all API endpoints.

File location: `config/cors.php`

```php
<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Cross-Origin Resource Sharing (CORS) Configuration
    |--------------------------------------------------------------------------
    */

    'paths' => ['api/*', 'sanctum/csrf-cookie'],

    'allowed_methods' => ['*'],

    'allowed_origins' => ['*'], // Permits requests from Vercel deployment URLs

    'allowed_origins_patterns' => [],

    'allowed_headers' => ['*'],

    'exposed_headers' => [],

    'max_age' => 0,

    'supports_credentials' => false,

];
```

---

## 📡 REST API Endpoint Reference

All API routes are prefixed with `/api`. Key endpoints include:

| Method | Endpoint | Description |
| :--- | :--- | :--- |
| `GET` | `/api/products` | Retrieve list of all available products |
| `POST` | `/api/products` | Add a new product to inventory |
| `GET` | `/api/categories` | Retrieve all product categories |
| `POST` | `/api/transactions` | Submit and process a sales transaction |
| `GET` | `/api/settings` | Retrieve store configuration settings |

---

## 💻 Local Development Setup

To run this backend locally for testing or development:

1. **Clone the repository:**
   ```bash
   git clone https://github.com/FirzaFJ/ffjpos-backend.git
   cd ffjpos-backend
   ```

2. **Install Composer Dependencies:**
   ```bash
   composer install
   ```

3. **Configure Environment:**
   Copy `.env.example` to `.env`:
   ```bash
   cp .env.example .env
   ```

4. **Generate Application Key:**
   ```bash
   php artisan key:generate
   ```

5. **Run Migrations & Seeders:**
   ```bash
   php artisan migrate --seed
   ```

6. **Serve Application:**
   ```bash
   php artisan serve
   ```
   The local server will run on `http://127.0.0.1:8000`.

---

## 🌐 Deployment to InfinityFree

1. Upload your Laravel project files to the `htdocs` directory via FTP or File Manager.
2. Configure `.env` with your InfinityFree database credentials.
3. Import your database schema using `phpMyAdmin` available in the InfinityFree Control Panel.
4. Ensure your domain points correctly to the `public/` directory or adjust routing via `.htaccess`.

---

## 📜 License

This system is built for **FFJ**. All rights reserved.
