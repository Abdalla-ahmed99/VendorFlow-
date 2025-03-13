<?php
session_start();
require 'includes/db.php'; // Database connection

if (!isset($_SESSION['vendor_id'])) {
    header("Location: index.php");
    exit();
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if (isset($_FILES['profile_picture'])) {
        $file = $_FILES['profile_picture'];
        $fileName = $_FILES['profile_picture']['name'];
        $fileTmpName = $_FILES['profile_picture']['tmp_name'];
        $fileSize = $_FILES['profile_picture']['size'];
        $fileError = $_FILES['profile_picture']['error'];

        // Define allowed file types and max size
        $allowedTypes = ['image/jpeg', 'image/png', 'image/gif'];
        $maxFileSize = 2 * 1024 * 1024; // 2 MB

        // Check for errors
        if ($fileError === 0) {
            if ($fileSize <= $maxFileSize) {
                $fileType = mime_content_type($fileTmpName);
                if (in_array($fileType, $allowedTypes)) {
                    // Create a unique filename
                    $fileNameNew = uniqid('', true) . '.' . pathinfo($fileName, PATHINFO_EXTENSION);
                    $fileDestination = 'uploads/' . $fileNameNew;

                    // Move the uploaded file
                    if (move_uploaded_file($fileTmpName, $fileDestination)) {
                        // Update vendor profile picture in the database
                        $vendorId = $_SESSION['vendor_id'];
                        $sql = "UPDATE vendors SET profile_picture = ? WHERE id = ?";
                        $stmt = $pdo->prepare($sql);
                        if ($stmt->execute([$fileNameNew, $vendorId])) {
                            header("Location: dashboard.php"); // Redirect back to dashboard
                            exit();
                        } else {
                            echo "<div class='alert alert-danger'>Failed to update database!</div>";
                        }
                    } else {
                        echo "<div class='alert alert-danger'>Failed to move uploaded file!</div>";
                    }
                } else {
                    echo "<div class='alert alert-danger'>Invalid file type!</div>";
                }
            } else {
                echo "<div class='alert alert-danger'>File size exceeds 2 MB limit!</div>";
            }
        } else {
            echo "<div class='alert alert-danger'>Error uploading file!</div>";
        }
    }
} else {
    header("Location: dashboard.php");
}
?>
