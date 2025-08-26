<?php
session_start();
// Agar login nahi hai toh redirect karega
if (!isset($_SESSION['loggedin']) || $_SESSION['loggedin'] != true) {
    header("location: login.php");
    exit;
}

include 'db_connect.php';
$insert = false;
$errorMsg = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $date = trim($_POST["date"]);
    $customerid = trim($_POST["customerid"]);
    $customername = trim($_POST["customername"]);
    $rate = trim($_POST["rate"]);
    $quantity = trim($_POST["qty"]);
    $product = trim($_POST["product"]);
    $amount = trim($_POST["amount"]);

    // Duplicate check
    $check_sql = "SELECT sno FROM sales WHERE date='$date' AND id='$customerid' AND product='$product'";
    $check_result = mysqli_query($conn, $check_sql);

    if (mysqli_num_rows($check_result) > 0) {
        $errorMsg = "⚠️ This sale already exists for this customer and product on $date.";
    } else {
        // Insert sale into the sales table
        $sql = "INSERT INTO sales (`date`, `id`, `name`, `qty`, `rate`, `product`, `amount`) 
                VALUES ('$date', '$customerid', '$customername', '$quantity', '$rate', '$product', '$amount')";

        if (mysqli_query($conn, $sql)) {
            $insert = true;
        } else {
            $errorMsg = "Error: " . mysqli_error($conn);
        }
    }
}
?>

<!doctype html>
<html lang="en">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Daily Sales</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="//cdn.datatables.net/1.13.4/css/jquery.dataTables.min.css">

    <style>
    body {
        background: #f8f9fa;
    }

    .form-container {
        width: 100%;
        max-width: 1200px;
        /* ✅ form ka max width */
        background: #ffffff;
        padding: 40px;
        margin: 40px auto;
        border-radius: 20px;
        box-shadow: 0px 8px 20px rgba(0, 0, 0, 0.1);
    }

    .form-container h2 {
        font-weight: bold;
        margin-bottom: 30px;
        color: #333;
    }

    .form-label {
        font-weight: 600;
    }

    .btn-lg {
        font-size: 18px;
        padding: 12px 25px;
        border-radius: 10px;
    }
    </style>
</head>

<body>
    <?php include "navbar.php"; ?>

    <div class="container-fluid">
        <?php if ($insert): ?>
        <div class="alert alert-success alert-dismissible fade show mt-3" role="alert">
            ✅ <strong>Success!</strong> Sale record for <b><?php echo $customername; ?></b> added successfully.
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
        <?php elseif (!empty($errorMsg)): ?>
        <div class="alert alert-danger alert-dismissible fade show mt-3" role="alert">
            <?php echo $errorMsg; ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
        <script>
        setTimeout(function() {
            var alertElement = document.querySelector('.alert');
            if (alertElement) {
                var alert = new bootstrap.Alert(alertElement);
                alert.close();
            }
        }, 1500);
        </script>
        <?php endif; ?>

        <div class="form-container">
            <h2 class="text-center">📝 Enter Daily Milk Sale</h2>
            <form class="row g-4" id="myform" action="daily_sale.php" method="POST">

                <!-- Date -->
                <div class="col-md-4">
                    <label for="date" class="form-label">Date:</label>
                    <input type="date" class="form-control form-control-lg" name="date" id="currentDateInput" required>
                </div>

                <!-- Customer ID -->
                <div class="col-md-4">
                    <label for="customerid" class="form-label">Customer ID:</label>
                    <input type="text" class="form-control form-control-lg" name="customerid" id="customerid" readonly>
                </div>

                <!-- Customer Name -->
                <div class="col-md-4">
                    <label for="customername" class="form-label">Customer Name:</label>
                    <select class="form-control form-control-lg" name="customername" id="customer_select" required>
                        <option value="">Select Customer</option>
                        <?php
                        // Sirf active customers dikhayenge
                        $query = "SELECT `id`, `name`, `rate`, `qty` FROM `customers` WHERE `status` = 'active'";
                        $result = mysqli_query($conn, $query);

                        while ($row = mysqli_fetch_assoc($result)) {
                            $customerid = $row['id'];
                            $customerName = $row['name'];
                            $rate = $row['rate'];
                            $quantity = $row['qty'];
                            echo "<option value='$customerName' 
                                        data-customerid='$customerid' 
                                        data-rate='$rate' 
                                        data-qty='$quantity'>$customerName</option>";
                        }
                        ?>
                    </select>
                </div>

                <!-- Rate -->
                <div class="col-md-2">
                    <label for="rate" class="form-label">Rate per Liter:</label>
                    <input type="number" step="0.1" class="form-control form-control-lg" name="rate" id="rate" required>
                </div>

                <!-- Quantity -->
                <div class="col-md-2">
                    <label for="qty" class="form-label">Quantity (Liters):</label>
                    <input type="number" step="0.1" class="form-control form-control-lg" name="qty" id="qty" required>
                </div>

                <!-- Product -->
                <div class="col-md-4">
                    <label for="product" class="form-label">Choose Product:</label>
                    <select id="product" class="form-control form-control-lg" name="product" required>
                        <option value="Cow_Milk">Cow Milk</option>
                        <option value="Ghee">Ghee</option>
                        <option value="Butter">Butter</option>
                    </select>
                </div>

                <!-- Amount -->
                <div class="col-md-4">
                    <label for="amount" class="form-label">Amount (₹)</label>
                    <input type="number" class="form-control form-control-lg" id="amount" step="0.01" name="amount"
                        readonly>
                </div>

                <!-- Buttons -->
                <div class="col-md-12 text-center">
                    <button type="submit" class="btn btn-primary btn-lg">➕ Add Sale</button>
                    <a class="btn btn-secondary btn-lg ms-2" href="/milkmanagement/index.php">🏠 Go Home</a>
                </div>
            </form>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://code.jquery.com/jquery-3.6.4.min.js"></script>
    <script src="//cdn.datatables.net/1.13.4/js/jquery.dataTables.min.js"></script>

    <script>
    // Customer select ke sath auto-fill
    const customerSelect = document.getElementById("customer_select");
    const customeridInput = document.getElementById("customerid");
    const rateInput = document.getElementById("rate");
    const quantityInput = document.getElementById("qty");
    const amountInput = document.getElementById("amount");

    customerSelect.addEventListener("change", function() {
        const selectedOption = customerSelect.options[customerSelect.selectedIndex];
        const customerid = selectedOption.getAttribute("data-customerid");
        const rate = selectedOption.getAttribute("data-rate");
        const qty = selectedOption.getAttribute("data-qty");

        customeridInput.value = customerid;
        rateInput.value = rate;
        quantityInput.value = qty;
        amountInput.value = rate * qty;
    });

    // Rate ya qty change hone par amount update
    $(document).ready(function() {
        $('#rate,#qty').on('keyup change', function() {
            var rate = $('#rate').val();
            var qty = $('#qty').val();
            $('#amount').val(rate * qty);
        });
    });
    </script>
</body>

</html>