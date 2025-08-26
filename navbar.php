<?php
if (isset($_SESSION['loggedin']) && $_SESSION['loggedin'] == true) {
    $loggedin = true;
} else {
    $loggedin = false;
}
?>

<nav class="navbar navbar-expand-lg navbar-dark custom-navbar">
    <div class="container-fluid">
        <a class="navbar-brand fw-bold fs-4" href="index.php">🥛 Milk Management</a>
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarSupportedContent"
            aria-controls="navbarSupportedContent" aria-expanded="false" aria-label="Toggle navigation">
            <span class="navbar-toggler-icon"></span>
        </button>
        <div class="collapse navbar-collapse" id="navbarSupportedContent">
            <ul class="navbar-nav ms-auto mb-2 mb-lg-0">
                <li class="nav-item">
                    <a class="nav-link active" aria-current="page" href="index.php">Home</a>
                </li>
                <?php if (!$loggedin) { ?>
                <li class="nav-item">
                    <a class="nav-link" href="login.php">Login</a>
                </li>
                <?php } ?>

                <?php if ($loggedin) { ?>
                <li class="nav-item dropdown">
                    <a class="nav-link dropdown-toggle" href="#" id="navbarDropdown" role="button"
                        data-bs-toggle="dropdown" aria-expanded="false">
                        Manage
                    </a>
                    <ul class="dropdown-menu shadow-lg border-0 rounded-3" aria-labelledby="navbarDropdown">
                        <li><a class="dropdown-item" href="add_customer.php">➕ Add Customers</a></li>
                        <li><a class="dropdown-item" href="daily_sale.php">🥛 Daily Milk Update</a></li>
                        <li>
                            <hr class="dropdown-divider">
                        </li>
                        <li><a class="dropdown-item" href="billing.php">🧾 Bill Generate</a></li>
                        <li><a class="dropdown-item" href="update_payment.php">💰 Payment Update</a></li>
                        <li><a class="dropdown-item" href="view_customers.php">👥 View Customers</a></li>
                        <li><a class="dropdown-item" href="view_sales.php">📊 View Sales</a></li>
                    </ul>
                </li>
                <li class="nav-item"><a class="nav-link" href="contactus.php">📩 Contact Us</a></li>
                <li class="nav-item"><a class="nav-link" href="monthlysellrecord.php">📅 Month Wise Data</a></li>
                <li class="nav-item"><a class="nav-link text-danger fw-bold" href="logout.php">🚪 Logout</a></li>
                <?php } ?>
            </ul>
        </div>
    </div>
</nav>

<!-- Custom CSS -->
<style>
/* Navbar Background (Glass Effect) */
.custom-navbar {
    background: rgba(0, 123, 255, 0.85);
    /* Blue with transparency */
    backdrop-filter: blur(10px);
    box-shadow: 0 4px 20px rgba(0, 0, 0, 0.2);
    border-bottom: 2px solid rgba(255, 255, 255, 0.2);
}

/* Brand Logo */
.navbar-brand {
    color: #fff !important;
    letter-spacing: 1px;
    transition: transform 0.2s ease-in-out;
}

.navbar-brand:hover {
    transform: scale(1.05);
    color: #ffd700 !important;
}

/* Nav Links */
.nav-link {
    color: #f1f1f1 !important;
    margin: 0 8px;
    font-weight: 500;
    position: relative;
    transition: color 0.3s ease-in-out;
}

.nav-link:hover {
    color: #ffd700 !important;
    /* Golden hover */
}

/* Active link underline */
.nav-link.active::after {
    content: "";
    position: absolute;
    bottom: 0;
    left: 0;
    height: 2px;
    width: 100%;
    background: #ffd700;
    border-radius: 5px;
}

/* Dropdown */
.dropdown-menu {
    background: rgba(255, 255, 255, 0.95);
    animation: fadeIn 0.3s ease-in-out;
}

.dropdown-item:hover {
    background: #007bff;
    color: #fff;
    border-radius: 6px;
}

/* Small animation */
@keyframes fadeIn {
    from {
        opacity: 0;
        transform: translateY(5px);
    }

    to {
        opacity: 1;
        transform: translateY(0);
    }
}
</style>