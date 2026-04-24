<?php
include "db.php";
$query = mysqli_query($conn, "SELECT * FROM suppliers");
?>

<!DOCTYPE html>
<html>
<head>
    <title>Suppliers</title>

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
        }

        .menu-item img { width:18px; }

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

        .btn-edit {
            padding:6px 12px;
            background:#b89c74;
            color:white;
            border-radius:12px;
            text-decoration:none;
            font-size:12px;
        }

        .btn-delete {
            padding:6px 12px;
            background:#a94442;
            color:white;
            border-radius:12px;
            text-decoration:none;
            font-size:12px;
        }

        /* ===== POPUP STYLE ===== */
        .popup-bg {
            position: fixed;
            inset: 0;
            background: rgba(0,0,0,0.4);

            display: flex;
            justify-content: center;
            align-items: center;

            opacity: 0;
            visibility: hidden;

            transition: 0.25s ease;
            z-index: 9999;
        }

        .popup-bg.active {
            opacity: 1;
            visibility: visible;
        }

        .popup-box {
            background: #e6d8c3;
            padding: 25px;
            border-radius: 15px;
            width: 320px;
            text-align: center;

            transform: scale(0.8);
            transition: 0.25s ease;
        }

        .popup-bg.active .popup-box {
            transform: scale(1);
        }

        .popup-actions {
            margin-top: 20px;
            display:flex;
            justify-content:space-between;
        }

        .btn-cancel {
            background:#ccc;
            border:none;
            padding:8px 14px;
            border-radius:8px;
            cursor:pointer;
        }

        .btn-confirm {
            background:#a94442;
            color:white;
            padding:8px 14px;
            border-radius:8px;
            text-decoration:none;
            cursor:pointer;
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

    transition: all 0.25s ease;
}

/* ICON + TEXT HOVER EFFECT */
.menu-item:hover {
    background: rgba(75, 59, 42, 0.15);
    transform: translateY(-2px);
}

/* ICON DARKEN EFFECT */
.menu-item img {
    width:18px;
    transition: 0.25s ease;
}

/* when hovering the whole menu item */
.menu-item:hover img {
    filter: brightness(0.6);
    transform: scale(1.1);
}

/* optional text effect */
.menu-item:hover span {
    color: #2f2418;
    font-weight: 500;
}

    </style>
</head>

<body>

<!-- TOPBAR -->
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

<!-- CONTENT -->
<div class="container">

    <div class="header-row">
        <h2>Suppliers</h2>
        <a href="add_supplier.php" class="btn">+ Add Supplier</a>
    </div>

    <table>
        <tr>
            <th>Name</th>
            <th>Contact</th>
            <th>Phone</th>
            <th>Email</th>
            <th>Address</th>
            <th>Action</th>
        </tr>

        <?php while($row = mysqli_fetch_assoc($query)) { ?>
        <tr>
            <td><?= $row['name']; ?></td>
            <td><?= $row['contact_person']; ?></td>
            <td><?= $row['phone']; ?></td>
            <td><?= $row['email']; ?></td>
            <td><?= $row['address']; ?></td>
            <td>
                <div class="action">
                    <a href="edit_supplier.php?id=<?= $row['id']; ?>" class="btn-edit">Edit</a>

                    <a href="#" class="btn-delete"
                       onclick="openDeletePopup(<?= $row['id']; ?>)">
                       Delete
                    </a>
                </div>
            </td>
        </tr>
        <?php } ?>
    </table>
</div>

<!-- DELETE POPUP -->
<div class="popup-bg" id="deletePopup">
    <div class="popup-box">
        <h3>Delete Supplier</h3>
        <p>Are you sure you want to delete this supplier?</p>

        <div class="popup-actions">
            <button class="btn-cancel" onclick="closeDeletePopup()">Cancel</button>
            <a id="confirmDeleteBtn" class="btn-confirm">Delete</a>
        </div>
    </div>
</div>

<!-- SUCCESS POPUP -->
<div class="popup-bg" id="successPopup">
    <div class="popup-box">
        <h3>Success</h3>
        <p>Supplier deleted successfully.</p>

        <div class="popup-actions">
            <button class="btn-confirm" onclick="closeSuccessPopup()">OK</button>
        </div>
    </div>
</div>

<script>
let deleteId = null;

function openDeletePopup(id){
    deleteId = id;
    document.getElementById("deletePopup").classList.add("active");

    document.getElementById("confirmDeleteBtn").onclick = function () {
        window.location.href = "delete_supplier.php?id=" + deleteId;
    }
}

function closeDeletePopup(){
    document.getElementById("deletePopup").classList.remove("active");
}

function closeSuccessPopup(){
    document.getElementById("successPopup").classList.remove("active");
}
</script>

<?php if(isset($_GET['deleted'])) { ?>
<script>
document.getElementById("successPopup").classList.add("active");
</script>
<?php } ?>

</body>
</html>