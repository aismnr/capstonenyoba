<?php
include "db.php";

/* CHECK ID */
if (!isset($_GET['id'])) {
    header("Location: purchase_orders.php");
    exit;
}

$id = $_GET['id'];

/* GET PO DATA */
$query = mysqli_query($conn, "
    SELECT 
        po.*,
        s.name AS supplier_name,
        u.name AS user_name
    FROM purchase_orders po
    LEFT JOIN suppliers s ON po.supplier_id = s.id
    LEFT JOIN users u ON po.user_id = u.id
    WHERE po.id = '$id'
");

$data = mysqli_fetch_assoc($query);

if (!$data) {
    header("Location: purchase_orders.php");
    exit;
}

/* GET PO ITEM DETAILS */
$detail_query = mysqli_query($conn, "
    SELECT
        pod.id,
        pod.quantity,
        pod.purchase_price,
        pod.subtotal,
        i.product_name,
        i.category
    FROM purchase_order_details pod
    LEFT JOIN inventory i ON pod.inventory_id = i.id
    WHERE pod.purchase_order_id = '$id'
    ORDER BY pod.id DESC
");

/* FORMAT MONEY */
$budget_estimate = '-';
if (!empty($data['budget_estimate'])) {
    $budget_estimate = 'Rp ' . number_format($data['budget_estimate'], 0, ',', '.');
}

$total_amount = 'Rp 0';
if (!empty($data['total_amount'])) {
    $total_amount = 'Rp ' . number_format($data['total_amount'], 0, ',', '.');
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>View Purchase Order</title>

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

        .info-grid {
            display:grid;
            grid-template-columns:1fr 1fr;
            gap:16px 20px;
        }

        .info-item {
            display:flex;
            flex-direction:column;
            background:rgba(255,255,255,0.35);
            border-radius:12px;
            padding:12px 14px;
        }

        .info-item.full {
            grid-column:1 / -1;
        }

        .info-label {
            font-size:12px;
            color:#7a6551;
            margin-bottom:5px;
        }

        .info-value {
            font-size:14px;
            font-weight:600;
            color:#4b3b2a;
            word-break:break-word;
        }

        .notes-box {
            background:rgba(255,255,255,0.35);
            border-radius:12px;
            padding:14px;
            font-size:14px;
            line-height:1.6;
            min-height:80px;
            white-space:pre-wrap;
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

        .money-box {
            background:rgba(255,255,255,0.35);
            border-radius:12px;
            padding:14px;
        }

        .money-box p {
            margin:0 0 8px;
            font-size:12px;
            color:#7a6551;
        }

        .money-box h3 {
            margin:0;
            font-size:24px;
            color:#4b3b2a;
        }

        table {
            width:100%;
            border-collapse:collapse;
            background:rgba(255,255,255,0.35);
            border-radius:16px;
            overflow:hidden;
        }

        th, td {
            padding:14px;
            text-align:left;
            font-size:14px;
        }

        th {
            border-bottom:1px solid rgba(75,59,42,0.12);
            color:#5f4a37;
            font-size:13px;
        }

        tr:not(:last-child) td {
            border-bottom:1px solid rgba(75,59,42,0.07);
        }

        tr:hover {
            background:rgba(255,255,255,0.22);
        }

        .empty-text {
            font-size:14px;
            color:#6f5b47;
            padding:8px 0;
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

        .btn-back {
            background:#d8d0c5;
            color:#3f3125;
        }

        .btn-back:hover {
            background:#cfc4b6;
        }

        .btn-edit {
            background:#7a5a3a;
            color:white;
        }

        .btn-edit:hover {
            background:#65472b;
        }

        .btn-add {
            background:#8b6a47;
            color:white;
        }

        .btn-add:hover {
            background:#745537;
        }

        @media (max-width: 1000px) {
            .content-grid {
                grid-template-columns:1fr;
            }

            .info-grid {
                grid-template-columns:1fr;
            }

            .container {
                padding:20px;
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
        <h2>View Purchase Order</h2>
    </div>

    <div class="main-box">

        <div class="section-title">
            <h3>Purchase Order #<?= $data['id']; ?></h3>
            <p>Review purchase order header information, current process status, and item details.</p>
        </div>

        <div class="content-grid">

            <div class="left-side">

                <div class="card">
                    <h4>PO Header Information</h4>

                    <div class="info-grid">
                        <div class="info-item">
                            <div class="info-label">Supplier</div>
                            <div class="info-value"><?= htmlspecialchars($data['supplier_name'] ?? '-') ?></div>
                        </div>

                        <div class="info-item">
                            <div class="info-label">Created By</div>
                            <div class="info-value"><?= htmlspecialchars($data['user_name'] ?? '-') ?></div>
                        </div>

                        <div class="info-item">
                            <div class="info-label">Order Date</div>
                            <div class="info-value"><?= !empty($data['order_date']) ? date('Y-m-d', strtotime($data['order_date'])) : '-' ?></div>
                        </div>

                        <div class="info-item">
                            <div class="info-label">Expected Arrival Date</div>
                            <div class="info-value"><?= !empty($data['expected_arrival_date']) ? htmlspecialchars($data['expected_arrival_date']) : '-' ?></div>
                        </div>

                        <div class="info-item">
                            <div class="info-label">Shipment Type</div>
                            <div class="info-value"><?= htmlspecialchars($data['shipment_type'] ?? '-') ?></div>
                        </div>

                        <div class="info-item">
                            <div class="info-label">Budget Estimate</div>
                            <div class="info-value"><?= $budget_estimate; ?></div>
                        </div>

                        <div class="info-item full">
                            <div class="info-label">Reference / Supplier PO No.</div>
                            <div class="info-value"><?= !empty($data['reference_no']) ? htmlspecialchars($data['reference_no']) : '-' ?></div>
                        </div>
                    </div>
                </div>

                <div class="card">
                    <h4>PO Item Details</h4>

                    <?php if (mysqli_num_rows($detail_query) > 0) { ?>
                        <table>
                            <tr>
                                <th>Product</th>
                                <th>Category</th>
                                <th>Qty</th>
                                <th>Purchase Price</th>
                                <th>Subtotal</th>
                            </tr>

                            <?php while($detail = mysqli_fetch_assoc($detail_query)) { ?>
                            <tr>
                                <td><?= htmlspecialchars($detail['product_name'] ?? '-') ?></td>
                                <td><?= htmlspecialchars($detail['category'] ?? '-') ?></td>
                                <td><?= (int)$detail['quantity']; ?></td>
                                <td>Rp <?= number_format($detail['purchase_price'], 0, ',', '.'); ?></td>
                                <td>Rp <?= number_format($detail['subtotal'], 0, ',', '.'); ?></td>
                            </tr>
                            <?php } ?>
                        </table>
                    <?php } else { ?>
                        <div class="empty-text">No item details added yet for this purchase order.</div>
                    <?php } ?>
                </div>

                <div class="card">
                    <h4>Notes / Remarks</h4>
                    <div class="notes-box"><?= !empty($data['notes']) ? htmlspecialchars($data['notes']) : '-' ?></div>
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

                <div class="card">
                    <h4>Actual PO Total</h4>
                    <div class="money-box">
                        <p>Total Amount</p>
                        <h3><?= $total_amount; ?></h3>
                    </div>
                </div>
            </div>

        </div>

        <div class="actions">
            <a href="purchase_orders.php" class="btn btn-back">Back</a>
            <div>
                <a href="add_po_items.php?id=<?= $data['id']; ?>" class="btn btn-add">Add Items</a>
                <a href="edit_po.php?id=<?= $data['id']; ?>" class="btn btn-edit">Edit Purchase Order</a>
            </div>
        </div>

    </div>

</div>

</body>
</html>