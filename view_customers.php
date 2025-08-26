<?php
session_start();
if (!isset($_SESSION['loggedin']) || $_SESSION['loggedin'] != true) {
    header("location: login.php");
    exit;
}

include 'db_connect.php';

// Toggle Status
if (isset($_GET['toggle'])) {
    $sno = $_GET['toggle'];
    $statusQuery = "SELECT status FROM customers WHERE sno = '$sno'";
    $statusResult = mysqli_query($conn, $statusQuery);
    $statusRow = mysqli_fetch_assoc($statusResult);
    $currentStatus = $statusRow['status'];
    $newStatus = ($currentStatus == 'active') ? 'inactive' : 'active';
    $sql = "UPDATE customers SET status = '$newStatus' WHERE sno = '$sno'";
    mysqli_query($conn, $sql);
    $update = true;
}

// Delete
if (isset($_GET['delete'])) {
    $sno = $_GET['delete'];
    $sql = "DELETE FROM `customers` WHERE `sno` = $sno";
    mysqli_query($conn, $sql);
    $delete = true;
}

// Update
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['snoedit'])) {
    $sno = $_POST['snoedit'];
    $date = $_POST['dateedit'];
    $customerid = $_POST['customeridedit'];
    $customername = $_POST['customernameedit'];
    $address = $_POST['customeraddressedit'];
    $contactnumber = $_POST['phoneedit'];
    $product = $_POST['productedit'];
    $rate = $_POST['rateedit'];
    $quantity = $_POST['qtyedit'];
    $amount = $_POST['amountedit'];

    $sql = "UPDATE `customers` SET `date`='$date', `id`='$customerid', `name`='$customername',
            `address`='$address', `phone`='$contactnumber', `product`='$product',
            `rate`='$rate', `qty`='$quantity', `amount`='$amount' 
            WHERE `sno`='$sno'";
    mysqli_query($conn, $sql);
    $update = true;
}
?>

<!doctype html>
<html lang="en">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>View Customers</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="//cdn.datatables.net/1.13.4/css/jquery.dataTables.min.css">
    <style>
    body {
        background: #f8f9fa;
    }

    .table-container {
        background: #fff;
        padding: 20px;
        border-radius: 20px;
        box-shadow: 0 6px 20px rgba(0, 0, 0, 0.1);
    }

    table th,
    table td {
        text-align: center;
        vertical-align: middle;
    }

    .modal-lg {
        max-width: 80%;
    }
    </style>
</head>

