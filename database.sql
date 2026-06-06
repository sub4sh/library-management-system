-- Library Management System Database
CREATE DATABASE IF NOT EXISTS library_db;
USE library_db;

-- Books Table
CREATE TABLE IF NOT EXISTS books (
    id INT AUTO_INCREMENT PRIMARY KEY,
    title VARCHAR(255) NOT NULL,
    author VARCHAR(255) NOT NULL,
    isbn VARCHAR(20) UNIQUE,
    genre VARCHAR(100),
    total_copies INT DEFAULT 1,
    available_copies INT DEFAULT 1,
    published_year INT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Members Table
CREATE TABLE IF NOT EXISTS members (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(255) NOT NULL,
    email VARCHAR(255) UNIQUE NOT NULL,
    phone VARCHAR(20),
    address TEXT,
    membership_date DATE DEFAULT (CURRENT_DATE),
    status ENUM('active', 'inactive') DEFAULT 'active',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Borrowings Table
CREATE TABLE IF NOT EXISTS borrowings (
    id INT AUTO_INCREMENT PRIMARY KEY,
    book_id INT NOT NULL,
    member_id INT NOT NULL,
    borrow_date DATE DEFAULT (CURRENT_DATE),
    due_date DATE NOT NULL,
    return_date DATE NULL,
    status ENUM('borrowed', 'returned', 'overdue') DEFAULT 'borrowed',
    FOREIGN KEY (book_id) REFERENCES books(id) ON DELETE CASCADE,
    FOREIGN KEY (member_id) REFERENCES members(id) ON DELETE CASCADE
);

-- Activity Log Table
CREATE TABLE IF NOT EXISTS activity_log (
    id INT AUTO_INCREMENT PRIMARY KEY,
    action_type ENUM('add_book','edit_book','delete_book','add_member','edit_member','delete_member','issue_book','return_book') NOT NULL,
    entity_type ENUM('book','member','borrowing') NOT NULL,
    entity_id INT,
    description TEXT NOT NULL,
    performed_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Dashboard Snapshots Table
CREATE TABLE IF NOT EXISTS dashboard_snapshots (
    id INT AUTO_INCREMENT PRIMARY KEY,
    snapshot_date DATE NOT NULL UNIQUE,
    total_books INT DEFAULT 0,
    total_members INT DEFAULT 0,
    books_borrowed INT DEFAULT 0,
    overdue_count INT DEFAULT 0,
    recorded_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Sample Books
INSERT INTO books (title, author, isbn, genre, total_copies, available_copies, published_year) VALUES
('The Great Gatsby', 'F. Scott Fitzgerald', '978-0-7432-7356-5', 'Fiction', 3, 3, 1925),
('To Kill a Mockingbird', 'Harper Lee', '978-0-06-112008-4', 'Fiction', 2, 2, 1960),
('1984', 'George Orwell', '978-0-452-28423-4', 'Dystopian', 4, 4, 1949),
('Harry Potter and the Sorcerer''s Stone', 'J.K. Rowling', '978-0-590-35340-3', 'Fantasy', 5, 5, 1997),
('The Hobbit', 'J.R.R. Tolkien', '978-0-547-92822-7', 'Fantasy', 3, 3, 1937),
('Sapiens: A Brief History of Humankind', 'Yuval Noah Harari', '978-0-06-231609-7', 'Non-Fiction', 2, 2, 2011),
('Clean Code', 'Robert C. Martin', '978-0-13-235088-4', 'Technology', 3, 3, 2008),
('The Alchemist', 'Paulo Coelho', '978-0-06-112241-5', 'Fiction', 4, 4, 1988),
('Palpasa Café', 'Narayan Wagle', '978-9937-597-00-1', 'Fiction/War', 3, 3, 2005),
('Seto Bagh', 'Diamond Shumsher Rana', '978-9937-597-01-8', 'Historical Fiction', 2, 2, 1970),
('Karnali Blues', 'Buddhi Sagar', '978-9937-597-02-5', 'Fiction', 4, 4, 2010),
('Shirishko Phool', 'Parijat', '978-9937-597-03-2', 'Fiction', 3, 3, 1965),
('Muna Madan', 'Laxmi Prasad Devkota', '978-9937-597-04-9', 'Epic Poetry', 5, 5, 1936);

-- Sample Members
INSERT INTO members (name, email, phone, address, membership_date) VALUES
('Subash', 'subash@gmail.com', '9869113631', 'Bhaktapur', '2024-01-15'),
('Yesbin', 'asbin@gmail.com', '9869223421', 'Kathmandu', '2024-02-20');



CREATE TABLE IF NOT EXISTS admins (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(100) NOT NULL UNIQUE,
    password_hash VARCHAR(255) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);
