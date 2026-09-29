-- =========================================================
-- Student Project Management System - Database Schema
-- =========================================================

CREATE DATABASE IF NOT EXISTS `student_pm` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE `student_pm`;

-- ---------------------------------------------------------
-- Table: users
-- ---------------------------------------------------------
CREATE TABLE IF NOT EXISTS `users` (
    `id`            INT AUTO_INCREMENT PRIMARY KEY,
    `name`          VARCHAR(100) NOT NULL,
    `email`         VARCHAR(191) NOT NULL UNIQUE,
    `password_hash` VARCHAR(255) NOT NULL,
    `created_at`    TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ---------------------------------------------------------
-- Table: courses
-- ---------------------------------------------------------
CREATE TABLE IF NOT EXISTS `courses` (
    `id`          INT AUTO_INCREMENT PRIMARY KEY,
    `user_id`     INT NOT NULL,
    `name`        VARCHAR(100) NOT NULL,
    `color_code`  VARCHAR(20)  NOT NULL DEFAULT '#6366f1',
    `created_at`  TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (`user_id`) REFERENCES `users`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ---------------------------------------------------------
-- Table: tasks
-- ---------------------------------------------------------
CREATE TABLE IF NOT EXISTS `tasks` (
    `id`          INT AUTO_INCREMENT PRIMARY KEY,
    `user_id`     INT NOT NULL,
    `course_id`   INT NULL,
    `title`       VARCHAR(255) NOT NULL,
    `status`      ENUM('todo','in_progress','blocked','done') NOT NULL DEFAULT 'todo',
    `priority`    ENUM('low','medium','high') NOT NULL DEFAULT 'medium',
    `due_date`    DATETIME NULL,
    `notes_body`  LONGTEXT NULL,
    `created_at`  TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    `updated_at`  TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (`user_id`)   REFERENCES `users`(`id`)   ON DELETE CASCADE,
    FOREIGN KEY (`course_id`) REFERENCES `courses`(`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ---------------------------------------------------------
-- Table: tags
-- ---------------------------------------------------------
CREATE TABLE IF NOT EXISTS `tags` (
    `id`       INT AUTO_INCREMENT PRIMARY KEY,
    `user_id`  INT NOT NULL,
    `name`     VARCHAR(50) NOT NULL,
    FOREIGN KEY (`user_id`) REFERENCES `users`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ---------------------------------------------------------
-- Table: task_tags (join)
-- ---------------------------------------------------------
CREATE TABLE IF NOT EXISTS `task_tags` (
    `task_id`  INT NOT NULL,
    `tag_id`   INT NOT NULL,
    PRIMARY KEY (`task_id`, `tag_id`),
    FOREIGN KEY (`task_id`) REFERENCES `tasks`(`id`) ON DELETE CASCADE,
    FOREIGN KEY (`tag_id`)  REFERENCES `tags`(`id`)  ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- =========================================================
-- Seed Data
-- =========================================================

-- Demo user (password: "password123" hashed)
INSERT IGNORE INTO `users` (`id`, `name`, `email`, `password_hash`) VALUES
(1, 'Alex Johnson', 'alex@student.edu', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi');

-- Courses with distinct colors
INSERT IGNORE INTO `courses` (`id`, `user_id`, `name`, `color_code`) VALUES
(1, 1, 'DBMS',              '#6366f1'),
(2, 1, 'Algorithms',        '#10b981'),
(3, 1, 'Web Engineering',   '#f59e0b'),
(4, 1, 'Operating Systems', '#f43f5e'),
(5, 1, 'Computer Networks', '#3b82f6');

-- Realistic student tasks
INSERT IGNORE INTO `tasks` (`id`, `user_id`, `course_id`, `title`, `status`, `priority`, `due_date`, `notes_body`) VALUES
(1,  1, 1, 'Database Normalization Assignment',        'todo',        'high',   DATE_ADD(NOW(), INTERVAL 1 DAY),   '<h2>Database Normalization</h2><p>Need to complete 1NF, 2NF, and 3NF for the library system.</p><ul><li>Identify functional dependencies</li><li>Draw ER diagram</li><li>Write SQL DDL</li></ul>'),
(2,  1, 1, 'ER Diagram for Hospital Management',      'in_progress', 'medium', DATE_ADD(NOW(), INTERVAL 3 DAY),   '<p>Creating ER diagram with entities: Patient, Doctor, Appointment, Ward.</p>'),
(3,  1, 2, 'Implement Dijkstra Algorithm',            'todo',        'high',   DATE_ADD(NOW(), INTERVAL 2 DAY),   '<p>Implement shortest-path algorithm in C++ with adjacency list representation.</p>'),
(4,  1, 2, 'Sorting Algorithms Analysis Report',      'done',        'medium', DATE_ADD(NOW(), INTERVAL -2 DAY),  '<p>Completed! Analyzed Merge Sort vs Quick Sort with time complexity proofs.</p>'),
(5,  1, 3, 'AngularJS CRUD Application',              'in_progress', 'high',   DATE_ADD(NOW(), INTERVAL 5 DAY),   '<h3>Project Setup</h3><p>Building a student management CRUD with AngularJS, PHP and MySQL.</p>'),
(6,  1, 3, 'RESTful API Design Assignment',           'todo',        'medium', DATE_ADD(NOW(), INTERVAL 7 DAY),   '<p>Design REST API endpoints for a library system. Include GET, POST, PUT, DELETE.</p>'),
(7,  1, 4, 'Process Scheduling Simulation',           'blocked',     'high',   DATE_ADD(NOW(), INTERVAL 4 DAY),   '<p>Blocked: Waiting for lab access to test Round Robin vs Priority scheduling.</p>'),
(8,  1, 4, 'Deadlock Detection Report',               'todo',        'low',    DATE_ADD(NOW(), INTERVAL 10 DAY),  '<p>Write report on Bankers Algorithm and Resource Allocation Graphs.</p>'),
(9,  1, 5, 'TCP/IP Protocol Stack Presentation',      'done',        'medium', DATE_ADD(NOW(), INTERVAL -5 DAY),  '<p>Presentation completed. Covered OSI layers 1-4 in detail with diagrams.</p>'),
(10, 1, 5, 'Socket Programming Lab',                  'in_progress', 'medium', DATE_ADD(NOW(), INTERVAL 6 DAY),   '<p>Implementing echo server and client using BSD sockets in C.</p>'),
(11, 1, 1, 'SQL Query Optimization Exercises',        'todo',        'low',    DATE_ADD(NOW(), INTERVAL 14 DAY),  '<p>Complete 20 query optimization exercises from Chapter 12 of Ramakrishnan.</p>'),
(12, 1, 2, 'Graph Traversal BFS/DFS Lab',             'todo',        'medium', DATE_ADD(NOW(), INTERVAL 2 DAY),   '<p>Implement BFS and DFS. Test on sample graph with 10 nodes.</p>'),
(13, 1, 3, 'Responsive Landing Page Design',          'done',        'low',    DATE_ADD(NOW(), INTERVAL -1 DAY),  '<p>Built a responsive landing page using Bootstrap 5 grid system. Mobile-first approach.</p>'),
(14, 1, 4, 'Virtual Memory Management Essay',         'blocked',     'medium', DATE_ADD(NOW(), INTERVAL 8 DAY),   '<p>Waiting for reference textbook. Need to explain page replacement algorithms.</p>'),
(15, 1, 5, 'Network Security Assignment',             'todo',        'high',   DATE_ADD(NOW(), INTERVAL 3 DAY),   '<p>Analyze SSL/TLS handshake process and write a 1500-word report.</p>');

-- Tags seed
INSERT IGNORE INTO `tags` (`id`, `user_id`, `name`) VALUES
(1, 1, 'Exam'), (2, 1, 'Lab'), (3, 1, 'Report'), (4, 1, 'Reading'), (5, 1, 'Group Project');

-- task_tags
INSERT IGNORE INTO `task_tags` (`task_id`, `tag_id`) VALUES
(1,3),(2,3),(3,2),(5,5),(7,2),(9,3),(10,2),(14,3);
