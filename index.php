<?php
require 'includes/db.php';
session_start();
$message = ""; // Variable to hold messages

// Initialize login attempts tracking
if (!isset($_SESSION['login_attempts'])) {
    $_SESSION['login_attempts'] = 0;
}

// Handle form submissions
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if (isset($_POST['register'])) {
        // Registration logic for vendors
        $company_name = htmlspecialchars($_POST['company_name']);
        $authorized_signatory = htmlspecialchars($_POST['authorized_signatory']);
        $phone_number = htmlspecialchars($_POST['phone_number']);
        $address = htmlspecialchars($_POST['address']);
        $vendor_field = htmlspecialchars($_POST['vendor_field']);
        $email = filter_var($_POST['email'], FILTER_SANITIZE_EMAIL);
        $experience = htmlspecialchars($_POST['experience']);
        $password = $_POST['password'];

        // Check for strong password
        if (strlen($password) < 8 || !preg_match('/[A-Z]/', $password) || !preg_match('/[a-z]/', $password) || !preg_match('/[0-9]/', $password)) {
            $message = "<div class='alert alert-danger'>Password must be at least 8 characters long, contain a number, an uppercase, and a lowercase letter.</div>";
        } else {
            $hashed_password = password_hash($password, PASSWORD_DEFAULT);

            // SQL for vendor registration
            $sql = "INSERT INTO vendors (company_name, authorized_signatory, phone_number, address, vendor_field, email, experience, password, verified) 
                    VALUES (?, ?, ?, ?, ?, ?, ?, ?, 0)";
            
            $stmt = $pdo->prepare($sql);
            if ($stmt->execute([$company_name, $authorized_signatory, $phone_number, $address, $vendor_field, $email, $experience, $hashed_password])) {
                $message = "<div class='alert alert-success'>Registration successful!</div>";
            } else {
                error_log("Database error: " . $stmt->errorInfo()[2]);
                $message = "<div class='alert alert-danger'>Registration failed! Please try again later.</div>";
            }
        }
    } elseif (isset($_POST['login'])) {
        // Login logic
        $email = filter_var($_POST['email'], FILTER_SANITIZE_STRING);
        $password = $_POST['password'];

        // Check if the user is a vendor
        $sql = "SELECT * FROM vendors WHERE email = ?";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([$email]);
        $vendor = $stmt->fetch();

        // Check if the user is a procurement user
        $procurement_sql = "SELECT * FROM procurement_users WHERE username = ?";
        $procurement_stmt = $pdo->prepare($procurement_sql);
        $procurement_stmt->execute([$email]);
        $procurement_user = $procurement_stmt->fetch();
        $_SESSION['procurement_user_role'] = $procurement_user['role'];
        if ($vendor) {
            if ($vendor['account_locked'] && (new DateTime() < (new DateTime($vendor['lock_time']))->modify('+30 minutes'))) {
                $message = "<div class='alert alert-danger'>Account locked. Too many failed attempts! Try again later.</div>";
            } elseif (password_verify($password, $vendor['password'])) {
                $_SESSION['vendor_id'] = $vendor['id'];
                session_regenerate_id(true);
                $sql = "UPDATE vendors SET login_attempts = 0, account_locked = 0, lock_time = NULL WHERE id = ?";
                $pdo->prepare($sql)->execute([$vendor['id']]);
                header("Location: dashboard.php");
                exit();
            } else {
                $sql = "UPDATE vendors SET login_attempts = login_attempts + 1 WHERE id = ?";
                $pdo->prepare($sql)->execute([$vendor['id']]);
                if ($vendor['login_attempts'] >= 4) {
                    $sql = "UPDATE vendors SET account_locked = 1, lock_time = NOW() WHERE id = ?";
                    $pdo->prepare($sql)->execute([$vendor['id']]);
                    $message = "<div class='alert alert-danger'>Account locked. Too many failed attempts!</div>";
                } else {
                    $message = "<div class='alert alert-danger'>Invalid email or password!</div>";
                }
            }
        } elseif ($procurement_user) {
            if ($procurement_user['account_locked'] && (new DateTime() < (new DateTime($procurement_user['lock_time']))->modify('+30 minutes'))) {
                $message = "<div class='alert alert-danger'>Account locked. Too many failed attempts! Try again later.</div>";
            } elseif (password_verify($password, $procurement_user['password'])) {
                $_SESSION['procurement_user_id'] = $procurement_user['id'];
                session_regenerate_id(true);
                $sql = "UPDATE procurement_users SET login_attempts = 0, account_locked = 0, lock_time = NULL WHERE id = ?";
                $pdo->prepare($sql)->execute([$procurement_user['id']]);
                header("Location: pdashboard.php");
                exit();
            } else {
                $sql = "UPDATE procurement_users SET login_attempts = login_attempts + 1 WHERE id = ?";
                $pdo->prepare($sql)->execute([$procurement_user['id']]);
                if ($procurement_user['login_attempts'] >= 4) {
                    $sql = "UPDATE procurement_users SET account_locked = 1, lock_time = NOW() WHERE id = ?";
                    $pdo->prepare($sql)->execute([$procurement_user['id']]);
                    $message = "<div class='alert alert-danger'>Account locked. Too many failed attempts!</div>";
                } else {
                    $message = "<div class='alert alert-danger'>Invalid username or password!</div>";
                }
            }
        } else {
            $message = "<div class='alert alert-danger'>Invalid email/username or password!</div>";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Vendor & Procurement Auth</title>
    <link rel="stylesheet" href="./assets/css/login.css">
    <link rel="stylesheet" href="assets/css/bootstrap.min.css">
</head>
<body>
<div class="form-container  mt-5">
        <div class="form-box ">
            <h2 class="text-center">Login</h2>
            <?= $message; ?>
            <form action="index.php" method="POST">
                <input type="hidden" name="login" value="1">
                <div class="form-group">
                    <label for="email">Email/Username</label>
                    <input type="text" class="form-control" placeholder="Enter your email" name="email" required id="email">
                </div>
                <div class="form-group">
                    <label for="password">Password</label>
                    <input type="password" class="form-control" placeholder="Enter your password" name="password" required id="password">
                </div>
                <button type="submit" class="btn btn-primary btn-block">Login</button>
            </form>
            <div class="text-center mt-3">
                <p>Don't have an account? <a href="register.php" data-bs-toggle="modal" data-bs-target="#registerModal">Register here</a></p>
            </div>
        </div>
    </div>

    <!-- Registration Modal -->
    <!-- <div class="modal fade" id="registerModal" tabindex="-1" role="dialog" aria-labelledby="registerModalLabel" aria-hidden="true">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="registerModalLabel">Vendor Registration</h5>
                    <button type="button" class="close" data-bs-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <form action="index.php" method="POST">
                        <input type="hidden" name="register" value="1">
                        <div class="form-group">
                            <label for="company_name">Company/Vendor Name</label>
                            <input type="text" class="form-control" name="company_name" required>
                        </div>
                        <div class="form-group">
                            <label for="authorized_signatory">Authorized Signatory</label>
                            <input type="text" class="form-control" name="authorized_signatory" required>
                        </div>
                        <div class="form-group">
                            <label for="phone_number">Phone Number</label>
                            <input type="text" class="form-control" name="phone_number" required>
                        </div>
                        <div class="form-group">
                            <label for="address">Address</label>
                            <textarea class="form-control" name="address" required></textarea>
                        </div>
                        <div class="form-group">
                            <label for="vendor_field">Vendor Field</label>
                            <select class="form-control" name="vendor_field" required>
                                <option value="Suppliers">Suppliers</option>
                                <option value="Consultant">Consultant</option>
                                <option value="Contractors">Contractors</option>
                                <option value="Sub-contractor">Sub-contractor</option>
                            </select>
                        </div>
                        <div class="form-group">
                            <label for="email">Email (optional)</label>
                            <input type="email" class="form-control" name="email">
                        </div>
                        <div class="form-group">
                            <label for="experience">Experience (optional)</label>
                            <input type="text" class="form-control" name="experience">
                        </div>
                        <div class="form-group">
                            <label for="password">Password</label>
                            <input type="password" class="form-control" name="password" required>
                        </div>
                        <button type="submit" class="btn btn-primary btn-block">Register</button>
                    </form>
                </div>
            </div>
        </div>
    </div> -->

    <script src="assets/js/jquery-3.7.1.min.js"></script>
    <script src="assets/js/bootstrap.bundle.min.js"></script>
</body>
</html>
