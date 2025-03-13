<?php
require 'includes/db.php';
session_start();

if (!isset($_SESSION['vendor_id'])) {
    header("Location: index.php"); // Redirect to login if not authenticated
    exit();
}
$n = 10;
function getRandomString($n) {
    return bin2hex(random_bytes($n/2));
}
// Check the session ID
$vendor_id = $_SESSION['vendor_id'];
$sql = "SELECT * FROM vendors WHERE id = ?";
$stmt = $pdo->prepare($sql);
$stmt->execute([$vendor_id]);
$vendor = $stmt->fetch();

$message = "";
$page = isset($_GET['page']) ? $_GET['page'] : 'home'; // Default to 'home'

// Handle profile picture upload
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_FILES['profile_picture'])) {
    $target_dir = "uploads/";
    $target_file = $target_dir . $vendor['company_name'] . '.jpg'; // Use vendor name as filename
    $uploadOk = 1;
    $imageFileType = strtolower(pathinfo($target_file, PATHINFO_EXTENSION));

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
        if (move_uploaded_file($_FILES["profile_picture"]["tmp_name"], $target_file)) {
            // Update the vendor's profile picture path in the database
            $sql = "UPDATE vendors SET profile_picture = ? WHERE id = ?";
            $stmt = $pdo->prepare($sql);
            $stmt->execute([$target_file, $vendor_id]);
            $message = "<div class='alert alert-success'>The file has been uploaded.</div>";
        } else {
            $message .= "<div class='alert alert-danger'>There was an error uploading your file.</div>";
        }
    }
}

// OOO============================

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_FILES['pic_id'])) {
    $target_dir = "uploads/";
    $target_file2 = $target_dir.getRandomString($n). '.jpg'; // Use vendor name as filename
    $uploadOk = 1;
    $imageFileType = strtolower(pathinfo($target_file2, PATHINFO_EXTENSION));

    // Check if the image file is an actual image or fake image
    $check = getimagesize($_FILES["pic_id"]["tmp_name"]);
    if ($check === false) {
        $message = "<div class='alert alert-danger'>File is not an image.</div>";
        $uploadOk = 0;
    }

    // Check file size (limit to 2MB)
    if ($_FILES["pic_id"]["size"] > 2000000) {
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
        if (move_uploaded_file($_FILES["pic_id"]["tmp_name"], $target_file2)) {
            // Update the vendor's profile picture path in the database
            $sql = "UPDATE vendors SET pic_id = ? WHERE id = ?";
            $stmt = $pdo->prepare($sql);
            $stmt->execute([$target_file2, $vendor_id]);
            $message = "<div class='alert alert-success'>The file has been uploaded.</div>";
        } else {
            $message .= "<div class='alert alert-danger'>There was an error uploading your file.</div>";
}
}
}

// Handle data editing
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['edit']) && !$vendor['verified']) {
    $updated_company_name = $_POST['company_name'];
    $updated_authorized_signatory = $_POST['authorized_signatory'];
    $updated_vendor_field = $_POST['vendor_field'];
    $updated_email = $_POST['email'];
    $updated_phone = $_POST['phone_number'];
    $updated_address = $_POST['address'];
    $updated_experience = $_POST['experience'];

    // Update the vendor's information in the database
    $sql = "UPDATE vendors SET 
                company_name = ?, 
                authorized_signatory = ?, 
                vendor_field = ?, 
                email = ?, 
                phone_number = ?, 
                address = ?, 
                experience = ? 
            WHERE id = ?";
    $stmt = $pdo->prepare($sql);

    // Execute the query with all parameters
    if ($stmt->execute([$updated_company_name, $updated_authorized_signatory, $updated_vendor_field, $updated_email, $updated_phone, $updated_address, $updated_experience, $vendor_id])) {
        $message = "<div class='alert alert-success'>Profile updated successfully!</div>";
        // Refresh the vendor data after the update
        $stmt = $pdo->prepare("SELECT * FROM vendors WHERE id = ?");
        $stmt->execute([$vendor_id]);
        $vendor = $stmt->fetch();
    } else {
        $message = "<div class='alert alert-danger'>Failed to update profile!</div>";
    }
}

