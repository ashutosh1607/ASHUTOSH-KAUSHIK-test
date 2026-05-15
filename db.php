<?php
/**
 * Core PHP Database Connection using SQLite
 */
try {
    $db = new PDO('sqlite:' . __DIR__ . '/invoice_crm.sqlite');
    $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $db->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);

    // Initialize Schema
    $db->exec("CREATE TABLE IF NOT EXISTS settings (
        id INTEGER PRIMARY KEY,
        admin_name TEXT,
        logo_url TEXT,
        business_address TEXT,
        email TEXT,
        phone TEXT,
        currency TEXT DEFAULT '$'
    )");

    $db->exec("CREATE TABLE IF NOT EXISTS customers (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        name TEXT NOT NULL,
        email TEXT,
        phone TEXT,
        address TEXT,
        created_at DATETIME DEFAULT CURRENT_TIMESTAMP
    )");

    $db->exec("CREATE TABLE IF NOT EXISTS invoices (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        invoice_number TEXT NOT NULL,
        customer_id INTEGER,
        customer_name TEXT,
        date DATETIME DEFAULT CURRENT_TIMESTAMP,
        subtotal REAL,
        tax REAL,
        total REAL,
        status TEXT DEFAULT 'pending',
        FOREIGN KEY (customer_id) REFERENCES customers(id)
    )");

    $db->exec("CREATE TABLE IF NOT EXISTS invoice_items (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        invoice_id INTEGER,
        description TEXT,
        quantity INTEGER,
        price REAL,
        total REAL,
        FOREIGN KEY (invoice_id) REFERENCES invoices(id)
    )");

    // Ensure default settings exist
    $stmt = $db->query("SELECT COUNT(*) FROM settings");
    if ($stmt->fetchColumn() == 0) {
        $db->exec("INSERT INTO settings (admin_name, currency) VALUES ('My Business', '$')");
    }

} catch (PDOException $e) {
    die("Database connection failed: " . $e->getMessage());
}
