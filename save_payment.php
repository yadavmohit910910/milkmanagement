<?php
header('Content-Type: application/json');
session_start();
include 'db_connect.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(["status" => "error", "message" => "Invalid request"]);
    exit;
}

$customer_id = isset($_POST['customer_id']) ? trim($_POST['customer_id']) : '';
$payment_date = isset($_POST['payment_date']) ? trim($_POST['payment_date']) : '';
$amount_paid = isset($_POST['amount_paid']) ? floatval($_POST['amount_paid']) : 0;
$payment_method = isset($_POST['payment_method']) ? trim($_POST['payment_method']) : '';

if ($customer_id === '' || $payment_date === '' || $amount_paid <= 0 || $payment_method === '') {
    echo json_encode(["status" => "error", "message" => "Please fill all fields properly"]);
    exit;
}

// normalize date to Y-m-d
$payment_date = date('Y-m-d', strtotime($payment_date));
$cid = $conn->real_escape_string($customer_id);

// Duplicate: same customer + same date
$chk = $conn->query("SELECT sno FROM payments WHERE customer_id='$cid' AND payment_date='$payment_date'");
if ($chk && $chk->num_rows > 0) {
    echo json_encode(["status" => "error", "message" => "Payment already exists for this customer on this date"]);
    exit;
}

// Compute balance/advance up to this date (inclusive)
$y = (int) date('Y', strtotime($payment_date));
$m = (int) date('n', strtotime($payment_date));
$d = (int) date('j', strtotime($payment_date));

// total sales till payment_date
$qSales = $conn->query("
    SELECT COALESCE(SUM(amount),0) AS s
    FROM sales
    WHERE id='$cid' AND date <= '$payment_date'
");
$totalSales = ($qSales && $qSales->num_rows) ? (float) $qSales->fetch_assoc()['s'] : 0;

// total payments till yesterday (exclude today first, then we will add current)
$qPaid = $conn->query("
    SELECT COALESCE(SUM(amount_paid),0) AS p
    FROM payments
    WHERE customer_id='$cid' AND payment_date < '$payment_date'
");
$totalPaidBefore = ($qPaid && $qPaid->num_rows) ? (float) $qPaid->fetch_assoc()['p'] : 0;

// After adding current payment
$totalPaidAfter = $totalPaidBefore + $amount_paid;

// Net position after this payment
$net = $totalSales - $totalPaidAfter;
$balance = $net > 0 ? $net : 0;
$advance = $net < 0 ? abs($net) : 0;

// Insert payment row
$stmt = $conn->prepare("INSERT INTO payments (payment_date, customer_id, amount_paid, payment_method, balance, advance) VALUES (?,?,?,?,?,?)");
$stmt->bind_param("ssdsss", $payment_date, $customer_id, $amount_paid, $payment_method, $balance, $advance);

if ($stmt->execute()) {
    echo json_encode(["status" => "success", "message" => "Payment Saved Successfully!"]);
} else {
    echo json_encode(["status" => "error", "message" => $stmt->error]);
}