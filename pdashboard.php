<?php
// Secure session settings
ini_set('session.cookie_secure', 1);
ini_set('session.cookie_httponly', 1);
ini_set('session.use_strict_mode', 1);

require 'includes/db.php'; // Database connection
session_start(); // Start session
require 'includes/auth.php';


// OOO======================
$n = 10;
function getRandomString($n) {
    return bin2hex(random_bytes($n / 2));
}



// Session regeneration every 30 minutes for security
if (!isset($_SESSION['CREATED'])) {
    $_SESSION['CREATED'] = time();
} elseif (time() - $_SESSION['CREATED'] > 1800) { // 1800 seconds = 30 minutes
    session_regenerate_id(true);
    $_SESSION['CREATED'] = time();
}

// Generate CSRF token if not set
if (!isset($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

$sql3="SELECT * FROM procurement_users ";
$stmt3 = $pdo->prepare($sql3);
$stmt3->execute();
$Alldatauser = $stmt3->fetchAll(PDO::FETCH_ASSOC);


// Fetch procurement user data and vendor groups
$procurement_user_id = $_SESSION['procurement_user_id'];
$sql = "SELECT * FROM procurement_users WHERE id = ?";
$stmt = $pdo->prepare($sql);
$stmt->execute([$procurement_user_id]);
$procurement_user = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$procurement_user) {
    header("Location: index.php");
    exit();
}

// Fetch verified vendors for the dropdown
$sql = "SELECT * FROM vendors WHERE verified = 1";
$stmt = $pdo->prepare($sql);
$stmt->execute();
$verified_vendors = $stmt->fetchAll(PDO::FETCH_ASSOC);


// OOO========================================
$sql = "SELECT * FROM vendors WHERE verified = 1";
$stmt = $pdo->prepare($sql);
$stmt->execute();
$allVendorVerified = $stmt->fetchAll(PDO::FETCH_ASSOC);
// $rowcount=mysql_num_rows($allVendorVerified);
$VendorcountV=1;
foreach ($allVendorVerified as $a){
    $VendorcountV++;
}
// OOO========================================unverified
$sql4 = "SELECT * FROM vendors WHERE verified = 0";
$stmt4 = $pdo->prepare($sql4);
$stmt4->execute();
$allVendorVerified = $stmt4->fetchAll(PDO::FETCH_ASSOC);
// $rowcount=mysql_num_rows($allVendorVerified);
$VendorcountunV=1;
foreach ($allVendorVerified as $a){
    $VendorcountunV++;
}

// <!-- OOO============================ -->
 if (isset($_GET['page']) && $_GET['page'] === 'vendor2_details' && isset($_GET['vendor_id'])){
    $authorized_signatory =$_POST['authorized_signatory'];
    $phone_number =$_POST['phone_number'];
    $address =$_POST['address'];
    $email = $_POST['email'];
    $experience = $_POST['experience'];
    $group = $_POST['group_id'];
    $verified=$_POST['verified'];
    
    $vendor_id = $_GET['vendor_id'];


    $sql = "UPDATE vendors SET authorized_signatory = ?, phone_number = ?, address = ?, email = ?, experience = ?, group_id = ? , verified = ? WHERE id = ? ";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$authorized_signatory, $phone_number, $address,$email, $experience,$group,$verified,$vendor_id]);
 
    header("Location:pdashboard.php?page=vendor_details&vendor_id=".$vendor_id);
    exit();


    }


// Fetch agreements for the current user
$sql = "SELECT * FROM agreements WHERE created_by = ?"; // Modify as needed to filter
$stmt = $pdo->prepare($sql);
$stmt->execute([$procurement_user_id]);
$agreements = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Fetch vendor groups for dropdown
$sql = "SELECT * FROM vendor_groups";
$stmt = $pdo->prepare($sql);
$stmt->execute();
$vendor_groups = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Initialize message
$message = "";

// Handle POST requests
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Handle Password Change
    if (isset($_POST['edit_password'])) {
        // CSRF Token Validation
        if (!hash_equals($_SESSION['csrf_token'], $_POST['csrf_token'])) {
            die('CSRF token validation failed.');
        }

        $updated_password = $_POST['password'] ?? '';
        $password_confirmation = $_POST['password_confirmation'] ?? '';

        // Proceed only if password fields are filled
        if (!empty($updated_password) || !empty($password_confirmation)) {
            // Check if both fields are filled
            if (empty($updated_password) || empty($password_confirmation)) {
                $message = "<div class='alert alert-danger'>Both password fields are required to change the password.</div>";
            } elseif ($updated_password !== $password_confirmation) {
                $message = "<div class='alert alert-danger'>Passwords do not match.</div>";
            } else {
                // Update password
                $sql = "UPDATE procurement_users SET password = ? WHERE id = ?";
                $stmt = $pdo->prepare($sql);
                try {
                    $stmt->execute([password_hash($updated_password, PASSWORD_DEFAULT), $procurement_user_id]);
                    $message = "<div class='alert alert-success'>Password updated successfully!</div>";
                } catch (PDOException $e) {
                    $message = "<div class='alert alert-danger'>Failed to update password! Error: " . htmlspecialchars($e->getMessage()) . "</div>";
                    error_log($e->getMessage());
                }
            }
        } else {
            // No password fields filled; do nothing or provide a message
            $message = "<div class='alert alert-info'>No changes made to the password.</div>";
        }
    }

    // Handle Profile Picture Upload
    if (isset($_POST['upload_picture'])) {
        // CSRF Token Validation
        if (!hash_equals($_SESSION['csrf_token'], $_POST['csrf_token'])) {
            die('CSRF token validation failed.');
        }

        if (isset($_FILES['profile_picture']) && $_FILES['profile_picture']['error'] == UPLOAD_ERR_OK) {
            $target_dir = "uploads/";
            $target_file = $target_dir . basename($procurement_user['username']) . '.jpg';
            $uploadOk = 1;
            $imageFileType = strtolower(pathinfo($target_file, PATHINFO_EXTENSION));

            // Validate if file is an image
            $check = getimagesize($_FILES["profile_picture"]["tmp_name"]);
            if ($check === false) {
                $message = "<div class='alert alert-danger'>File is not an image.</div>";
                $uploadOk = 0;
            }

            // Validate file size (2MB limit)
            if ($_FILES["profile_picture"]["size"] > 2000000) {
                $message = "<div class='alert alert-danger'>Sorry, your file is too large.</div>";
                $uploadOk = 0;
            }

            // Allow only specific file types
            if (!in_array($imageFileType, ['jpg', 'jpeg', 'png'])) {
                $message = "<div class='alert alert-danger'>Sorry, only JPG, JPEG, and PNG files are allowed.</div>";
                $uploadOk = 0;
            }

            // Check if upload is valid and move the file
            if ($uploadOk) {
                if (move_uploaded_file($_FILES["profile_picture"]["tmp_name"], $target_file)) {
                    $sql = "UPDATE procurement_users SET profile_picture = ? WHERE id = ?";
                    $stmt = $pdo->prepare($sql);
                    try {
                        $stmt->execute([$target_file, $procurement_user_id]);
                        $message .= "<div class='alert alert-success'>Profile picture updated successfully!</div>";
                    } catch (PDOException $e) {
                        $message .= "<div class='alert alert-danger'>Failed to update profile picture! Error: " . htmlspecialchars($e->getMessage()) . "</div>";
                        error_log($e->getMessage());
                    }
                } else {
                    $message .= "<div class='alert alert-danger'>There was an error uploading your file.</div>";
                }
            }
        } else {
            $message .= "<div class='alert alert-danger'>No file uploaded or there was an upload error.</div>";
        }
    }

    // Handle Update Agreement
    if (isset($_POST['update_agreement'])) {
        // CSRF Token Validation
        if (!hash_equals($_SESSION['csrf_token'], $_POST['csrf_token'])) {
            die('CSRF token validation failed.');
        }

        // Retrieve and sanitize form inputs
        $agreement_id = $_POST['edit_agreement_id'] ?? '';
        $vendor_id = $_POST['vendor_id'] ?? '';
        $contract_name = trim($_POST['contract_name'] ?? '');
        $signed_date = $_POST['signed_date'] ?? '';
        $expired_date = $_POST['expired_date'] ?? '';

        // Validate inputs
        if (empty($agreement_id) || empty($vendor_id) || empty($contract_name) || empty($signed_date) || empty($expired_date)) {
            $message = "<div class='alert alert-danger'>Please fill in all required fields.</div>";
        } else {
            // Optionally, validate dates and other inputs here

            // Update the agreement in the database
            $sql = "UPDATE agreements SET vendor_id = ?, contract_name = ?, signed_date = ?, expired_date = ? WHERE id = ? AND created_by = ?";
            $stmt = $pdo->prepare($sql);
            try {
                $stmt->execute([$vendor_id, $contract_name, $signed_date, $expired_date, $agreement_id, $procurement_user_id]);
                $message = "<div class='alert alert-success'>Agreement updated successfully!</div>";
            } catch (PDOException $e) {
                $message = "<div class='alert alert-danger'>Failed to update agreement! Error: " . htmlspecialchars($e->getMessage()) . "</div>";
                error_log($e->getMessage());
            }
        }
    }

