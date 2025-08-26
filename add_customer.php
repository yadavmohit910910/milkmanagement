<?php
session_start();
if (!isset($_SESSION['loggedin']) || $_SESSION['loggedin'] != true) {
    header("location: login.php");
    exit;
}
include 'db_connect.php';

$insert = false;
$errorMsg = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $date = $_POST['date'];
    $id = $_POST['id'];
    $name = $_POST['name'];
    $address = $_POST['customeraddress'];
    $phone = $_POST['phone'];
    $product = $_POST['product'];
    $rate = $_POST['rate'];
    $qty = $_POST['qty'];
    $amount = $_POST['amount'];

    // 🔴 Duplicate ID check
    $check_sql = "SELECT id FROM customers WHERE id = '$id'";
    $result = mysqli_query($conn, $check_sql);

    if (mysqli_num_rows($result) > 0) {
        $errorMsg = "❌ Customer ID already exists. Please change Name or Phone.";
    } else {
        // Insert new record

        $sql = "INSERT INTO customers (`date`, `id`, `name`, `address`, `phone`, `product`, `rate`, `qty`, `amount`) 
            VALUES ('$date', '$id', '$name', '$address', '$phone', '$product', '$rate', '$qty', '$amount')";

        if (mysqli_query($conn, $sql)) {
            $insert = true;
        } else {
            echo "Error: " . $sql . "<br>" . mysqli_error($conn);
        }
    }
}
?>
<!doctype html>
<html lang="en">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Add Customers</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="/milkapp/partials/style.css">
    <style>
    body {
        background: #f8f9fa;
    }

    .form-container {
        max-width: 1200px;
        /* ✅ और चौड़ा */
        background: #ffffff;
        padding: 40px;
        margin: 30px auto;
        border-radius: 20px;
        box-shadow: 0px 8px 20px rgba(0, 0, 0, 0.1);
    }


    h2 {
        font-weight: 600;
        color: #343a40;
        margin-bottom: 20px;
    }

    label {
        font-weight: 500;
    }

    .btn-custom {
        padding: 12px 25px;
        font-size: 18px;
        border-radius: 10px;
    }
    </style>
</head>

<body>
    <?php include "navbar.php"; ?>

    <div class="container">
        <?php if ($insert): ?>
        <div class='alert alert-success alert-dismissible fade show mt-3' role='alert'>
            <strong>✅ Success!</strong> Record for <b><?php echo $name; ?></b> added successfully.
            <button type='button' class='btn-close' data-bs-dismiss='alert'></button>
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
            <h2 class="text-center">➕ Add New Customer</h2>
            <form action="add_customer.php" method="POST" class="row g-4">

                <!-- Left Column -->
                <div class="col-md-2">
                    <label for="date" class="form-label">Date</label>
                    <input type="date" class="form-control" id="currentDateInput" name="date" required>
                </div>

                <div class="col-md-4">
                    <label for="id" class="form-label">Customer ID</label>
                    <input type="text" class="form-control" id="id" name="id" readonly>
                </div>

                <div class="col-md-4">
                    <label for="name" class="form-label">Customer Name</label>
                    <input type="text" class="form-control" name="name" id="name" required>
                </div>

                <div class="col-md-4">
                    <label for="phone" class="form-label">Phone</label>
                    <input type="text" class="form-control" name="phone" id="phone" required>
                </div>

                <div class="col-md-6">
                    <label for="customeraddress" class="form-label">Customer Address</label>
                    <input type="text" class="form-control" id="customeraddress" name="customeraddress" required>
                </div>

                <!-- Right Column -->
                <div class="col-md-4">
                    <label for="product" class="form-label">Product</label>
                    <select id="product" class="form-select" name="product" required>
                        <option value="">Choose...</option>
                        <option>Cow Milk</option>
                        <option>Ghee</option>
                        <option>Butter</option>
                    </select>
                </div>

                <div class="col-md-2">
                    <label for="rate" class="form-label">Rate</label>
                    <input type="number" class="form-control" name="rate" id="rate" required>
                </div>

                <div class="col-md-2">
                    <label for="qty" class="form-label">Quantity</label>
                    <input type="number" class="form-control" name="qty" id="qty" required>
                </div>

                <div class="col-md-4">
                    <label for="amount" class="form-label">Amount</label>
                    <input type="number" class="form-control" id="amount" step="0.01" name="amount" readonly>
                </div>

                <div class="col-12 text-center mt-4">
                    <button type="submit" class="btn btn-success btn-lg">✅ Add Customer</button>
                    <a href="/milkmanagement/index.php" class="btn btn-secondary btn-lg">🏠 Go Back Home</a>
                </div>
            </form>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://code.jquery.com/jquery-3.6.4.min.js"></script>

    <script>
    // Date set
    document.getElementById('currentDateInput').valueAsDate = new Date();

    // Auto calculate amount
    $(document).ready(function() {
        $('#rate, #qty').on('input', function() {
            let rate = parseFloat($('#rate').val()) || 0;
            let qty = parseFloat($('#qty').val()) || 0;
            let amount = rate * qty;
            $('#amount').val(amount.toFixed(2));
        });

        // Auto generate Customer ID
        $('#name, #phone').on('input', function() {
            let customername = $('#name').val().substring(0, 3).toUpperCase();
            let mobileno = $('#phone').val().slice(-4);
            $('#id').val(customername + mobileno);
        });
    });
    </script>
</body>

</html>