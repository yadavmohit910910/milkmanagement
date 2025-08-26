<?php
session_start();
if (!isset($_SESSION['loggedin']) || $_SESSION['loggedin'] != true) {
    header("location: login.php");
    exit;
}

include 'db_connect.php';

if (!isset($_GET['customer_id'], $_GET['month'], $_GET['year'])) {
    die("Invalid Request");
}

$customer_id = intval($_GET['customer_id']);
$month = intval($_GET['month']);
$year = intval($_GET['year']);

// Fetch customer name
$cust = $conn->query("SELECT name FROM customers WHERE id=$customer_id");
if ($cust->num_rows == 0)
    die("Customer not found");
$cust_name = preg_replace('/[^A-Za-z0-9]/', '_', $cust->fetch_assoc()['name']);

// Query sales + customer name
$sql = "SELECT s.date, s.qty, s.rate, (s.qty*s.rate) as amount, c.name AS customer_name
        FROM sales s
        JOIN customers c ON c.id = s.id
        WHERE s.id = $customer_id
        AND MONTH(s.date) = $month 
        AND YEAR(s.date) = $year 
        ORDER BY s.date ASC";
$res = $conn->query($sql);

// CSV Headers
header('Content-Type: text/csv');
header("Content-Disposition: attachment; filename=Milk_Bill_{$cust_name}_{$month}_{$year}.csv");

$output = fopen("php://output", "w");

// Add customer details row
fputcsv($output, ["Customer Name:", $cust_name]);
// fputcsv($output, ["Month:", date("F", mktime(0, 0, 0, $month, 1)), "Year:", $year]);
fputcsv($output, []); // empty line

// Table headers
fputcsv($output, ['Date', 'Quantity', 'Rate', 'Amount']);

// Data rows
$total = 0;
while ($row = $res->fetch_assoc()) {
    // Date format: 21-Aug-2025 जैसा
    $formattedDate = date("d-M-Y", strtotime($row['date']));
    fputcsv($output, [$formattedDate, $row['qty'], $row['rate'], $row['amount']]);
    $total += $row['amount'];
}

// Total row
fputcsv($output, []);
fputcsv($output, ['', '', 'Total', $total]);

fclose($output);
exit;