// OOO==================================================================================================
if (isset($_POST['update_agreement2'])) {

    $user_id=$_POST['user_id'];
    $role=$_POST['role'];

        $sql = "UPDATE procurement_users SET role = ? WHERE id = ? ";
        $stmt = $pdo->prepare($sql);
        try {
            $stmt->execute([$role,$user_id]);
            $message = "<div class='alert alert-success'>Agreement updated successfully!</div>";
        } catch (PDOException $e) {
            $message = "<div class='alert alert-danger'>Failed to update agreement! Error: " . htmlspecialchars($e->getMessage()) . "</div>";
            error_log($e->getMessage());
        }
    }
if (isset($_POST['update_agreement3'])) {
    
    $username = $_POST['username'];
    $role = $_POST['role'];
    $password = $_POST['password'];

    // Validate input (basic validation)
    if (empty($username) || empty($role) || empty($password) ) {
    } else {
        // Hash the password before storing it
        $hashed_password = password_hash($password, PASSWORD_DEFAULT);

        // Insert vendor data into the database
        $sql = "INSERT INTO procurement_users (username, password, role, created_at) VALUES (?, ?, ?, NOW())";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([$username,$hashed_password, $role]);
}
}




    // Handling Edit Vendor Request
    if (isset($_POST['edit_vendor'])) {
        $vendor_id = $_POST['vendor_id'];
        $company_name = $_POST['company_name'];
        $authorized_signatory = $_POST['authorized_signatory'];
        $phone_number = $_POST['phone_number'];
        $email = $_POST['email'];
        $address = $_POST['address'];
        $vendor_field = $_POST['vendor_field'];
        $experience = $_POST['experience'];

        // Update vendor details
        $sql = "UPDATE vendors SET company_name = ?, authorized_signatory = ?, phone_number = ?, email = ?, address = ?, vendor_field = ?, experience = ? WHERE id = ?";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([$company_name, $authorized_signatory, $phone_number, $email, $address, $vendor_field, $experience, $vendor_id]);

        header("Location: pdashboard.php?page=unverified_vendors");
        exit();
    }

    // Handling Verify Vendor Request
    if (isset($_POST['verify_vendor_id'])) {
        $vendor_id = $_POST['verify_vendor_id'];

        // Update the vendor's status to verified
        $sql = "UPDATE vendors SET verified = 1 WHERE id = ?";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([$vendor_id]);

        header("Location: pdashboard.php?page=unverified_vendors");
        exit();
    }

    // Handling Delete Agreement Form Submission
    if (isset($_POST['delete_agreement_id'])) {
        $agreement_id = $_POST['delete_agreement_id'];

        // Validate input
        if (empty($agreement_id)) {
            $message = "<div class='alert alert-danger'>Invalid agreement ID.</div>";
        } else {
            // Delete the agreement from the database
            $sql = "DELETE FROM agreements WHERE id = ?";
            $stmt = $pdo->prepare($sql);
            try {
                $stmt->execute([$agreement_id]);
                $message = "<div class='alert alert-success'>Agreement deleted successfully!</div>";
            } catch (PDOException $e) {
                $message = "<div class='alert alert-danger'>Failed to delete agreement! Error: " . htmlspecialchars($e->getMessage()) . "</div>";
                error_log($e->getMessage()); // Log error information
            }
        }
    }
    // OOO ===========================================================================================================
    if (isset($_POST['delete_user_id'])) {
        $user_id = $_POST['delete_user_id'];

        // Validate input
        if (empty($user_id)) {
            $message = "<div class='alert alert-danger'>Invalid User ID.</div>";
        } else {
            // Delete the agreement from the database
            $sql = "DELETE FROM procurement_users WHERE id = ?";
            $stmt = $pdo->prepare($sql);
            try {
                $stmt->execute([$user_id]);
                $message = "<div class='alert alert-success'>User deleted successfully!</div>";
            } catch (PDOException $e) {
                $message = "<div class='alert alert-danger'>Failed to delete User! Error: " . htmlspecialchars($e->getMessage()) . "</div>";
                error_log($e->getMessage()); // Log error information
            }
        }
    }
}

// Fetch unverified vendors only if the user is on the unverified vendors page
if (isset($_GET['page']) && $_GET['page'] === 'unverified_vendors') {
    // Pagination variables
    $limit = $VendorcountunV; // Number of vendors per page
    $offset = 0; // Offset for SQL query

    // Check if a page number is set
    if (isset($_GET['page_number']) && is_numeric($_GET['page_number'])) {
        $current_page = (int)$_GET['page_number'];
        $offset = ($current_page - 1) * $limit; // Calculate offset
    } else {
        $current_page = 1; // Default to the first page
    }

    // Group filter (if needed)
    $group_filter = isset($_GET['group_sort']) ? $_GET['group_sort'] : '';

    // Base SQL for unverified vendor count and vendor selection
    $sql = "SELECT COUNT(*) FROM vendors WHERE verified = 0"; // Only show unverified vendors
    $vendor_sql = "SELECT * FROM vendors WHERE verified = 0"; // Only select unverified vendors
    $params = [];

    // Add group filter if selected
    if (!empty($group_filter)) {
        $sql .= " AND group_id = :group_id";
        $vendor_sql .= " AND group_id = :group_id";
        $params[':group_id'] = $group_filter;
    }

    // Pagination for vendor selection
    $vendor_sql .= " LIMIT :limit OFFSET :offset";

    // Fetch total number of unverified vendors for pagination
    $stmt = $pdo->prepare($sql);
    foreach ($params as $key => &$val) {
        $stmt->bindParam($key, $val);
    }
    $stmt->execute();
    $total_vendors = $stmt->fetchColumn(); // Get total count
    $total_pages = ceil($total_vendors / $limit); // Calculate total pages

    // Fetch unverified vendors for the current page
    $stmt = $pdo->prepare($vendor_sql);
    foreach ($params as $key => &$val) {
        $stmt->bindParam($key, $val);
    }
    $stmt->bindParam(':limit', $limit, PDO::PARAM_INT);
    $stmt->bindParam(':offset', $offset, PDO::PARAM_INT);

    // Execute the query
    $stmt->execute();
    $unverified_vendors = $stmt->fetchAll(PDO::FETCH_ASSOC); // Fetch unverified vendors for the current page
}

// Fetch verified vendors only if the user is on the vendors list page
if (isset($_GET['page']) && $_GET['page'] === 'vendors_list') {
    // Pagination variables
    $limit = $VendorcountV; // Number of vendors per page
    $offset = 0; // Offset for SQL query

    // Check if a page number is set
    if (isset($_GET['page_number']) && is_numeric($_GET['page_number'])) {
        $current_page = (int)$_GET['page_number'];
        $offset = ($current_page - 1) * $limit; // Calculate offset
    } else {
        $current_page = 1; // Default to the first page
    }

    // Group filter
    $group_filter = isset($_GET['group_sort']) ? $_GET['group_sort'] : '';

    // Base SQL for vendor count and vendor selection (show only verified vendors)
    $sql = "SELECT COUNT(*) FROM vendors WHERE verified = 1"; // Only show verified vendors
    $vendor_sql = "SELECT * FROM vendors WHERE verified = 1"; // Only select verified vendors
    $params = [];

    // Add group filter if selected
    if (!empty($group_filter)) {
        $sql .= " AND group_id = :group_id";
        $vendor_sql .= " AND group_id = :group_id";
        $params[':group_id'] = $group_filter;
    }

    // Pagination for vendor selection
    $vendor_sql .= " LIMIT :limit OFFSET :offset";

    // Fetch total number of verified vendors for pagination
    $stmt = $pdo->prepare($sql);
    foreach ($params as $key => &$val) {
        $stmt->bindParam($key, $val);
    }
    $stmt->execute();
    $total_vendors = $stmt->fetchColumn(); // Get total count
    $total_pages = ceil($total_vendors / $limit); // Calculate total pages

    // Fetch verified vendors for the current page
    $stmt = $pdo->prepare($vendor_sql);
    foreach ($params as $key => &$val) {
        $stmt->bindParam($key, $val);
    }
    $stmt->bindParam(':limit', $limit, PDO::PARAM_INT);
    $stmt->bindParam(':offset', $offset, PDO::PARAM_INT);

    // Execute the query
    $stmt->execute();
    $vendors = $stmt->fetchAll(PDO::FETCH_ASSOC); // Fetch verified vendors for the current page
}

