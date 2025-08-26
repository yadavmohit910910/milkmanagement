<?php
session_start();
if (!isset($_SESSION['loggedin']) || $_SESSION['loggedin'] != true) {
    header("location: login.php");
    exit;
}

// Database connection setup
include 'db_connect.php';

// Export CSV
if (isset($_GET['export']) && $_GET['export'] == 'csv' && isset($_GET['month']) && isset($_GET['year'])) {
    $month = $_GET['month'];
    $year = $_GET['year'];
    $customerCondition = "";

    if (isset($_GET['customer_id']) && $_GET['customer_id'] != '') {
        $customerId = $_GET['customer_id'];
        $customerCondition = " AND payments.customer_id = '$customerId'";
    }

    $filename = "payments_{$month}_{$year}.csv";
    header('Content-Type: text/csv');
    header("Content-Disposition: attachment; filename=\"$filename\"");

    $output = fopen("php://output", "w");
    fputcsv($output, array('Customer Name', 'Date', 'Amount Paid', 'Payment Method', 'Balance', 'Advance'));

    $query = "SELECT payments.*, customers.name AS customer_name FROM payments 
              INNER JOIN customers ON payments.customer_id = customers.id 
              WHERE MONTH(payments.payment_date) = '$month' 
              AND YEAR(payments.payment_date) = '$year' 
              $customerCondition";

    $result = mysqli_query($conn, $query);
    while ($row = mysqli_fetch_assoc($result)) {
        fputcsv($output, array(
            $row['customer_name'],
            $row['payment_date'],
            $row['amount_paid'],
            $row['payment_method'],
            $row['balance'],
            $row['advance']
        ));
    }
    fclose($output);
    exit;
}
// ✅ Delete payment
if (isset($_GET['delete'])) {
    $sno = $_GET['delete'];
    $sql = "DELETE FROM `payments` WHERE `sno` = $sno";
    mysqli_query($conn, $sql);
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>View Payments</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.4/css/jquery.dataTables.min.css">
</head>

<body>
    <?php include "navbar.php"; ?>
    <div class="col-2 text-center mt-2">
        <a href="/milkmanagement/index.php" class="btn btn-secondary btn-lg">🏠 Go Back Home</a>
    </div>

    <div class="container mt-5">
        <h2 class="mb-4">View Payments</h2>

        <form method="GET" class="row g-3 mb-4">
            <div class="col-md-3">
                <label for="month">Month:</label>
                <select class="form-control" name="month" required>
                    <option value="">Select Month</option>
                    <?php
                    for ($m = 1; $m <= 12; $m++) {
                        $selected = (isset($_GET['month']) && $_GET['month'] == $m) ? 'selected' : '';
                        echo "<option value='$m' $selected>" . date('F', mktime(0, 0, 0, $m, 1)) . "</option>";
                    }
                    ?>
                </select>
            </div>

            <div class="col-md-3">
                <label for="year">Year:</label>
                <select class="form-control" name="year" required>
                    <option value="">Select Year</option>
                    <?php
                    $currentYear = date('Y');
                    for ($y = $currentYear; $y >= 2020; $y--) {
                        $selected = (isset($_GET['year']) && $_GET['year'] == $y) ? 'selected' : '';
                        echo "<option value='$y' $selected>$y</option>";
                    }
                    ?>
                </select>
            </div>

            <div class="col-md-4">
                <label for="customer">Select Customer:</label>
                <select class="form-control" name="customer_id">
                    <option value="">All Customers</option>
                    <?php
                    $customer_query = mysqli_query($conn, "SELECT id, name FROM customers ORDER BY name ASC");
                    while ($cust = mysqli_fetch_assoc($customer_query)) {
                        $selected = (isset($_GET['customer_id']) && $_GET['customer_id'] == $cust['id']) ? 'selected' : '';
                        echo "<option value='{$cust['id']}' $selected>{$cust['name']}</option>";
                    }
                    ?>
                </select>
            </div>

            <div class="col-md-2 d-grid align-items-end">
                <button type="submit" class="btn btn-primary">Filter</button>
            </div>
        </form>

        <?php if (isset($_GET['month']) && isset($_GET['year'])): ?>
        <a href="view_payments.php?month=<?php echo $_GET['month']; ?>&year=<?php echo $_GET['year']; ?>&customer_id=<?php echo $_GET['customer_id'] ?? ''; ?>&export=csv"
            class="btn btn-success mb-3">
            ⬇️ Download CSV
        </a>
        <?php endif; ?>

        <table id="paymentsTable" class="table table-bordered table-striped">
            <thead>
                <tr>
                    <th>#</th>
                    <th>Customer Name</th>
                    <th>Date</th>
                    <th>Amount Paid</th>
                    <th>Payment Method</th>
                    <th>Balance</th>
                    <th>Advance</th>
                    <th>Action</th>
                </tr>
            </thead>
            <tbody>
                <?php
                if (isset($_GET['month']) && isset($_GET['year'])) {
                    $month = $_GET['month'];
                    $year = $_GET['year'];
                    $customerCondition = "";

                    if (isset($_GET['customer_id']) && $_GET['customer_id'] != '') {
                        $customerId = $_GET['customer_id'];
                        $customerCondition = " AND payments.customer_id = '$customerId'";
                    }

                    $query = "SELECT payments.sno, payments.customer_id, customers.name AS customer_name, 
                              payments.payment_date, payments.amount_paid, payments.payment_method, 
                              payments.balance, payments.advance 
                              FROM payments 
                              INNER JOIN customers ON payments.customer_id = customers.id 
                              WHERE MONTH(payments.payment_date) = '$month' 
                              AND YEAR(payments.payment_date) = '$year' 
                              $customerCondition";

                    $result = mysqli_query($conn, $query);
                    $sno = 1;
                    while ($row = mysqli_fetch_assoc($result)) {
                        $formattedDate = date("d-m-Y", strtotime($row['payment_date']));
                        echo "<tr>
                                <td>$sno</td>
                                <td>{$row['customer_name']}</td>
                                <td>$formattedDate</td>
                                <td>{$row['amount_paid']}</td>
                                <td>{$row['payment_method']}</td>
                                <td>{$row['balance']}</td>
                                <td>{$row['advance']}</td>
                                <td>
                                    <button class='delete btn btn-sm btn-danger' data-sno='{$row['sno']}'>Delete</button>
                                </td>
                              </tr>";
                        $sno++;
                    }
                }
                ?>
            </tbody>
        </table>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://code.jquery.com/jquery-3.6.4.min.js"></script>
    <script src="//cdn.datatables.net/1.13.4/js/jquery.dataTables.min.js"></script>
    <script>
    $(document).ready(function() {
        $('#paymentsTable').DataTable();
    });
    </script>
    <script>
    // Delete payment entry
    $('#paymentsTable').on('click', '.delete', function(e) {
        var sno = $(this).data('sno');

        if (confirm("Are you sure you want to delete this payment entry?")) {
            window.location = `view_payments.php?delete=${sno}`;
        }
    });
    </script>
</body>

</html>