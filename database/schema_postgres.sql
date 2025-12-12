-- PostgreSQL Schema for Course Page

CREATE TABLE IF NOT EXISTS users (
  id SERIAL PRIMARY KEY,
  username VARCHAR(50) NOT NULL UNIQUE,
  email VARCHAR(100) NOT NULL UNIQUE,
  password_hash VARCHAR(255) NOT NULL,
  role VARCHAR(20) DEFAULT 'student' CHECK (role IN ('admin', 'student', 'instructor')),
  student_id VARCHAR(20) DEFAULT NULL,
  created_by INTEGER DEFAULT NULL REFERENCES users(id) ON DELETE SET NULL,
  created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  last_login TIMESTAMP NULL DEFAULT NULL,
  is_active BOOLEAN DEFAULT TRUE
);

CREATE TABLE IF NOT EXISTS activity_logs (
  id SERIAL PRIMARY KEY,
  user_id INTEGER DEFAULT NULL REFERENCES users(id) ON DELETE SET NULL,
  action VARCHAR(100) NOT NULL,
  description TEXT DEFAULT NULL,
  ip_address VARCHAR(45) DEFAULT NULL,
  user_agent TEXT DEFAULT NULL,
  created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE IF NOT EXISTS assignments (
  id SERIAL PRIMARY KEY,
  title VARCHAR(255) NOT NULL,
  description TEXT DEFAULT NULL,
  due_date TIMESTAMP NOT NULL,
  max_score DECIMAL(5,2) DEFAULT NULL,
  assignment_type VARCHAR(20) DEFAULT 'homework' CHECK (assignment_type IN ('homework', 'project', 'quiz', 'exam')),
  file_path VARCHAR(500) DEFAULT NULL,
  file_name VARCHAR(255) DEFAULT NULL,
  submission_instructions TEXT DEFAULT NULL,
  created_by INTEGER NOT NULL REFERENCES users(id) ON DELETE CASCADE,
  created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  is_active BOOLEAN DEFAULT TRUE
);

CREATE TABLE IF NOT EXISTS assignment_comments (
  id SERIAL PRIMARY KEY,
  assignment_id INTEGER NOT NULL REFERENCES assignments(id) ON DELETE CASCADE,
  user_id INTEGER NOT NULL REFERENCES users(id) ON DELETE CASCADE,
  comment_text TEXT NOT NULL,
  is_question BOOLEAN DEFAULT FALSE,
  parent_comment_id INTEGER DEFAULT NULL,
  created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  is_active BOOLEAN DEFAULT TRUE
);

ALTER TABLE assignment_comments ADD CONSTRAINT fk_parent_comment 
  FOREIGN KEY (parent_comment_id) REFERENCES assignment_comments(id) ON DELETE CASCADE;

CREATE TABLE IF NOT EXISTS course_resources (
  id SERIAL PRIMARY KEY,
  title VARCHAR(255) NOT NULL,
  description TEXT DEFAULT NULL,
  resource_type VARCHAR(20) NOT NULL CHECK (resource_type IN ('book_chapter', 'lecture_notes', 'web_link', 'video', 'document')),
  file_path VARCHAR(500) DEFAULT NULL,
  file_name VARCHAR(255) DEFAULT NULL,
  file_size INTEGER DEFAULT NULL,
  external_url VARCHAR(500) DEFAULT NULL,
  created_by INTEGER NOT NULL REFERENCES users(id) ON DELETE CASCADE,
  created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  is_active BOOLEAN DEFAULT TRUE
);

CREATE TABLE IF NOT EXISTS discussion_boards (
  id SERIAL PRIMARY KEY,
  title VARCHAR(255) NOT NULL,
  description TEXT DEFAULT NULL,
  category VARCHAR(100) DEFAULT 'General',
  created_by INTEGER NOT NULL REFERENCES users(id) ON DELETE CASCADE,
  created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  is_active BOOLEAN DEFAULT TRUE,
  is_locked BOOLEAN DEFAULT FALSE
);

CREATE TABLE IF NOT EXISTS discussion_posts (
  id SERIAL PRIMARY KEY,
  discussion_id INTEGER NOT NULL REFERENCES discussion_boards(id) ON DELETE CASCADE,
  user_id INTEGER NOT NULL REFERENCES users(id) ON DELETE CASCADE,
  post_text TEXT NOT NULL,
  parent_post_id INTEGER DEFAULT NULL,
  created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  is_active BOOLEAN DEFAULT TRUE
);

ALTER TABLE discussion_posts ADD CONSTRAINT fk_parent_post 
  FOREIGN KEY (parent_post_id) REFERENCES discussion_posts(id) ON DELETE CASCADE;

CREATE TABLE IF NOT EXISTS password_reset_tokens (
  id SERIAL PRIMARY KEY,
  user_id INTEGER NOT NULL REFERENCES users(id) ON DELETE CASCADE,
  token VARCHAR(255) NOT NULL UNIQUE,
  expires_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  used_at TIMESTAMP NULL DEFAULT NULL,
  created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE IF NOT EXISTS resource_comments (
  id SERIAL PRIMARY KEY,
  resource_id INTEGER NOT NULL REFERENCES course_resources(id) ON DELETE CASCADE,
  user_id INTEGER NOT NULL REFERENCES users(id) ON DELETE CASCADE,
  comment_text TEXT NOT NULL,
  created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  is_active BOOLEAN DEFAULT TRUE
);

CREATE TABLE IF NOT EXISTS student_submissions (
  id SERIAL PRIMARY KEY,
  assignment_id INTEGER NOT NULL REFERENCES assignments(id) ON DELETE CASCADE,
  student_id INTEGER NOT NULL REFERENCES users(id) ON DELETE CASCADE,
  submission_text TEXT DEFAULT NULL,
  file_path VARCHAR(500) DEFAULT NULL,
  file_name VARCHAR(255) DEFAULT NULL,
  submitted_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  score DECIMAL(5,2) DEFAULT NULL,
  feedback TEXT DEFAULT NULL,
  graded_by INTEGER DEFAULT NULL REFERENCES users(id) ON DELETE SET NULL,
  graded_at TIMESTAMP NULL DEFAULT NULL,
  UNIQUE (assignment_id, student_id)
);

CREATE TABLE IF NOT EXISTS user_sessions (
  id SERIAL PRIMARY KEY,
  user_id INTEGER NOT NULL REFERENCES users(id) ON DELETE CASCADE,
  session_token VARCHAR(255) NOT NULL UNIQUE,
  ip_address VARCHAR(45) NOT NULL,
  user_agent TEXT DEFAULT NULL,
  created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  expires_at TIMESTAMP NOT NULL,
  is_active BOOLEAN DEFAULT TRUE
);

CREATE TABLE IF NOT EXISTS weekly_breakdown (
  id SERIAL PRIMARY KEY,
  week_id INTEGER NOT NULL UNIQUE,
  title VARCHAR(255) NOT NULL,
  description TEXT DEFAULT NULL,
  links JSONB DEFAULT NULL,
  start_date DATE NOT NULL,
  created_by INTEGER NOT NULL REFERENCES users(id) ON DELETE CASCADE,
  created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE IF NOT EXISTS weekly_comments (
  id SERIAL PRIMARY KEY,
  week_id INTEGER NOT NULL,
  user_id INTEGER NOT NULL REFERENCES users(id) ON DELETE CASCADE,
  comment_text TEXT NOT NULL,
  parent_comment_id INTEGER DEFAULT NULL,
  created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (week_id) REFERENCES weekly_breakdown(week_id) ON DELETE CASCADE
);

ALTER TABLE weekly_comments ADD CONSTRAINT fk_weekly_parent_comment 
  FOREIGN KEY (parent_comment_id) REFERENCES weekly_comments(id) ON DELETE CASCADE;

-- Create indexes
CREATE INDEX IF NOT EXISTS idx_activity_logs_created_at ON activity_logs(created_at);
CREATE INDEX IF NOT EXISTS idx_password_reset_token ON password_reset_tokens(token);
CREATE INDEX IF NOT EXISTS idx_user_sessions_token ON user_sessions(session_token);
CREATE INDEX IF NOT EXISTS idx_user_sessions_expires ON user_sessions(expires_at);

-- Insert sample data
INSERT INTO users (id, username, email, password_hash, role, student_id, created_at, updated_at, is_active) VALUES
(1, 'admin', 'admin@example.com', '$2y$10$Jw75dSZR/8Fwo3m6ewa6P.emvAxWcEw/buAx4209BQ6ORlqIcp4gO', 'admin', NULL, '2025-12-08 21:23:40', '2025-12-10 21:59:40', TRUE),
(2, 'instructor1', 'instructor1@example.com', '$2y$10$Jw75dSZR/8Fwo3m6ewa6P.emvAxWcEw/buAx4209BQ6ORlqIcp4gO', 'instructor', NULL, '2025-12-08 21:23:40', '2025-12-10 21:59:40', TRUE),
(3, 'student1', 'student1@example.com', '$2y$10$Jw75dSZR/8Fwo3m6ewa6P.emvAxWcEw/buAx4209BQ6ORlqIcp4gO', 'student', 'STU2025001', '2025-12-08 21:23:40', '2025-12-10 21:59:40', TRUE),
(4, 'Ali Hassan', '202101234@stu.uob.edu.bh', '$2y$10$Jw75dSZR/8Fwo3m6ewa6P.emvAxWcEw/buAx4209BQ6ORlqIcp4gO', 'student', '202101234', '2025-12-08 21:23:40', '2025-12-10 21:59:40', TRUE),
(5, 'Fatema Ahmed', '202205678@stu.uob.edu.bh', '$2y$10$Jw75dSZR/8Fwo3m6ewa6P.emvAxWcEw/buAx4209BQ6ORlqIcp4gO', 'student', '202205678', '2025-12-08 21:23:40', '2025-12-10 21:59:40', TRUE),
(6, 'Mohamed Abdulla', '202311001@stu.uob.edu.bh', '$2y$10$Jw75dSZR/8Fwo3m6ewa6P.emvAxWcEw/buAx4209BQ6ORlqIcp4gO', 'student', '202311001', '2025-12-08 21:23:40', '2025-12-10 21:59:40', TRUE),
(7, 'Noora Salman', '202100987@stu.uob.edu.bh', '$2y$10$Jw75dSZR/8Fwo3m6ewa6P.emvAxWcEw/buAx4209BQ6ORlqIcp4gO', 'student', '202100987', '2025-12-08 21:23:40', '2025-12-10 21:59:40', TRUE),
(8, 'Zainab Ebrahim', '202207766@stu.uob.edu.bh', '$2y$10$Jw75dSZR/8Fwo3m6ewa6P.emvAxWcEw/buAx4209BQ6ORlqIcp4gO', 'student', '202207766', '2025-12-08 21:23:40', '2025-12-10 21:59:40', TRUE),
(9, 'admin2', 'admin2@example.com', '$2y$10$Jw75dSZR/8Fwo3m6ewa6P.emvAxWcEw/buAx4209BQ6ORlqIcp4gO', 'admin', NULL, '2025-12-10 21:52:18', '2025-12-10 21:59:40', TRUE),
(11, 'admin3', 'admin3@example.com', '$2y$10$Jw75dSZR/8Fwo3m6ewa6P.emvAxWcEw/buAx4209BQ6ORlqIcp4gO', 'admin', NULL, '2025-12-10 21:58:15', '2025-12-10 21:58:15', TRUE)
ON CONFLICT (id) DO NOTHING;

SELECT setval('users_id_seq', (SELECT MAX(id) FROM users));

INSERT INTO weekly_breakdown (id, week_id, title, description, links, start_date, created_by, created_at, updated_at) VALUES
(1, 1, 'Week 1: Introduction to HTML', 'This week covers the fundamental building blocks of the web: HTML. We will explore semantic tags, document structure, and basic elements like headings, paragraphs, links, and images.', '["https://developer.mozilla.org/en-US/docs/Web/HTML","https://www.w3schools.com/html/html_basic.asp"]', '2025-10-27', 1, '2025-12-08 21:35:57', '2025-12-08 21:35:57'),
(2, 2, 'Week 2: Introduction to CSS', 'Learn how to style your HTML documents. We will cover selectors, the box model, colors, fonts, and basic layouts.', '["https://developer.mozilla.org/en-US/docs/Web/CSS","https://css-tricks.com/guides/beginner/"]', '2025-11-03', 1, '2025-12-08 21:35:57', '2025-12-08 21:35:57'),
(3, 3, 'Week 3: CSS Flexbox and Grid', 'A deep dive into modern CSS layout techniques. We will master Flexbox for 1D layouts and CSS Grid for 2D layouts.', '["https://css-tricks.com/snippets/css/a-guide-to-flexbox/","https://css-tricks.com/snippets/css/complete-guide-grid/"]', '2025-11-10', 1, '2025-12-08 21:35:57', '2025-12-08 21:35:57')
ON CONFLICT (id) DO NOTHING;

SELECT setval('weekly_breakdown_id_seq', (SELECT MAX(id) FROM weekly_breakdown));

INSERT INTO weekly_comments (id, week_id, user_id, comment_text, parent_comment_id, created_at) VALUES
(1, 1, 4, 'I''m confused about the difference between <section> and <article>.', NULL, '2025-12-08 22:06:01'),
(2, 1, 5, 'Are we allowed to use <b> and <i> tags, or should we always use <strong> and <em>?', NULL, '2025-12-08 22:06:01'),
(3, 2, 4, 'The box model is tricky. Does the border count as part of the width?', NULL, '2025-12-08 22:06:01')
ON CONFLICT (id) DO NOTHING;

SELECT setval('weekly_comments_id_seq', (SELECT MAX(id) FROM weekly_comments));
