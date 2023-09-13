<?php
require './db.php';

$sql = "
    CREATE TABLE IF NOT EXISTS Users (
        user_id INT(11) UNSIGNED AUTO_INCREMENT PRIMARY KEY,
        username VARCHAR(50) NOT NULL,
        email VARCHAR(100) NOT NULL,
        password VARCHAR(255) NOT NULL,
        user_image TEXT DEFAULT NULL,
        number VARCHAR(50) DEFAULT NULL,
        address VARCHAR(50) DEFAULT NULL,
        city VARCHAR(50) DEFAULT NULL,
        user_token VARCHAR(12) UNIQUE,
        role ENUM('user', 'admin') NOT NULL,
        email_verify BOOLEAN DEFAULT FALSE,
        created_at TIMESTAMP DEFAULT NOW(),
        updated_at TIMESTAMP DEFAULT NOW() ON UPDATE NOW()
    );

    CREATE TABLE IF NOT EXISTS verification_codes (
        verification_id INT(11) UNSIGNED AUTO_INCREMENT PRIMARY KEY,
        email VARCHAR(100) NOT NULL,
        code VARCHAR(16) UNIQUE NOT NULL,
        created_at TIMESTAMP DEFAULT NOW()
    );

    CREATE TABLE IF NOT EXISTS Categories (
        category_id INT(11) UNSIGNED AUTO_INCREMENT PRIMARY KEY,
        name VARCHAR(100) NOT NULL,
        description VARCHAR(255) NOT NULL,
        image TEXT,
        created_at TIMESTAMP DEFAULT NOW(),
        updated_at TIMESTAMP DEFAULT NOW() ON UPDATE NOW()
    );

    CREATE TABLE IF NOT EXISTS SubCategories (
        subcategory_id INT(11) UNSIGNED AUTO_INCREMENT PRIMARY KEY,
        category_id INT NOT NULL,
        name VARCHAR(100) NOT NULL,
        size TEXT,
        created_at TIMESTAMP DEFAULT NOW(),
        updated_at TIMESTAMP DEFAULT NOW() ON UPDATE NOW()
    );

    CREATE TABLE IF NOT EXISTS Products (
        product_id INT(11) UNSIGNED AUTO_INCREMENT PRIMARY KEY,
        subcategory_id INT NOT NULL,
        category_id INT NOT NULL,
        title VARCHAR(255) NOT NULL,
        description TEXT NOT NULL,
        slug VARCHAR(255) NOT NULL,
        price DECIMAL(10, 2) NOT NULL,
        images TEXT,
        available BOOLEAN DEFAULT TRUE,
        created_at TIMESTAMP DEFAULT NOW(),
        updated_at TIMESTAMP DEFAULT NOW() ON UPDATE NOW()
    );

    CREATE TABLE IF NOT EXISTS FavoritesLists (
        list_id INT(11) UNSIGNED AUTO_INCREMENT PRIMARY KEY,
        user_id INT NOT NULL
    );

    CREATE TABLE IF NOT EXISTS Favorites (
        favorite_id INT(11) UNSIGNED AUTO_INCREMENT PRIMARY KEY,
        list_id INT NOT NULL,
        product_id INT NOT NULL,
        created_at TIMESTAMP DEFAULT NOW()
    );

    CREATE TABLE IF NOT EXISTS CartLists (
        list_id INT(11) UNSIGNED AUTO_INCREMENT PRIMARY KEY,
        user_id INT
    );

    CREATE TABLE IF NOT EXISTS Carts (
        cart_id INT(11) UNSIGNED AUTO_INCREMENT PRIMARY KEY,
        list_id INT,
        product_id INT,
        quantity INT,
        size VARCHAR(50) DEFAULT NULL,
        created_at TIMESTAMP DEFAULT NOW()
    );

    CREATE TABLE IF NOT EXISTS Orders (
        id INT(11) UNSIGNED AUTO_INCREMENT PRIMARY KEY,
        order_id VARCHAR(12) NOT NULL,
        products TEXT NOT NULL,
        products_ids TEXT NOT NULL,
        name VARCHAR(50) NOT NULL,
        phone VARCHAR(50) NOT NULL,
        address TEXT NOT NULL,
        price DECIMAL(10, 2) NOT NULL,
        status VARCHAR(100) NOT NULL,
        is_paid TINYINT(1) DEFAULT 0,
        user_id INT NOT NULL,
        created_at TIMESTAMP DEFAULT NOW(),
        updated_at TIMESTAMP DEFAULT NOW() ON UPDATE NOW()
    );

    CREATE TABLE IF NOT EXISTS Comments (
        comment_id INT(11) UNSIGNED AUTO_INCREMENT PRIMARY KEY,
        user_id INT,
        product_id INT,
        comment TEXT,
        created_at TIMESTAMP DEFAULT NOW(),
        updated_at TIMESTAMP DEFAULT NOW() ON UPDATE NOW()
    );

    CREATE TABLE IF NOT EXISTS Blogs (
        blog_id INT(11) UNSIGNED AUTO_INCREMENT PRIMARY KEY,
        title VARCHAR(50) NOT NULL,
        description TEXT NOT NULL,
        image TEXT,
        slug TEXT NOT NULL,
        tags TEXT NOT NULL,
        user_id INT,
        created_at TIMESTAMP DEFAULT NOW(),
        updated_at TIMESTAMP DEFAULT NOW() ON UPDATE NOW()
    );

";

$sqlStatements = explode(';', $sql);

foreach ($sqlStatements as $sqlStatement) {
    $sqlStatement = trim($sqlStatement);

    if (!empty($sqlStatement)) {
        if ($conn->query($sqlStatement) !== TRUE) {
            echo json_encode(array('error' => 'db error'));
            exit;
        }
    }
}