if (isset($_GET['page']) && $_GET['page'] === 'add_agreement') {
    $vendor_id = $_POST['vendor_id'] ?? '  ';
    $contract_name = trim($_POST['contract_name'] ?? '  ');
    $signed_date = $_POST['signed_date'] ?? '  ';
    $expired_date = $_POST['expired_date'] ?? '  ';

    // Validate input
    if (empty($vendor_id) || empty($contract_name) || empty($signed_date) || empty($expired_date)) {
    } else {
        // Insert agreement into the database
        $sql = "INSERT INTO agreements (vendor_id, contract_name, signed_date, expired_date, created_by) VALUES (?, ?, ?, ?, ?)";
        $stmt = $pdo->prepare($sql);

        try {
            $stmt->execute([$vendor_id, $contract_name, $signed_date, $expired_date, $procurement_user_id]);
            $message = "<div class='alert alert-success'>Agreement added successfully!</div>";
        } catch (PDOException $e) {
            $message = "<div class='alert alert-danger'>Failed to add agreement! Error: " . htmlspecialchars($e->getMessage()) . "</div>";
            error_log($e->getMessage());
        }
    }
}
// OOO=====================مارجعة=====================================
if (isset($_GET['page']) && $_GET['page'] === 'new_group') {
    $groubname = $_POST['group_name']??'';
    $desciption = $_POST['description']??'';

        $sql9 = "INSERT INTO vendor_groups (group_name, description) VALUES (?, ?)";
        $stmt9 = $pdo->prepare($sql9);
        if (empty($groubname) || empty($desciption)) {
        } else {
        try {
            $stmt9->execute([$groubname, $desciption]);
            $message = "<div class='alert alert-success'>Group added successfully!</div>";
        } catch (PDOException $e) {
            $message = "<div class='alert alert-danger'>Failed to create gruop! Error: " . htmlspecialchars($e->getMessage()) . "</div>";
            error_log($e->getMessage());
        }
    }
}

if (isset($_GET['page']) && $_GET['page'] === 'list_agreement') {
    $procurement_user_id = $_SESSION['procurement_user_id'];

    // Modify the SQL query to join with the vendors table
    $sql = "SELECT a.*, v.company_name 
            FROM agreements a
            JOIN vendors v ON a.vendor_id = v.id 
            WHERE a.created_by = ?";

    $stmt = $pdo->prepare($sql);
    $stmt->execute([$procurement_user_id]);
    $agreements = $stmt->fetchAll(PDO::FETCH_ASSOC);
}



// Handle Delete Agreement Form Submission
if (isset($_POST['delete_agreement_id'])) {
    $agreement_id = $_POST['delete_agreement_id'];

    // Validate input
    if (empty($agreement_id)) {
        $message = "<div class='alert alert-danger'>Invalid agreement ID.</div>";
    } else {
        // Delete the agreement from the database
        $sql = "DELETE FROM agreements WHERE id = ?";
        $stmt = $pdo->prepare($sql);
        try {
            $stmt->execute([$agreement_id]);
            $message = "<div class='alert alert-success'>Agreement deleted successfully!</div>";
        } catch (PDOException $e) {
            $message = "<div class='alert alert-danger'>Failed to delete agreement! Error: " . htmlspecialchars($e->getMessage()) . "</div>";
            error_log($e->getMessage()); // Log error information
        }
    }
}

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Procurement Dashboard</title>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js" integrity="sha384-..." crossorigin="anonymous"></script>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Roboto:wght@400;700&display=swap" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css"> <!-- Include Font Awesome -->
    <link rel="stylesheet" href="assets/css/style.css"> <!-- Link to your external CSS file -->
</head>
<body>

<img src="<?= htmlspecialchars($procurement_user['profile_picture'] ?? 'assets/images/Vendors/john.jpg'); ?>" 
    alt="Profile Picture" class="profile-picture-toggle d-md-none" onclick="toggleSidebar()">
<div class="sidebar">
<span class="close-btn" onclick="toggleSidebar()">&times;</span>
    <div class="text-center mb-4">
        <img src="<?= htmlspecialchars($procurement_user['profile_picture'] ?? 'assets/images/Procurement/john.jpg'); ?>" 
             alt="Profile Picture" 
             class="profile-picture">
        <h4><?= htmlspecialchars($procurement_user['username']); ?></h4>
    </div>
    <ul class="nav flex-column nav-pills">
        <li class="nav-item">
            <a class="nav-link" href="?page=vendors_list"><i class="fas fa-users me-2"></i> Vendors Management</a>
        </li>
        <li class="nav-item">
            <a class="nav-link" href="?page=unverified_vendors"><i class="fas fa-tasks me-2"></i>Unverified_vendors</a>
        </li>

        
            <li class="nav-item">
                
                <li class="nav-item">
                    <a class="nav-link" href="?page=list_agreement"><i class="fas fa-file-signature me-2"></i> List Agreement</a>
                </li>
                <a class="nav-link" href="?page=add_agreement"><i class="fas fa-file-signature me-2"></i> Add Agreement</a>
            </li>
    
                <li class="nav-item">
                <a class="nav-link" href="?page=new_group"><i class="fas fa-file-signature me-2"></i>Create new group</a>
            </li>
       
        <li class="nav-item">
            <a class="nav-link" href="?page=update_profile"><i class="fas fa-user-edit me-2"></i> Update Profile</a>
        </li>

        <!--================== OOO------========================================================================================================================================== -->
       <?php if($_SESSION['procurement_user_role'] =="admin"):?>
        <li class="nav-item">
            <a class="nav-link text-danger" href="?page=requests"> Admins and Users</a>
        </li>
        <?php endif;?>
        <!--------------------------------------------------------------------------------------------------------------  -->
        <li class="nav-item">
            <a class="nav-link text-danger" href="logout.php"><i class="fas fa-sign-out-alt me-2"></i> Logout</a>
        </li>
    </ul>
</div>

<div class="content"  > 
 <?php if (isset($_GET['page']) && $_GET['page'] === 'list_agreement'): ?>
    <h2 class="text-center">Agreements List</h2>
    <?= isset($message) ? $message : ''; ?> <!-- Display messages -->

    

    <!-- Search Form -->
    <form action="pdashboard.php?page=list_agreement" method="GET" class="mb-4">
        <input type="hidden" name="page" value="list_agreement">
        <div class="input-group">
            <input type="text" class="form-control" placeholder="Search agreements..." name="search" value="<?= htmlspecialchars($_GET['search'] ?? '') ?>">
            <button class="btn btn-primary" type="submit"><i class="fas fa-search"></i> Search</button>
        </div>
    </form>

    <table class="table  rounded-4" style="width:100%; background-color: antiquewhite;">
        <thead>
            <tr>
                <th class="bg-black text-white">Vendor Name</th>
                <th class="bg-black text-white">Contract Name</th>
                <th class="bg-black text-white">Signed Date</th>
                <th class="bg-black text-white">Expired Date</th>
                <th class="bg-black text-white">Actions</th>
            </tr>
        </thead>
        <tbody class="rounded-5">
            <?php
            $search = $_GET['search'] ?? '';
            $search = "%$search%";
            $sql = "SELECT a.*, v.company_name 
                    FROM agreements a
                    JOIN vendors v ON a.vendor_id = v.id 
                    WHERE a.created_by = ? AND (v.company_name LIKE ? OR a.contract_name LIKE ?)";
            $stmt = $pdo->prepare($sql);
            $stmt->execute([$procurement_user_id, $search, $search]);
            $agreements = $stmt->fetchAll(PDO::FETCH_ASSOC);

            if (!empty($agreements)): 
                foreach ($agreements as $agreement): ?>
                    <tr>
                        <td><?= htmlspecialchars($agreement['company_name']); ?></td>
                        <td><?= htmlspecialchars($agreement['contract_name']); ?></td>
                        <td><?= htmlspecialchars($agreement['signed_date']); ?></td>
                        <td><?= htmlspecialchars($agreement['expired_date']); ?></td>
                        <td>
                        <button class="btn btn-primary btn-sm" onclick="openEditModal(<?= htmlspecialchars(json_encode($agreement), ENT_QUOTES, 'UTF-8'); ?>)">Edit</button>
                        <button class="btn btn-danger btn-sm" onclick="confirmDelete(<?= htmlspecialchars($agreement['id']); ?>)">Delete</button>
                        </td>
                    </tr>
                <?php endforeach; 
            else: ?>
                <tr>
                    <td colspan="5" class="text-center">No agreements found.</td>
                </tr>
            <?php endif; ?>
        </tbody>
    </table>
<?php endif; ?>

<!-- Edit Agreement Modal -->
<div class="modal fade" id="editAgreementModal" tabindex="-1" aria-labelledby="editAgreementModalLabel" aria-hidden="true">
  <div class="modal-dialog">
    <form id="editAgreementForm" method="POST" action="pdashboard.php?page=list_agreement">
      <div class="modal-content">
        <div class="modal-header">
          <h5 class="modal-title" id="editAgreementModalLabel">Edit Agreement</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>
        <div class="modal-body">
          <!-- CSRF Token -->
          <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token']; ?>">
          <!-- Agreement ID -->
          <input type="hidden" name="edit_agreement_id" id="edit_agreement_id">

          <div class="mb-3">
            <label for="edit_vendor_id" class="form-label">Vendor</label>
            <select name="vendor_id" id="edit_vendor_id" class="form-control" required>
              <option value="">Select Vendor</option>
              <?php foreach ($verified_vendors as $vendor): ?>
                <option value="<?= htmlspecialchars($vendor['id']); ?>"><?= htmlspecialchars($vendor['company_name']); ?></option>
              <?php endforeach; ?>
            </select>
          </div>

          <div class="mb-3">
            <label for="edit_contract_name" class="form-label">Contract Name</label>
            <input type="text" name="contract_name" id="edit_contract_name" class="form-control" required>
          </div>

          <div class="mb-3">
            <label for="edit_signed_date" class="form-label">Signed Date</label>
            <input type="date" name="signed_date" id="edit_signed_date" class="form-control" required>
          </div>

          <div class="mb-3">
            <label for="edit_expired_date" class="form-label">Expired Date</label>
            <input type="date" name="expired_date" id="edit_expired_date" class="form-control" required>
          </div>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
          <button type="submit" name="update_agreement" class="btn btn-primary">Save Changes</button>
        </div>
      </div>
    </form>
  </div>
