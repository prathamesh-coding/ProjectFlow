<?php
/**
 * setup_db.php
 * One-click PostgreSQL / Supabase database initializer.
 * Creates all tables and seeds sample data.
 *
 * ⚠️  Run once after deploying to Render.
 * Visit: https://your-render-url.onrender.com/api/setup_db.php
 *        OR: http://localhost/proj-manage/api/setup_db.php (local)
 *
 * After setup, protect or delete this file.
 */
header('Content-Type: application/json; charset=utf-8');

// Load .env for local development
$_envFile = dirname(__DIR__) . '/.env';
if (file_exists($_envFile)) {
    foreach (file($_envFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES) as $_line) {
        if (empty($_line) || $_line[0] === '#' || strpos($_line, '=') === false) continue;
        [$_k, $_v] = explode('=', $_line, 2);
        putenv(trim($_k) . '=' . trim($_v));
    }
}

$host = getenv('DB_HOST') ?: 'localhost';
$port = getenv('DB_PORT') ?: '5432';
$db   = getenv('DB_NAME') ?: 'postgres';
$user = getenv('DB_USER') ?: 'postgres';
$pass = getenv('DB_PASS') ?: '';
$dsn  = "pgsql:host=$host;port=$port;dbname=$db;sslmode=require";

$log = [];

