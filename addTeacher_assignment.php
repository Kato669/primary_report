<?php
 ob_start();
    include("partials/header.php");
    include("partials/adminOnly.php") ?>
<div class="container-fluid my-3">
    <div class="row">
        <div class="col-lg-6 col-sm-12 col-md-12 shadow rounded p-5">
            <form method="POST" action="">
                <div class="mb-3">
                    <label for="addClass" class="form-label text-capitalize fw-bold">teacher</label>
                    <select class="form-select shadow-none" name="user_name" required>
                        <option selected disabled>Choose teacher</option>
                        <?php 
                            $users = 'SELECT * FROM users';
                            $executeUser = mysqli_query($conn, $users);
                            if($executeUser && mysqli_num_rows($executeUser)){
                                while($fetchUser = mysqli_fetch_assoc($executeUser)){
                                    $user_id = $fetchUser['user_id'];
                                    $userName = $fetchUser['username'];
                                    echo "<option value='$user_id'>$userName</option>";
                                }
                            }
                        ?>
                    </select>
                </div>

                <div class="mb-3">
                    <label for="addClass" class="form-label text-capitalize fw-bold">class</label>
                    <select class="form-select shadow-none" name="class_name" id="classDropdown" required>
                        <option selected disabled>Choose class</option>
                        <?php 
                            $classes = 'SELECT * FROM classes';
                            $executeClasses = mysqli_query($conn, $classes);
                            if($executeClasses && mysqli_num_rows($executeClasses)>0){
                                while($fetchClass = mysqli_fetch_assoc($executeClasses)){
                                    $class_id = $fetchClass['id'];
                                    $className = $fetchClass['class_name'];
                                    echo "<option value='$class_id'>$className</option>";
                                }
                            }
                        ?>
                    </select>
                </div>

                <div class="mb-3">
                    <label for="addClass" class="form-label text-capitalize fw-bold">stream</label>
                    <select class="form-select shadow-none" name="stream" id="streamDropdown" required>
                        <option selected disabled>Choose stream</option>
                        <?php 
                            $streams = 'SELECT * FROM streams';
                            $executestreams = mysqli_query($conn, $streams);
                            if($executestreams && mysqli_num_rows($executestreams)>0){
                                while($fetchStream = mysqli_fetch_assoc($executestreams)){
                                    $stream_id = $fetchStream['id'];
                                    $streamName = $fetchStream['stream_name'];
                                    echo "<option value='$stream_id'>$streamName</option>";
                                }
                            }
                        ?>
                    </select>
                </div>

                <button type="submit" name="addRole" class="btn btn-primary text-capitalize">Add class</button>
            </form>
        </div>
    </div>
</div>

<script>
document.addEventListener("DOMContentLoaded", function() {
    const classDropdown = document.getElementById("classDropdown");
    const streamDropdown = document.getElementById("streamDropdown");

    classDropdown.addEventListener("change", function() {
        const classId = this.value;

        if(classId) {
            fetch(`get_streams.php?class_id=${classId}`)
                .then(response => response.text())
                .then(data => {
                    streamDropdown.innerHTML = data;
                    // Optionally, if no streams returned, show a default message
                    if (!data.includes("option")) {
                        streamDropdown.innerHTML = '<option value="" disabled selected>No streams found</option>';
                    }
                })
                .catch(err => console.error("Error fetching streams:", err));
        } else {
            streamDropdown.innerHTML = '<option value="" disabled selected>Choose stream</option>';
        }
    });
});
</script>

<!-- inserting in database -->
<?php
    if (isset($_POST['addRole'])) {
        $userId = $_POST['user_name'] ?? "";
        $classID = $_POST['class_name'] ?? "";
        $streamID = $_POST['stream'] ?? "";

        if (!empty($userId) && !empty($classID) && !empty($streamID)) {
            // Use prepared statements to prevent SQL injection
            $stmt = mysqli_prepare($conn, "SELECT 1 FROM teacher_assignments WHERE user_id=? AND class_id=? AND stream_id=?");
            mysqli_stmt_bind_param($stmt, "iii", $userId, $classID, $streamID);
            mysqli_stmt_execute($stmt);
            mysqli_stmt_store_result($stmt);

            if (mysqli_stmt_num_rows($stmt) === 0) {
                mysqli_stmt_close($stmt);

                $insertStmt = mysqli_prepare($conn, "INSERT INTO teacher_assignments (user_id, class_id, stream_id) VALUES (?, ?, ?)");
                mysqli_stmt_bind_param($insertStmt, "iii", $userId, $classID, $streamID);
                $res = mysqli_stmt_execute($insertStmt);
                mysqli_stmt_close($insertStmt);

                if ($res) {
                    echo '
                    <script type="text/javascript">
                        toastr.options = {
                            "closeButton": true,
                            "positionClass": "toast-top-right",
                            "timeOut": "3000"
                        };
                        Command: toastr["success"]("Role assigned successfully");
                    </script>
                    ';
                    exit;
                } else {
                    echo '
                    <script type="text/javascript">
                        toastr.options = {
                            "closeButton": true,
                            "positionClass": "toast-top-right",
                            "timeOut": "3000"
                        };
                        Command: toastr["error"]("Failed to assign role");
                    </script>
                    ';
                }
            } else {
                mysqli_stmt_close($stmt);
                echo '
                <script type="text/javascript">
                    toastr.options = {
                        "closeButton": true,
                        "positionClass": "toast-top-right",
                        "timeOut": "3000"
                    };
                    Command: toastr["error"]("Role already assigned");
                </script>
                ';
            }
        } else {
            echo '
            <script type="text/javascript">
                toastr.options = {
                    "closeButton": true,
                    "positionClass": "toast-top-right",
                    "timeOut": "3000"
                };
                Command: toastr["error"]("Fill all fields");
            </script>
            ';
        }
    }
?>
<?php include("partials/footer.php")?>