<?php
include "db.php";

/* CHECK ID */
if (!isset($_GET['id'])) {
    header("Location: purchase_orders.php");
    exit;
}

$id = $_GET['id'];

/* GET SUPPLIERS */
$suppliers = mysqli_query($conn, "SELECT id, name FROM suppliers ORDER BY name ASC");

/* GET CURRENT PO DATA */
$query = mysqli_query($conn, "
    SELECT *
    FROM purchase_orders
    WHERE id = '$id'
");

$data = mysqli_fetch_assoc($query);

if (!$data) {
    header("Location: purchase_orders.php");
    exit;
}

/* UPDATE LOGIC */
if (isset($_POST['update'])) {
    $supplier_id = $_POST['supplier_id'];
    $order_date = $_POST['order_date'];
    $expected_arrival_date = !empty($_POST['expected_arrival_date']) ? $_POST['expected_arrival_date'] : NULL;
    $shipment_type = $_POST['shipment_type'];
    $reference_no = $_POST['reference_no'];
    $notes = $_POST['notes'];
    $budget_estimate = !empty($_POST['budget_estimate']) ? $_POST['budget_estimate'] : NULL;

    $expected_arrival_sql = $expected_arrival_date ? "'$expected_arrival_date'" : "NULL";
    $budget_estimate_sql = ($budget_estimate !== NULL && $budget_estimate !== '') ? "'$budget_estimate'" : "NULL";

    mysqli_query($conn, "
        UPDATE purchase_orders
        SET
            supplier_id = '$supplier_id',
            order_date = '$order_date',
            expected_arrival_date = $expected_arrival_sql,
            shipment_type = '$shipment_type',
            reference_no = '$reference_no',
            notes = '$notes',
            budget_estimate = $budget_estimate_sql
        WHERE id = '$id'
    ");

    header("Location: purchase_orders.php?updated=1");
    exit;
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Edit Purchase Order</title>

    <style>
        * {
            box-sizing: border-box;
        }

        body {
            margin:0;
            font-family:'Segoe UI';
            background: url('picture/Background.png') no-repeat center center fixed;
            background-size: cover;
            color:#4b3b2a;
        }

        body::before {
            content:"";
            position:fixed;
            inset:0;
            background:rgba(255,255,255,0.25);
            z-index:-1;
        }

        .topbar {
            display:flex;
            justify-content:space-between;
            align-items:center;
            padding:6px 30px;
            height:70px;
            background:rgba(200,183,158,0.9);
        }

        .brand img {
            width:80px;
        }

        .menu {
            display:flex;
            align-items:center;
            gap:30px;
        }

        .menu-item {
            display:flex;
            align-items:center;
            gap:6px;
            text-decoration:none;
            color:#4b3b2a;
            font-size:13px;
            padding:8px 10px;
            border-radius:10px;
            transition:all 0.25s ease;
        }

        .menu-item img {
            width:18px;
            transition:0.25s ease;
        }

        .menu-item:hover {
            background:rgba(75, 59, 42, 0.15);
            transform:translateY(-2px);
        }

        .menu-item:hover img {
            filter:brightness(0.6);
            transform:scale(1.1);
        }

        .menu-item:hover span {
            color:#2f2418;
            font-weight:500;
        }

        .profile img {
            width:28px;
        }

        .container {
            padding:30px 50px;
        }

        .page-header {
            display:flex;
            justify-content:space-between;
            align-items:center;
            margin-bottom:20px;
        }

        .page-header h2 {
            margin:0;
            font-size:22px;
        }

        .main-box {
            background:rgba(230,216,195,0.88);
            border-radius:24px;
            padding:28px;
        }

        .section-title {
            margin-bottom:18px;
            padding-bottom:12px;
            border-bottom:1px solid rgba(75,59,42,0.12);
        }

        .section-title h3 {
            margin:0;
            font-size:20px;
        }

        .section-title p {
            margin:6px 0 0;
            font-size:13px;
            color:#6f5b47;
        }

        .content-grid {
            display:grid;
            grid-template-columns:2fr 1fr;
            gap:24px;
        }

        .left-side,
        .right-side {
            display:flex;
            flex-direction:column;
            gap:20px;
        }

        .card {
            background:rgba(255,255,255,0.35);
            border-radius:18px;
            padding:22px;
        }

        .card h4 {
            margin:0 0 16px;
            font-size:18px;
        }

        .form-grid {
            display:grid;
            grid-template-columns:1fr 1fr;
            gap:16px 20px;
        }

        .form-group {
            display:flex;
            flex-direction:column;
        }

        .form-group.full {
            grid-column:1 / -1;
        }

        .form-group label {
            margin-bottom:6px;
            font-size:14px;
            font-weight:600;
        }

        .form-group input,
        .form-group select,
        .form-group textarea {
            width:100%;
            padding:10px 12px;
            border-radius:12px;
            border:1px solid rgba(75,59,42,0.15);
            background:rgba(255,255,255,0.55);
            font-family:'Segoe UI';
            color:#4b3b2a;
            font-size:14px;
            outline:none;
        }

        .form-group textarea {
            min-height:100px;
            resize:vertical;
        }

        .form-group input:focus,
        .form-group select:focus,
        .form-group textarea:focus {
            border-color:#9b7650;
            box-shadow:0 0 0 3px rgba(155,118,80,0.12);
        }

        .helper-text {
            margin-top:6px;
            font-size:12px;
            color:#75614d;
        }

        .status-list {
            display:flex;
            flex-direction:column;
            gap:12px;
        }

        .status-row {
            display:flex;
            justify-content:space-between;
            align-items:center;
            background:rgba(255,255,255,0.35);
            padding:12px 14px;
            border-radius:12px;
            font-size:14px;
        }

        .badge {
            padding:6px 10px;
            border-radius:999px;
            font-size:12px;
            font-weight:600;
        }

        .badge-open {
            background:#f8e7b8;
            color:#946200;
        }

        .badge-released {
            background:#dcecff;
            color:#2964a3;
        }

        .badge-closed {
            background:#d7f0dc;
            color:#2d7a3e;
        }

        .badge-cancelled {
            background:#f3d5d5;
            color:#a94442;
        }

        .badge-hold {
            background:#e5e5e5;
            color:#555;
        }

        .badge-ship {
            background:#e7e7e7;
            color:#555;
        }

        .badge-receive {
            background:#dcecff;
            color:#2964a3;
        }

        .badge-qc {
            background:#e9defd;
            color:#6744a3;
        }

        .actions {
            display:flex;
            justify-content:space-between;
            margin-top:24px;
        }

        .btn {
            padding:11px 18px;
            border:none;
            border-radius:18px;
            text-decoration:none;
            font-size:14px;
            cursor:pointer;
            transition:0.2s;
            display:inline-block;
        }

        .btn-save {
            background:#7a5a3a;
            color:white;
        }

        .btn-save:hover {
            background:#65472b;
        }

        .btn-cancel {
            background:#d8d0c5;
            color:#3f3125;
        }

        .btn-cancel:hover {
            background:#cfc4b6;
        }

        @media (max-width: 1000px) {
            .content-grid {
                grid-template-columns:1fr;
            }

            .form-grid {
                grid-template-columns:1fr;
            }
        }
    </style>
</head>

<body>

<div class="topbar">
    <div class="brand">
        <img src="picture/logo2.png">
    </div>

    <div class="menu">
        <a href="dashboard.php" class="menu-item">
            <img src="picture/dashboard.png">
            <span>Dashboard</span>
        </a>

        <a href="purchase_orders.php" class="menu-item">
            <img src="picture/clipboard.png">
            <span>Orders</span>
        </a>

        <a href="suppliers.php" class="menu-item">
            <img src="picture/supplier.png">
            <span>Suppliers</span>
        </a>

        <a href="inventory.php" class="menu-item">
            <img src="picture/box.png">
            <span>Inventory</span>
        </a>
    </div>

    <div class="profile">
        <img src="picture/profile.png">
    </div>
</div>

<div class="container">

    <div class="page-header">
        <h2>Edit Purchase Order</h2>
    </div>

    <form method="POST" class="main-box">

        <div class="section-title">
            <h3>Edit Purchase Order #<?= $data['id']; ?></h3>
            <p>Update purchase order header information. Process statuses are shown for reference and should be changed by workflow actions later.</p>
        </div>

        <div class="content-grid">

            <div class="left-side">

                <div class="card">
                    <h4>PO Header Information</h4>

                    <div class="form-grid">
                        <div class="form-group">
                            <label>Supplier *</label>
                            <select name="supplier_id" required>
                                <option value="">-- Choose Supplier --</option>
                                <?php while($supplier = mysqli_fetch_assoc($suppliers)) { ?>
                                    <option value="<?= $supplier['id']; ?>"
                                        <?= ($supplier['id'] == $data['supplier_id']) ? 'selected' : ''; ?>>
                                        <?= htmlspecialchars($supplier['name']); ?>
                                    </option>
                                <?php } ?>
                            </select>
                        </div>

                        <div class="form-group">
                            <label>Order Date *</label>
                            <input type="date" name="order_date"
                                value="<?= !empty($data['order_date']) ? date('Y-m-d', strtotime($data['order_date'])) : ''; ?>" required>
                        </div>

                        <div class="form-group">
                            <label>Expected Arrival Date</label>
                            <input type="date" name="expected_arrival_date"
                                value="<?= !empty($data['expected_arrival_date']) ? date('Y-m-d', strtotime($data['expected_arrival_date'])) : ''; ?>">
                        </div>

                        <div class="form-group">
                            <label>Shipment Type *</label>
                            <select name="shipment_type" required>
                                <option value="">-- Choose Shipment Type --</option>
                                <option value="Regular Delivery" <?= ($data['shipment_type'] == 'Regular Delivery') ? 'selected' : ''; ?>>Regular Delivery</option>
                                <option value="Express Delivery" <?= ($data['shipment_type'] == 'Express Delivery') ? 'selected' : ''; ?>>Express Delivery</option>
                                <option value="Pickup" <?= ($data['shipment_type'] == 'Pickup') ? 'selected' : ''; ?>>Pickup</option>
                                <option value="Cold Chain" <?= ($data['shipment_type'] == 'Cold Chain') ? 'selected' : ''; ?>>Cold Chain</option>
                            </select>
                        </div>

                        <div class="form-group full">
                            <label>Reference / Supplier PO No.</label>
                            <input type="text" name="reference_no"
                                value="<?= htmlspecialchars($data['reference_no'] ?? ''); ?>"
                                placeholder="Enter supplier reference number">
                        </div>

                        <div class="form-group full">
                            <label>Budget Estimate</label>
                            <input type="number" name="budget_estimate" step="0.01" min="0"
                                value="<?= htmlspecialchars($data['budget_estimate'] ?? ''); ?>"
                                placeholder="0.00">
                            <div class="helper-text">Optional early estimate before detailed item calculation is added.</div>
                        </div>

                        <div class="form-group full">
                            <label>Notes / Remarks</label>
                            <textarea name="notes" placeholder="Enter notes or special instructions..."><?= htmlspecialchars($data['notes'] ?? ''); ?></textarea>
                        </div>
                    </div>
                </div>

            </div>

            <div class="right-side">
                <div class="card">
                    <h4>Current Status</h4>

                    <div class="status-list">
                        <div class="status-row">
                            <span>PO Status</span>
                            <span class="badge badge-<?= htmlspecialchars($data['po_status']); ?>">
                                <?= ucfirst(str_replace('_', ' ', $data['po_status'])); ?>
                            </span>
                        </div>

                        <div class="status-row">
                            <span>Shipment Status</span>
                            <span class="badge badge-ship">
                                <?= ucfirst(str_replace('_', ' ', $data['shipment_status'])); ?>
                            </span>
                        </div>

                        <div class="status-row">
                            <span>Receiving Status</span>
                            <span class="badge badge-receive">
                                <?= ucfirst(str_replace('_', ' ', $data['receiving_status'])); ?>
                            </span>
                        </div>

                        <div class="status-row">
                            <span>QC Status</span>
                            <span class="badge badge-qc">
                                <?= ucfirst(str_replace('_', ' ', $data['qc_status'])); ?>
                            </span>
                        </div>
                    </div>
                </div>
            </div>

        </div>

        <div class="actions">
            <a href="view_po.php?id=<?= $data['id']; ?>" class="btn btn-cancel">Cancel</a>
            <button type="submit" name="update" class="btn btn-save">Update Purchase Order</button>
        </div>

    </form>

</div>

</body>
</html>