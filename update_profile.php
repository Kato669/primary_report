<?php 
    ob_start();
    include("partials/header.php"); 
    include("partials/adminOnly.php");
    // Database connection (add if missing)
    // $conn = mysqli_connect("localhost", "username", "password", "database");

    // Handle form submission
    if(isset($_POST['update'])){
        $school_name = mysqli_real_escape_string($conn, $_POST['school_name']);
        $location = mysqli_real_escape_string($conn, $_POST['location']);
        $contact_1 = mysqli_real_escape_string($conn, $_POST['contact_1']);
        $contact_2 = mysqli_real_escape_string($conn, $_POST['contact_2']);
        $email = mysqli_real_escape_string($conn, $_POST['email']);
        $motto = mysqli_real_escape_string($conn, $_POST['motto']);

        // Handle logo upload
        $logo = '';
        if(isset($_FILES['profile']) && $_FILES['profile']['error'] == UPLOAD_ERR_OK){
            $allowed_types = ['image/png', 'image/jpeg', 'image/jpg'];
            $max_size = 2 * 1024 * 1024; // 2MB
            $file_type = $_FILES['profile']['type'];
            $file_size = $_FILES['profile']['size'];
            $file_tmp = $_FILES['profile']['tmp_name'];
            $file_name = basename($_FILES['profile']['name']);

            if(in_array($file_type, $allowed_types) && $file_size <= $max_size){
                $upload_dir = "img/stdent_image/";
                $target_file = $upload_dir . uniqid() . "_" . $file_name;
                if(move_uploaded_file($file_tmp, $target_file)){
                    $logo = $target_file;
                }
            }
        }

        // Get current logo if no new logo uploaded
        $select_logo = "SELECT profile_image FROM school_profile LIMIT 1";
        $result_logo = mysqli_query($conn, $select_logo);
        $row_logo = mysqli_fetch_assoc($result_logo);
        if(empty($logo)){
            $logo = $row_logo['profile_image'];
        }

        // Update query
        $update = "UPDATE school_profile SET 
            school_name='$school_name',
            address='$location',
            phone_1='$contact_1',
            phone_2='$contact_2',
            email='$email',
            motto='$motto',
            profile_image='$logo'
            LIMIT 1";
        $execute_update = mysqli_query($conn, $update);

        if($execute_update){
            echo '<div class="alert alert-success">Profile updated successfully.</div>';
            header("Location:".SITEURL."view_profile.php");
        }else{
            echo '<div class="alert alert-danger">Failed to update profile: '.mysqli_error($conn).'</div>';
        }
    }

    // Fetch current profile
    $select = "SELECT * FROM school_profile LIMIT 1";
    $execute = mysqli_query($conn, $select);
    if(!$execute){
        die("Failed execution". mysqli_error($conn));
    }
    $row = mysqli_fetch_assoc($execute);
    $school_name = $row['school_name'];
    $location = $row['address'];
    $contact_1 = $row['phone_1'];
    $contact_2 = $row['phone_2'];
    $email = $row['email'];
    $motto = $row['motto'];
    $logo = $row['profile_image'];
?>
<div class="container-fluid">
    <h3 class="text-capitalize fs-6 text-dark py-2">update school profile as directed</h3>

    <div class="row g-0 shadow rounded my-4 p-4">

        <div class="col-lg-6 col-12 p-2">
            <form method="POST" action="" enctype="multipart/form-data">
                <div class="mb-3">
                    <label class="form-label fw-bold">Enter school name</label>
                    <input type="text" class="form-control shadow-none" name="school_name" value="<?php echo htmlspecialchars($school_name, ENT_QUOTES, 'UTF-8'); ?>" required>
                </div>
                <div class="mb-3">
                    <label class="form-label fw-bold">Enter school location</label>
                    <input type="text" class="form-control shadow-none" name="location" value="<?php echo htmlspecialchars($location, ENT_QUOTES, 'UTF-8'); ?>" required>
                </div>
                <div class="mb-3">
                    <label class="form-label fw-bold">Enter Contacts</label>
                    <input type="text" class="form-control shadow-none" name="contact_1" value="<?php echo htmlspecialchars($contact_1, ENT_QUOTES, 'UTF-8'); ?>" required>
                </div>
                <div class="mb-3">
                    <label class="form-label fw-bold">Enter Postal Num:</label>
                    <input type="text" class="form-control shadow-none" value="<?php echo htmlspecialchars($contact_2, ENT_QUOTES, 'UTF-8'); ?>" name="contact_2">
                </div>
                    <div class="mb-3">
                    <label class="form-label fw-bold">Enter school email</label>
                    <input type="email" class="form-control shadow-none" name="email" value="<?php echo htmlspecialchars($email, ENT_QUOTES, 'UTF-8'); ?>">
                </div>
        </div>

        <div class="col-lg-6 col-12 p-2">
            
            <div class="mb-3">
                <label class="form-label fw-bold">Enter school motto</label>
                <input type="text" class="form-control shadow-none" name="motto" value="<?php echo htmlspecialchars($motto, ENT_QUOTES, 'UTF-8'); ?>" required>
            </div>
            <div class="mb-3">
                <label class="form-label fw-bold">Current school logo</label>
                <?php if(!empty($logo)): ?>
                    <img src="<?php echo htmlspecialchars($logo, ENT_QUOTES, 'UTF-8'); ?>" class="img-fluid form-control" alt="School Logo" style="height: 100px; width: 100px">
                <?php else: ?>
                    <span class="text-muted">No logo uploaded.</span>
                <?php endif; ?>
            </div>
            <div class="mb-3">
                <label class="form-label fw-bold">Upload school logo</label>
                <input class="form-control" type="file" name="profile" accept=".png,.jpg,.jpeg">
                <span class="text-muted small">Only PNG, JPG, JPEG (max 2MB)</span>
            </div>
            <button type="submit" name="update" class="btn btn-success w-100">Submit</button>
            </form>
        </div>
    </div>
        <div class="text-center mt-4">
        <a href="fees_management.php" class="btn btn-primary">
        Manage Fees per Class
        </a>
</div>

</div>
<?php include("partials/footer.php") ?>