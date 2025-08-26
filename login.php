<?php
$login = false;
$showerror = false;
if ($_SERVER['REQUEST_METHOD'] == "POST") {
    // Hardcoded admin credentials
    $admin_username = "admin"; // Define admin username
    $admin_password = "password123"; // Define admin password (use a strong password)

    // Get input from the form
    $username = $_POST['username'];
    $password = $_POST['password'];

    // Check if the provided username and password match the admin credentials
    if ($username === $admin_username && $password === $admin_password) {
        // Admin successfully logged in
        $login = true;
        session_start();
        $_SESSION['loggedin'] = true;
        $_SESSION['username'] = $username;
        header("location: index.php"); // Redirect to homepage or admin dashboard
        exit;
    } else {
        // If credentials are incorrect, show an error message
        $showerror = "Invalid admin credentials!";
    }
}
?>

<!doctype html>
<html lang="en">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Admin Login</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.1/dist/css/bootstrap.min.css" rel="stylesheet">
</head>

<body>
    <?php require "navbar.php"; ?>

    <!-- Success message -->
    <?php if ($login): ?>
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <strong>Success!</strong> You are logged in as admin.
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    <?php endif; ?>

    <!-- Error message -->
    <?php if ($showerror): ?>
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <strong>Error!</strong> <?= $showerror ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    <?php endif; ?>

    <!-- Login Form -->
    <div class="container">
        <h1>Admin Login</h1>
        <form action="login.php" method="POST">
            <div class="mb-3 col-md-6">
                <label for="username" class="form-label">Username</label>
                <input type="text" class="form-control" id="username" name="username" autocomplete="off" required>
            </div>
            <div class="mb-3 col-md-6">
                <label for="password" class="form-label">Password</label>
                <input type="password" class="form-control" id="password" name="password" required>
            </div>
            <button type="submit" class="btn btn-primary">Login</button>
        </form>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.1/dist/js/bootstrap.bundle.min.js"></script>
</body>

</html>