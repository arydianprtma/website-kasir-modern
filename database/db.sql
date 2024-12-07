-- Users Table
CREATE TABLE users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(50) NOT NULL,
    email VARCHAR(100) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    role ENUM('Admin') NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Employees Table
CREATE TABLE employees (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    email VARCHAR(100) UNIQUE NOT NULL,
    position VARCHAR(100),
    department VARCHAR(100),
    salary DECIMAL(10,2),
    hire_date DATE,
    status ENUM('Active', 'Inactive', 'Suspended') DEFAULT 'Active',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Sales Table
CREATE TABLE sales (
    id INT AUTO_INCREMENT PRIMARY KEY,
    employee_id INT,
    total DECIMAL(10,2) NOT NULL,
    date DATE NOT NULL,
    description TEXT,
    FOREIGN KEY (employee_id) REFERENCES employees(id)
);

-- Expenses Table
CREATE TABLE expenses (
    id INT AUTO_INCREMENT PRIMARY KEY,
    category VARCHAR(100) NOT NULL,
    amount DECIMAL(10,2) NOT NULL,
    date DATE NOT NULL,
    description TEXT,
    approved_by INT,
    FOREIGN KEY (approved_by) REFERENCES users(id)
);

-- Activity Log Table
CREATE TABLE activity_log (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT,
    description TEXT NOT NULL,
    ip_address VARCHAR(45),
    timestamp TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id)
);

-- Warnings Table
CREATE TABLE warnings (
    id INT AUTO_INCREMENT PRIMARY KEY,
    employee_id INT,
    warning_type VARCHAR(100),
    description TEXT,
    issued_by INT,
    issued_date DATE,
    status ENUM('Active', 'Resolved') DEFAULT 'Active',
    FOREIGN KEY (employee_id) REFERENCES employees(id),
    FOREIGN KEY (issued_by) REFERENCES users(id)
);