</div>

<!-- Edit role ================================================================================================================ -->
<div class="modal fade" id="editAgreementModal2" tabindex="-1" aria-labelledby="editAgreementModal2Label" aria-hidden="true">
  <div class="modal-dialog">
    <form id="editAgreementForm" method="POST" action="pdashboard.php?page=requests">
      <div class="modal-content">
        <div class="modal-header">
          <h5 class="modal-title" id="editAgreementModal2Label">Edit Agreement</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>
        <div class="modal-body">
          <!-- CSRF Token -->
          <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token']; ?>">
          <!-- Agreement ID -->
          <input type="hidden" name="edit_agreement_id" id="edit_agreement_id">
          <div class="mb-3">
            <label for="user_id" class="form-label">User Name</label>
            <select name="user_id" id="edit_vendor_id2" class="form-control" required>
              
              <?php foreach ($Alldatauser as $user2): if($user2["id"]==$_SESSION['procurement_user_id'] || $user2["id"]==1) { continue; } ?>
                <option value="<?= htmlspecialchars($user2['id']); ?>"> <?= htmlspecialchars($user2['username']); ?>  </option>
              <?php endforeach; ?>
            </select>
          </div>
          <div class="mb-3">
            <label for="edit_contract_name" class="form-label">Role</label>
            <select name="role" id="role"><option value="admin">Admin<option value="user">User</option></option></select>
            
          </div>
        </div>
        
        <div class="modal-footer">
          <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
          <button type="submit" name="update_agreement2" class="btn btn-primary">Save Changes</button>
        </div>
      </div>
      
    </form>
  </div>
</div>
<div class="modal fade" id="editAgreementModal3" tabindex="-1" aria-labelledby="editAgreementModal3Label" aria-hidden="true">
  <div class="modal-dialog">
    <form id="editAgreementForm" method="POST" action="pdashboard.php?page=requests">
      <div class="modal-content">
        <div class="modal-header">
          <h5 class="modal-title" id="editAgreementModal3Label">Add Admin/User</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>
        <div class="modal-body">
          <!-- CSRF Token -->
          <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token']; ?>">
          <!-- Agreement ID -->
          
          <div class="mb-3">
            <label for="role" class="form-label">Role</label>
            <select name="role" id="role"><option value="admin">Admin<option value="user">User</option></option></select><br>

            <label for="username" class="form-label">User Name</label>
            <input type='text' name="username" id="addnew" class="form-control" required placeholder="Enter name">
              
            
            <label for="password" class="form-label">password</label>
            <input type='password' name="password" id="addnew" class="form-control" required>
              
          </div>
        </div>
        
        <div class="modal-footer">
          <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
          <button type="submit" name="update_agreement3" class="btn btn-primary">Save Changes</button>
        </div>
      </div>
      
    </form>
</div>
</div>

<script>

function openEditModal3(agreement) {
        // Parse the agreement data if it's a JSON string
        if (typeof agreement === 'string') {
            agreement = JSON.parse(agreement);
        }

        // Fill the form fields with the agreement data
        document.getElementById('edit_agreement_id').value = agreement.id;
        document.getElementById('edit_vendor_id2').value = agreement.vendor_id;
        document.getElementById('edit_contract_name').value = agreement.contract_name;
        document.getElementById('edit_signed_date').value = agreement.signed_date;
        document.getElementById('edit_expired_date').value = agreement.expired_date;

        // Show the modal
        var editModal = new bootstrap.Modal(document.getElementById('editAgreementModal3'));
        editModal.show();
}
    // Function to open the edit modal and pre-fill the form
    
    function openEditModal2(agreement) {
        // Parse the agreement data if it's a JSON string
        if (typeof agreement === 'string') {
            agreement = JSON.parse(agreement);
        }

        // Fill the form fields with the agreement data
        document.getElementById('edit_agreement_id').value = agreement.id;
        document.getElementById('edit_vendor_id2').value = agreement.vendor_id;
        document.getElementById('edit_contract_name').value = agreement.contract_name;
        document.getElementById('edit_signed_date').value = agreement.signed_date;
        document.getElementById('edit_expired_date').value = agreement.expired_date;

        // Show the modal
        var editModal = new bootstrap.Modal(document.getElementById('editAgreementModal2'));
        editModal.show();
    }
    
    function openEditModal(agreement) {
        // Parse the agreement data if it's a JSON string
        if (typeof agreement === 'string') {
            agreement = JSON.parse(agreement);
        }

        // Fill the form fields with the agreement data
        document.getElementById('edit_agreement_id').value = agreement.id;
        document.getElementById('edit_vendor_id').value = agreement.vendor_id;
        document.getElementById('edit_contract_name').value = agreement.contract_name;
        document.getElementById('edit_signed_date').value = agreement.signed_date;
        document.getElementById('edit_expired_date').value = agreement.expired_date;

        // Show the modal
        var editModal = new bootstrap.Modal(document.getElementById('editAgreementModal'));
        editModal.show();
    }

    // Function to confirm deletion with SweetAlert
    // OOO==================================================================================================
    function confirmDelete2(userId,username){
        // $sql="DELETE FROM procurement_users WHERE id=?;";
        // $stmt = $pdo->prepare($sql);
        // $stmt->execute([Ad_Ur_ID]);
         if (confirm(`Do you want remove ${username}?`)) {
            // إذا تم التأكيد، يتم إرسال طلب الحذف إلى خادم PHP باستخدام AJAX
            fetch(`delete_user.php?id=${userId}`, {
                method: 'GET'
            })
            .then(response => response.json())
            .then(data => {
                location.reload();
            })
            .catch(error => console.error('Error:', error));
        }
    }


   
</script>

<script>
// Function to confirm deletion with SweetAlert
function confirmDelete(agreementId) {
    Swal.fire({
        title: 'Are you sure?',
        text: "You are about to delete this agreement.",
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#3085d6',
        cancelButtonColor: '#d33',
        confirmButtonText: 'Yes, delete it!'
    }).then((result) => {
        if (result.isConfirmed) {
            // Submit the deletion form
            var form = document.createElement('form');
            form.method = 'POST';
            form.action = 'pdashboard.php?page=list_agreement';
            
            var input = document.createElement('input');
            input.type = 'hidden';
            input.name = 'delete_agreement_id'; // Name should match the PHP check
            input.value = agreementId;

            form.appendChild(input);
            document.body.appendChild(form);
            form.submit();
        }
    });
}
</script>
<script>
// Function to confirm deletion with SweetAlert OOO =======================================================================
function confirmDelete3(agreementId) {
    Swal.fire({
        title: 'Are you sure?',
        text: "You are about to delete this User.",
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#3085d6',
        cancelButtonColor: '#d33',
        confirmButtonText: 'Yes, delete it!'
    }).then((result) => {
        if (result.isConfirmed) {
            // Submit the deletion form
            var form = document.createElement('form');
            form.method = 'POST';
            form.action = 'pdashboard.php?page=requests';
            
            var input = document.createElement('input');
            input.type = 'hidden';
            input.name = 'delete_user_id'; // Name should match the PHP check
            input.value = agreementId;

            form.appendChild(input);
            document.body.appendChild(form);
            form.submit();
        }
    });
}
</script>


<?php if (isset($_GET['page']) && $_GET['page'] === 'add_agreement'): ?>
    <div class=" p-5  rounded-5" style="background-color: rgb(146, 147, 143); 
">
    <h2 class="text-center fw-bold" >Add Agreement</h2>
    
    <?= $message; ?>
    
    <form action="pdashboard.php?page=add_agreement" method="POST"  >
        <div class="form-group ">
            <label for="vendor_id" class="mt-2">Select Vendor:</label>
            <select name="vendor_id" class="form-control mt-2" required>
                <option value="">Select Vendor</option>
                <?php foreach ($verified_vendors as $vendor): ?>
                    <option value="<?= htmlspecialchars($vendor['id']); ?>">
                        <?= htmlspecialchars($vendor['company_name']); ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>

        <div class="form-group">
            <label for="contract_name" class="mt-2">Contract Name:</label>
            <input type="text" name="contract_name" class="form-control mt-2" required>
            <div class="invalid-feedback">Please provide a contract name.</div>
        </div>
        
        <div class="form-row d-flex">
            <div class="form-group col-md-6 mx-1">
                <label for="signed_date" class="mt-2">Signed Date:</label>
                <input type="date" name="signed_date" class="form-control mt-2" required>
                <div class="invalid-feedback">Please select a signed date.</div>
            </div>

            <div class="form-group col-md-6">
                <label for="expired_date " class="mt-2">Expired Date:</label>
                <input type="date" name="expired_date" class="form-control mt-2" required>
                <div class="invalid-feedback">Please select an expired date.</div>
            </div>
          </div>
       
        

        <div class="text-center mt-2">
            <button type="submit" class="btn btn-success mt-3" id="addAgreementButton">Add Agreement</button>
        </div>
    </form>
    </div>
   
    
