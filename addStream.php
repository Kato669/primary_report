<?php 
    ob_start();
    include("partials/header.php");
    include("partials/adminOnly.php");
    if(isset($_POST['addStream'])){
        $class_name = mysqli_real_escape_string($conn, $_POST['class_name']);
        $streamName = strtolower(trim(mysqli_real_escape_string($conn, $_POST['streamName'])));
        $onlyLetters = preg_match('/^[a-zA-Z]+$/', $streamName);
        if(empty($class_name) || empty($streamName)){
            echo '<script>toastr.error("All fields are required");</script>';
        } elseif(!$onlyLetters){
            echo '<script>toastr.error("Stream name should contain only letters");</script>';
        } else {
            // Prepared statement to check if stream exists
            $stmt = $conn->prepare("SELECT id FROM streams WHERE stream_name = ? AND class_id = ?");
            $stmt->bind_param("si", $streamName, $class_name);
            $stmt->execute();
            $stmt->store_result();   

            if($stmt->num_rows > 0){
                echo '<script>toastr.error("Stream already exists for this class");</script>';
            } else {
                // Insert new stream
                $insertStmt = $conn->prepare("INSERT INTO streams (stream_name, class_id) VALUES (?, ?)");
                $insertStmt->bind_param("si", $streamName, $class_name);
                if($insertStmt->execute()){
                    $_SESSION['added_stream'] = "Stream added successfully";
                    header("Location:".SITEURL."streams.php");
                    exit;
                } else {
                    error_log("DB Error: ".$conn->error);
                    echo '<script>toastr.error("Failed to add stream");</script>';
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
                <!-- class Id -->
                <div class="mb-3">
                    <select class="form-select" name="class_name">
                        <option selected disabled>Choose class</option>
                        <!-- choose all classes in database -->
                         <?php 
                            $selectClass = "SELECT * FROM classes";
                            $executeClass = mysqli_query($conn, $selectClass);
                            if(!$executeClass){
                                die("Failed execution". mysqli_error($conn));
                            };
                            $countClasses = mysqli_num_rows($executeClass);
                            if($countClasses > 0){
                                while($row = mysqli_fetch_assoc($executeClass)){
                                    $class_id = $row['id'];
                                    $className = $row['class_name'];
                                    ?>
                                    <option class="text-uppercase" value="<?php echo $class_id ?>"><?php echo $className ?></option>
                                    <?php
                                }
                            }
                         ?>
                        
                    </select>
                </div>
                <!-- stream name -->
                <div class="mb-3">
                    <label for="addClass" class="form-label text-capitalize fw-bold">stream name</label>
                    <input type="text" class="form-control shadow-none" name="streamName" id="addClass" placeholder="Enter stream..." autocomplete="off" required>
                </div>
                <button type="submit" name="addStream" class="btn btn-primary text-capitalize">Add stream</button>
            </form>
        </div>
    </div>
</div>

<?php include("partials/footer.php") ?>