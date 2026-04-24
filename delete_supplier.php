<?php
include "db.php";

if (isset($_GET['id'])) {
    $id = $_GET['id'];

    mysqli_query($conn, "DELETE FROM suppliers WHERE id = $id");
}

header("Location: suppliers.php?deleted=1");
exit;
?>