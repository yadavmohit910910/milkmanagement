<?php
session_start();
if (!isset($_SESSION['loggedin']) || $_SESSION['loggedin'] != true) {
    header("location: login.php");
    exit;
}
include 'db_connect.php';

// Customers list (from sales)
$customers = $conn->query("SELECT DISTINCT id, name FROM sales ORDER BY name ASC");

// Preselect current month/year
$currMonth = date('n');
$currYear = date('Y');
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>Update Payment</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/dataTables.bootstrap5.min.css">
    <style>
    body {
        background: #f5f7fb
    }

    .card {
        border: none;
        border-radius: 18px;
        box-shadow: 0 10px 22px rgba(0, 0, 0, .06)
    }

    .card-header {
        border-top-left-radius: 18px;
        border-top-right-radius: 18px
    }

    .stat {
        border-radius: 16px;
        background: #fff;
        box-shadow: 0 6px 16px rgba(20, 20, 20, .08)
    }

    .stat h6 {
        font-weight: 700;
        margin: 0;
        color: #777
    }

    .stat .val {
        font-size: 22px;
        font-weight: 800;
        color: #111
    }

    .badge-paid {
        background: #16a34a
    }

    .badge-unpaid {
        background: #ef4444
    }

    .form-select,
    .form-control {
        border-radius: 10px
    }
    </style>
</head>
<div class="col-2 text-center mt-2">
    <a href="/milkmanagement/index.php" class="btn btn-secondary btn-lg">🏠 Go Back Home</a>
</div>

