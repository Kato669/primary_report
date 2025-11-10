<?php 
    ob_start();
    include("partials/header.php");
?>
<?php
// Include database connection
// include("config/db_connect.php");

if (isset($_POST['updatestdnt'])) {
    $student_id = intval($_POST['student_id']);
    $firstName  = strtoupper(trim(mysqli_real_escape_string($conn, $_POST['first_name'])));
    $lastName   = strtoupper(trim(mysqli_real_escape_string($conn, $_POST['last_name'])));
    $gender     = strtoupper(mysqli_real_escape_string($conn, $_POST['gender']));
    $dob        = mysqli_real_escape_string($conn, $_POST['dob']);
    $status     = strtoupper(mysqli_real_escape_string($conn, $_POST['status']));
    $lin        = strtoupper(trim(mysqli_real_escape_string($conn, $_POST['lin'])));
    $classID    = intval($_POST['stdnt_class']);
    $streamID   = strtoupper(intval($_POST['stdnt_stream']));

    // Array to store validation errors
    $errors = [];

    // ✅ VALIDATIONS
    if (!preg_match("/^[a-zA-Z]+$/", $firstName)) {
        $errors[] = "First name must contain only letters.";
    }
    if (!preg_match("/^[a-zA-Z ]+$/", $lastName)) {
        $errors[] = "Last name must contain only letters.";
    }
    if (empty($gender)) {
        $errors[] = "Please select gender.";
    }
    if (empty($dob)) {
        $errors[] = "Date of birth is required.";
    }
    if (empty($status)) {
        $errors[] = "Please select status.";
    }
    if (empty($classID)) {
        $errors[] = "Please select class.";
    }
    if (empty($streamID)) {
        $errors[] = "Please select stream.";
    }

    // Check for duplicate (ignore current student)
    $checkDuplicate = "SELECT * FROM students 
                       WHERE first_name='$firstName' 
                       AND last_name='$lastName' 
                       AND student_id != $student_id";
    $runDuplicate = mysqli_query($conn, $checkDuplicate);
    if (mysqli_num_rows($runDuplicate) > 0) {
        $errors[] = "A student with the same name already exists.";
    }

    // If errors exist, display them
    if (!empty($errors)) {
        foreach ($errors as $err) {
            echo "<div class='alert alert-danger'>$err</div>";
        }
    } else {
        // ✅ HANDLE IMAGE UPLOAD
        $image_name = ""; // Will hold final image name
        $selectOldImg = mysqli_query($conn, "SELECT image FROM students WHERE student_id=$student_id");
        $oldData = mysqli_fetch_assoc($selectOldImg);
        $old_image = $oldData['image'];

        if (!empty($_FILES['image']['name'])) {
            $image_name = $_FILES['image']['name'];
            $image_ext = pathinfo($image_name, PATHINFO_EXTENSION);
            $allowed_ext = ['jpg', 'jpeg', 'png'];

            if (in_array(strtolower($image_ext), $allowed_ext)) {
                $new_image_name = "student_" . time() . "." . $image_ext;
                $destination = "img/stdent_image/" . $new_image_name;

                if (move_uploaded_file($_FILES['image']['tmp_name'], $destination)) {
                    // Delete old image if exists
                    if (!empty($old_image) && file_exists("img/stdent_image/" . $old_image)) {
                        unlink("img/stdent_image/" . $old_image);
                    }
                    $image_name = $new_image_name;
                } else {
                    echo "<div class='alert alert-danger'>Failed to upload image.</div>";
                    $image_name = $old_image; // fallback to old
                }
            } else {
                echo "<div class='alert alert-danger'>Invalid image type. Only JPG, PNG, JPEG allowed.</div>";
                $image_name = $old_image;
            }
        } else {
            $image_name = $old_image; // No new image, keep old
        }

        // ✅ UPDATE STUDENT DATA
        $updateQuery = "UPDATE students SET
                        first_name='$firstName',
                        last_name='$lastName',
                        gender='$gender',
                        dob='$dob',
                        status='$status',
                        LIN='$lin',
                        class_id='$classID',
                        stream_id='$streamID',
                        image='$image_name'
                        WHERE student_id=$student_id";

        $executeUpdate = mysqli_query($conn, $updateQuery);

        if ($executeUpdate) {
            echo "<div class='alert alert-success'>Student updated successfully.</div>";
            header('Location:'.SITEURL."students.php");
            // Optionally redirect
            // header("Location: students.php?success=1");
            // exit;
        } else {
            echo "<div class='alert alert-danger'>Failed to update student: " . mysqli_error($conn) . "</div>";
        }
    }
}
?>

<?php
// Fetch student data before displaying form
if (isset($_GET['student_id'])) {
    $student_id = intval($_GET['student_id']);
    $selectStudent = "SELECT * FROM students WHERE student_id = $student_id";
    $executeStudent = mysqli_query($conn, $selectStudent);
    if ($executeStudent && mysqli_num_rows($executeStudent) > 0) {
        $studentData = mysqli_fetch_assoc($executeStudent);

        $firstName = $studentData['first_name'];
        $lastName = $studentData['last_name'];
        $gender = $studentData['gender'];
        $dob = $studentData['dob'];
        $status = $studentData['status'];
        $lin = $studentData['LIN'];
        $classID = $studentData['class_id'];
        $streamID = $studentData['stream_id'];
        $image_name = $studentData['image'];
    } else {
        die("Student not found.");
    }
} else {
    die("No student ID provided.");
}
?>

