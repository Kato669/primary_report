<?php 
ob_start();
include("partials/header.php");
include("partials/adminOnly.php");

if(isset($_POST['addClass'])){
    $className = strtolower(trim($_POST['className'])); 

    if(empty($className)){
        echo '<script>toastr.error("Class name cannot be empty");</script>';
    } else {
        // Prepared statement to check if class exists
        $stmt = $conn->prepare("SELECT id FROM classes WHERE class_name = ?");
        $stmt->bind_param("s", $className);
        $stmt->execute();
        $stmt->store_result();   

        if($stmt->num_rows > 0){
            echo '<script>toastr.error("Class already exists");</script>';
        } else {
            // Insert new class
            $insertStmt = $conn->prepare("INSERT INTO classes (class_name) VALUES (?)");
            $insertStmt->bind_param("s", $className);
            if($insertStmt->execute()){
                $_SESSION['added_class'] = "Class added successfully";
                header("Location:".SITEURL."class.php");
                exit;
            } else {
                error_log("DB Error: ".$conn->error);
                echo '<script>toastr.error("Failed to add class");</script>';
            }
            $insertStmt->close();
        }
        $stmt->close();
    }
}
?>


<div class="container-fluid my-3">
    <div class="row">
        <div class="col-lg-6 col-sm-12 col-md-12 shadow rounded p-5">
            <form method="POST" action="">
                <div class="mb-3">
                    <label for="addClass" class="form-label text-capitalize fw-bold">class name</label>
                    <input type="text" class="form-control shadow-none" name="className" id="addClass" placeholder="P1, P2, P6..." autocomplete="off" required>
                    <span class="text-danger fs-6">please enter classes as p1,p2 not primary one..</span>
                </div>
                <button type="submit" name="addClass" class="btn btn-primary text-capitalize">Add class</button>
            </form>
        </div>
    </div>
</div>

<?php include("partials/footer.php") ?>