<body class="bg-light">
    <div class="container py-5">
        <div class="row">
            <div class="col-xxl-10 col-xl-11 col-lg-12 mx-auto">

                <!-- TOP: Filters + Customer -->
                <div class="card mb-4">
                    <div class="card-header bg-primary text-white text-center">
                        <h4 class="mb-0">💰 Update Customer Payment</h4>
                    </div>
                    <div class="card-body">
                        <form id="filterForm" class="row g-3">
                            <div class="col-md-4">
                                <label class="form-label fw-bold">Select Customer</label>
                                <select id="customer_id" name="customer_id" class="form-select" required>
                                    <option value="">-- Choose Customer --</option>
                                    <?php while ($row = $customers->fetch_assoc()) { ?>
                                    <option value="<?= htmlspecialchars($row['id']) ?>">
                                        <?= htmlspecialchars($row['name']) ?> (<?= htmlspecialchars($row['id']) ?>)
                                    </option>
                                    <?php } ?>
                                </select>
                            </div>

                            <div class="col-md-2">
                                <label class="form-label fw-bold">Month</label>
                                <select id="month" name="month" class="form-select" required>
                                    <?php for ($m = 1; $m <= 12; $m++): ?>
                                    <option value="<?= $m ?>" <?= $m == $currMonth ? 'selected' : '' ?>>
                                        <?= date('M', mktime(0, 0, 0, $m, 1)) ?>
                                    </option>
                                    <?php endfor; ?>
                                </select>
                            </div>

                            <div class="col-md-2">
                                <label class="form-label fw-bold">Year</label>
                                <select id="year" name="year" class="form-select" required>
                                    <?php for ($y = 2020; $y <= date('Y'); $y++): ?>
                                    <option value="<?= $y ?>" <?= $y == $currYear ? 'selected' : '' ?>><?= $y ?>
                                    </option>
                                    <?php endfor; ?>
                                </select>
                            </div>

                            <div class="col-md-4 d-flex align-items-end gap-2">
                                <button type="button" class="btn btn-primary w-50" id="btnLoad">📥 Load</button>
                                <button type="button" class="btn btn-dark w-50" id="btnStatus">📊 Monthly
                                    Status</button>
                            </div>
                        </form>
                    </div>
                </div>

                <!-- KPIs -->
                <div class="row g-3 mb-4" id="kpis" style="display:none;">
                    <div class="col-md-3">
                        <div class="p-3 stat">
                            <h6>Previous Bill</h6>
                            <div class="val" id="k_prev">0</div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="p-3 stat">
                            <h6>Current Bill</h6>
                            <div class="val" id="k_curr">0</div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="p-3 stat">
                            <h6>Total Payments (till month)</h6>
                            <div class="val" id="k_pay">0</div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="p-3 stat">
                            <h6>Balance / Advance</h6>
                            <div class="val"><span id="k_bal">0</span> / <span id="k_adv">0</span></div>
                        </div>
                    </div>
                </div>

                <!-- Payment Form -->
                <div class="card mb-4">
                    <div class="card-header bg-success text-white">
                        <strong>➕ Add Payment</strong>
                    </div>
                    <div class="card-body">
                        <form id="paymentForm" class="row g-3">
                            <div class="col-md-3">
                                <label class="form-label fw-bold">Payment Date</label>
                                <input type="date" id="payment_date" name="payment_date" class="form-control" required
                                    value="<?= date('Y-m-d') ?>">
                            </div>
                            <div class="col-md-3">
                                <label class="form-label fw-bold">Amount Paid</label>
                                <input type="number" step="0.01" id="amount_paid" name="amount_paid"
                                    class="form-control" required>
                            </div>
                            <div class="col-md-3">
                                <label class="form-label fw-bold">Method</label>
                                <select name="payment_method" id="payment_method" class="form-select" required>
                                    <option value="">-- Select --</option>
                                    <option value="Cash">Cash</option>
                                    <option value="Online">Online</option>
                                </select>
                            </div>
                            <div class="col-md-3 d-flex align-items-end">
                                <button type="submit" class="btn btn-success w-100">💾 Save Payment</button>
                            </div>
                            <input type="hidden" name="customer_id" id="pf_customer_id">
                        </form>
                        <div id="saveMsg" class="mt-3"></div>
                    </div>
                </div>

                <!-- Payment History -->
                <div class="card">
                    <div class="card-header bg-dark text-white">
                        <strong>📜 Payment History</strong>
                    </div>
                    <div class="card-body">
                        <div id="paymentHistory" class="table-responsive"></div>
                    </div>
                </div>

                <!-- Monthly Status Modal -->
                <div class="modal fade" id="statusModal" tabindex="-1" aria-hidden="true">
                    <div class="modal-dialog modal-xl modal-dialog-scrollable">
                        <div class="modal-content">
                            <div class="modal-header">
                                <h5 class="modal-title">📊 Monthly Status</h5>
                                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                            </div>
                            <div class="modal-body">
                                <div id="statusWrap" class="table-responsive"></div>
                            </div>
                        </div>
                    </div>
                </div>

            </div>
        </div>
    </div>

    <!-- JS -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.6/js/dataTables.bootstrap5.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

    <script>
    function loadCustomerData() {
        const customer_id = $('#customer_id').val();
        const month = $('#month').val();
        const year = $('#year').val();
        if (!customer_id) return;

        $('#pf_customer_id').val(customer_id);

        $.ajax({
            url: 'fetch_customer_bill.php',
            type: 'POST',
            dataType: 'json',
            data: {
                customer_id,
                month,
                year
            },
            success: function(res) {
                $('#kpis').show();
                $('#k_prev').text(res.previous_bill);
                $('#k_curr').text(res.current_month_bill);
                $('#k_pay').text(res.total_payments_till_month);
                $('#k_bal').text(res.balance);
                $('#k_adv').text(res.advance);

                $('#paymentHistory').html(res.payment_history);

                // init DataTable safely
                if ($.fn.DataTable.isDataTable('#paymentTable')) {
                    $('#paymentTable').DataTable().destroy();
                }
                $('#paymentTable').DataTable();
            },
            error: function(xhr) {
                console.error(xhr.responseText);
            }
        });
    }

    $(function() {
        $('#btnLoad').on('click', loadCustomerData);
        $('#customer_id').on('change', loadCustomerData);
        $('#month,#year').on('change', loadCustomerData);

        // Save Payment
        $('#paymentForm').on('submit', function(e) {
            e.preventDefault();
            $.ajax({
                url: 'save_payment.php',
                type: 'POST',
                dataType: 'json',
                data: $(this).serialize(),
                success: function(res) {
                    const cls = res.status === 'success' ? 'alert-success' : 'alert-danger';
                    $('#saveMsg').html('<div class="alert ' + cls + '">' + res.message +
                        '</div>');
                    if (res.status === 'success') {
                        // reset amount/method, keep date in same month
                        $('#amount_paid').val('');
                        $('#payment_method').val('');
                        loadCustomerData();
                    }
                },
                error: function(xhr) {
                    $('#saveMsg').html(
                        '<div class="alert alert-danger">Server error</div>');
                    console.error(xhr.responseText);
                }
            });
        });

        // Monthly Status: modal
        $('#btnStatus').on('click', function() {
            const month = $('#month').val();
            const year = $('#year').val();
            $.ajax({
                url: 'fetch_monthly_status.php',
                type: 'POST',
                data: {
                    month,
                    year
                },
                success: function(html) {
                    $('#statusWrap').html(html);

                    // init table
                    if ($.fn.DataTable.isDataTable('#statusTable')) {
                        $('#statusTable').DataTable().destroy();
                    }
                    $('#statusTable').DataTable();

                    const modal = new bootstrap.Modal(document.getElementById(
                        'statusModal'));
                    modal.show();
                },
                error: function(xhr) {
                    console.error(xhr.responseText);
                }
            });
        });
    });
    </script>
</body>

</html>