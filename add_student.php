<?php 
ob_start();
include("partials/header.php"); 
include("partials/adminOnly.php");

// Show success message if redirected after insert
if (isset($_GET['success']) && $_GET['success'] == 1) {
    echo '<script>
        toastr.options = { "closeButton": true, "timeOut": "3000" };
        Command: toastr["success"]("Student added successfully");
    </script>';
}

// Initialize variables
$firstName = $lastName = $gender = $dob = $lin = $classID = $streamID = $status = "";
$image_name = "";

if (isset($_POST['addstdnt'])) {
    // Get and sanitize input
    $firstName = strtolower(trim(mysqli_real_escape_string($conn, $_POST['first_name'] ?? "")));
    $lastName  = strtolower(trim(mysqli_real_escape_string($conn, $_POST['last_name'] ?? "")));
    $gender    = $_POST['gender'] ?? '';
    $dob       = trim(mysqli_real_escape_string($conn, $_POST['dob'] ?? ""));
    $lin       = trim(mysqli_real_escape_string($conn, $_POST['lin'] ?? ""));
    $classID   = intval($_POST['stdnt_class'] ?? 0);
    $streamID  = intval($_POST['stdnt_stream'] ?? 0);
    $status    = $_POST['status'] ?? "";

    // Handle image upload
    if (!empty($_FILES['image']['name'])) {
        $img_name = $_FILES['image']['name'];
        $ext = strtolower(pathinfo($img_name, PATHINFO_EXTENSION));
        $allowed_ext = ['jpg', 'jpeg', 'png'];

        if (in_array($ext, $allowed_ext)) {
            $image_name = "student_image_" . time() . rand(100, 999) . "." . $ext;
            $image_src = $_FILES['image']['tmp_name'];
            $image_destination = "img/stdent_image/$image_name";

            if (!move_uploaded_file($image_src, $image_destination)) {
                echo '<script>Command: toastr["error"]("Failed to upload image.");</script>';
            }
        } else {
            echo '<script>Command: toastr["error"]("Only JPG, PNG, and JPEG images are allowed.");</script>';
        }
    }

    // Validate name format
    $first_valid = preg_match('/^[a-zA-Z]+$/', $firstName);
    $last_valid  = preg_match('/^[a-zA-Z ]+$/', $lastName);

    if (!empty($firstName) && !empty($lastName) && !empty($gender) && !empty($dob) && $classID && $streamID) {
        if ($first_valid && $last_valid) {
            // Check for duplicate student
            $selectStdnt = "SELECT * FROM students 
                WHERE first_name='$firstName' AND last_name='$lastName' 
                AND dob='$dob' AND class_id=$classID AND stream_id=$streamID AND status='$status'";
            $executestdnt = mysqli_query($conn, $selectStdnt);

            if (mysqli_num_rows($executestdnt) > 0) {
                echo '<script>Command: toastr["error"]("A student with the same name, DOB, class, and stream already exists.");</script>';
            } else {
                $insert_data = "INSERT INTO students SET
                    first_name = '$firstName',
                    last_name = '$lastName',
                    gender = '$gender',
                    dob = '$dob',
                    LIN = '$lin',
                    class_id = $classID,
                    stream_id = $streamID,
                    status = '$status',
                    image = '$image_name'
                ";
                if (mysqli_query($conn, $insert_data)) {
                    header("Location: " . $_SERVER['PHP_SELF'] . "?success=1");
                    exit;
                }
            }
        } else {
            echo '<script>Command: toastr["error"]("First and last name can only contain letters.");</script>';
        }
    } else {
        echo '<script>Command: toastr["error"]("Please fill all required fields.");</script>';
    }
}
?>

<div class="container-fluid my-3">
    <div class="row rounded shadow">
        <div class="col-lg-6 col-sm-12 p-3">
            <form method="POST" action="" enctype="multipart/form-data">
                <div class="mb-3">
                    <label class="form-label fw-bold">First name</label>
                    <input type="text" class="form-control" name="first_name" value="<?= htmlspecialchars($firstName) ?>" required>
                </div>
                <div class="mb-3">
                    <label class="form-label fw-bold">Last name</label>
                    <input type="text" class="form-control" name="last_name" value="<?= htmlspecialchars($lastName) ?>" required>
                    <span class="text-danger fs-6">If a student has other name, put it here too</span>
                </div>
                <div class="mb-3">
                    <label class="form-label fw-bold">Gender</label>
                    <select class="form-select" name="gender" required>
                        <option value="" disabled selected>Choose gender</option>
                        <option value="male" <?= $gender=="male" ? "selected": "" ?>>Male</option>
                        <option value="female" <?= $gender=="female" ? "selected": "" ?>>Female</option>
                    </select>
                </div>
                <div class="mb-3">
                    <label class="form-label fw-bold">Date of birth</label>
                    <input type="date" class="form-control" name="dob" value="<?= htmlspecialchars($dob) ?>" required>
                </div>
                <div class="mb-3">
                    <label class="form-label fw-bold">Status</label>
                    <select class="form-select" name="status" required>
                        <option value="" disabled selected>Choose status</option>
                        <option value="day" <?= $status=="day" ? "selected": "" ?>>Day</option>
                        <option value="boarding" <?= $status=="boarding" ? "selected": "" ?>>Boarding</option>
                    </select>
                </div>
        </div>

        <div class="col-lg-6 col-sm-12 p-3">
            <div class="mb-3">
                <label class="form-label fw-bold">LIN</label>
                <input type="text" class="form-control text-uppercase" name="lin" value="<?= htmlspecialchars($lin) ?>">
            </div>
            <div class="mb-3">
                <label class="form-label fw-bold">Student class</label>
                <select class="form-select" name="stdnt_class" id="stdnt_class" required>
                    <option value="">Choose class</option>
                    <?php 
                    $selectClass = "SELECT id, class_name FROM classes ORDER BY class_name";
                    $executeClass = mysqli_query($conn, $selectClass);
                    while($fetchedClass = mysqli_fetch_assoc($executeClass)){
                        echo '<option value="'.$fetchedClass['id'].'" '.(($classID==$fetchedClass['id']) ? 'selected':'').'>'.htmlspecialchars($fetchedClass['class_name']).'</option>';
                    }
                    ?>
                </select>
            </div>
            <div class="mb-3">
                <label class="form-label fw-bold">Student stream</label>
                <select class="form-select" name="stdnt_stream" id="stdnt_stream" required>
                    <option value="">Choose class first</option>
                </select>
            </div>
            <div class="mb-3">
                <label class="form-label fw-bold">Upload image</label>
                <input type="file" class="form-control" name="image" accept=".jpg, .jpeg, .png">
            </div>
            <button type="submit" name="addstdnt" class="btn btn-primary">Add student</button>
            </form>
        </div>
    </div>
</div>

<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script>
$(document).ready(function () {
    $('#stdnt_class').on('change', function () {
        let classId = $(this).val();
        if (classId) {
            // ✅ Send request using GET instead of POST
            $.get('get_streams.php', { class_id: classId })
                .done(function (response) {
                    $('#stdnt_stream').html(response);
                })
                .fail(function () {
                    $('#stdnt_stream').html('<option value="">Error loading streams</option>');
                });
        } else {
            $('#stdnt_stream').html('<option value="">Choose class first</option>');
        }
    });
});
</script>

<?php include("partials/footer.php"); ?>