// If vendor is verified, show the full dashboard, otherwise show the profile completion process
if ($vendor['verified']):
?>

    <!DOCTYPE html>
    <html lang="en">

    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>Vendor Dashboard</title>
        <link rel="stylesheet" href="assets/css/bootstrap.min.css">
        <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
        <style>
            body {
                display: flex;
                font-family: 'Arial', sans-serif;
                background-color: #f4f7fa;
                /* Light gray background */
            }

            .sidebar {
                width: 280px;
                /* Sidebar width */
                background-color: #f8f9fa;
                padding: 20px;
                height: 100%;
                box-shadow: 2px 0 5px rgba(0, 0, 0, 0.1);
                position: sticky;
                /* Sticky sidebar */
                top: 0;
                /* Stick to the top */
            }

            .content {
                flex-grow: 1;
                padding: 20px;
                background-color: #ffffff;
                border-radius: 8px;
                /* Rounded corners */
                box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
                /* Box shadow for depth */
                margin-left: 20px;
                /* Space between sidebar and content */
            }

            .profile-picture {
                width: 100px;
                /* Set smaller width */
                height: 100px;
                /* Set smaller height */
                border-radius: 50%;
                /* Circular shape */
                object-fit: cover;
                /* Maintain aspect ratio */
                margin-bottom: 15px;
                /* Space below the picture */
                border: 2px solid #007bff;
                /* Optional: Blue border around the picture */
            }

            .nav-link {
                border-radius: 4px;
                /* Rounded corners */
                padding: 10px 15px;
                /* More padding for touch targets */
                transition: background-color 0.3s ease;
                /* Smooth transition for hover effect */
            }

            .nav-link:hover {
                background-color: #dcdcdc;
                /* Change hover color to light gray */
                color: black;
                /* Change text color on hover */
            }

            .nav-link.active {
                background-color: #007bff;
                /* Active link background color */
                color: white;
                /* Active link text color */
            }
.sidebar {
    width: 270px;
    background-color: #f8f9fa;
    padding: 20px;
    height: 100%;
    transition: transform 0.3s ease;
}

.profile-picture {
    width: 100px;
    height: 100px;
    border-radius: 50%;
}
.close-btn {
    display: none;
    position: absolute;
    top: 10px;
    right: 10px;
    font-size: 24px;
    color: #333;
    cursor: pointer;
}

@media (max-width: 768px) {
    .sidebar {
        transform: translateX(-100%);
        position: fixed;

    }
    .close-btn {
        display: block;
    }
    .profile-picture-toggle {
        display: block;
        width: 30px;
        height: 30px;
        border-radius: 50%;
        cursor: pointer;
    }

    .sidebar.active {
        transform: translateX(0);
    }
}





        </style>
        <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet"
        integrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH" crossorigin="anonymous" />
    </head>

    <body>
<img src="<?= htmlspecialchars($vendor['profile_picture'] ?? 'assets/images/Vendors/john.jpg'); ?>" 
    alt="Profile Picture" class="profile-picture-toggle d-md-none" onclick="toggleSidebar()">
<div class="sidebar">
<span class="close-btn" onclick="toggleSidebar()">&times;</span>
    <div class="text-center mb-4">
        <img src="<?= htmlspecialchars($vendor['profile_picture'] ?? 'assets/images/Vendors/john.jpg'); ?>"
             alt="Vendor Picture"
             class="profile-picture">
        <?php if ($vendor['verified']): ?>
            <i class="fas fa-check-circle verified-icon" style="color: green;"></i> <!-- Green checkmark for verified -->
        <?php else: ?>
            <i class="fas fa-question-circle unverified-icon" style="color: gray;"></i> <!-- Gray question mark for unverified -->
        <?php endif; ?>
        <h4 class="font-weight-bold"><?= htmlspecialchars($vendor['company_name']); ?></h4> <!-- Display company name -->
    </div>
    <ul class="nav flex-column nav-pills">
        <li class="nav-item mb-2">
            <a class="nav-link" href="?page=home">Dashboard</a>
        </li>
        <li class="nav-item mb-2">
            <a class="nav-link" href="?page=update_profile">Update Profile</a>
        </li>
        <li class="nav-item mb-2">
            <a class="nav-link" href="?page=signed_agreements">Signed Agreements</a>
        </li>
        <li class="nav-item mb-2">
            <a class="nav-link text-danger" href="logout.php">Logout</a>
        </li>
    </ul>
