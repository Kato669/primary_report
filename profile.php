<?php 
ob_start();
include("partials/header.php"); 

$errors = [];
$success = "";

// Handle form submission
if(isset($_POST['profile'])) {
    // Sanitize and format inputs
    $school_name = strtoupper(trim(mysqli_real_escape_string($conn, $_POST['school_name'] ?? "")));
    $location    = strtoupper(trim(mysqli_real_escape_string($conn, $_POST['location'] ?? "")));
    $contact_1   = trim(mysqli_real_escape_string($conn, $_POST['contact_1'] ?? ""));
    $contact_2   = trim(mysqli_real_escape_string($conn, $_POST['contact_2'] ?? ""));
    $email       = strtolower(trim(mysqli_real_escape_string($conn, $_POST['email'] ?? "")));
    $motto       = strtoupper(trim(mysqli_real_escape_string($conn, $_POST['motto'] ?? "")));

    // Validate required fields
    if(empty($school_name) || empty($location) || empty($contact_1) || empty($contact_2) || empty($motto)) {
        $errors[] = "School name, location, first contact, and motto cannot be empty.";
    }

    // Validate image upload (optional)
    $image_path = null;
    if(isset($_FILES['profile']) && $_FILES['profile']['error'] !== UPLOAD_ERR_NO_FILE) {
        $file_tmp  = $_FILES['profile']['tmp_name'];
        $file_name = $_FILES['profile']['name'];
        $file_size = $_FILES['profile']['size'];
        $file_ext  = strtolower(pathinfo($file_name, PATHINFO_EXTENSION));
        $allowed   = ['png','jpg','jpeg'];

        if(!in_array($file_ext, $allowed)) {
            $errors[] = "Only PNG, JPG, and JPEG formats are allowed for the logo.";
        } elseif($file_size > 2*1024*1024) { // limit: 2MB
            $errors[] = "Logo file size should not exceed 2MB.";
        } else {
            $new_name = "school_logo_" . time() . "." . $file_ext;
            $upload_dir = "img/stdent_image/";
            if(!is_dir($upload_dir)) mkdir($upload_dir, 0777, true);

            if(move_uploaded_file($file_tmp, $upload_dir.$new_name)) {
                $image_path = $upload_dir.$new_name;
            } else {
                $errors[] = "Failed to upload logo. Try again.";
            }
        }
    }

    // Proceed if no validation errors
    if(empty($errors)) {
        $select = "SELECT profile_id FROM school_profile WHERE school_name='$school_name' LIMIT 1";
        $execute = mysqli_query($conn, $select);
        if(!$execute) {
            die("Failed execution: " . mysqli_error($conn));
        }

        if(mysqli_num_rows($execute) === 0) {
            // Insert new profile
            $insert = "INSERT INTO school_profile (school_name, address, phone_1, phone_2, email, motto, profile_image) 
                       VALUES ('$school_name','$location','$contact_1','$contact_2','$email','$motto',
                       " . ($image_path ? "'$image_path'" : "NULL") . ")";
            $res = mysqli_query($conn, $insert);
            if($res) {
                $success = "School profile created successfully.";
                header("Location:".SITEURL."view_profile.php");
            } else {
                $errors[] = "Failed to insert: " . mysqli_error($conn);
            }
        } else {
            // Update existing profile
            $errors[] = "school name already exists";
        }
    }
}
?>

<div class="container-fluid">
    <h3 class="text-capitalize fs-6 text-dark py-2">Enter school profile as directed</h3>

    <div class="row g-0 shadow rounded my-4 p-4">
        <?php if(!empty($errors)): ?>
            <div class="alert alert-danger">
                <?php foreach ($errors as $e) echo "<div>$e</div>"; ?>
            </div>
        <?php elseif(!empty($success)): ?>
            <div class="alert alert-success"><?php echo $success; ?></div>
        <?php endif; ?>

        <div class="col-lg-6 col-12 p-2">
            <form method="POST" action="" enctype="multipart/form-data">
                <div class="mb-3">
                    <label class="form-label fw-bold">Enter school name</label>
                    <input type="text" class="form-control shadow-none" name="school_name" placeholder="Enter school name" value="<?php echo isset($school_name) ? htmlspecialchars($school_name):"" ?>" required>
                    <span class="text-danger fs-6">Just enter name dont enter "primary school"</span>
                </div>
                <div class="mb-3">
                    <label class="form-label fw-bold">Enter school location</label>
                    <input type="text" class="form-control shadow-none" name="location" placeholder="Enter school location" value="<?php echo isset($location) ? htmlspecialchars($location):"" ?>" required>
                    <span class="text-danger fs-6">E.g: Kyotera, Kalisizo...</span>
                </div>
                <div class="mb-3">
                    <label class="form-label fw-bold">Enter contacts</label>
                    <input type="text" class="form-control shadow-none" name="contact_1" placeholder="Enter first contact" value="<?php echo isset($contact_1)?htmlspecialchars($contact_1):"" ?>" required>
                    <span class="text-danger fs-6">If you have more than one number, seperate them with "/"</span>
                </div>
                <div class="mb-3">
                    <label class="form-label fw-bold">Enter Post Office Number</label>
                    <input type="text" class="form-control shadow-none" value="<?php echo isset($contact_2)?htmlspecialchars($contact_2):"" ?>" name="contact_2" placeholder="Enter postal number">
                    
                </div>
        </div>

        <div class="col-lg-6 col-12 p-2">
            <div class="mb-3">
                <label class="form-label fw-bold">Enter school email</label>
                <input type="email" class="form-control shadow-none" name="email" placeholder="Enter school email" value="<?php echo isset($email)?htmlspecialchars($email):"" ?>"?>
            </div>
            <div class="mb-3">
                <label class="form-label fw-bold">Enter school motto</label>
                <input type="text" class="form-control shadow-none" name="motto" placeholder="Enter school motto" value="<?php echo isset($motto)?htmlspecialchars($motto) :""?>" required>
            </div>
            <div class="mb-3">
                <label class="form-label fw-bold">Upload school logo</label>
                <input class="form-control" type="file" name="profile" accept=".png,.jpg,.jpeg">
                <span class="text-muted small">Only PNG, JPG, JPEG (max 2MB)</span>
            </div>
            <button type="submit" name="profile" class="btn btn-success w-100">Submit</button>
            </form>
        </div>
    </div>
</div>

<?php include("partials/footer.php"); ?>
