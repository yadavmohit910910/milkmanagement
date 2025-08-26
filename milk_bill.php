<?php
session_start();
if (!isset($_SESSION['loggedin']) || $_SESSION['loggedin'] != true) {
    header("location: login.php");
    exit;
}
include 'db_connect.php';

// Fetch all customers
$customers = $conn->query("SELECT id, name FROM customers ORDER BY name ASC");
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>Billing - Monthly Report</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- DataTables CSS -->
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/dataTables.bootstrap5.min.css">
    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.6/js/dataTables.bootstrap5.min.js"></script>
</head>

<body class="bg-light">
    <div class="container py-4">
        <h2 class="mb-4 text-center text-primary">Download Monthly Bill</h2>

        <!-- Filter Form -->
        <div class="card shadow-sm mb-4">
            <div class="card-body">
                <form method="get" action="download_bill.php" class="row g-3">
                    <div class="col-md-4">
                        <label class="form-label">Customer</label>
                        <select name="customer_id" class="form-select" required>
                            <option value="">Select</option>
                            <?php while ($c = $customers->fetch_assoc()): ?>
                            <option value="<?= $c['id'] ?>"><?= htmlspecialchars($c['name']) ?></option>
                            <?php endwhile; ?>
                        </select>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">Month</label>
                        <select name="month" class="form-select" required>
                            <?php for ($m = 1; $m <= 12; $m++): ?>
                            <option value="<?= $m ?>"><?= date("F", mktime(0, 0, 0, $m, 1)) ?></option>
                            <?php endfor; ?>
                        </select>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">Year</label>
                        <select name="year" class="form-select" required>
                            <?php for ($y = date("Y"); $y >= 2020; $y--): ?>
                            <option value="<?= $y ?>"><?= $y ?></option>
                            <?php endfor; ?>
                        </select>
                    </div>
                    <div class="col-md-2 d-flex align-items-end">
                        <button type="submit" class="btn btn-success w-100">Download CSV</button>
                    </div>
                </form>
            </div>
        </div>

        <!-- Latest Bills Table -->
        <div class="card shadow-sm">
            <div class="card-body">
                <h5 class="mb-3">Recent Sales</h5>
                <div class="table-responsive">
                    <table id="billTable" class="table table-bordered table-striped">
                        <thead class="table-dark">
                            <tr>
                                <th>Date</th>
                                <th>Customer</th>
                                <th>Quantity</th>
                                <th>Rate</th>
                                <th>Amount</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                            // Show latest 50 sales
                            $sql = "SELECT s.date, s.qty, s.rate, (s.qty*s.rate) as amount, c.name 
                                FROM sales s 
                                JOIN customers c ON c.id = s.id 
                                ORDER BY s.date DESC LIMIT 50";
                            $res = $conn->query($sql);
                            while ($r = $res->fetch_assoc()):
                                ?>
                            <tr>
                                <td><?= $r['date'] ?></td>
                                <td><?= htmlspecialchars($r['name']) ?></td>
                                <td><?= $r['qty'] ?></td>
                                <td><?= $r['rate'] ?></td>
                                <td><?= number_format($r['amount'], 2) ?></td>
                            </tr>
                            <?php endwhile; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
        <div class="col-12 text-center mt-2">
            <a href="/milkmanagement/index.php" class="btn btn-secondary px-4">🏠 Go Back Home</a>
        </div>
    </div>

    <script>
    $(document).ready(function() {
        $('#billTable').DataTable({
            pageLength: 10,
            order: [
                [0, 'desc']
            ]
        });
    });
    </script>

</body>

</html>