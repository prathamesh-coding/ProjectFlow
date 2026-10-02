-- =========================================================
-- Student Project Management System - PostgreSQL Schema
-- Target: Supabase (PostgreSQL 15+)
-- =========================================================

-- ---------------------------------------------------------
-- Table: users
-- ---------------------------------------------------------
CREATE TABLE IF NOT EXISTS users (
    id            SERIAL       PRIMARY KEY,
    full_name     VARCHAR(100) NOT NULL,
    email         VARCHAR(191) NOT NULL UNIQUE,
    password_hash VARCHAR(255) NOT NULL,
    role          VARCHAR(10)  NOT NULL DEFAULT 'student'
                  CHECK (role IN ('student', 'admin')),
    created_at    TIMESTAMPTZ  DEFAULT NOW()
);

-- ---------------------------------------------------------
-- Table: courses
-- ---------------------------------------------------------
CREATE TABLE IF NOT EXISTS courses (
    id          SERIAL      PRIMARY KEY,
    user_id     INTEGER     NOT NULL REFERENCES users(id) ON DELETE CASCADE,
    name        VARCHAR(100) NOT NULL,
    color_code  VARCHAR(20)  NOT NULL DEFAULT '#6366f1',
    type        VARCHAR(20)  NOT NULL DEFAULT 'other'
                CHECK (type IN ('personal','work','freelance','study','other')),
    created_at  TIMESTAMPTZ  DEFAULT NOW()
);

-- ---------------------------------------------------------
-- Table: tasks
-- ---------------------------------------------------------
CREATE TABLE IF NOT EXISTS tasks (
    id          SERIAL      PRIMARY KEY,
    user_id     INTEGER     NOT NULL REFERENCES users(id)    ON DELETE CASCADE,
    course_id   INTEGER              REFERENCES courses(id)  ON DELETE SET NULL,
    title       VARCHAR(255) NOT NULL,
    status      VARCHAR(20)  NOT NULL DEFAULT 'todo'
                CHECK (status IN ('todo','in_progress','blocked','done')),
    priority    VARCHAR(10)  NOT NULL DEFAULT 'medium'
                CHECK (priority IN ('low','medium','high')),
    due_date    TIMESTAMPTZ,
    notes_body  TEXT,
    created_at  TIMESTAMPTZ  DEFAULT NOW(),
    updated_at  TIMESTAMPTZ  DEFAULT NOW()
);

-- Auto-update updated_at on row change
CREATE OR REPLACE FUNCTION fn_set_updated_at()
RETURNS TRIGGER AS $$
BEGIN
    NEW.updated_at = NOW();
    RETURN NEW;
END;
$$ LANGUAGE plpgsql;

DROP TRIGGER IF EXISTS trg_tasks_updated_at ON tasks;
CREATE TRIGGER trg_tasks_updated_at
    BEFORE UPDATE ON tasks
    FOR EACH ROW EXECUTE FUNCTION fn_set_updated_at();

-- ---------------------------------------------------------
-- Table: tags
-- ---------------------------------------------------------
CREATE TABLE IF NOT EXISTS tags (
    id      SERIAL      PRIMARY KEY,
    user_id INTEGER     NOT NULL REFERENCES users(id) ON DELETE CASCADE,
    name    VARCHAR(50) NOT NULL
);

-- ---------------------------------------------------------
-- Table: task_tags (join)
-- ---------------------------------------------------------
CREATE TABLE IF NOT EXISTS task_tags (
    task_id INTEGER NOT NULL REFERENCES tasks(id) ON DELETE CASCADE,
    tag_id  INTEGER NOT NULL REFERENCES tags(id)  ON DELETE CASCADE,
    PRIMARY KEY (task_id, tag_id)
);

-- =========================================================
-- Seed Data  (run via setup_db.php, not directly here)
-- ON CONFLICT DO NOTHING = safe to re-run
-- =========================================================

-- Demo users  (passwords inserted by setup_db.php with password_hash())
-- alex@student.edu / password123
-- admin@student.edu / admin123
