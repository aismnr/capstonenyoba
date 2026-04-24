<?php
include "db.php";

$query = mysqli_query($conn, "
    SELECT 
        po.id,
        po.order_date,
        po.po_status,
        po.total_amount,
        s.name AS supplier_name,
        u.name AS user_name
    FROM purchase_orders po
    LEFT JOIN suppliers s ON po.supplier_id = s.id
    LEFT JOIN users u ON po.user_id = u.id
    ORDER BY po.id DESC
");
?>

<!DOCTYPE html>
<html>
<head>
    <title>Purchase Orders</title>

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

        /* ===== TOPBAR ===== */
        .topbar {
            display:flex;
            justify-content:space-between;
            align-items:center;
            padding:6px 30px;
            height:70px;
            background:rgba(200,183,158,0.9);
        }

        .brand img { width:80px; }

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
            transition:0.25s;
        }

        .menu-item img { width:18px; }

        .menu-item:hover {
            background: rgba(75, 59, 42, 0.15);
            transform: translateY(-2px);
        }

        .menu-item:hover img {
            filter: brightness(0.6);
            transform: scale(1.1);
        }

        .profile img { width:28px; }

        /* ===== CONTENT ===== */
        .container {
            padding:25px 50px;
        }

        .header-row {
            display:flex;
            justify-content:space-between;
            align-items:center;
        }

        .btn {
            padding:10px 18px;
            background:#7a5a3a;
            color:white;
            border-radius:20px;
            text-decoration:none;
            font-size:14px;
        }

        table {
            width:100%;
            margin-top:20px;
            background:rgba(230,216,195,0.85);
            border-radius:20px;
            border-collapse:collapse;
            overflow:hidden;
        }

        th, td {
            padding:14px;
            text-align:left;
        }

        th {
            border-bottom:2px solid rgba(75,59,42,0.2);
        }

        tr:hover {
            background:rgba(255,255,255,0.3);
        }

        td {
            max-width:200px;
            word-wrap:break-word;
        }

        .action {
            display:flex;
            gap:8px;
        }

        .btn-view {
            padding:6px 12px;
            background:#5bc0de;
            color:white;
            border-radius:12px;
            text-decoration:none;
            font-size:12px;
        }

        .btn-edit {
            padding:6px 12px;
            background:#b89c74;
            color:white;
            border-radius:12px;
            text-decoration:none;
            font-size:12px;
        }

        /* ===== STATUS STYLE ===== */
        .status {
            padding:4px 10px;
            border-radius:12px;
            font-size:12px;
            color:white;
        }

        .open { background:#d39e00; }
        .released { background:#2c7be5; }
        .closed { background:#1c7c54; }
        .cancelled { background:#a94442; }
        .hold { background:#6c757d; }

    </style>
</head>

<body>

<!-- ===== TOPBAR ===== -->
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

<!-- ===== CONTENT ===== -->
<div class="container">

    <div class="header-row">
        <h2>Purchase Orders</h2>
        <a href="create_po.php" class="btn">+ Create PO</a>
    </div>

    <table>
        <tr>
            <th>PO ID</th>
            <th>Supplier</th>
            <th>Created By</th>
            <th>Date</th>
            <th>Status</th>
            <th>Total</th>
            <th>Action</th>
        </tr>

        <?php while($row = mysqli_fetch_assoc($query)) { ?>
        <tr>
            <td>#<?= $row['id']; ?></td>
            <td><?= $row['supplier_name']; ?></td>
            <td><?= $row['user_name']; ?></td>
            <td><?= $row['order_date']; ?></td>

            <td>
                <span class="status <?= $row['po_status']; ?>">
                    <?= ucfirst(str_replace('_', ' ', $row['po_status'])); ?>
                </span>
            </td>

            <td>Rp <?= number_format($row['total_amount']); ?></td>

            <td>
                <div class="action">
                    <a href="view_po.php?id=<?= $row['id']; ?>" class="btn-view">View</a>
                    <a href="edit_po.php?id=<?= $row['id']; ?>" class="btn-edit">Edit</a>
                </div>
            </td>
        </tr>
        <?php } ?>

    </table>

</div>

</body>
</html>