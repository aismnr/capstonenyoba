<?php
include "db.php";

/* ===== SUMMARY CARDS ===== */
$total_po_query = mysqli_query($conn, "SELECT COUNT(*) AS total_po FROM purchase_orders");
$total_po = mysqli_fetch_assoc($total_po_query)['total_po'];

$pending_po_query = mysqli_query($conn, "SELECT COUNT(*) AS pending_po FROM purchase_orders WHERE po_status = 'open'");
$pending_po = mysqli_fetch_assoc($pending_po_query)['pending_po'];

$received_po_query = mysqli_query($conn, "SELECT COUNT(*) AS received_po FROM purchase_orders WHERE receiving_status = 'received'");
$received_po = mysqli_fetch_assoc($received_po_query)['received_po'];

$low_stock_count_query = mysqli_query($conn, "SELECT COUNT(*) AS low_stock_count FROM inventory WHERE stock <= minimum_stock");
$low_stock_count = mysqli_fetch_assoc($low_stock_count_query)['low_stock_count'];

/* ===== LOW STOCK ITEMS ===== */
$low_stock_items = mysqli_query($conn, "
    SELECT id, product_name, stock, minimum_stock, price
    FROM inventory
    WHERE stock <= minimum_stock
    ORDER BY stock ASC, product_name ASC
    LIMIT 5
");

/* ===== RECENT PURCHASE ORDERS ===== */
$recent_po = mysqli_query($conn, "
    SELECT 
        po.id,
        po.order_date,
        po.po_status,
        s.name AS supplier_name
    FROM purchase_orders po
    LEFT JOIN suppliers s ON po.supplier_id = s.id
    ORDER BY po.id DESC
    LIMIT 5
");
?>

<!DOCTYPE html>
<html>
<head>
    <title>Dashboard</title>

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
            margin-bottom:20px;
        }

        .page-header h2 {
            margin:0;
            font-size:24px;
        }

        .page-header p {
            margin:6px 0 0;
            font-size:13px;
            color:#6f5b47;
        }

        .cards {
            display:grid;
            grid-template-columns:repeat(4, 1fr);
            gap:18px;
            margin-bottom:24px;
        }

        .card-link {
            text-decoration:none;
            color:inherit;
        }

        .summary-card {
            background:rgba(230,216,195,0.88);
            border-radius:20px;
            padding:20px;
            transition:0.25s ease;
            min-height:115px;
        }

        .summary-card:hover {
            transform:translateY(-4px);
            box-shadow:0 8px 18px rgba(75,59,42,0.10);
        }

        .summary-label {
            font-size:13px;
            color:#6f5b47;
            margin-bottom:10px;
        }

        .summary-value {
            font-size:32px;
            font-weight:700;
            color:#4b3b2a;
        }

        .summary-sub {
            margin-top:8px;
            font-size:12px;
            color:#816954;
        }

        .content-grid {
            display:grid;
            grid-template-columns:1.2fr 1fr;
            gap:24px;
        }

        .section-box {
            background:rgba(230,216,195,0.88);
            border-radius:24px;
            padding:24px;
        }

        .section-header {
            display:flex;
            justify-content:space-between;
            align-items:center;
            margin-bottom:16px;
        }

        .section-header h3 {
            margin:0;
            font-size:20px;
        }

        .small-link {
            text-decoration:none;
            font-size:13px;
            color:#7a5a3a;
            font-weight:600;
        }

        .small-link:hover {
            text-decoration:underline;
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

        .badge {
            display:inline-block;
            padding:5px 10px;
            border-radius:999px;
            font-size:12px;
            font-weight:600;
        }

        .badge-low {
            background:#f3d5d5;
            color:#a94442;
        }

        .badge-ok {
            background:#d7f0dc;
            color:#2d7a3e;
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

        .btn-mini {
            display:inline-block;
            padding:7px 12px;
            border-radius:12px;
            text-decoration:none;
            font-size:12px;
            font-weight:600;
            background:#7a5a3a;
            color:white;
        }

        .btn-mini:hover {
            background:#65472b;
        }

        .quick-actions {
            display:grid;
            grid-template-columns:1fr 1fr;
            gap:16px;
            margin-top:24px;
        }

        .quick-card {
            background:rgba(230,216,195,0.88);
            border-radius:20px;
            padding:22px;
            text-decoration:none;
            color:#4b3b2a;
            transition:0.25s ease;
        }

        .quick-card:hover {
            transform:translateY(-3px);
            box-shadow:0 8px 18px rgba(75,59,42,0.10);
        }

        .quick-card h4 {
            margin:0 0 8px;
            font-size:18px;
        }

        .quick-card p {
            margin:0;
            font-size:13px;
            color:#6f5b47;
        }

        .empty-text {
            font-size:14px;
            color:#6f5b47;
            padding:8px 0;
        }

        @media (max-width: 1100px) {
            .cards {
                grid-template-columns:repeat(2, 1fr);
            }

            .content-grid {
                grid-template-columns:1fr;
            }
        }

        @media (max-width: 700px) {
            .cards,
            .quick-actions {
                grid-template-columns:1fr;
            }

            .container {
                padding:20px;
            }

            .menu {
                gap:12px;
            }

            .menu-item span {
                display:none;
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
            <span>Home</span>
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
        <h2>Dashboard</h2>
        <p>Overview of purchase orders, stock alerts, and quick actions.</p>
    </div>

    <!-- TOP CARDS -->
    <div class="cards">
        <a href="purchase_orders.php" class="card-link">
            <div class="summary-card">
                <div class="summary-label">Total Purchase Orders</div>
                <div class="summary-value"><?= $total_po; ?></div>
                <div class="summary-sub">Click to view all POs</div>
            </div>
        </a>

        <a href="purchase_orders.php" class="card-link">
            <div class="summary-card">
                <div class="summary-label">Pending Orders</div>
                <div class="summary-value"><?= $pending_po; ?></div>
                <div class="summary-sub">Current open purchase orders</div>
            </div>
        </a>

        <a href="purchase_orders.php" class="card-link">
            <div class="summary-card">
                <div class="summary-label">Received Orders</div>
                <div class="summary-value"><?= $received_po; ?></div>
                <div class="summary-sub">Orders already received</div>
            </div>
        </a>

        <a href="inventory.php" class="card-link">
            <div class="summary-card">
                <div class="summary-label">Low Stock Items</div>
                <div class="summary-value"><?= $low_stock_count; ?></div>
                <div class="summary-sub">Items needing attention</div>
            </div>
        </a>
    </div>

    <div class="content-grid">

        <!-- LOW STOCK ALERT -->
        <div class="section-box">
            <div class="section-header">
                <h3>Low Stock Alert</h3>
                <a href="inventory.php" class="small-link">View Inventory</a>
            </div>

            <?php if (mysqli_num_rows($low_stock_items) > 0) { ?>
                <table>
                    <tr>
                        <th>Product</th>
                        <th>Stock</th>
                        <th>Min</th>
                        <th>Status</th>
                        <th>Action</th>
                    </tr>

                    <?php while($item = mysqli_fetch_assoc($low_stock_items)) { ?>
                    <tr>
                        <td><?= htmlspecialchars($item['product_name']); ?></td>
                        <td><?= (int)$item['stock']; ?></td>
                        <td><?= (int)$item['minimum_stock']; ?></td>
                        <td>
                            <span class="badge badge-low">Low Stock</span>
                        </td>
                        <td>
                            <a href="create_po.php?product_id=<?= $item['id']; ?>" class="btn-mini">Order</a>
                        </td>
                    </tr>
                    <?php } ?>
                </table>
            <?php } else { ?>
                <div class="empty-text">No low stock items right now.</div>
            <?php } ?>
        </div>

        <!-- RECENT PURCHASE ORDERS -->
        <div class="section-box">
            <div class="section-header">
                <h3>Recent Purchase Orders</h3>
                <a href="purchase_orders.php" class="small-link">View All</a>
            </div>

            <?php if (mysqli_num_rows($recent_po) > 0) { ?>
                <table>
                    <tr>
                        <th>PO ID</th>
                        <th>Supplier</th>
                        <th>Date</th>
                        <th>Status</th>
                    </tr>

                    <?php while($po = mysqli_fetch_assoc($recent_po)) { ?>
                    <tr onclick="window.location='view_po.php?id=<?= $po['id']; ?>'" style="cursor:pointer;">
                        <td>#<?= $po['id']; ?></td>
                        <td><?= htmlspecialchars($po['supplier_name'] ?? '-'); ?></td>
                        <td><?= !empty($po['order_date']) ? date('Y-m-d', strtotime($po['order_date'])) : '-'; ?></td>
                        <td>
                            <span class="badge badge-<?= htmlspecialchars($po['po_status']); ?>">
                                <?= ucfirst(str_replace('_', ' ', $po['po_status'])); ?>
                            </span>
                        </td>
                    </tr>
                    <?php } ?>
                </table>
            <?php } else { ?>
                <div class="empty-text">No purchase orders created yet.</div>
            <?php } ?>
        </div>

    </div>

    <!-- QUICK ACTIONS -->
    <div class="quick-actions">
        <a href="create_po.php" class="quick-card">
            <h4>+ Create Purchase Order</h4>
            <p>Create a new purchase order header and continue the procurement process.</p>
        </a>

        <a href="add_supplier.php" class="quick-card">
            <h4>+ Add Supplier</h4>
            <p>Add a new supplier into the system for future purchase orders.</p>
        </a>
    </div>

</div>

</body>
</html>