<?php

// Connect to the database
$host = "mysql.wiu.edu:2633";
$username = "root";
$password = "ros6reb2";
$db_name = "products_db";
$table_name_products = "products";
$table_name_comments = "comments";

$db_conn = mysql_connect($host, $username, $password) or die("Unable to connect to the database.");

// Check if the database exists and create it if it doesn't
$db_select = mysql_select_db($db_name, $db_conn);
if (!$db_select) {
    $sql = "CREATE DATABASE $db_name";
    if (mysql_query($sql, $db_conn)) {
        mysql_select_db($db_name, $db_conn);
    } else {
        echo json_encode(['error' => mysql_error($db_conn)]);
        mysql_close($db_conn);
        exit;
    }
}

// Check if the products table exists and create it if it doesn't
$table_check_products = mysql_query("SHOW TABLES LIKE '$table_name_products'", $db_conn);
if (mysql_num_rows($table_check_products) == 0) {
    $sql = "CREATE TABLE $table_name_products (
        id INT AUTO_INCREMENT PRIMARY KEY,
        title VARCHAR(255) NOT NULL,
        description TEXT,
        image VARCHAR(255) NOT NULL,
        price FLOAT(10, 2) NOT NULL
    )";
    if (!mysql_query($sql, $db_conn)) {
        echo json_encode(['error' => mysql_error($db_conn)]);
        mysql_close($db_conn);
        exit;
    }
}

// Check if the comments table exists and create it if it doesn't
$table_check_comments = mysql_query("SHOW TABLES LIKE '$table_name_comments'", $db_conn);
if (mysql_num_rows($table_check_comments) == 0) {
    $sql = "CREATE TABLE $table_name_comments (
        id INT AUTO_INCREMENT PRIMARY KEY,
        product_id INT NOT NULL,
        name VARCHAR(255) NOT NULL,
        comment TEXT NOT NULL,
        created_at DATETIME NOT NULL,
        updated_at DATETIME NOT NULL,
        FOREIGN KEY (product_id) REFERENCES $table_name_products(id)
    )";
    if (!mysql_query($sql, $db_conn)) {
        echo json_encode(['error' => mysql_error($db_conn)]);
        mysql_close($db_conn);
        exit;
    }
}