</div>


        <div class="content">
            <?php if ($page === 'home'): ?>
                <h2 class="text-center">Hello World!</h2> <!-- Main page content -->
                <p class="text-center">Welcome to your dashboard. Use the sidebar to navigate.</p>
            <?php elseif ($page === 'update_profile'): ?>
                <h2 class="text-center">Update Your Profile</h2>
                <?= $message; ?>
                <div class="row mt-5">
    <div class="col-md-4 col-12 order-1 order-md-1">
        <div id="imgClick" type="button" class="text-start" style="margin-left: 25%;" onclick="imgClick1()">
            <img src="<?= htmlspecialchars($vendor['profile_picture'] ?? 'assets/images/Vendors/john.jpg'); ?>" alt="Vendor Picture" class="profile-picture">
        </div>
        <form action="dashboard.php?page=update_profile" method="POST" enctype="multipart/form-data">
            <input type="file" name="profile_picture" accept="image/*" required class="border border-25 border-1 rounded-5 text-start" style="margin-left: 4%;">
            <br>
            <button type="submit" class="btn btn-primary btn-sm upload-btn mt-2" style="margin-left: 15%;">Upload New Picture</button>
        </form>
    </div>

    <div class="col-md-8 col-12 order-2 order-md-2">
        <h4 class="mt-4 text-center mb-4">Account Information</h4>
        <form action="dashboard.php?page=update_profile" method="POST" id="edit-form">
            <input type="hidden" name="edit" value="1">
            <div class="row text-center">
                <div class="form-group col">
                    <span for="company_name">Company Name :</span>
                    <?php if (!$vendor['verified']): ?>
                        <input type="text" class="form-control" name="company_name" value="<?= htmlspecialchars($vendor['company_name']); ?>" required>
                    <?php else: ?>
                        <span class="text-primary"><?= htmlspecialchars($vendor['company_name']); ?></span>
                    <?php endif; ?>
                </div>
                <div class="form-group col">
                    <span for="authorized_signatory">Authorized Signatory :</span>
                    <?php if (!$vendor['verified']): ?>
                        <input type="text" class="form-control" name="authorized_signatory" value="<?= htmlspecialchars($vendor['authorized_signatory']); ?>" required>
                    <?php else: ?>
                        <span class="text-primary"><?= htmlspecialchars($vendor['authorized_signatory']); ?></span>
                    <?php endif; ?>
                </div>
            </div>
            <hr>
            <div class="row text-center mt-2">
                <div class="form-group col">
                    <span for="vendor_field">Vendor Field :</span>
                    <?php if (!$vendor['verified']): ?>
                        <input type="text" class="form-control" name="vendor_field" value="<?= htmlspecialchars($vendor['vendor_field']); ?>" required>
                    <?php else: ?>
                        <span class="text-primary"><?= htmlspecialchars($vendor['vendor_field']); ?></span>
                    <?php endif; ?>
                </div>
                <div class="form-group col">
                    <span for="email">Email :</span>
                    <?php if (!$vendor['verified']): ?>
                        <input type="email" class="form-control" name="email" value="<?= htmlspecialchars($vendor['email']); ?>" required>
                    <?php else: ?>
                        <span class="text-primary"><?= htmlspecialchars($vendor['email']); ?></span>
                    <?php endif; ?>
                </div>
            </div>
            <hr>
            <div class="row text-center mt-2">
                <div class="form-group col">
                    <span for="phone_number">Phone :</span>
                    <?php if (!$vendor['verified']): ?>
                        <input type="text" class="form-control" name="phone_number" value="<?= htmlspecialchars($vendor['phone_number']); ?>" required>
                    <?php else: ?>
                        <span class="text-primary"><?= htmlspecialchars($vendor['phone_number']); ?></span>
                    <?php endif; ?>
                </div>
                <div class="form-group col">
                    <span for="address">Address :</span>
                    <?php if (!$vendor['verified']): ?>
                        <textarea class="form-control" name="address" required><?= htmlspecialchars($vendor['address']); ?></textarea>
                    <?php else: ?>
                        <span class="text-primary"><?= htmlspecialchars($vendor['address']); ?></span>
                    <?php endif; ?>
                </div>
            </div>
            <hr>
            <div class="form-group mt-2" style="margin-left: 17%;">
                <span for="experience">Experience:</span>
                <?php if (!$vendor['verified']): ?>
                    <input type="text" class="form-control" name="experience" value="<?= htmlspecialchars($vendor['experience']); ?>" required>
                <?php else: ?>
                    <span class="text-primary"><?= htmlspecialchars($vendor['experience']); ?></span>
                <?php endif; ?>
            </div>
        </form>
    </div>