<body>
    <?php include "navbar.php"; ?>

    <div class="container mt-4">
        <?php if (!empty($delete)): ?>
        <div class="alert alert-success text-center">✅ Record deleted successfully!</div>
        <?php endif; ?>

        <?php if (!empty($update)): ?>
        <div class="alert alert-success text-center">✅ Record updated successfully!</div>
        <?php endif; ?>

        <div class="table-container">
            <h3 class="mb-3">Customer List</h3>
            <div class="table-responsive">
                <table class="table table-bordered table-hover" id="myTable">
                    <thead class="table-dark">
                        <tr>
                            <th>S.No</th>
                            <th>Date</th>
                            <th>Customer ID</th>
                            <th>Name</th>
                            <th>Address</th>
                            <th>Phone</th>
                            <th>Product</th>
                            <th>Rate</th>
                            <th>Qty</th>
                            <th>Amount</th>
                            <th>Status</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        $result = mysqli_query($conn, "SELECT * FROM customers");
                        $sno = 0;
                        while ($row = mysqli_fetch_assoc($result)) {
                            $sno++;
                            echo "<tr>
                                <td>{$sno}</td>
                                <td>{$row['date']}</td>
                                <td>{$row['id']}</td>
                                <td>{$row['name']}</td>
                                <td>{$row['address']}</td>
                                <td>{$row['phone']}</td>
                                <td>{$row['product']}</td>
                                <td>{$row['rate']}</td>
                                <td>{$row['qty']}</td>
                                <td>{$row['amount']}</td>
                                <td><span class='badge " . ($row['status'] == 'active' ? 'bg-success' : 'bg-secondary') . "'>{$row['status']}</span></td>
                                <td>
                                    <div class='btn-group'>
                                        <button class='toggle btn btn-sm btn-warning' id='t{$row['sno']}'>" . ($row['status'] == 'active' ? 'Deactivate' : 'Activate') . "</button>
                                        <button data-bs-target='#editModal' data-bs-toggle='modal' class='edit btn btn-sm btn-primary' id='{$row['sno']}'>Edit</button>
                                        <button class='delete btn btn-sm btn-danger' id='d{$row['sno']}'>Delete</button>
                                    </div>
                                </td>
                            </tr>";
                        }
                        ?>
                    </tbody>
                </table>
            </div>
        </div>
        <div class="col-12 text-center mt-4">
            <a href="/milkmanagement/index.php" class="btn btn-secondary btn-lg">🏠 Go Back Home</a>
        </div>
    </div>

    <!-- Edit Modal -->
    <div class="modal fade" id="editModal" tabindex="-1">
        <div class="modal-dialog modal-lg">
            <div class="modal-content rounded-4 shadow-lg">
                <div class="modal-header bg-dark text-white">
                    <h5 class="modal-title">Edit Customer</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <form class="row g-3 p-3" action="view_customers.php" method="post">
                    <input type="hidden" name="snoedit" id="snoedit">
                    <div class="col-md-3">
                        <label class="form-label">Date</label>
                        <input type="date" class="form-control" name="dateedit" id="dateedit">
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">Customer ID</label>
                        <input type="text" class="form-control" name="customeridedit" id="customeridedit">
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">Name</label>
                        <input type="text" class="form-control" name="customernameedit" id="customernameedit">
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">Phone</label>
                        <input type="number" class="form-control" name="phoneedit" id="phoneedit">
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Address</label>
                        <input type="text" class="form-control" name="customeraddressedit" id="customeraddressedit">
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">Product</label>
                        <select id="productedit" class="form-select" name="productedit">
                            <option>Cow Milk</option>
                            <option>Ghee</option>
                            <option>Butter</option>
                        </select>
                    </div>
                    <div class="col-md-2">
                        <label class="form-label">Rate</label>
                        <input type="number" class="form-control" name="rateedit" id="rateedit">
                    </div>
                    <div class="col-md-2">
                        <label class="form-label">Qty</label>
                        <input type="number" class="form-control" step="0.01" id="qtyedit" name="qtyedit">
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">Amount</label>
                        <input type="number" class="form-control" id="amountedit" step="0.01" name="amountedit"
                            readonly>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                        <button type="submit" class="btn btn-success">Save Changes</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Scripts -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://code.jquery.com/jquery-3.6.4.min.js"></script>
    <script src="//cdn.datatables.net/1.13.4/js/jquery.dataTables.min.js"></script>
    <script>
    $(document).ready(function() {
        $('#myTable').DataTable({
            "scrollX": true
        });
    });

    // Toggle status
    document.querySelectorAll('.toggle').forEach(btn => {
        btn.addEventListener("click", e => {
            let sno = e.target.id.substr(1);
            if (confirm("Change this customer's status?")) {
                window.location = `view_customers.php?toggle=${sno}`;
            }
        });
    });

    // Edit button
    document.querySelectorAll('.edit').forEach(btn => {
        btn.addEventListener("click", e => {
            let tr = e.target.closest('tr');
            document.getElementById("dateedit").value = tr.children[1].innerText;
            document.getElementById("customeridedit").value = tr.children[2].innerText;
            document.getElementById("customernameedit").value = tr.children[3].innerText;
            document.getElementById("customeraddressedit").value = tr.children[4].innerText;
            document.getElementById("phoneedit").value = tr.children[5].innerText;
            document.getElementById("productedit").value = tr.children[6].innerText;
            document.getElementById("rateedit").value = tr.children[7].innerText;
            document.getElementById("qtyedit").value = tr.children[8].innerText;
            document.getElementById("amountedit").value = tr.children[9].innerText;
            document.getElementById("snoedit").value = e.target.id;
        });
    });

    // Delete
    document.querySelectorAll('.delete').forEach(btn => {
        btn.addEventListener("click", e => {
            let sno = e.target.id.substr(1);
            if (confirm("Delete this record?")) {
                window.location = `view_customers.php?delete=${sno}`;
            }
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