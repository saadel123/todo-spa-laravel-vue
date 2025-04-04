# Laravel ToDo SPA with Reminders

![Project Banner](https://raw.githubusercontent.com/saadel123/test-repo/refs/heads/main/testraw/SPA-LARAVEL-VUE-VUETIFY.gif?token=GHSAT0AAAAAAC4R5SWTXGVFEPO7OASZ4GGSZ7QAWDQ)

A To-Do Single Page Application with email reminders, built with:
- **Backend**: Laravel 12 + Rest API + Sanctum
- **Frontend**: Vue 3 + TypeScript + Vuetify
- **Features**: Secure authentication, reminder scheduling, and user-specific todo management

## Key Features

- Sanctum-authenticated API endpoints
- Scheduled email reminders
- CRUD operations for todos
- Policies to restrict unauthorized user actions
- Responsive Vuetify UI
- Type-safe frontend and backend

## Installation Guide

### Prerequisites

| Software         | Version | Installation Guide                                              |
|------------------|---------|-----------------------------------------------------------------|
| PHP              | ≥ 8.2   | [php.net](https://www.php.net/download)                         |
| Composer         | Latest  | [getcomposer.org](https://getcomposer.org/download/)            |
| Node.js          | ≥ 18.x  | [nodejs.org](https://nodejs.org/)                                |
| MySQL/PostgreSQL | ≥ 5.7   | [mysql.com](https://dev.mysql.com/downloads/)                    |

### 1. Clone Repository

```bash
git clone https://github.com/YOUR_USERNAME/todo-spa.git
cd todo-spa
```

### 2. Environment Setup

```bash
cp .env.example .env
# After copying the .env file, clear the configuration and cache:
php artisan config:clear
php artisan cache:clear
```

Configure these key variables in `.env`:

```ini
APP_URL=http://localhost:8000
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=your_db_name
DB_USERNAME=root
DB_PASSWORD=

# Sanctum Configuration
SANCTUM_STATEFUL_DOMAINS=localhost
SESSION_DOMAIN=localhost

```

### 3. Install Dependencies

```bash
# Backend
composer install

# Frontend
cd frontend
npm install
```

### 4. Application Setup

```bash
# Generate app key
php artisan key:generate

# Run migrations with seed
php artisan migrate --seed
```

_Default test user created:_
- **Email:** test@example.com
- **Password:** password

### 5. Start Development Servers

**Terminal 1 – Backend:**

```bash
php artisan serve
```

**Terminal 2 – Frontend:**

```bash
npm run dev
```

**Terminal 3 – Scheduler (for reminders):**

```bash
php artisan schedule:work
```

### Testing the Application

- **Access:** [http://localhost:8000](http://localhost:8000)
- **Login:** Use test credentials.
- **Create todos:** Add reminder dates so that the user is notified 15 minutes before the task is due.

### Email Testing Setup

To test the schedule locally, you can use **Mailtrap** or **Mailpit**