</div>

<?php endif; ?>
<!-- OOO=================================================================================== -->
<?php if (isset($_GET['page']) && $_GET['page'] === 'new_group'): ?>
    <div class="p-5 rounded-5" style="background-color: rgb(146, 147, 143);">
    <h2 class="text-center fw-bold">Create new group</h2>
    
    <?= $message; ?>
    
    <form action="pdashboard.php?page=new_group" method="POST">
       
        <div class="form-group">
            <label for="contract_name" class="mt-3">Group Name:</label>
            <input type="text" name="group_name" class="form-control mt-3" required>
            <div class="invalid-feedback">Please provide a group name.</div>
        </div>
        <div class="form-group">
            <label for="contract_name" class="mt-3">Description:</label>
            <input type="text" name="description" class="form-control mt-3" required>
            <div class="invalid-feedback">Please provide a group name.</div>
        </div>

        <div class="text-center">
            <button type="submit" class="btn btn-success mt-3 w-100" id="addAgreementButton">Create</button>
        </div>
    </form>
    </div>
    
    </div>
<?php endif; ?>

<!-- OOO======================================================================================================================================================================= -->
<?php if (isset($_GET['page']) && $_GET['page'] === 'requests' && $_SESSION['procurement_user_role'] =="admin" ): ?>
    <h2 class="text-center fw-bold">  Admins & Users</h2>
        <?= isset($message) ? $message : ''; ?> <!-- Display messages -->

    <?php 
        $sql1 = "SELECT id,username,created_at,role FROM procurement_users ";
        $stmt1 = $pdo->prepare($sql1);
        $stmt1->execute();
        $ents = $stmt1->fetchAll(PDO::FETCH_ASSOC);
    
        if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_GET['page']) && $_GET['page'] === 'add_Admin_Users') {
            if ($procurement_user['role'] === 'admin') {
                // Get vendor data from POST request
                $username = $_POST['username'];
                $role = $_POST['role'];
                $password = $_POST['password'];
        
                // Validate input (basic validation)
                if (empty($username) || empty($role) || empty($password) ) {
                } else {
                    // Hash the password before storing it
                    $hashed_password = password_hash($password, PASSWORD_DEFAULT);
        
                    // Insert vendor data into the database
                    $sql = "INSERT INTO procurement_users (username, password, role, created_at) VALUES (?, ?, ?, NOW())";
                    $stmt = $pdo->prepare($sql);
                    try {
                        $stmt->execute([$username,$hashed_password, $role]);
                        $message = "<div class='alert alert-success'>Added successfully!</div>";
                    } catch (PDOException $e) {
                        $message = "<div class='alert alert-danger'>Failed to added ! Error: " . $e->getMessage() . "</div>";
                        error_log($e->getMessage()); // Log error information
                    }
                }
            } else {
                $message = "<div class='alert alert-danger'>Only admins can add vendors.</div>";
       }
}
    ?>
     <button class="btn btn-primary btn-sm w-25" onclick="openEditModal2(<?= htmlspecialchars(json_encode($ents), ENT_QUOTES, 'UTF-8'); ?>)">Edit</button>
     <button class="btn btn-primary btn-sm w-50" onclick="openEditModal3(<?= htmlspecialchars(json_encode($ents), ENT_QUOTES, 'UTF-8'); ?>)">Add New Admin or User</button>
     <table class="table table-striped table-bordered mt-3" style="width:100%;">
        <thead>
            <tr>
                <th class="bg-black text-white">ID</th>
                <th class="bg-black text-white">Username</th>
                <th class="bg-black text-white">Created At</th>
                <th class="bg-black text-white">Role</th>
                <th class="bg-black text-white">Action</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($ents as $ent): if($ent["id"]==$_SESSION['procurement_user_id'] || $ent["id"]==1){continue;} ?>
                <tr>
                    <td><?php echo htmlspecialchars($ent["id"]); ?></td>
                    <td><?php echo htmlspecialchars($ent["username"]); ?></td>
                    <td><?php echo htmlspecialchars($ent["created_at"]); ?></td>
                    <td><?php echo htmlspecialchars($ent["role"]); ?></td>
                    
                    <td>
                    <button class="btn btn-danger btn-sm" onclick="confirmDelete3(<?= htmlspecialchars($ent['id']); ?>)">Delete</button>
                    </td>
                </tr>
            <?php endforeach; ?>
            
        </tbody>
    </table>
  
    <?php endif; ?>
<!-- ======================================================================================================================================================================= -->





    <?php if (isset($_GET['page']) && $_GET['page'] === 'unverified_vendors'): ?>
    <h2 class="text-center mb-4 text-white fw-bold p-2" style="background-color: #343a40;">Unverified Vendors</h2>

    <!-- Search Input -->
    <div class="search-container mb-4 d-flex align-items-center w-50">
        <i class="fas fa-search search-icon "></i> <!-- Font Awesome search icon -->
        <input type="text" id="vendor-search" class="form-control me-2 mx-2" placeholder="Search for vendors..." style="flex: 1; width:200px;">
    </div>

    <!-- Vendor Cards -->
    <div class="d-flex flex-wrap gap-5" id="vendor-cards">
        <?php if (!empty($unverified_vendors)): ?>
            <?php foreach ($unverified_vendors as $vendor): ?>
                <div class=" mb-4">
                    <div class="card shadow-sm vendor-card h-100">
                        <form method="POST" action="pdashboard.php?page=unverified_vendors" class="edit-vendor-form">
                            <input type="hidden" name="vendor_id" value="<?= $vendor['id']; ?>">

                            <!-- Card Image -->
                            <img src="<?= htmlspecialchars($vendor['profile_picture'] ?? 'assets/images/Vendors/john.jpg'); ?>" 
                                 alt="Vendor Image" 
                                 class="card-img-top profile-picture mx-auto d-block mt-4" style="width: 100px; height: 100px; border-radius: 50%;">

                            <!-- Card Body -->
                            <div class="card-body  ">
                                <div class="d-flex">
                                <div class="mb-2 mx-2">
                                    <label class="font-weight-bold">Company Name:</label>
                                    <input type="text" name="company_name" value="<?= htmlspecialchars($vendor['company_name']); ?>" class="form-control">
                                </div>

                                <div class="mb-2 mx-2">
                                    <label class="font-weight-bold">Authorized Signatory:</label>
                                    <input type="text" name="authorized_signatory" value="<?= htmlspecialchars($vendor['authorized_signatory']); ?>" class="form-control">
                                </div>
                                </div>
                                <div class="d-flex">
                                <div class="mb-2 mx-2">
                                    <label class="font-weight-bold">Phone Number:</label>
                                    <input type="text" name="phone_number" value="<?= htmlspecialchars($vendor['phone_number']); ?>" class="form-control">
                                </div>
                                <div class="mb-2">
                                    <label class="font-weight-bold">Address:</label>
                                    <input type="text" name="address" value="<?= htmlspecialchars($vendor['address']); ?>" class="form-control">
                                </div>
                                
                                </div>
                                <div class="mb-2 mx-2">
                                    <label class="font-weight-bold">Email:</label>
                                    <input type="email" name="email" value="<?= htmlspecialchars($vendor['email']); ?>" class="form-control">
                                </div>
                                

                                <div class="mb-2">
                                    <label class="font-weight-bold">Vendor Field:</label>
                                    <input type="text" name="vendor_field" value="<?= htmlspecialchars($vendor['vendor_field']); ?>" class="form-control">
                                </div>

                                <div class="mb-2">
                                    <label class="font-weight-bold">Experience:</label>
                                    <input type="text" name="experience" value="<?= htmlspecialchars($vendor['experience']); ?>" class="form-control">
                                </div>
                                <?php if($_SESSION['procurement_user_role']=='admin'):?>
                                <div class="mb-2">
                                    <label class="font-weight-bold">Passport / National ID:</label>
                                    <img src="<?= htmlspecialchars($vendor['pic_id'] ?? 'assets/images/Vendors/john.jpg'); ?>" 
                                        alt="Passport / National ID Image" 
                                        class="card-img-top profile-picture mx-auto d-block mt-4" style="width: 100%; height: 100%;">
                                </div>
                                    <?php endif;?>
                                <!-- Action Buttons -->
                                <div >
                                    <!-- Edit button -->
                                    <button type="submit" name="edit_vendor" class="btn btn-info btn-sm w-100" >Save</button>

                                    <!-- Verify button with SweetAlert -->
                                    <button type="button" class="btn btn-success btn-sm w-100 mt-2" onclick="confirmVerification(<?= $vendor['id']; ?>)">Verify</button>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
            <?php endforeach; ?>
        <?php else: ?>
            <p class="text-center">No unverified vendors found.</p>
        <?php endif; ?>
    </div>

    
            <?php if ($current_page > 1): ?>
                <li class="page-item">
                    <a class="page-link" href="?page=unverified_vendors&page_number=<?= $current_page - 1; ?>">Previous</a>
                </li>
            <?php endif; ?>

            <?php for ($i = 1; $i <= $total_pages; $i++): ?>
                <li class="page-item <?= ($i === $current_page) ? 'active' : ''; ?>">
                    <a class="page-link" href="?page=unverified_vendors&page_number=<?= $i; ?>"><?= $i; ?></a>
                </li>
            <?php endfor; ?>

            <?php if ($current_page < $total_pages): ?>
                <li class="page-item">
                    <a class="page-link" href="?page=unverified_vendors&page_number=<?= $current_page + 1; ?>">Next</a>
                </li>
            <?php endif; ?>
        </ul>
    </nav> -->
    <?php endif; ?>
