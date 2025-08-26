<?php
session_start();
include 'db_connect.php';

// sanitize
$month = isset($_POST['month']) ? intval($_POST['month']) : intval(date('n'));
$year = isset($_POST['year']) ? intval($_POST['year']) : intval(date('Y'));

// customers from sales (distinct)
$custQ = $conn->query("SELECT DISTINCT id, name FROM sales ORDER BY name ASC");

$html = "
<table id='statusTable' class='table table-bordered table-striped'>
    <thead class='table-dark'>
        <tr>
            <th>Customer</th>
            <th>Current Bill</th>
            <th>Payments (This Month)</th>
            <th>Status</th>
        </tr>
    </thead>
    <tbody>
";

function fmtN($n)
{
    return number_format((float) $n, 2);
}

while ($c = $custQ->fetch_assoc()) {
    $cid = $conn->real_escape_string($c['id']);
    $cname = htmlspecialchars($c['name']);

    // current month sales
    $sQ = $conn->query("
        SELECT COALESCE(SUM(amount),0) AS s 
        FROM sales
        WHERE id='$cid' AND YEAR(date)=$year AND MONTH(date)=$month
    ");
    $curr_sales = $sQ ? (float) $sQ->fetch_assoc()['s'] : 0;

    // current month payments
    $pQ = $conn->query("
        SELECT COALESCE(SUM(amount_paid),0) AS p 
        FROM payments
        WHERE customer_id='$cid' AND YEAR(payment_date)=$year AND MONTH(payment_date)=$month
    ");
    $curr_pay = $pQ ? (float) $pQ->fetch_assoc()['p'] : 0;

    $status = $curr_pay >= $curr_sales ?
        "<span class='badge badge-paid text-white px-3 py-2'>Paid</span>" :
        "<span class='badge badge-unpaid text-white px-3 py-2'>Unpaid</span>";

    $html .= "<tr>
        <td>$cname ($cid)</td>
        <td>" . fmtN($curr_sales) . "</td>
        <td>" . fmtN($curr_pay) . "</td>
        <td>$status</td>
    </tr>";
}

$html .= "</tbody></table>";

echo $html;