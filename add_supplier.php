<?php
include "db.php";

/* INSERT LOGIC */
if (isset($_POST['save'])) {

    $name = $_POST['name'];
    $contact = $_POST['contact_person'];
    $phone = $_POST['phone'];
    $email = $_POST['email'];
    $address = $_POST['address'];

    mysqli_query($conn, "
        INSERT INTO suppliers (name, contact_person, phone, email, address)
        VALUES ('$name','$contact','$phone','$email','$address')
    ");

    header("Location: suppliers.php");
    exit;
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Add Supplier</title>

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

        /* ===== TOPBAR (SAME STYLE) ===== */
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

        /* ===== FORM ===== */
        .container {
            display:flex;
            justify-content:center;
            margin-top:40px;
        }

        .form-box {
            background:rgba(230,216,195,0.9);
            padding:30px;
            border-radius:20px;
            width:400px;
        }

        .form-box h2 {
            margin-bottom:20px;
        }

        .form-group {
            margin-bottom:15px;
        }

        .form-group label {
            display:block;
            margin-bottom:5px;
            font-size:14px;
        }

        .form-group input,
        .form-group textarea {
            width:100%;
            padding:8px;
            border-radius:10px;
            border:1px solid rgba(75,59,42,0.2);
            background:rgba(255,255,255,0.4);
        }

        .btn-group {
            display:flex;
            justify-content:space-between;
            margin-top:20px;
        }

        .btn {
            padding:10px 15px;
            border:none;
            border-radius:15px;
            cursor:pointer;
            text-decoration:none;
        }

        .btn-save {
            background:#7a5a3a;
            color:white;
        }

        .btn-cancel {
            background:#ccc;
            color:black;
        }
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
            <img src="picture/dashbard.png"> Dashboard
        </a>

        <a href="purchase_orders.php" class="menu-item">
            <img src="picture/clipboard.png"> Orders
        </a>

        <a href="suppliers.php" class="menu-item">
            <img src="picture/supplier.png"> Suppliers
        </a>

        <a href="inventory.php" class="menu-item">
            <img src="picture/box.png"> Inventory
        </a>
    </div>

    <div class="profile">
        <img src="picture/profile.png">
    </div>

</div>

<!-- ===== FORM ===== -->
<div class="container">

    <form method="POST" class="form-box">

        <h2>Add Supplier</h2>

        <div class="form-group">
            <label>Supplier Name</label>
            <input type="text" name="name" required>
        </div>

        <div class="form-group">
            <label>Contact Person</label>
            <input type="text" name="contact_person">
        </div>

        <div class="form-group">
            <label>Phone</label>
            <input type="text" name="phone">
        </div>

        <div class="form-group">
            <label>Email</label>
            <input type="email" name="email">
        </div>

        <div class="form-group">
            <label>Address</label>
            <textarea name="address"></textarea>
        </div>

        <div class="btn-group">
            <button type="submit" name="save" class="btn btn-save">Save</button>
            <a href="suppliers.php" class="btn btn-cancel">Cancel</a>
        </div>

    </form>

</div>

</body>
</html>