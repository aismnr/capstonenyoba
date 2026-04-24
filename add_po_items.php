<?php
include "db.php";

if (!isset($_GET['id'])) {
    header("Location: purchase_orders.php");
    exit;
}

$po_id = $_GET['id'];

$po_query = mysqli_query($conn, "
    SELECT po.*, s.name AS supplier_name
    FROM purchase_orders po
    LEFT JOIN suppliers s ON po.supplier_id = s.id
    WHERE po.id = '$po_id'
");

$po = mysqli_fetch_assoc($po_query);

if (!$po) {
    header("Location: purchase_orders.php");
    exit;
}

$inventory_query = mysqli_query($conn, "
    SELECT id, product_name, category, price
    FROM inventory
    ORDER BY category ASC, product_name ASC
");

if (isset($_POST['save_item'])) {
    $inventory_id = $_POST['inventory_id'];
    $quantity = (int) $_POST['quantity'];
    $purchase_price = (float) $_POST['purchase_price'];

    if ($inventory_id != '' && $quantity > 0 && $purchase_price >= 0) {
        $subtotal = $quantity * $purchase_price;

        mysqli_query($conn, "
            INSERT INTO purchase_order_details
            (purchase_order_id, inventory_id, quantity, purchase_price, subtotal)
            VALUES
            ('$po_id', '$inventory_id', '$quantity', '$purchase_price', '$subtotal')
        ");

        $total_query = mysqli_query($conn, "
            SELECT SUM(subtotal) AS grand_total
            FROM purchase_order_details
            WHERE purchase_order_id = '$po_id'
        ");

        $total_data = mysqli_fetch_assoc($total_query);
        $grand_total = $total_data['grand_total'] ? $total_data['grand_total'] : 0;

        mysqli_query($conn, "
            UPDATE purchase_orders
            SET total_amount = '$grand_total'
            WHERE id = '$po_id'
        ");

        header("Location: add_po_items.php?id=" . $po_id . "&added=1");
        exit;
    }
}

if (isset($_GET['delete_detail'])) {
    $detail_id = $_GET['delete_detail'];

    mysqli_query($conn, "
        DELETE FROM purchase_order_details
        WHERE id = '$detail_id' AND purchase_order_id = '$po_id'
    ");

    $total_query = mysqli_query($conn, "
        SELECT SUM(subtotal) AS grand_total
        FROM purchase_order_details
        WHERE purchase_order_id = '$po_id'
    ");

    $total_data = mysqli_fetch_assoc($total_query);
    $grand_total = $total_data['grand_total'] ? $total_data['grand_total'] : 0;

    mysqli_query($conn, "
        UPDATE purchase_orders
        SET total_amount = '$grand_total'
        WHERE id = '$po_id'
    ");

    header("Location: add_po_items.php?id=" . $po_id . "&deleted=1");
    exit;
}

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
    WHERE pod.purchase_order_id = '$po_id'
    ORDER BY pod.id DESC
");

$current_total_query = mysqli_query($conn, "
    SELECT total_amount
    FROM purchase_orders
    WHERE id = '$po_id'
");

$current_total = mysqli_fetch_assoc($current_total_query);
$total_amount = $current_total['total_amount'] ? $current_total['total_amount'] : 0;

$inventory_items = [];
$categories = [];

while ($item = mysqli_fetch_assoc($inventory_query)) {
    $inventory_items[] = $item;

    if (!empty($item['category']) && !in_array($item['category'], $categories)) {
        $categories[] = $item['category'];
    }
}

sort($categories);
?>

<!DOCTYPE html>
<html>
<head>
    <title>Add PO Items</title>

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
            flex-direction:column;
            align-items:center;
            gap:4px;
            text-decoration:none;
            color:#4b3b2a;
            font-size:11px;
            padding:8px 10px;
            border-radius:12px;
            transition:all 0.25s ease;
            min-width:70px;
        }

        .menu-item img {
            width:22px;
            transition:0.25s ease;
        }

        .menu-item span {
            font-size:11px;
            text-align:center;
        }

        .menu-item:hover {
            background:rgba(75, 59, 42, 0.15);
            transform:translateY(-2px);
        }

        .menu-item:hover img {
            filter:brightness(0.6);
            transform:scale(1.1);
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
            grid-template-columns:1.5fr 1fr;
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
            gap:14px 18px;
        }

        .info-item {
            display:flex;
            flex-direction:column;
            background:rgba(255,255,255,0.35);
            border-radius:12px;
            padding:12px 14px;
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
        }

        .toolbar {
            display:flex;
            flex-direction:column;
            gap:14px;
            margin-bottom:16px;
        }

        .search-box input {
            width:100%;
            padding:12px 14px;
            border-radius:14px;
            border:1px solid rgba(75,59,42,0.15);
            background:rgba(255,255,255,0.60);
            font-family:'Segoe UI';
            font-size:14px;
            color:#4b3b2a;
            outline:none;
        }

        .category-filters {
            display:flex;
            flex-wrap:wrap;
            gap:10px;
        }

        .filter-btn {
            padding:8px 14px;
            border:none;
            border-radius:999px;
            background:rgba(255,255,255,0.55);
            color:#4b3b2a;
            cursor:pointer;
            font-size:13px;
            transition:0.2s;
        }

        .filter-btn.active,
        .filter-btn:hover {
            background:#7a5a3a;
            color:white;
        }

        .medicine-list {
            display:grid;
            grid-template-columns:repeat(2, 1fr);
            gap:14px;
            max-height:360px;
            overflow-y:auto;
            padding-right:4px;
        }

        .medicine-card {
            background:rgba(255,255,255,0.45);
            border:1px solid transparent;
            border-radius:16px;
            padding:14px;
            cursor:pointer;
            transition:0.2s ease;
        }

        .medicine-card:hover {
            transform:translateY(-2px);
            border-color:rgba(122,90,58,0.25);
            box-shadow:0 6px 14px rgba(75,59,42,0.08);
        }

        .medicine-card.selected {
            border-color:#7a5a3a;
            background:rgba(255,255,255,0.70);
            box-shadow:0 0 0 3px rgba(122,90,58,0.12);
        }

        .medicine-name {
            font-size:15px;
            font-weight:600;
            margin-bottom:6px;
            color:#3f3125;
        }

        .medicine-category {
            display:inline-block;
            font-size:12px;
            padding:4px 10px;
            border-radius:999px;
            background:#eee4d8;
            color:#6b5540;
            margin-bottom:8px;
        }

        .medicine-price {
            font-size:14px;
            color:#6f5b47;
            font-weight:600;
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

        .form-group input {
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

        .form-note {
            font-size:12px;
            color:#75614d;
            margin-top:6px;
        }

        .subtotal-box {
            background:rgba(255,255,255,0.35);
            border-radius:12px;
            padding:12px 14px;
            font-size:14px;
            font-weight:600;
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

        .total-box {
            background:rgba(255,255,255,0.35);
            border-radius:14px;
            padding:16px;
        }

        .total-box p {
            margin:0 0 8px;
            font-size:13px;
            color:#6f5b47;
        }

        .total-box h3 {
            margin:0;
            font-size:28px;
            color:#4b3b2a;
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

        .btn-back {
            background:#d8d0c5;
            color:#3f3125;
        }

        .btn-delete {
            padding:7px 12px;
            border-radius:12px;
            text-decoration:none;
            font-size:12px;
            font-weight:600;
            background:#a94442;
            color:white;
        }

        .actions {
            display:flex;
            justify-content:space-between;
            margin-top:24px;
        }

        .message {
            background:rgba(215,240,220,0.9);
            color:#2d7a3e;
            padding:12px 14px;
            border-radius:12px;
            margin-bottom:16px;
            font-size:14px;
        }

        .empty-text,
        .empty-result {
            font-size:14px;
            color:#6f5b47;
            padding:8px 0;
        }

        .hidden {
            display:none;
        }

        @media (max-width: 1000px) {
            .content-grid,
            .form-grid,
            .info-grid,
            .medicine-list {
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
        <h2>Add Purchase Order Items</h2>
    </div>

    <?php if (isset($_GET['added'])) { ?>
        <div class="message">Item added successfully.</div>
    <?php } ?>

    <?php if (isset($_GET['deleted'])) { ?>
        <div class="message">Item deleted successfully.</div>
    <?php } ?>

    <div class="main-box">

        <div class="section-title">
            <h3>Purchase Order #<?= $po['id']; ?></h3>
            <p>Select medicines by category or search, then add them into this purchase order.</p>
        </div>

        <div class="content-grid">

            <div class="left-side">

                <div class="card">
                    <h4>PO Header Summary</h4>

                    <div class="info-grid">
                        <div class="info-item">
                            <div class="info-label">Supplier</div>
                            <div class="info-value"><?= htmlspecialchars($po['supplier_name'] ?? '-'); ?></div>
                        </div>

                        <div class="info-item">
                            <div class="info-label">Order Date</div>
                            <div class="info-value">
                                <?= !empty($po['order_date']) ? date('Y-m-d', strtotime($po['order_date'])) : '-'; ?>
                            </div>
                        </div>

                        <div class="info-item">
                            <div class="info-label">PO Status</div>
                            <div class="info-value"><?= ucfirst(str_replace('_', ' ', $po['po_status'])); ?></div>
                        </div>

                        <div class="info-item">
                            <div class="info-label">Budget Estimate</div>
                            <div class="info-value">
                                <?= !empty($po['budget_estimate']) ? 'Rp ' . number_format($po['budget_estimate'], 0, ',', '.') : '-'; ?>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="card">
                    <h4>Select Medicine</h4>

                    <div class="toolbar">
                        <div class="search-box">
                            <input type="text" id="searchInput" placeholder="Search medicine name...">
                        </div>

                        <div class="category-filters">
                            <button type="button" class="filter-btn active" data-category="all">All</button>

                            <?php foreach ($categories as $category) { ?>
                                <button type="button" class="filter-btn" data-category="<?= htmlspecialchars($category); ?>">
                                    <?= htmlspecialchars($category); ?>
                                </button>
                            <?php } ?>
                        </div>
                    </div>

                    <div class="medicine-list" id="medicineList">
                        <?php foreach ($inventory_items as $item) { ?>
                            <div class="medicine-card"
                                 data-id="<?= $item['id']; ?>"
                                 data-name="<?= htmlspecialchars($item['product_name']); ?>"
                                 data-category="<?= htmlspecialchars($item['category']); ?>"
                                 data-price="<?= $item['price']; ?>">
                                <div class="medicine-name"><?= htmlspecialchars($item['product_name']); ?></div>
                                <div class="medicine-category"><?= htmlspecialchars($item['category']); ?></div>
                                <div class="medicine-price">Rp <?= number_format($item['price'], 0, ',', '.'); ?></div>
                            </div>
                        <?php } ?>
                    </div>

                    <div class="empty-result hidden" id="emptyResult">
                        No medicines found.
                    </div>
                </div>

                <div class="card">
                    <h4>Add Selected Item</h4>

                    <form method="POST">
                        <input type="hidden" name="inventory_id" id="inventory_id" required>

                        <div class="form-grid">
                            <div class="form-group full">
                                <label>Selected Medicine</label>
                                <input type="text" id="selected_medicine" placeholder="Please select a medicine above" readonly>
                            </div>

                            <div class="form-group">
                                <label>Quantity *</label>
                                <input type="number" name="quantity" id="quantity" min="1" required>
                            </div>

                            <div class="form-group">
                                <label>Purchase Price *</label>
                                <input type="number" name="purchase_price" id="purchase_price" step="0.01" min="0" required>
                                <div class="form-note">Default price is taken from inventory, but you can change it.</div>
                            </div>

                            <div class="form-group full">
                                <label>Subtotal</label>
                                <div class="subtotal-box" id="subtotal_display">Rp 0</div>
                            </div>
                        </div>

                        <div class="actions" style="margin-top:20px;">
                            <a href="view_po.php?id=<?= $po['id']; ?>" class="btn btn-back">Back to PO</a>
                            <button type="submit" name="save_item" class="btn btn-save">Add Item</button>
                        </div>
                    </form>
                </div>

            </div>

            <div class="right-side">
                <div class="card">
                    <h4>Current PO Total</h4>
                    <div class="total-box">
                        <p>Actual Total Amount</p>
                        <h3>Rp <?= number_format($total_amount, 0, ',', '.'); ?></h3>
                    </div>
                </div>
            </div>

        </div>

        <div style="height:24px;"></div>

        <div class="card">
            <h4>Current PO Items</h4>

            <?php if (mysqli_num_rows($detail_query) > 0) { ?>
                <table>
                    <tr>
                        <th>Product</th>
                        <th>Category</th>
                        <th>Quantity</th>
                        <th>Purchase Price</th>
                        <th>Subtotal</th>
                        <th>Action</th>
                    </tr>

                    <?php while($detail = mysqli_fetch_assoc($detail_query)) { ?>
                    <tr>
                        <td><?= htmlspecialchars($detail['product_name'] ?? '-'); ?></td>
                        <td><?= htmlspecialchars($detail['category'] ?? '-'); ?></td>
                        <td><?= (int)$detail['quantity']; ?></td>
                        <td>Rp <?= number_format($detail['purchase_price'], 0, ',', '.'); ?></td>
                        <td>Rp <?= number_format($detail['subtotal'], 0, ',', '.'); ?></td>
                        <td>
                            <a href="add_po_items.php?id=<?= $po_id; ?>&delete_detail=<?= $detail['id']; ?>"
                               class="btn-delete"
                               onclick="return confirm('Delete this item?')">
                               Delete
                            </a>
                        </td>
                    </tr>
                    <?php } ?>
                </table>
            <?php } else { ?>
                <div class="empty-text">No items added yet for this purchase order.</div>
            <?php } ?>
        </div>

    </div>
</div>

<script>
const medicineCards = document.querySelectorAll('.medicine-card');
const searchInput = document.getElementById('searchInput');
const filterButtons = document.querySelectorAll('.filter-btn');
const inventoryIdInput = document.getElementById('inventory_id');
const selectedMedicineInput = document.getElementById('selected_medicine');
const quantityInput = document.getElementById('quantity');
const priceInput = document.getElementById('purchase_price');
const subtotalDisplay = document.getElementById('subtotal_display');
const emptyResult = document.getElementById('emptyResult');

let activeCategory = 'all';

function formatRupiah(number) {
    return 'Rp ' + new Intl.NumberFormat('id-ID').format(number || 0);
}

function calculateSubtotal() {
    const qty = parseFloat(quantityInput.value) || 0;
    const price = parseFloat(priceInput.value) || 0;
    const subtotal = qty * price;
    subtotalDisplay.textContent = formatRupiah(subtotal);
}

function filterMedicines() {
    const searchValue = searchInput.value.toLowerCase().trim();
    let visibleCount = 0;

    medicineCards.forEach(card => {
        const name = card.dataset.name.toLowerCase();
        const category = card.dataset.category;

        const matchSearch = name.includes(searchValue);
        const matchCategory = activeCategory === 'all' || category === activeCategory;

        if (matchSearch && matchCategory) {
            card.classList.remove('hidden');
            visibleCount++;
        } else {
            card.classList.add('hidden');
        }
    });

    emptyResult.classList.toggle('hidden', visibleCount !== 0);
}

medicineCards.forEach(card => {
    card.addEventListener('click', function() {
        medicineCards.forEach(c => c.classList.remove('selected'));
        this.classList.add('selected');

        inventoryIdInput.value = this.dataset.id;
        selectedMedicineInput.value = this.dataset.name;
        priceInput.value = this.dataset.price;

        calculateSubtotal();
    });
});

filterButtons.forEach(button => {
    button.addEventListener('click', function() {
        filterButtons.forEach(btn => btn.classList.remove('active'));
        this.classList.add('active');

        activeCategory = this.dataset.category;
        filterMedicines();
    });
});

searchInput.addEventListener('input', filterMedicines);
quantityInput.addEventListener('input', calculateSubtotal);
priceInput.addEventListener('input', calculateSubtotal);

filterMedicines();
</script>

</body>
</html>