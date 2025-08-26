<?php
session_start();
if (!isset($_SESSION['loggedin']) || $_SESSION['loggedin'] !== true) {
    header("location: login.php");
    exit;
}
include 'db_connect.php';
?>
<!doctype html>
<html lang="en">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Monthly Bill Report</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="//cdn.datatables.net/1.13.4/css/jquery.dataTables.min.css">
    <style>
    body {
        background-color: #f8f9fa;
    }

    h2 {
        color: #0d6efd;
    }

    label {
        font-weight: 500;
    }

    .btn-primary {
        border-radius: 8px;
    }
    </style>
</head>

<body>
    <?php require 'navbar.php'; ?>

    <div class="container my-4">
        <div class="card shadow-sm p-4">
            <h2 class="mb-4">Generate Monthly Bill Report</h2>
            <form class="row g-3" action="" method="POST">
                <div class="col-md-3">
                    <label for="start_date" class="form-label">Start Date:</label>
                    <input type="date" id="start_date" name="start_date" class="form-control" required>
                </div>
                <div class="col-md-3">
                    <label for="end_date" class="form-label">End Date:</label>
                    <input type="date" id="end_date" name="end_date" class="form-control" required>
                </div>
                <div class="col-md-4">
                    <label for="customername" class="form-label">Customer:</label>
                    <select id="customername" class="form-select" name="customername" required>
                        <option value="">Select Customer</option>
                        <?php
                        $customerQuery = "SELECT DISTINCT `name` FROM customers ORDER BY name ASC";
                        $customerResult = mysqli_query($conn, $customerQuery);
                        while ($customerData = mysqli_fetch_assoc($customerResult)) {
                            echo "<option value='" . $customerData['name'] . "'>" . $customerData['name'] . "</option>";
                        }
                        ?>
                    </select>
                </div>
                <div class="col-md-2 d-flex align-items-end">
                    <button type="submit" class="btn btn-primary w-100">Generate</button>
                </div>
            </form>
        </div>

        <div class="col-12 text-center mt-4">
            <a href="/milkmanagement/index.php" class="btn btn-secondary btn-lg">🏠 Go Back Home</a>
        </div>

        <?php
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $customerName = mysqli_real_escape_string($conn, $_POST['customername']);
            $startDate = mysqli_real_escape_string($conn, $_POST['start_date']);
            $endDate = mysqli_real_escape_string($conn, $_POST['end_date']);

            $query = "SELECT c.name, c.address, s.date, s.product, s.rate, s.qty, s.amount
                      FROM customers c
                      INNER JOIN sales s ON c.id = s.id
                      WHERE c.name = '$customerName' 
                      AND s.date BETWEEN '$startDate' AND '$endDate'
                      ORDER BY s.date ASC";

            $result = mysqli_query($conn, $query);

            if (mysqli_num_rows($result) > 0) {
                // CSV folder create check
                $csvDir = 'reports';
                if (!is_dir($csvDir)) {
                    mkdir($csvDir, 0777, true);
                }

                $customerNameSanitized = preg_replace('/[^a-zA-Z0-9_-]/', '_', $customerName);
                $csvFileName = $csvDir . '/' . $customerNameSanitized . '_' . date('Ymd_His') . '.csv';
                $csvFile = fopen($csvFileName, 'w');

                $headerRow = ['Customer Name', 'Address', 'Date', 'Product', 'Rate', 'Quantity', 'Amount'];
                fputcsv($csvFile, $headerRow);

                echo "<div class='alert alert-success mt-4'>
                        Report generated successfully for <strong>$customerName</strong>.<br>
                        <a href='$csvFileName' class='btn btn-success mt-2'>Download CSV</a>
                      </div>";

                echo "<div class='card shadow-sm p-3 mt-4'>
                        <h5>Report Preview</h5>
                        <div class='table-responsive'>
                        <table id='reportTable' class='table table-striped table-bordered'>
                            <thead>
                                <tr>
                                    <th>Customer Name</th>
                                    <th>Address</th>
                                    <th>Date</th>
                                    <th>Product</th>
                                    <th>Rate</th>
                                    <th>Quantity</th>
                                    <th>Amount</th>
                                </tr>
                            </thead>
                            <tbody>";

                while ($row = mysqli_fetch_assoc($result)) {
                    $formattedDate = date('d-M-Y', strtotime($row['date']));
                    echo "<tr>
                            <td>{$row['name']}</td>
                            <td>{$row['address']}</td>
                            <td>$formattedDate</td>
                            <td>{$row['product']}</td>
                            <td>{$row['rate']}</td>
                            <td>{$row['qty']}</td>
                            <td>{$row['amount']}</td>
                          </tr>";

                    fputcsv($csvFile, [
                        $row['name'],
                        $row['address'],
                        $formattedDate,
                        $row['product'],
                        $row['rate'],
                        $row['qty'],
                        $row['amount']
                    ]);
                }
                echo "      </tbody>
                        </table>
                        </div>
                      </div>";

                fclose($csvFile);
            } else {
                echo "<div class='alert alert-warning mt-3'>No records found for the selected period.</div>";
            }
        }
        ?>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://code.jquery.com/jquery-3.6.4.min.js"></script>
    <script src="//cdn.datatables.net/1.13.4/js/jquery.dataTables.min.js"></script>
    <script>
    $(document).ready(function() {
        $('#reportTable').DataTable();
    });
    </script>
</body>

</html>