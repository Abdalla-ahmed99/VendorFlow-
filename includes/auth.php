<?php
    if (!isset($_SESSION['procurement_user_id'])) {
        header("Location: index.php");
        exit();
    }
?>