</div>

                

                
            <?php elseif ($page === 'signed_agreements'): ?>
                <h2 class="text-center mb-5">Signed Agreements</h2>
                <h4>Effective Contracts</h4>
                <?php   
        $sql1="SELECT * from agreements where vendor_id=? ;";
        $stmt1 = $pdo->prepare($sql1);
        $stmt1->execute([$_SESSION['vendor_id']]);
        $result=$stmt1->fetchAll();
        
        ?>
        <table class="table table-bordered table-responsive">
            <thead>
                <tr>
                    <th class="bg-black text-white">#</th>
                    <th class="bg-black text-white">Contract Name</th>
                    <th class="bg-black text-white">Signed Date</th>
                    <th class="bg-black text-white">Expired Date</th>
                </tr>
            </thead>
            <!-- OOO======================================== -->
            <tbody><?php $i=1 ;foreach($result as $res) :  ?>
                <tr>
                    <td><?php echo $i ?></td>
                    <td><?php echo $res['contract_name'] ?></td>
                    <td><?php echo $res['signed_date'] ?></td>
                    <td><?php echo $res['expired_date'] ?></td>
                </tr><?php $i++; endforeach;?>
            </tbody>
        </table>
    <?php endif;?>
</div>
<script>function toggleSidebar() {
    document.querySelector('.sidebar').classList.toggle('active');
}
function toggleSidebar() {
    document.querySelector('.sidebar').classList.toggle('active');
}
</script>
        <script src="https://code.jquery.com/jquery-3.5.1.min.js"></script> <!-- jQuery from CDN -->
        <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.bundle.min.js"></script> <!-- Bootstrap JS from CDN -->
    </body>

    </html>

<?php else: ?>
    <!-- Vendor is not verified: Show profile completion steps -->
    <!DOCTYPE html>
    <html lang="en">

    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">

        <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet"
            integrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH" crossorigin="anonymous" />
        <title>Profile Completion</title>
        <!-- <link rel="stylesheet" href="assets/css/bootstrap.min.css"> -->
        <link rel="stylesheet" href="./assets/css/waiting.css">
    </head>

    <body>
        <div class="container mt-2">
            <div class="alert alert-warning text-center">
            <strong>يرجى رفع الصورة الشخصية والهوية او جواز السفر ثم الانتظار لحين مراجعة البيانات من قبل قسم مشتريات روابي</strong>
            </div>

            <h2 class="text-center ">Complete Your Profile</h2>
            <form action="dashboard.php" method="POST" enctype="multipart/form-data">
                <input type="hidden" name="edit" value="1">
<div class="row"> 
    <div class="form-group col">
                    <label for="company_name">Company Name</label>
                    <input type="text" class="form-control" name="company_name" style="border-radius: 20px;" value="<?= htmlspecialchars($vendor['company_name']); ?>" required>
                </div>
                <div class="form-group col ">
                    <label for="authorized_signatory">Authorized Signatory</label>
                    <input type="text" class="form-control" name="authorized_signatory" style="border-radius: 20px;" value="<?= htmlspecialchars($vendor['authorized_signatory']); ?>" required>
                </div>
            </div>
            <div class="row">
            <div class="form-group col ">
                    <label for="vendor_field">Vendor Field</label>
                    <input type="text" class="form-control" name="vendor_field" style="border-radius: 20px;" value="<?= htmlspecialchars($vendor['vendor_field']); ?>" required>
                </div>
                <div class="form-group col ">
                    <label for="email">Email</label>
                    <input type="email" class="form-control" name="email" style="border-radius: 20px;" value="<?= htmlspecialchars($vendor['email']); ?>" required>
                </div>
            </div>
                <div class="row">
                <div class="form-group col ">
                    <label for="phone_number">Phone</label>
                    <input type="text" class="form-control" name="phone_number" style="border-radius: 20px;" value="<?= htmlspecialchars($vendor['phone_number']); ?>" required>
                </div>
                <div class="form-group col ">
                <label for="address">Address</label>
                <input class="form-control" name="address" style="border-radius: 20px;" value="<?= htmlspecialchars($vendor['address']); ?>" required>
                </div>
                </div>
                
                <div class="form-group ">
                    <label for="experience">Experience</label>
                    <input type="text" class="form-control" name="experience" style="border-radius: 20px;" value="<?= htmlspecialchars($vendor['experience']); ?>" required>
                </div>
                 <div class="form-group mb-3">
                <label for="pic_id">Upload Your Passport / National ID</label>
                <input type="file" class="form-control" name="pic_id" accept="image/*" required>
                </div>

            <div class="form-group mb-3">
                <label for="profile_picture">Upload Profile Picture</label>
                <input type="file" class="form-control" name="profile_picture" accept="image/*" required>
            </div>
                <div class="text-center">
                    <button type="submit" class="btn btn-primary">Submit Profile for Review</button>
                </div >
                
            </form>
            <div class="text-center mt-3">
            <a href="index.php" class="text-white text-decoration-none"><button  class="btn btn bg-black w-100 text-white rounded-3 ">Logout</button></a>
                </div>
        </div>
    </body>
   
    </html>

<?php endif; ?>