try {
    $pdo = new PDO($dsn, $user, $pass, [
        PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES   => true, // Required for PgBouncer transaction mode
    ]);
    $log[] = "✅ Connected to PostgreSQL at $host:$port/$db";

    // ── Table: users ──────────────────────────────────────────────────────────
    $pdo->exec("CREATE TABLE IF NOT EXISTS users (
        id            SERIAL PRIMARY KEY,
        full_name     VARCHAR(100) NOT NULL,
        email         VARCHAR(191) NOT NULL UNIQUE,
        password_hash VARCHAR(255) NOT NULL,
        role          VARCHAR(10)  NOT NULL DEFAULT 'student'
                      CHECK (role IN ('student', 'admin')),
        created_at    TIMESTAMPTZ  DEFAULT NOW()
    )");
    $log[] = "✅ Table `users` ready.";

    // ── Table: courses ────────────────────────────────────────────────────────
    $pdo->exec("CREATE TABLE IF NOT EXISTS courses (
        id          SERIAL PRIMARY KEY,
        user_id     INTEGER NOT NULL REFERENCES users(id) ON DELETE CASCADE,
        name        VARCHAR(100) NOT NULL,
        color_code  VARCHAR(20)  NOT NULL DEFAULT '#6366f1',
        type        VARCHAR(20)  NOT NULL DEFAULT 'other'
                    CHECK (type IN ('personal','work','freelance','study','other')),
        created_at  TIMESTAMPTZ  DEFAULT NOW()
    )");
    $log[] = "✅ Table `courses` ready.";

    // ── Table: tasks ──────────────────────────────────────────────────────────
    $pdo->exec("CREATE TABLE IF NOT EXISTS tasks (
        id          SERIAL PRIMARY KEY,
        user_id     INTEGER NOT NULL REFERENCES users(id)    ON DELETE CASCADE,
        course_id   INTEGER          REFERENCES courses(id)  ON DELETE SET NULL,
        title       VARCHAR(255) NOT NULL,
        status      VARCHAR(20)  NOT NULL DEFAULT 'todo'
                    CHECK (status IN ('todo','in_progress','blocked','done')),
        priority    VARCHAR(10)  NOT NULL DEFAULT 'medium'
                    CHECK (priority IN ('low','medium','high')),
        due_date    TIMESTAMPTZ,
        notes_body  TEXT,
        created_at  TIMESTAMPTZ  DEFAULT NOW(),
        updated_at  TIMESTAMPTZ  DEFAULT NOW()
    )");
    $log[] = "✅ Table `tasks` ready.";

    // ── updated_at trigger ────────────────────────────────────────────────────
    $pdo->exec("
        CREATE OR REPLACE FUNCTION fn_set_updated_at()
        RETURNS TRIGGER AS \$\$
        BEGIN
            NEW.updated_at = NOW();
            RETURN NEW;
        END;
        \$\$ LANGUAGE plpgsql;
    ");
    $pdo->exec("
        DROP TRIGGER IF EXISTS trg_tasks_updated_at ON tasks;
        CREATE TRIGGER trg_tasks_updated_at
            BEFORE UPDATE ON tasks
            FOR EACH ROW EXECUTE FUNCTION fn_set_updated_at();
    ");
    $log[] = "✅ updated_at trigger created.";

    // ── Table: tags ───────────────────────────────────────────────────────────
    $pdo->exec("CREATE TABLE IF NOT EXISTS tags (
        id      SERIAL PRIMARY KEY,
        user_id INTEGER NOT NULL REFERENCES users(id) ON DELETE CASCADE,
        name    VARCHAR(50) NOT NULL
    )");
    $log[] = "✅ Table `tags` ready.";

    // ── Table: task_tags ──────────────────────────────────────────────────────
    $pdo->exec("CREATE TABLE IF NOT EXISTS task_tags (
        task_id INTEGER NOT NULL REFERENCES tasks(id) ON DELETE CASCADE,
        tag_id  INTEGER NOT NULL REFERENCES tags(id)  ON DELETE CASCADE,
        PRIMARY KEY (task_id, tag_id)
    )");
    $log[] = "✅ Table `task_tags` ready.";

    // ── Seed: demo users (ON CONFLICT DO NOTHING = INSERT IGNORE equivalent) ──
    $stmt = $pdo->prepare(
        "INSERT INTO users (id, full_name, email, password_hash, role)
         VALUES (1, 'Alex Johnson', 'alex@student.edu', :p1, 'student'),
                (2, 'Admin User',  'admin@student.edu', :p2, 'admin')
         ON CONFLICT (email) DO NOTHING"
    );
    $stmt->execute([
        ':p1' => password_hash('password123', PASSWORD_BCRYPT),
        ':p2' => password_hash('admin123',    PASSWORD_BCRYPT),
    ]);
    $log[] = "✅ Demo users seeded (alex@student.edu / password123, admin@student.edu / admin123).";

    // ── Seed: courses ─────────────────────────────────────────────────────────
    $stmtC = $pdo->prepare(
        "INSERT INTO courses (id, user_id, name, color_code)
         VALUES (:id, :uid, :name, :color)
         ON CONFLICT DO NOTHING"
    );
    $courses = [
        [1, 1, 'DBMS',              '#6366f1'],
        [2, 1, 'Algorithms',        '#10b981'],
        [3, 1, 'Web Engineering',   '#f59e0b'],
        [4, 1, 'Operating Systems', '#f43f5e'],
        [5, 1, 'Computer Networks', '#3b82f6'],
    ];
    foreach ($courses as [$cid, $uid, $name, $color]) {
        $stmtC->execute([':id' => $cid, ':uid' => $uid, ':name' => $name, ':color' => $color]);
    }
    $log[] = "✅ " . count($courses) . " courses seeded.";

    // ── Seed: tasks ───────────────────────────────────────────────────────────
    $stmtT = $pdo->prepare(
        "INSERT INTO tasks (id, user_id, course_id, title, status, priority, due_date, notes_body)
         VALUES (:id, :uid, :cid, :title, :status, :priority, :due, :notes)
         ON CONFLICT DO NOTHING"
    );
    $tasks = [
        [1,  1,1,'Database Normalization Assignment',   'todo',       'high',  '+1 day',  '<h2>Database Normalization</h2><p>Need to complete 1NF, 2NF, and 3NF for the library system.</p><ul><li>Identify functional dependencies</li><li>Draw ER diagram</li><li>Write SQL DDL</li></ul>'],
        [2,  1,1,'ER Diagram for Hospital Management',  'in_progress','medium','+3 days', '<p>Creating ER diagram with entities: Patient, Doctor, Appointment, Ward.</p>'],
        [3,  1,2,'Implement Dijkstra Algorithm',        'todo',       'high',  '+2 days', '<p>Implement shortest-path algorithm in C++ with adjacency list representation.</p>'],
        [4,  1,2,'Sorting Algorithms Analysis Report',  'done',       'medium','-2 days', '<p>Completed! Analyzed Merge Sort vs Quick Sort with time complexity proofs.</p>'],
        [5,  1,3,'AngularJS CRUD Application',          'in_progress','high',  '+5 days', '<h3>Project Setup</h3><p>Building a student management CRUD with AngularJS, PHP and PostgreSQL.</p>'],
        [6,  1,3,'RESTful API Design Assignment',       'todo',       'medium','+7 days', '<p>Design REST API endpoints for a library system. Include GET, POST, PUT, DELETE.</p>'],
        [7,  1,4,'Process Scheduling Simulation',       'blocked',    'high',  '+4 days', '<p>Blocked: Waiting for lab access to test Round Robin vs Priority scheduling.</p>'],
        [8,  1,4,'Deadlock Detection Report',           'todo',       'low',   '+10 days','<p>Write report on Bankers Algorithm and Resource Allocation Graphs.</p>'],
        [9,  1,5,'TCP/IP Protocol Stack Presentation',  'done',       'medium','-5 days', '<p>Presentation completed. Covered OSI layers 1-4 in detail with diagrams.</p>'],
        [10, 1,5,'Socket Programming Lab',              'in_progress','medium','+6 days', '<p>Implementing echo server and client using BSD sockets in C.</p>'],
        [11, 1,1,'SQL Query Optimization Exercises',    'todo',       'low',   '+14 days','<p>Complete 20 query optimization exercises from Chapter 12 of Ramakrishnan.</p>'],
        [12, 1,2,'Graph Traversal BFS/DFS Lab',         'todo',       'medium','+2 days', '<p>Implement BFS and DFS. Test on sample graph with 10 nodes.</p>'],
        [13, 1,3,'Responsive Landing Page Design',      'done',       'low',   '-1 day',  '<p>Built a responsive landing page using Bootstrap 5 grid system. Mobile-first approach.</p>'],
        [14, 1,4,'Virtual Memory Management Essay',     'blocked',    'medium','+8 days', '<p>Waiting for reference textbook. Need to explain page replacement algorithms.</p>'],
        [15, 1,5,'Network Security Assignment',         'todo',       'high',  '+3 days', '<p>Analyze SSL/TLS handshake process and write a 1500-word report.</p>'],
    ];
    foreach ($tasks as [$tid,$uid,$cid,$title,$status,$priority,$interval,$notes]) {
        // PostgreSQL interval expression
        $due = "NOW() + INTERVAL '$interval'";
        $stmtRaw = $pdo->prepare(
            "INSERT INTO tasks (id, user_id, course_id, title, status, priority, due_date, notes_body)
             VALUES (:id, :uid, :cid, :title, :status, :priority, $due, :notes)
             ON CONFLICT DO NOTHING"
        );
        $stmtRaw->execute([
            ':id'      => $tid, ':uid'   => $uid, ':cid'   => $cid,
            ':title'   => $title, ':status' => $status, ':priority' => $priority,
            ':notes'   => $notes,
        ]);
    }
    $log[] = "✅ " . count($tasks) . " sample tasks seeded.";

    // ── Seed: tags & task_tags ────────────────────────────────────────────────
    $pdo->exec("
        INSERT INTO tags (id, user_id, name)
        VALUES (1,1,'Exam'),(2,1,'Lab'),(3,1,'Report'),(4,1,'Reading'),(5,1,'Group Project')
        ON CONFLICT DO NOTHING
    ");
    $pdo->exec("
        INSERT INTO task_tags (task_id, tag_id)
        VALUES (1,3),(2,3),(3,2),(5,5),(7,2),(9,3),(10,2),(14,3)
        ON CONFLICT DO NOTHING
    ");
    $log[] = "✅ Tags and task-tag associations seeded.";

    // ── Update sequences so next INSERT gets correct IDs ─────────────────────
    foreach (['users','courses','tasks','tags'] as $tbl) {
        $pdo->exec("SELECT setval('{$tbl}_id_seq', (SELECT MAX(id) FROM $tbl))");
    }
    $log[] = "✅ ID sequences updated.";

    echo json_encode([
        'success' => true,
        'message' => '🎉 Database setup complete! You can now use the application.',
        'log'     => $log,
    ], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);

} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'error'   => $e->getMessage(),
        'log'     => $log,
    ], JSON_PRETTY_PRINT);
}