</div>

<script>
    // Function to handle verification with SweetAlert confirmation
    function confirmVerification(vendorId) {
        Swal.fire({
            title: 'Are you sure?',
            text: "You are about to verify this vendor.",
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#3085d6',
            cancelButtonColor: '#d33',
            confirmButtonText: 'Yes, verify it!'
        }).then((result) => {
            if (result.isConfirmed) {
                // Submit the verification form
                var form = document.createElement('form');
                form.method = 'POST';
                form.action = 'pdashboard.php?page=unverified_vendors';
                
                var input = document.createElement('input');
                input.type = 'hidden';
                input.name = 'verify_vendor_id';
                input.value = vendorId;
                
                form.appendChild(input);
                document.body.appendChild(form);
                form.submit();
            }
        });
    }

document.getElementById('vendor-search').addEventListener('input', function() {
    var searchTerm = this.value.toLowerCase();
    var cards = document.querySelectorAll('#vendor-cards .col-md-4');

    cards.forEach(function(card) {
        // Retrieve all text content from each card and convert to lower case for comparison
        var cardText = card.textContent.toLowerCase();

        // Check if the search term is in the card text
        if (cardText.includes(searchTerm)) {
            card.style.display = ''; // Show the card
        } else {
            card.style.display = 'none'; // Hide the card
        }
    });
});

</script>

<div class="">  
    <?php if (isset($_GET['page']) && $_GET['page'] === 'vendors_list'): ?>
    <h2 class="text-center mt-3 p-2" style="font-weight:bolder; background-color:#343a40; color:white">Vendor List</h2>

    <!-- Group Sorting and Verification Filter Dropdown -->
    <div>
        <div class="mb-4 d-flex align-items-center mt-5">
    <label for="group_sort" class="mx-2 fw-bold">Sort by Group </label>
    <form action="pdashboard.php" method="GET" class="d-flex align-items-center w-50">
        <input type="hidden" name="page" value="vendors_list">
        <select name="group_sort" id="group_sort" class="form-control me-2  ">
            <option value="">All Groups</option>
            <?php foreach ($vendor_groups as $group): ?>
                <option value="<?= htmlspecialchars($group['id']) ?>" <?= (isset($_GET['group_sort']) && $_GET['group_sort'] == $group['id']) ? 'selected' : '' ?>>
                    <?= htmlspecialchars($group['group_name']) ?>
                </option>
            <?php endforeach; ?>
        </select>


        <button type="submit" class="btn btn w-50 fw-bold" style="background-color: #09c528;">Filter</button>
    </form>
</div>

    <!-- Search Input -->
    <div class="search-container mb-4 d-flex align-items-center w-75 flex-wrap">
        <input type="text" id="vendor-search" class="form-control me-2" placeholder="  Search for vendors..." style="flex: 1;">
        <i class="fas fa-search search-icon "></i> 
        <a href="pdashboard.php?page=add_vendor" class="btn btn-success mx-4 mt-2" style="width: 300px;">
            <i class="fas fa-plus"></i> <!-- Font Awesome plus icon -->
            Add New Vendor
        </a>
    </div>

    <!-- Vendor Cards -->
    <div class="d-flex flex-wrap gap-5">
        <?php if (!empty($vendors)): ?>
            <?php foreach ($vendors as $vendor): ?>
                <div class="mb-4">
                    <div class="vendor-card card mb-4 position-relative">
                        <div class="image-container d-flex align-items-center position-relative"> 
    <!-- Flex container for the image and verification icon -->
    
    <img src="<?= htmlspecialchars($vendor['profile_picture'] ?? 'assets/images/Vendors/john.jpg'); ?>" 
         alt="<?= htmlspecialchars($vendor['company_name']); ?>" 
         class="card-img-top profile-picture " style="width: 100px; height: 100px; border-radius: 50%; margin-left:36%"> <!-- Display vendor image -->

    <?php if ($vendor['verified']): ?>
        <!-- Green checkmark for verified (absolute positioned on the image) -->
        <i class="fas fa-check-circle verified-icon position-absolute" 
           style="color: green; font-size: 24px; top: 10px; right: 10px;"></i> 
    <?php else: ?>
        <!-- Gray question mark for unverified -->
        <i class="fas fa-question-circle unverified-icon position-absolute" 
           style="color: gray; font-size: 24px; top: 10px; right: 10px;"></i>
    <?php endif; ?>
</div>


                        <div class="card-body">
                            <h5 class="card-title text-center"><?= htmlspecialchars($vendor['company_name']); ?></h5> <!-- Display company name -->
                            <p class="card-text">
                                <i class="fas fa-phone" style="color:blue;"></i> <?= htmlspecialchars($vendor['phone_number']); ?>
                            </p>
                            <p class="card-text">
                                <i class="fas fa-map-marker-alt" style="color:coral;"></i> <?= htmlspecialchars($vendor['address']); ?>
                            </p>
                            <p class="card-text">
                                <i class="fas fa-envelope" style="color:darkorchid;"></i> <?= htmlspecialchars($vendor['email'] ?? 'No email provided'); ?>
                            </p>
                            <a href="pdashboard.php?page=vendor_details&vendor_id=<?= htmlspecialchars($vendor['id']); ?>" class="btn btn  w-100 fw-bold" style="background-color: #09c528;" >See More</a>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        <?php else: ?>
            <p class="text-center">No vendors found.</p>
        <?php endif; ?>
    </div>

    <!-- Pagination -->
    <!-- <nav aria-label="Page navigation">
        <ul class="pagination justify-content-center">
            <?php if ($current_page > 1): ?>
                <li class="page-item">
                    <a class="page-link" href="?page=vendors_list&page_number=<?= $current_page - 1; ?>&group_sort=<?= htmlspecialchars($group_filter ?? '') ?>&verified_filter=<?= htmlspecialchars($verified_filter ?? '') ?>">Previous</a>
                </li>
            <?php endif; ?>

            <?php for ($i = 1; $i <= $total_pages; $i++): ?>
                <li class="page-item <?= ($i === $current_page) ? 'active' : ''; ?>">
                    <a class="page-link" href="?page=vendors_list&page_number=<?= $i; ?>&group_sort=<?= htmlspecialchars($group_filter ?? '') ?>&verified_filter=<?= htmlspecialchars($verified_filter ?? '') ?>"><?= $i; ?></a>
                </li>
            <?php endfor; ?>

            <?php if ($current_page < $total_pages): ?>
                <li class="page-item">
                    <a class="page-link" href="?page=vendors_list&page_number=<?= $current_page + 1; ?>&group_sort=<?= htmlspecialchars($group_filter ?? '') ?>&verified_filter=<?= htmlspecialchars($verified_filter ?? '') ?>">Next</a>
                </li>
            <?php endif; ?>
        </ul>
    </nav> -->

<?php endif; ?>