<div class="container-fluid">
        <div class="row">
         <h3 class="text-capitalize fs-6 text-dark py-2">edit students</h3>
            <div class="col-lg-6">
                <form method="POST" action="" enctype="multipart/form-data">
        <!-- Hidden input for student ID -->
                    <input type="hidden" name="student_id" value="<?php echo $student_id; ?>">

                    <div class="mb-3">
                        <label for="firstname" class="form-label text-capitalize fw-bold">first name</label>
                        <input type="text" class="form-control shadow-none" name="first_name" id="firstname"
                            value="<?php echo htmlspecialchars($firstName); ?>" placeholder="first name" autocomplete="off">
                    </div>

                    <div class="mb-3">
                        <label for="lastname" class="form-label text-capitalize fw-bold">last name</label>
                        <input type="text" class="form-control shadow-none" name="last_name" id="lastname"
                            value="<?php echo htmlspecialchars($lastName); ?>" placeholder="last name" autocomplete="off">
                    </div>

                    <div class="mb-3">
                        <label class="form-label text-capitalize fw-bold">gender</label>
                        <select class="form-select shadow-none" name="gender" required>
                            <option disabled>Choose gender</option>
                            <option value="MALE" <?php echo ($gender == "male") ? "selected" : ""; ?>>male</option>
                            <option value="FEMALE" <?php echo ($gender == "female") ? "selected" : ""; ?>>female</option>
                        </select>
                    </div>

                    <div class="mb-3">
                        <label for="dob" class="form-label text-capitalize fw-bold">date of birth</label>
                        <input type="date" value="<?php echo htmlspecialchars($dob); ?>" class="form-control shadow-none" name="dob"
                            id="dob" autocomplete="off">
                    </div>

                    <div class="mb-3">
                        <label class="form-label text-capitalize fw-bold">status</label>
                        <select class="form-select shadow-none" name="status">
                            <option disabled>Choose status</option>
                            <option value="day" <?php echo ($status == "day") ? "selected" : ""; ?>>day</option>
                            <option value="boarding" <?php echo ($status == "boarding") ? "selected" : ""; ?>>boarding</option>
                        </select>
                    </div>
            </div>
            <div class="col-lg-6">
                    <div class="mb-3">
                        <label for="lin" class="form-label text-capitalize fw-bold">LIN</label>
                        <input type="text" class="form-control shadow-none text-uppercase" name="lin"
                            value="<?php echo htmlspecialchars($lin); ?>" id="lin" placeholder="Enter lin" autocomplete="off">
                    </div>

                    <div class="mb-3">
    <label class="form-label text-capitalize fw-bold">Student Class</label>
    <select id="studentClass" class="form-select shadow-none" name="stdnt_class" required>
        <option value="" disabled <?= !$classID ? 'selected' : ''; ?>>Choose class</option>
        <?php
        $selectClass = "SELECT * FROM classes";
        $executeClass = mysqli_query($conn, $selectClass);
        while ($fetchedClass = mysqli_fetch_assoc($executeClass)) {
            $class_id = $fetchedClass['id'];
            $className = $fetchedClass['class_name'];
            echo "<option value='$class_id' " . (($classID == $class_id) ? 'selected' : '') . ">$className</option>";
        }
        ?>
    </select>
</div>

<div class="mb-3">
    <label class="form-label text-capitalize fw-bold">Student Stream</label>
    <select id="studentStream" class="form-select shadow-none" name="stdnt_stream" required>
        <option value="" disabled <?= !$streamID ? 'selected' : ''; ?>>Choose stream</option>
        <?php
        // Preload streams only if a class is selected
        if ($classID) {
            $selectStream = "SELECT * FROM streams WHERE class_id=$classID";
            $executeStream = mysqli_query($conn, $selectStream);
            while ($fetchedStrm = mysqli_fetch_assoc($executeStream)) {
                $stream_id = $fetchedStrm['id'];
                $streamName = $fetchedStrm['stream_name'];
                echo "<option value='$stream_id' " . (($streamID == $stream_id) ? 'selected' : '') . ">$streamName</option>";
            }
        }
        ?>
    </select>
</div>

<!-- jQuery for AJAX -->
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script>
$(document).ready(function(){
    function loadStreams(classID, selectedStream = null){
        if(!classID) return;
        $.get("get_streams.php", { class_id: classID })
         .done(function(data){
            $("#studentStream").html(data);
            if(selectedStream) $("#studentStream").val(selectedStream);
         })
         .fail(function(xhr){
            console.error("Stream AJAX Error:", xhr.responseText);
         });
    }

    // On class change → fetch streams
    $("#studentClass").change(function(){
        loadStreams($(this).val());
    });

    // Preload streams if a class is already selected
    <?php if($classID): ?>
        loadStreams(<?= intval($classID) ?>, <?= $streamID ? intval($streamID) : 'null' ?>);
    <?php endif; ?>
});
</script>


                    <div class="mb-3">
                        <label for="image" class="form-label text-capitalize fw-bold">upload new image</label>
                        <input type="file" class="form-control shadow-none" name="image" id="image" accept=".jpg, .png, .jpeg">
                        <?php if (!empty($image_name)) : ?>
                            <small class="d-block mt-1">Current Image: <img class="img-fluid d-block" src="<?php echo SITEURL ?>img/stdent_image/<?php echo $image_name ?>" width="100" height="100" ></small>
                            <?php else: ?>
                                <?php echo "No Image" ?>
                        <?php endif; ?>
                    </div>

                    <button type="submit" name="updatestdnt" class="btn btn-warning text-capitalize">Update student</button>
                </form>
            </div>

         </div>
    </div>
</div>

<?php include("partials/footer.php") ?>