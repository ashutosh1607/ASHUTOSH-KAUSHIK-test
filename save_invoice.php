<?php
require_once 'db.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $customer_id = $_POST['customer_id'];
    $invoice_number = $_POST['invoice_number'];
    
    // Get customer info
    $c_stmt = $db->prepare("SELECT name FROM customers WHERE id = ?");
    $c_stmt->execute([$customer_id]);
    $customer_name = $c_stmt->fetchColumn();

    $descs = $_POST['desc'];
    $qtys = $_POST['qty'];
    $prices = $_POST['price'];

    $subtotal = 0;
    foreach ($qtys as $i => $qty) {
        $subtotal += $qty * $prices[$i];
    }
    $tax = $subtotal * 0.10;
    $total = $subtotal + $tax;

    try {
        $db->beginTransaction();

        $inv_stmt = $db->prepare("INSERT INTO invoices (invoice_number, customer_id, customer_name, subtotal, tax, total, status) VALUES (?, ?, ?, ?, ?, ?, 'pending')");
        $inv_stmt->execute([$invoice_number, $customer_id, $customer_name, $subtotal, $tax, $total]);
        $invoice_id = $db->lastInsertId();

        $item_stmt = $db->prepare("INSERT INTO invoice_items (invoice_id, description, quantity, price, total) VALUES (?, ?, ?, ?, ?)");
        foreach ($descs as $i => $desc) {
            $item_total = $qtys[$i] * $prices[$i];
            $item_stmt->execute([$invoice_id, $desc, $qtys[$i], $prices[$i], $item_total]);
        }

        $db->commit();
        header("Location: view_invoice.php?id=" . $invoice_id);
        exit;

    } catch (Exception $e) {
        $db->rollBack();
        die("Error saving invoice: " . $e->getMessage());
    }
}