<?php if (isset($_GET['page']) && $_GET['page'] === 'vendor_details' && isset($_GET['vendor_id'])): ?>
    <?php
    // Fetch vendor details based on the vendor_id from the query parameter
    $vendor_id = $_GET['vendor_id'];
    $sql = "SELECT * FROM vendors WHERE id = ?";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$vendor_id]);
    $vendor = $stmt->fetch(PDO::FETCH_ASSOC); // Fetch vendor details

    // Check if the user is an admin
    $is_admin = ($procurement_user['role'] === 'admin');
    ?>

    <?php if ($vendor): ?>
        <div class="mt-4">
            <h2 class="text-center"><?= htmlspecialchars($vendor['company_name']); ?> - Details</h2>
            <div class="row">
                <div class="col-md-6">
                    <img src="<?= htmlspecialchars($vendor['profile_picture'] ?? 'assets/images/Vendors/john.jpg'); ?>" 
                         alt="<?= htmlspecialchars($vendor['company_name']); ?>" 
                         class="img-fluid">
                </div>
                <div class="col-md-6">
                    <h4>Details:</h4>
                    <form id="vendorEditForm" action="pdashboard.php?page=vendor2_details&vendor_id=<?= $vendor_id; ?>" method="POST">
                        <input type="hidden" name="vendor_id" value="<?= $vendor_id; ?>">
                        <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token']; ?>"><!-- CSRF Token-->

                        <!-- Authorized Signatory -->
                        <p><strong>Authorized Signatory:</strong> 
                            <span id="authorized_signatory_display"><?= htmlspecialchars($vendor['authorized_signatory']); ?></span>
                            <?php if ($is_admin): ?>
                                <input type="text" id="authorized_signatory_edit" name="authorized_signatory" class="form-control d-none" value="<?= htmlspecialchars($vendor['authorized_signatory']); ?>">
                                <a href="#" onclick="toggleEdit('authorized_signatory'); return false;">
                                    <i class="fas fa-edit"></i>
                                </a>
                            <?php endif; ?>
                        </p>

                        <!-- Phone Number -->
                        <p><strong>Phone Number:</strong> 
                            <span id="phone_number_display"><?= htmlspecialchars($vendor['phone_number']); ?></span>
                            <?php if ($is_admin): ?>
                                <input type="text" id="phone_number_edit" name="phone_number" class="form-control d-none" value="<?= htmlspecialchars($vendor['phone_number']); ?>">
                                <a href="#" onclick="toggleEdit('phone_number'); return false;">
                                    <i class="fas fa-edit"></i>
                                </a>
                            <?php endif; ?>
                        </p>

                        <!-- Address -->
                        <p><strong>Address:</strong> 
                            <span id="address_display"><?= htmlspecialchars($vendor['address']); ?></span>
                            <?php if ($is_admin): ?>
                                <input type="text" id="address_edit" name="address" class="form-control d-none" value="<?= htmlspecialchars($vendor['address']); ?>">
                                <a href="#" onclick="toggleEdit('address'); return false;">
                                    <i class="fas fa-edit"></i>
                                </a>
                            <?php endif; ?>
                        </p>

                        <!-- Email -->
                        <p><strong>Email:</strong> 
                            <span id="email_display"><?= htmlspecialchars($vendor['email'] ?? 'No email provided'); ?></span>
                            <?php if ($is_admin): ?>
                                <input type="email" id="email_edit" name="email" class="form-control d-none" value="<?= htmlspecialchars($vendor['email']); ?>">
                                <a href="#" onclick="toggleEdit('email'); return false;">
                                    <i class="fas fa-edit"></i>
                                </a>
                            <?php endif; ?>
                        </p>

                        <!-- Experience -->
                        <p><strong>Experience:</strong> 
                            <span id="experience_display"><?= htmlspecialchars($vendor['experience'] ?? 'No experience provided'); ?></span>
                            <?php if ($is_admin): ?>
                                <input type="text" id="experience_edit" name="experience" class="form-control d-none" value="<?= htmlspecialchars($vendor['experience']); ?>">
                                <a href="#" onclick="toggleEdit('experience'); return false;">
                                    <i class="fas fa-edit"></i>
                                </a>
                            <?php endif; ?>
                        </p>

                        <!-- Group Assignment -->
                        <p><strong>Group:</strong> 
                            <span id="group_display"><?= htmlspecialchars($vendor['group_id'] ? getGroupName($vendor['group_id'], $pdo) : 'No group assigned'); ?></span>
                            <?php if ($is_admin): ?>
                                <select id="group_edit" name="group_id" class="form-control d-none">
                                    <option value="">Select Group</option>
                                    <?php foreach ($vendor_groups as $group): ?>
                                        <option value="<?= htmlspecialchars($group['id']); ?>" <?= ($vendor['group_id'] == $group['id']) ? 'selected' : ''; ?>>
                                            <?= htmlspecialchars($group['group_name']); ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                                <a href="#" onclick="toggleEdit('group'); return false;">
                                    <i class="fas fa-edit"></i>
                                </a>
                            <?php endif; ?>
                        </p>

                        <!-- Verified Status Field -->
                        <p><strong>Status:</strong> 
                            <span id="verified_display"><?= $vendor['verified'] ? 'Verified' : 'Unverified'; ?></span>
                            <?php if ($is_admin): ?>
                                <!-- Dropdown to edit verified status -->
                                <select id="verified_edit" name="verified" class="form-control d-none">
                                    <option value="1" <?= $vendor['verified'] ? 'selected' : ''; ?>>Verified</option>
                                    <option value="0" <?= !$vendor['verified'] ? 'selected' : ''; ?>>Unverified</option>
                                </select>
                                <a href="#" onclick="toggleEdit('verified'); return false;">
                                    <i class="fas fa-edit"></i>
                                </a>
                            <?php endif; ?>
                        </p>
                        <!-- OOO=========================================================================================== -->
                        <?php if($_SESSION['procurement_user_role']=='admin'):?>
                        <p><strong>ID:</strong> 
                        <div class="col-md-6">
                        <a href="<?= htmlspecialchars($vendor['pic_id'] ); ?>" download>
                            <img src="<?= htmlspecialchars($vendor['pic_id'] ); ?>" 
                         alt="<?= htmlspecialchars($vendor['company_name']); ?>" 
                         class="img-fluid"></a>
                        </div>
                            
                        </p>
                        <?php endif ;?>
                        <?php if ($is_admin): ?>
                            <!-- Save button -->
                            <button type="submit" id="save_changes" class="btn btn-success d-none" onclick="reloadPage();">Save Changes</button>
                        <?php endif; ?>
                    </form>
                </div>
            </div>
            <a href="pdashboard.php?page=vendors_list" class="btn btn-primary mt-3">Back to Vendor List</a>
            
        </div>
    <?php else: ?>
        <p class="text-center">Vendor not found.</p>
    <?php endif; ?>
<?php endif; ?>

<?php
// Helper function to get group name by ID
function getGroupName($group_id, $pdo) {
    $sql = "SELECT group_name FROM vendor_groups WHERE id = ?";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$group_id]);
    return $stmt->fetchColumn();
}
?>


<script>
    
function toggleEdit(field) {
    const displayElement = document.getElementById(`${field}_display`);
    const editElement = document.getElementById(`${field}_edit`);
    const saveButton = document.getElementById('save_changes');

    // Toggle visibility of display and edit elements
    if (displayElement && editElement) {
        displayElement.classList.toggle('d-none');
        editElement.classList.toggle('d-none');
        
        // Ensure the Save Changes button is visible
        saveButton.classList.remove('d-none');
    }
   
}

const currentUrl = "<?php echo $_SERVER['REQUEST_URI']; ?>";

function reloadPage() {
    window.location.href = currentUrl;
}
</script>
</div>


<?php
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['vendor_id'])) {
    // Check if user is admin before allowing updates
    if ($procurement_user['role'] === 'admin') {
        $vendor_id = $_POST['vendor_id'];
        $authorized_signatory = $_POST['authorized_signatory'] ?? '';
        $phone_number = $_POST['phone_number'] ?? '';
        $address = $_POST['address'] ?? '';
        $email = $_POST['email'] ?? '';
        $experience = $_POST['experience'] ?? '';
        $verified = isset($_POST['verified']) ? (int)$_POST['verified'] : 0; // Default to 0 if not set
        $group_id = $_POST['group_id'] ?? null; // New field

        // Validate required fields
        if (empty($vendor_id) || empty($authorized_signatory) || empty($phone_number) || empty($address)) {
            exit; // Exit to prevent further execution
        }

        // Prepare the SQL statement
        $sql = "UPDATE vendors SET authorized_signatory = ?, phone_number = ?, address = ?, email = ?, experience = ?, verified = ?, group_id = ? WHERE id = ?";
        $stmt = $pdo->prepare($sql);

        try {
            // Execute the statement
            $stmt->execute([$authorized_signatory, $phone_number, $address, $email, $experience, $verified, $group_id, $vendor_id]);
            echo "<div class='alert alert-success'>Vendor information updated successfully!</div>";
        } catch (PDOException $e) {
            echo "<div class='alert alert-danger'>Failed to update vendor information! Error: " . htmlspecialchars($e->getMessage()) . "</div>";
            error_log($e->getMessage()); // Log error for debugging
        }
    } else {
        echo "<div class='alert alert-danger'>Only admins can edit vendor information.</div>";
    }
}



// Display vendor details if `vendor_id` is set in the query parameter
if (isset($_GET['page']) && $_GET['page'] === 'vendor_details' && isset($_GET['vendor_id'])) {
    $vendor_id = $_GET['vendor_id'];
    $sql = "SELECT * FROM vendors WHERE id = ?";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$vendor_id]);
    $vendor = $stmt->fetch(PDO::FETCH_ASSOC); // Fetch vendor details
}

?>

