<?php
session_start();
// yaha per agar koe loggin nahi hai tho uske liye hai ye code 
if (!isset($_SESSION['loggedin']) || $_SESSION['loggedin'] != true) {
    header("location: login.php");
    exit;
}
?>
<!doctype html>
<html lang="en">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Milk Management</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet"
        integrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH" crossorigin="anonymous">
    <link rel="stylesheet" href="/milkapp/partials/style.css">
    <link rel="stylesheet" href="//cdn.datatables.net/1.13.4/css/jquery.dataTables.min.css">
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <style>
    .container {
        margin-top: 20px;
        text-align: center;
        max-width: 1200px;
        /* ✅ container size increase */
    }

    .menu-card {
        padding: 30px;
        border-radius: 15px;
        box-shadow: 0px 4px 12px rgba(0, 0, 0, 0.2);
        transition: transform 0.2s ease-in-out;
        font-size: 20px;
        font-weight: bold;
    }

    .menu-card:hover {
        transform: scale(1.05);
    }

    .menu-link {
        text-decoration: none;
        color: white;
    }
    </style>
</head>

<body>
    <?php include "navbar.php"; ?>
    <div class="container container-fluid">
        <h2 class="mb-4">Welcome to the Milk Management System</h2>
        <div class="row g-4">
            <div class="col-md-4">
                <a href="add_customer.php" class="menu-link">
                    <div class="menu-card bg-primary text-white">➕ Add Customer</div>
                </a>
            </div>
            <div class="col-md-4">
                <a href="daily_sale.php" class="menu-link">
                    <div class="menu-card bg-warning text-dark">📅 Add Daily Sale</div>
                </a>
            </div>
            <div class="col-md-4">
                <a href="view_customers.php" class="menu-link">
                    <div class="menu-card bg-success">👥 View Total Customers</div>
                </a>
            </div>
            <div class="col-md-4">
                <a href="view_sales.php" class="menu-link">
                    <div class="menu-card bg-success">📊 View Total Sales</div>
                </a>
            </div>
            <div class="col-md-4">
                <a href="view_payments.php" class="menu-link">
                    <div class="menu-card bg-success">💰 View Payments Details</div>
                </a>
            </div>
            <div class="col-md-4">
                <a href="billing.php" class="menu-link">
                    <div class="menu-card bg-info">🧾 Generate Billing</div>
                </a>
            </div>
            <div class="col-md-4">
                <a href="update_payment.php" class="menu-link">
                    <div class="menu-card bg-danger">💵 Update Payment</div>
                </a>
            </div>
            <div class="col-md-4">
                <a href="milk_bill.php" class="menu-link">
                    <div class="menu-card bg-danger">📝 Milk Bill</div>
                </a>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"
        integrity="sha384-YvpcrYf0tY3lHB60NNkmXc5s9fDVZLESaAA55NDzOxhy9GkcIdslK1eN7N6jIeHz" crossorigin="anonymous">
    </script>
    <script src="https://code.jquery.com/jquery-3.6.4.min.js"
        integrity="sha256-oP6HI9z1XaZNBrJURtCoUT5SUnxFr8s3BzRl+cbzUq8=" crossorigin="anonymous"></script>
    <script src="//cdn.datatables.net/1.13.4/js/jquery.dataTables.min.js"></script>
</body>

</html>