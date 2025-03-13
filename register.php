<?php
require 'includes/db.php'; // Include your database connection
$message = ""; // Variable to hold messages

// Handle form submissions
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Registration logic
    $company_name = $_POST['company_name'];
    $authorized_signatory = $_POST['authorized_signatory'];
    $phone_number = $_POST['phone_number'];
    $address = $_POST['address'];
    $vendor_field = $_POST['vendor_field'];
    $email = $_POST['email'];
    $experience = $_POST['experience'];
    $password = password_hash($_POST['password'], PASSWORD_DEFAULT);

    $sql = "INSERT INTO vendors (company_name, authorized_signatory, phone_number, address, vendor_field, email, experience, password) 
            VALUES (?, ?, ?, ?, ?, ?, ?, ?)";
    
    $stmt = $pdo->prepare($sql);

    if ($stmt->execute([$company_name, $authorized_signatory, $phone_number, $address, $vendor_field, $email, $experience, $password])) {
        $message = "<div class='alert alert-success'>Registration successful!</div>";
    } else {
        $message = "<div class='alert alert-danger'>Registration failed!</div>";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Vendor Registration</title>
    <link rel="stylesheet" href="assets/css/bootstrap.min.css">
    <link rel="stylesheet" href="./assets/css/regester.css">    
</head>
<body>
<div class="container mt-1">
        <div class="registration-container">
            <h2>Vendor Registration</h2>
            <?= $message; ?>
            <form action="register.php" method="POST">
                <div class="form-group">
                    <label for="company_name">Company/Vendor Name</label>
                    <input type="text" class="form-control" name="company_name" required id="company_name">
                </div>
                <div class="form-group">
                    <label for="authorized_signatory">Authorized Signatory</label>
                    <input type="text" class="form-control" name="authorized_signatory" required id="authorized_signatory">
                </div>
                <div class="form-group">
                    <label for="phone_number">Phone Number</label>
                    <input type="text" class="form-control" name="phone_number" required id="phone_number">
                </div>
                <div class="form-group">
                    <label for="address">Address</label>
                    <textarea class="form-control" name="address" required id="address"></textarea>
                </div>
                <div class="form-group">
                    <label for="vendor_field">Vendor Field</label>
                    <select class="form-control" name="vendor_field" required id="vendor_field">
                        <option value="Suppliers">Suppliers</option>
                        <option value="Consultant">Consultant</option>
                        <option value="Contractors">Contractors</option>
                        <option value="Sub-contractor">Sub-contractor</option>
                    </select>
                </div>
                <div class="form-group">
                    <label for="email">Email (optional)</label>
                    <input type="email" class="form-control" name="email" id="email">
                </div>
                <div class="form-group">
                    <label for="experience">Experience (optional)</label>
                    <input type="text" class="form-control" name="experience" id="experience">
                </div>
                <div class="form-group">
                    <label for="password">Password</label>
                    <input type="password" class="form-control" name="password" required id="password">
                </div>
                <button type="submit" class="btn btn-primary btn-block">Register</button>
                <a href="index.php">Do you have account ?</a>
            </form>

        </div>
                                     
    </div>
</body>
</html>