<?php
// Handle Add Vendor Form Submission (admin only)
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_GET['page']) && $_GET['page'] === 'add_vendor') {
    if ($procurement_user['role'] === 'admin') {
        // Get vendor data from POST request
        $company_name = trim($_POST['company_name']);
        $authorized_signatory = trim($_POST['authorized_signatory']);
        $phone_number = trim($_POST['phone_number']);
        $address = trim($_POST['address']);
        $vendor_field = $_POST['vendor_field'];
        $email = trim($_POST['email']);
        $experience = trim($_POST['experience']);
        $password = $_POST['password'];

        // Validate input (basic validation)
        if (empty($company_name) || empty($authorized_signatory) || empty($phone_number) || empty($address) || empty($vendor_field) || empty($password)) {
            $message = "<div class='alert alert-danger'>Please fill in all required fields.</div>";
        } else {
            // Hash the password before storing it
            $hashed_password = password_hash($password, PASSWORD_DEFAULT);

            // Insert vendor data into the database
            $sql = "INSERT INTO vendors (company_name, authorized_signatory, phone_number, address, vendor_field, email, experience, password, created_at) VALUES (?, ?, ?, ?, ?, ?, ?, ?, NOW())";
            $stmt = $pdo->prepare($sql);
            try {
                $stmt->execute([$company_name, $authorized_signatory, $phone_number, $address, $vendor_field, $email, $experience, $hashed_password]);
                $message = "<div class='alert alert-success'>Vendor added successfully!</div>";
            } catch (PDOException $e) {
                $message = "<div class='alert alert-danger'>Failed to add vendor! Error: " . $e->getMessage() . "</div>";
                error_log($e->getMessage()); // Log error information
            }
        }
    } else {
        $message = "<div class='alert alert-danger'>Only admins can add vendors.</div>";
    }
}
?>

<?php if (isset($_GET['page']) && $_GET['page'] === 'add_vendor'): ?>
    <div class="content">
        <h2 class="text-center">Add New Vendor</h2>
        <?= $message; ?>
        <form action="pdashboard.php?page=add_vendor" method="POST">
            <div class="form-group">
                <label for="company_name">Company/Vendor Name:</label>
                <input type="text" class="form-control" name="company_name" required>
            </div>
            <div class="form-group">
                <label for="authorized_signatory">Authorized Signatory:</label>
                <input type="text" class="form-control" name="authorized_signatory" required>
            </div>
            <div class="form-group">
                <label for="phone_number">Phone Number:</label>
                <input type="text" class="form-control" name="phone_number" required>
            </div>
            <div class="form-group">
                <label for="address">Address:</label>
                <textarea class="form-control" name="address" required></textarea>
            </div>
            <div class="form-group">
                <label for="vendor_field">Vendor Field:</label>
                <select class="form-control" name="vendor_field" required>
                    <option value="Suppliers">Suppliers</option>
                    <option value="Consultant">Consultant</option>
                    <option value="Contractors">Contractors</option>
                    <option value="Sub-contractor">Sub-contractor</option>
                </select>
            </div>
            <div class="form-group">
                <label for="group_id">Assign to Group:</label>
                <select class="form-control" name="group_id" required>
                    <option value="">Select Group</option>
                    <?php foreach ($vendor_groups as $group): ?>
                        <option value="<?= htmlspecialchars($group['id']); ?>">
                            <?= htmlspecialchars($group['group_name']); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
                <small class="form-text text-muted">Choose a group to categorize the vendor.</small>
            </div>
            <div class="form-group">
                <label for="email">Email (optional):</label>
                <input type="email" class="form-control" name="email">
            </div>
            <div class="form-group">
                <label for="experience">Experience (optional):</label>
                <input type="text" class="form-control" name="experience">
            </div>
            <div class="form-group">
                <label for="password">Password:</label>
                <input type="password" class="form-control" name="password" required>
            </div>
            <div class="text-center">
                <button type="submit" class="btn btn-success">Add Vendor</button>
            </div>
        </form>
        <div class="text-center mt-4">
            <a href="pdashboard.php?page=vendors_list" class="btn btn-secondary">Back to Vendor List</a>
        </div>
    </div>
<?php endif; ?>

<div class="content">
<!-- Update Profile Page -->
<?php
// OOO==========================================================================
if($procurement_user['profile_picture']){
}
elseif ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_FILES['profile_picture'])) {
    $target_dir = "uploads/";
    $target_file3 = $target_dir.getRandomString($n). '.jpg'; // Use user name as filename
    $uploadOk = 1;
    $imageFileType = strtolower(pathinfo($target_file3, PATHINFO_EXTENSION));

    // Check if the image file is an actual image or fake image
    $check = getimagesize($_FILES["profile_picture"]["tmp_name"]);
    if ($check === false) {
        $message = "<div class='alert alert-danger'>File is not an image.</div>";
        $uploadOk = 0;
    }

    // Check file size (limit to 2MB)
    if ($_FILES["profile_picture"]["size"] > 2000000) {
        $message = "<div class='alert alert-danger'>Sorry, your file is too large.</div>";
        $uploadOk = 0;
    }

    // Allow certain file formats
    if (!in_array($imageFileType, ['jpg', 'jpeg', 'png'])) {
        $message = "<div class='alert alert-danger'>Sorry, only JPG, JPEG, PNG files are allowed.</div>";
        $uploadOk = 0;
    }

    // Check if $uploadOk is set to 0 by an error
    if ($uploadOk == 0) {
        $message .= "<div class='alert alert-danger'>Your file was not uploaded.</div>";
    } else {
        if (move_uploaded_file($_FILES["profile_picture"]["tmp_name"], $target_file3)) {
            // Update the vendor's profile picture path in the database
            $sql = "UPDATE procurement_users SET profile_picture = ? WHERE id = ?";
            $stmt = $pdo->prepare($sql);
            $stmt->execute([$target_file3,$procurement_user['id']]);
            $message = "<div class='alert alert-success'>The file has been uploaded.</div>";
        } else {
            $message .= "<div class='alert alert-danger'>There was an error uploading your file.</div>";
        }
    }
}


?>


<?php if (isset($_GET['page']) && $_GET['page'] === 'update_profile'): ?>
    <div class="p-5 rounded-5" style="background-color: rgb(146, 147, 143);">
    <h2 class="text-center fw-bold">Update Your Profile</h2>
    <?= $message; ?>
    <form action="pdashboard.php?page=update_profile" method="POST" enctype="multipart/form-data">
    <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token']; ?>">

    <h4 class="text-center">Change Password</h4>
    <div class="form-group">
        <label for="password" class="mt-2">New Password:</label>
        <input type="password" class="form-control mt-3" name="password" >
    </div>
    <div class="form-group">
        <label for="password_confirmation" class="mt-2">Confirm New Password:</label>
        <input type="password" class="form-control mt-3" name="password_confirmation" >
    </div>

    <h4 class="mt-2">Update Profile Picture</h4>
    <div class="form-group">
        <label for="profile_picture" class="mt-2">Profile Picture:</label>
        <input type="file" class="form-control mt-3" name="profile_picture" accept="image/*">
    </div>

    <div class="text-center">
        <button type="submit" name="edit_password" class="btn btn-primary mt-3 w-100">Save Changes</button>
    </div>
</form>
    </div>
    

</div>
<?php endif; ?>
<script src="https://code.jquery.com/jquery-3.5.1.min.js"></script> <!-- jQuery -->
<script src="assets/js/bootstrap.min.js"></script> <!-- Bootstrap JS -->
<script>
$(document).ready(function() {
    // Filter vendors based on the search input
    $('#vendor-search').on('keyup', function() {
        var searchTerm = $(this).val().toLowerCase(); // Get the value from the search input
        $('.vendor-card').each(function() {
            var vendorName = $(this).find('.card-title').text().toLowerCase(); // Get the vendor name
            var authorizedSignatory = $(this).find('.card-text').eq(0).text().toLowerCase(); // Get authorized signatory

            // Check if either vendor name or authorized signatory matches the search term
            if (vendorName.includes(searchTerm) || authorizedSignatory.includes(searchTerm)) {
                $(this).parent().show(); // Show the column if the card matches
            } else {
                $(this).parent().hide(); // Hide the column if the card doesn't match
            }
        });
    });
});







</script>
<!-- jQuery (if needed for other functionalities) -->
<script src="https://code.jquery.com/jquery-3.5.1.min.js"></script>

<!-- Bootstrap Bundle with Popper -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js" integrity="sha384-..." crossorigin="anonymous"></script>

<!-- SweetAlert2 -->
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<!-- Your Custom JS -->
<script src="assets/js/bootstrap.min.js"></script> <!-- Remove or update if necessary -->
<script>function toggleSidebar() {
    document.querySelector('.sidebar').classList.toggle('active');
}
function toggleSidebar() {
    document.querySelector('.sidebar').classList.toggle('active');
}
document.querySelector('.open-slide').addEventListener('click', function() {
  const slide = document.querySelector('.slide');
  slide.style.display = 'block'; // عرض السلايد
});

document.querySelector('.close-slide').addEventListener('click', function() {
  const slide = document.querySelector('.slide');
  slide.style.display = 'none'; // إخفاء السلايد
});
</script>

</body>
</html>
