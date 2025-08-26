<?php
session_start();
if (!isset($_SESSION['loggedin']) || $_SESSION['loggedin'] != true) {
    header("location: login.php");
    exit;
}
?>
<?php
include 'db_connect.php';
if (isset($_GET['delete'])) {
    $sno = $_GET['delete'];
    $delete = true;
    $sql = "DELETE FROM `sales` WHERE `sno` = $sno";
    $result = mysqli_query($conn, $sql);
}
// Check if the form is submitted
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if (isset($_POST['snoedit'])) {
        // Get the sales entry details from the form
        $sno = $_POST['snoedit'];
        $date = $_POST['dateedit'];
        $customerid = $_POST['customeridedit'];
        $customername = $_POST['customernameedit'];
        $product = $_POST['productedit'];
        $rate = $_POST['rateedit'];
        $quantity = $_POST['qtyedit'];
        $amount = $_POST['amountedit'];

        // Update query for sales entry
        $sql = "UPDATE `sales` SET `date` = '$date', `id` = '$customerid', `name` = '$customername', 
                `product` = '$product', `rate` = '$rate', `qty` = '$quantity', `amount` = '$amount' 
                WHERE `sno` = '$sno'";

        // Execute the query
        if (mysqli_query($conn, $sql)) {
            // header("Location: view_sales.php?message=Sales+Updated+Successfully");
            // echo "The record has been inserted successfully <br>";
            $update = true;
        } else {
            echo "Error updating record: " . mysqli_error($conn);
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>View Sales</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>

<body>
    <div class="col-12 text-center mt-4">
        <a href="/milkmanagement/index.php" class="btn btn-secondary btn-lg">🏠 Go Back Home</a>
    </div>
    <div class="container mt-4">
        <h2 class="mb-3">View Sales</h2>

        <!-- Filter -->
        <form method="GET" class="row g-3 mb-3">
            <div class="col-md-3">
                <label>Select Month</label>
                <select class="form-control" name="month">
                    <option value="">All</option>
                    <?php
                    for ($m = 1; $m <= 12; $m++) {
                        $sel = (isset($_GET['month']) && $_GET['month'] == $m) ? "selected" : "";
                        echo "<option value='$m' $sel>" . date("F", mktime(0, 0, 0, $m, 1)) . "</option>";
                    }
                    ?>
                </select>
            </div>
            <div class="col-md-3">
                <label>Select Year</label>
                <select class="form-control" name="year">
                    <option value="">All</option>
                    <?php
                    $cy = date("Y");
                    for ($y = 2020; $y <= $cy; $y++) {
                        $sel = (isset($_GET['year']) && $_GET['year'] == $y) ? "selected" : "";
                        echo "<option value='$y' $sel>$y</option>";
                    }
                    ?>
                </select>
            </div>
            <div class="col-md-3">
                <label>Select Customer</label>
                <select class="form-control" name="customer">
                    <option value="">All</option>
                    <?php
                    $res = mysqli_query($conn, "SELECT id,name FROM customers ORDER BY name");
                    while ($r = mysqli_fetch_assoc($res)) {
                        $sel = (isset($_GET['customer']) && $_GET['customer'] == $r['id']) ? "selected" : "";
                        echo "<option value='{$r['id']}' $sel>{$r['name']} ({$r['id']})</option>";
                    }
                    ?>
                </select>
            </div>
            <div class="col-md-2 align-self-end">
                <button class="btn btn-primary w-100">Filter</button>
            </div>
        </form>

        <!-- Table -->
        <div class="table-responsive">
            <table class="table table-bordered table-striped align-middle">
                <thead class="table-dark">
                    <tr>
                        <th>S.No</th>
                        <th>Date</th>
                        <th>Customer ID</th>
                        <th>Name</th>
                        <th>Product</th>
                        <th>Rate</th>
                        <th>Qty</th>
                        <th>Amount</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    $where = [];
                    if (!empty($_GET['month']))
                        $where[] = "MONTH(s.date)=" . intval($_GET['month']);
                    if (!empty($_GET['year']))
                        $where[] = "YEAR(s.date)=" . intval($_GET['year']);
                    if (!empty($_GET['customer']))
                        $where[] = "s.id='" . mysqli_real_escape_string($conn, $_GET['customer']) . "'";
                    $wsql = (count($where) > 0) ? "WHERE " . implode(" AND ", $where) : "";
                    $q = "SELECT s.sno,s.id,c.name,s.date,s.product,s.rate,s.qty,s.amount
                  FROM sales s JOIN customers c ON s.id=c.id 
                  $wsql ORDER BY s.date DESC";
                    $res = mysqli_query($conn, $q);
                    if ($res && mysqli_num_rows($res) > 0) {
                        $i = 1;
                        while ($row = mysqli_fetch_assoc($res)) {
                            $dt = date("d-M-Y", strtotime($row['date']));
                            echo "<tr>
                    <td>$i</td>
                    <td>$dt</td>
                    <td>{$row['id']}</td>
                    <td>{$row['name']}</td>
                    <td>{$row['product']}</td>
                    <td>{$row['rate']}</td>
                    <td>{$row['qty']}</td>
                    <td>{$row['amount']}</td>
                    <td>
                        <button class='btn btn-sm btn-primary editBtn' 
                            data-sno='{$row['sno']}'
                            data-date='{$row['date']}'
                            data-customerid='{$row['id']}'
                            data-name='{$row['name']}'
                            data-product='{$row['product']}'
                            data-rate='{$row['rate']}'
                            data-qty='{$row['qty']}'>Edit</button>
                        <a href='view_sales.php?delete={$row['sno']}' 
                            class='btn btn-sm btn-danger ms-1'
                            onclick=\"return confirm('Delete this record?')\">Delete</a>
                    </td>
                    </tr>";
                            $i++;
                        }
                    } else {
                        echo "<tr><td colspan='9' class='text-center'>No records</td></tr>";
                    }
                    ?>
                </tbody>
            </table>
        </div>
    </div>

    <!-- Edit Modal -->
    <div class="modal fade" id="editModal" tabindex="-1">
        <div class="modal-dialog">
            <form method="POST" class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Edit Sale</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <input type="hidden" name="snoedit" id="snoedit">
                    <div class="mb-2">
                        <label>Date</label>
                        <input type="date" name="dateedit" id="dateedit" class="form-control" required>
                    </div>
                    <div class="mb-2">
                        <label>Customer ID</label>
                        <input type="text" name="customeridedit" id="customeridedit" class="form-control" readonly>
                    </div>
                    <div class="mb-2">
                        <label>Customer Name</label>
                        <input type="text" name="customernameedit" id="customernameedit" class="form-control" readonly>
                    </div>
                    <div class="mb-2">
                        <label>Product</label>
                        <input type="text" name="productedit" id="productedit" class="form-control" required>
                    </div>
                    <div class="mb-2">
                        <label>Rate</label>
                        <input type="number" step="0.01" name="rateedit" id="rateedit" class="form-control" required>
                    </div>
                    <div class="mb-2">
                        <label>Qty</label>
                        <input type="number" step="0.01" name="qtyedit" id="qtyedit" class="form-control" required>
                    </div>
                    <div class="mb-2">
                        <label>Amount</label>
                        <input type="number" step="0.01" name="amountedit" id="amountedit" class="form-control"
                            readonly>
                    </div>
                </div>
                <div class="modal-footer">
                    <button class="btn btn-success">Save</button>
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                </div>
            </form>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
    // Handle Edit button click
    document.querySelectorAll('.editBtn').forEach(btn => {
        btn.addEventListener('click', function() {
            document.getElementById('snoedit').value = this.dataset.sno;
            document.getElementById('dateedit').value = this.dataset.date;
            document.getElementById('customeridedit').value = this.dataset.customerid;
            document.getElementById('customernameedit').value = this.dataset.name; // ✅ ये add करो
            document.getElementById('productedit').value = this.dataset.product;
            document.getElementById('rateedit').value = this.dataset.rate;
            document.getElementById('qtyedit').value = this.dataset.qty;
            document.getElementById('amountedit').value = (this.dataset.rate * this.dataset.qty)
                .toFixed(2);
            var modal = new bootstrap.Modal(document.getElementById('editModal'));
            modal.show();
        });
    });
    // Auto calculate amount on qty or rate change
    const qtyInput = document.getElementById('qtyedit');
    const rateInput = document.getElementById('rateedit');

    function calculateAmount() {
        const qty = parseFloat(qtyInput.value) || 0;
        const rate = parseFloat(rateInput.value) || 0;
        document.getElementById('amountedit').value = (qty * rate).toFixed(2);
    }
    qtyInput.addEventListener('input', calculateAmount);
    rateInput.addEventListener('input', calculateAmount);
    </script>
</body>

</html>