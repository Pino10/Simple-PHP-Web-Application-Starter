-- schema.sql

-- Create Users table
CREATE TABLE users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    fullname VARCHAR(100) NOT NULL,
    email VARCHAR(100) UNIQUE NOT NULL,
    password VARCHAR(255) NOT NULL,
    phone VARCHAR(20),
    bio TEXT,
    verified TINYINT(1) DEFAULT 0,
    verification_code VARCHAR(255),
    reset_token_hash VARCHAR(64),
    reset_token_expires DATETIME,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- Create Projects table
CREATE TABLE projects (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    name VARCHAR(100) NOT NULL,
    description TEXT,
    status ENUM('active', 'completed', 'on_hold') DEFAULT 'active',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);

-- Create Tasks table
CREATE TABLE tasks (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    project_id INT,
    title VARCHAR(100) NOT NULL,
    description TEXT,
    status ENUM('pending', 'in_progress', 'completed') DEFAULT 'pending',
    due_date DATE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (project_id) REFERENCES projects(id) ON DELETE SET NULL
);

-- Create Notifications table
CREATE TABLE notifications (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    message TEXT NOT NULL,
    is_read TINYINT(1) DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);

-- Create index on frequently queried columns
CREATE INDEX idx_user_email ON users(email);
CREATE INDEX idx_project_user ON projects(user_id);
CREATE INDEX idx_task_user ON tasks(user_id);
CREATE INDEX idx_task_project ON tasks(project_id);
CREATE INDEX idx_notification_user ON notifications(user_id);

-- Insert a test user (password is 'password123')
INSERT INTO users (fullname, email, password, verified) VALUES 
('Test User', 'test@example.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 1);

-- Insert some test projects for the test user
INSERT INTO projects (user_id, name, description) VALUES 
(1, 'Project 1', 'This is a test project'),
(1, 'Project 2', 'This is another test project');

-- Insert some test tasks for the test user
INSERT INTO tasks (user_id, project_id, title, description) VALUES 
(1, 1, 'Task 1', 'This is a test task for Project 1'),
(1, 1, 'Task 2', 'This is another test task for Project 1'),
(1, 2, 'Task 3', 'This is a test task for Project 2');

-- Insert a test notification for the test user
INSERT INTO notifications (user_id, message) VALUES 
(1, 'Welcome to the application!');