# 🚀 ProjectFlow — Jira-Style Project & Task Management System

[![PHP Version](https://img.shields.io/badge/PHP-8.2-777BB4?style=for-the-badge&logo=php&logoColor=white)](https://www.php.net/)
[![AngularJS](https://img.shields.io/badge/AngularJS-1.8-E23237?style=for-the-badge&logo=angularjs&logoColor=white)](https://angularjs.org/)
[![Bootstrap](https://img.shields.io/badge/Bootstrap-5.3-7952B3?style=for-the-badge&logo=bootstrap&logoColor=white)](https://getbootstrap.com/)
[![PostgreSQL](https://img.shields.io/badge/PostgreSQL-Supabase-336791?style=for-the-badge&logo=postgresql&logoColor=white)](https://supabase.com/)
[![Docker](https://img.shields.io/badge/Docker-Ready-2496ED?style=for-the-badge&logo=docker&logoColor=white)](https://www.docker.com/)
[![Render](https://img.shields.io/badge/Deploy-Render-46E3B7?style=for-the-badge&logo=render&logoColor=black)](https://render.com/)

> **ProjectFlow** is a modern, Jira-inspired universal project and task management web application designed for students, developers, and teams. It seamlessly blends the visual workflow of a **Kanban board**, the structured efficiency of a **spreadsheet database**, and the focus of a **rich-text note-taking canvas**.

---

## 🌟 Key Features

### 📋 1. Jira-Style Kanban Board
- **Agile Workflow**: Organize tasks across `To Do`, `In Progress`, `Blocked`, and `Done` status columns.
- **Visual Issue Cards**: Priority indicators (High, Medium, Low), color-coded project category badges, and due date countdown badges.
- **Quick Status Transitions**: Move tasks seamlessly between columns with one-click transitions or card actions.

### 📊 2. High-Density Database View
- **Spreadsheet / Table Layout**: Tabular interface for rapid data entry, syllabus mapping, and backlog scanning.
- **Multi-Filter & Search**: Instant real-time filtering by course/category, status, priority, and text search.
- **Inline Editing & Sorting**: Sort by due date, urgency, priority, or creation time.

### 📝 3. Task Canvas & Notes Workspace
- **Contextual Note Taking**: Dedicated rich-text workspace embedded directly into each task.
- **Essay & Assignment Drafting**: Keep research notes, links, rubrics, and draft outlines attached to their parent issues.

### 🏷️ 4. Course & Category Management
- Categorize assignments and milestones by context (`study`, `work`, `freelance`, `personal`, `other`).
- Custom hex color badges and live task completion counters.

### ⚡ 5. Global Quick-Capture Modal
- Accessible from any view to quickly log an assignment or idea before context switching.

### 🌓 6. Atlassian Design System & Dark Mode
- Pixel-crafted Jira UI inspired by Atlassian Design Guidelines.
- Instant Light/Dark mode switcher with persistent preference saving.

### 🔒 7. Role-Based Access Control (RBAC)
- Secure session-based authentication with Bcrypt password hashing.
- Role management (`student` and `admin`) with a dedicated administrative control panel.

---

## 🏗️ Architecture & Tech Stack

```
┌─────────────────────────────────────────────────────────────┐
│                     Frontend (SPA)                          │
│   AngularJS 1.8 · Bootstrap 5.3 · Bootstrap Icons · CSS3    │
│   (Atlassian Design Tokens, Responsive, Dark/Light Mode)    │
└──────────────────────────────┬──────────────────────────────┘
                               │  JSON REST API / Sessions
┌──────────────────────────────▼──────────────────────────────┐
│                      Backend API                            │
│           PHP 8.2 (Apache / Docker Container)               │
│    PDO PostgreSQL · Prepared Statements · PgBouncer Safe    │
└──────────────────────────────┬──────────────────────────────┘
                               │  Connection Pool (Port 6543/5432)
┌──────────────────────────────▼──────────────────────────────┐
│                    Cloud Database                           │
│               Supabase (PostgreSQL 15+)                     │
│         Relational Schema · Triggers · Foreign Keys         │
└─────────────────────────────────────────────────────────────┘
```

| Layer | Technology | Details |
|---|---|---|
| **Frontend** | AngularJS 1.8, Bootstrap 5.3 | Client-side routing (`ngRoute`), controllers, custom directives |
| **Backend** | PHP 8.2 REST API | Modular endpoint architecture, secure session cookies, PDO |
| **Database** | PostgreSQL (Supabase) | Strict foreign keys, `ON DELETE CASCADE`, auto-timestamp triggers |
| **Container** | Docker | Official `php:8.2-apache` with `libpq-dev` & `pdo_pgsql` |
| **Deployment** | Render.com & Supabase | Automated blueprint (`render.yaml`) deployment |

---

## 📁 Repository Structure

```plaintext
proj-manage/
├── api/                          # Backend REST API endpoints (PHP)
│   ├── check_auth.php            # Session validation endpoint
│   ├── create_course.php         # Create category/course
│   ├── create_task.php           # Create task/issue
│   ├── db_connect.php            # Supabase PostgreSQL PDO connection
│   ├── delete_course.php         # Delete category
│   ├── delete_task.php           # Delete task
│   ├── get_courses.php           # List user courses with task counts
│   ├── get_tasks.php             # List tasks with filtering & tags
│   ├── get_users.php             # Admin user list
│   ├── login.php                 # Authenticate & issue session
│   ├── logout.php                # Destroy session
│   ├── register.php              # Create student account
│   ├── setup_db.php              # Automated schema setup & migrations
│   ├── update_course.php         # Update category details
│   └── update_task.php           # Update task status/notes/priority
├── assets/
│   ├── css/
│   │   ├── bootstrap.min.css     # Bootstrap styling
│   │   └── style.css             # Jira design system & dark mode tokens
│   └── js/
│       ├── app.js                # Angular module & route configuration
│       ├── controllers.js        # Main, Board, Database, Notes, Admin controllers
│       ├── directives.js         # Custom UI directives & helpers
│       ├── services.js           # API HTTP services
│       └── lib/                  # Vendor JavaScript files
├── sql/
│   └── schema.sql                # Complete PostgreSQL schema & indexes
├── views/                        # Single-page app HTML view templates
│   ├── admin.html                # Admin management dashboard
│   ├── dashboard.html            # Kanban board view
│   ├── database.html             # Spreadsheet database view
│   ├── login.html                # User login view
│   ├── notes.html                # Task canvas & rich notes view
│   └── register.html             # Account registration view
├── .env.example                  # Environment configuration template
├── .gitignore                    # Git ignore file (excludes secrets)
├── Dockerfile                    # Container definition for Render/Docker
├── index.html                    # Main HTML shell & top navigation
└── render.yaml                   # Infrastructure-as-code for Render.com
```

---

## ⚙️ Local Development Setup

### Option A: Using XAMPP / Local Apache (Windows / macOS / Linux)

#### 1. Prerequisites
- **PHP 8.1+** with the `pdo_pgsql` and `pgsql` extensions enabled.
- **Apache** (bundled with XAMPP).
- A free **Supabase** account or a local PostgreSQL instance.

#### 2. Clone the Repository
Clone into your web root (e.g. `htdocs/proj-manage`):
```bash
git clone https://github.com/your-username/proj-manage.git
cd proj-manage
```

#### 3. Enable PostgreSQL Extension in PHP
Open your `php.ini` file and make sure the following line is uncommented:
```ini
extension=pdo_pgsql
extension=pgsql
```
*Note: Restart Apache after modifying `php.ini`.*

#### 4. Configure Environment Variables
Copy `.env.example` to `.env`:
```bash
cp .env.example .env
```
Open `.env` and fill in your Supabase or PostgreSQL credentials:
```env
# For IPv4 connection compatibility (e.g. Windows/XAMPP), use the Supabase Transaction Pooler:
DB_HOST=aws-0-YOUR_REGION.pooler.supabase.com
DB_PORT=6543
DB_NAME=postgres
DB_USER=postgres.YOUR_PROJECT_REF
DB_PASS=YOUR_DATABASE_PASSWORD
FRONTEND_URL=*
```

> 💡 **Tip for Supabase Users**: If your local network or Windows environment does not support IPv6, use the **Transaction Pooler** hostname (`pooler.supabase.com` on port `6543`) with the username format `postgres.[YOUR-PROJECT-REF]`.

#### 5. Initialize the Database
Open your browser and navigate to:
```
http://localhost/proj-manage/api/setup_db.php
```
This runs the automated migration script that creates all tables (`users`, `courses`, `tasks`, `tags`, `task_tags`), indices, and triggers.

#### 6. Open the Application
Navigate to:
```
http://localhost/proj-manage/index.html
```

---

### Option B: Using Docker

Run the entire application in a container without configuring local PHP:

```bash
# 1. Build the Docker image
docker build -t projectflow .

# 2. Run the container passing your environment variables
docker run -d -p 8080:80 \
  -e DB_HOST="aws-0-YOUR_REGION.pooler.supabase.com" \
  -e DB_PORT="6543" \
  -e DB_NAME="postgres" \
  -e DB_USER="postgres.YOUR_PROJECT_REF" \
  -e DB_PASS="YOUR_DATABASE_PASSWORD" \
  -e FRONTEND_URL="*" \
  --name projectflow-app projectflow
```
Then visit `http://localhost:8080`.

---

## 🌐 Deploying to Render.com

This repository includes a production-ready `Dockerfile` and a `render.yaml` specification for 1-click deployment on Render.

### Step-by-Step Deployment:

1. **Push to GitHub**:
   Push your repository to GitHub. Ensure `.env` is **not** committed (`.gitignore` protects this).

2. **Create Web Service on Render**:
   - Log in to [Render Dashboard](https://dashboard.render.com/).
   - Click **New +** → **Blueprint** (or **Web Service** selecting Docker).
   - Connect your GitHub repository.

3. **Set Environment Variables**:
   Under the **Environment** tab in your Render service, configure:
   | Key | Example Value | Description |
   |---|---|---|
   | `DB_HOST` | `aws-0-ap-south-1.pooler.supabase.com` | Supabase host |
   | `DB_PORT` | `6543` | 6543 (Pooler) or 5432 (Direct) |
   | `DB_NAME` | `postgres` | Default database name |
   | `DB_USER` | `postgres.your_project_ref` | Database user |
   | `DB_PASS` | `your_supabase_password` | Database password |
   | `FRONTEND_URL` | `https://your-service.onrender.com` | Production URL |

4. **Initialize Production Database**:
   Once the service is deployed, visit:
   ```
   https://your-service.onrender.com/api/setup_db.php
   ```
   Your database schema will be initialized and ready for production traffic.

---

## 👥 Demo Credentials & Seed Data

When you run `setup_db.php` with seed data enabled, the following demo accounts are created:

| Role | Email | Password | Access Level |
|---|---|---|---|
| **Student** | `alex@student.edu` | `password123` | Personal Board, Database, Notes & Categories |
| **Admin** | `admin@student.edu` | `admin123` | Full Access + Admin User Overview |

*You can also click **Sign Up** on the login page to register a new student account instantly.*

---

## 🔌 API Reference

All API endpoints return JSON responses with standard HTTP status codes.

| Method | Endpoint | Description | Auth Required |
|---|---|---|---|
| `POST` | `/api/login.php` | Authenticates user & sets session cookie | No |
| `POST` | `/api/register.php` | Creates a new student user | No |
| `GET` | `/api/check_auth.php` | Returns currently logged-in user profile | No |
| `POST` | `/api/logout.php` | Terminates active session | Yes |
| `GET` | `/api/get_tasks.php` | Fetches tasks with category and tag joins | Yes |
| `POST` | `/api/create_task.php` | Creates a new task or issue | Yes |
| `POST` | `/api/update_task.php` | Updates title, status, priority, due date, notes | Yes |
| `POST` | `/api/delete_task.php` | Permanently deletes a task | Yes |
| `GET` | `/api/get_courses.php` | Lists categories/courses with task counts | Yes |
| `POST` | `/api/create_course.php` | Creates a new project category | Yes |
| `POST` | `/api/update_course.php` | Updates category name, color, or type | Yes |
| `POST` | `/api/delete_course.php` | Deletes a category | Yes |
| `GET` | `/api/get_users.php` | Lists all registered users (Admin only) | Yes (Admin) |
| `GET` | `/api/setup_db.php` | Runs PostgreSQL database setup & migrations | No |

---

## 🛡️ Security Features

- **SQL Injection Prevention**: 100% parameterized queries using PDO prepared statements with `ATTR_EMULATE_PREPARES` configuration.
- **Password Security**: Strong hashing via PHP's native `password_hash()` with `PASSWORD_BCRYPT`.
- **CORS Protection**: Configurable allowed origins via `FRONTEND_URL` environment variable.
- **Session Security**: `HttpOnly`, `SameSite=Lax`, and conditional `Secure` cookie flags.
- **Scoped Data Isolation**: All task, course, and note queries strictly filter by `user_id` from the verified session.

---

## 🤝 Contributing

Contributions, bug reports, and feature requests are welcome!

1. Fork the Project
2. Create your Feature Branch (`git checkout -b feature/AmazingFeature`)
3. Commit your Changes (`git commit -m 'Add some AmazingFeature'`)
4. Push to the Branch (`git push origin feature/AmazingFeature`)
5. Open a Pull Request

---

## 📄 License

Distributed under the **MIT License**. See `LICENSE` for more information.
