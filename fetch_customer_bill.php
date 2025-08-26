<?php
header('Content-Type: application/json');
session_start();
include 'db_connect.php';

// sanitize
$customer_id = isset($_POST['customer_id']) ? trim($_POST['customer_id']) : '';
$month = isset($_POST['month']) ? intval($_POST['month']) : intval(date('n'));
$year = isset($_POST['year']) ? intval($_POST['year']) : intval(date('Y'));

if ($customer_id === '') {
    echo json_encode(["error" => "Missing customer_id"]);
    exit;
}

// Helpers
function fmt($n)
{
    return number_format((float) $n, 2);
}

// --- SALES (amount) ---
/** total sales strictly before the selected month */
$q_prev_sales = $conn->query("
    SELECT COALESCE(SUM(amount),0) AS s 
    FROM sales 
    WHERE id='{$conn->real_escape_string($customer_id)}'
      AND (YEAR(date) < $year OR (YEAR(date) = $year AND MONTH(date) < $month))
");
$prev_sales = ($q_prev_sales && $q_prev_sales->num_rows) ? (float) $q_prev_sales->fetch_assoc()['s'] : 0;

/** sales in the selected month */
$q_curr_sales = $conn->query("
    SELECT COALESCE(SUM(amount),0) AS s 
    FROM sales 
    WHERE id='{$conn->real_escape_string($customer_id)}'
      AND YEAR(date) = $year AND MONTH(date) = $month
");
$curr_sales = ($q_curr_sales && $q_curr_sales->num_rows) ? (float) $q_curr_sales->fetch_assoc()['s'] : 0;

/** total sales up to end of selected month */
$q_total_sales_till = $conn->query("
    SELECT COALESCE(SUM(amount),0) AS s 
    FROM sales 
    WHERE id='{$conn->real_escape_string($customer_id)}'
      AND (YEAR(date) < $year OR (YEAR(date) = $year AND MONTH(date) <= $month))
");
$total_sales_till = ($q_total_sales_till && $q_total_sales_till->num_rows) ? (float) $q_total_sales_till->fetch_assoc()['s'] : 0;

// --- PAYMENTS ---
/** payments strictly before selected month */
$q_prev_pay = $conn->query("
    SELECT COALESCE(SUM(amount_paid),0) AS p 
    FROM payments 
    WHERE customer_id='{$conn->real_escape_string($customer_id)}'
      AND (YEAR(payment_date) < $year OR (YEAR(payment_date) = $year AND MONTH(payment_date) < $month))
");
$prev_pay = ($q_prev_pay && $q_prev_pay->num_rows) ? (float) $q_prev_pay->fetch_assoc()['p'] : 0;

/** payments in selected month */
$q_curr_pay = $conn->query("
    SELECT COALESCE(SUM(amount_paid),0) AS p 
    FROM payments 
    WHERE customer_id='{$conn->real_escape_string($customer_id)}'
      AND YEAR(payment_date) = $year AND MONTH(payment_date) = $month
");
$curr_pay = ($q_curr_pay && $q_curr_pay->num_rows) ? (float) $q_curr_pay->fetch_assoc()['p'] : 0;

/** total payments till end of selected month */
$q_total_pay_till = $conn->query("
    SELECT COALESCE(SUM(amount_paid),0) AS p 
    FROM payments 
    WHERE customer_id='{$conn->real_escape_string($customer_id)}'
      AND (YEAR(payment_date) < $year OR (YEAR(payment_date) = $year AND MONTH(payment_date) <= $month))
");
$total_pay_till = ($q_total_pay_till && $q_total_pay_till->num_rows) ? (float) $q_total_pay_till->fetch_assoc()['p'] : 0;

// KPIs
$previous_bill = $prev_sales - $prev_pay;                 // strictly before month
$current_month_bill = $curr_sales;                        // in month
$total_payments_till_month = $total_pay_till;             // till end of month
$net_due = $total_sales_till - $total_pay_till;
$balance = $net_due > 0 ? $net_due : 0;
$advance = $net_due < 0 ? abs($net_due) : 0;

// Payment history table (all payments for this customer, latest first)
$history = "
<table id='paymentTable' class='table table-striped table-bordered'>
    <thead class='table-dark'>
        <tr>
            <th>Date</th>
            <th>Amount</th>
            <th>Method</th>
            <th>Balance</th>
            <th>Advance</th>
        </tr>
    </thead>
    <tbody>
";
$res = $conn->query("
    SELECT payment_date, amount_paid, payment_method, balance, advance
    FROM payments
    WHERE customer_id='{$conn->real_escape_string($customer_id)}'
    ORDER BY payment_date DESC, sno DESC
");
if ($res) {
    while ($r = $res->fetch_assoc()) {
        $dateFmt = date('d-M-Y', strtotime($r['payment_date'])); // 23-Aug-2025
        $history .= "<tr>
            <td>{$dateFmt}</td>
            <td>" . fmt($r['amount_paid']) . "</td>
            <td>{$r['payment_method']}</td>
            <td>" . fmt($r['balance']) . "</td>
            <td>" . fmt($r['advance']) . "</td>
        </tr>";
    }
}
$history .= "</tbody></table>";

echo json_encode([
    "previous_bill" => number_format($previous_bill, 2),
    "current_month_bill" => number_format($current_month_bill, 2),
    "total_payments_till_month" => number_format($total_payments_till_month, 2),
    "balance" => number_format($balance, 2),
    "advance" => number_format($advance, 2),
    "payment_history" => $history
]);