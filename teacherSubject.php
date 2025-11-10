<?php
ob_start();
include("partials/header.php");
include("partials/adminOnly.php");
?>

<?php
// Toastr notifications
if(isset($_SESSION['teacher_subject_deleted'])){
    echo "
    <script type='text/javascript'>
        toastr.options = {
            closeButton: true,
            positionClass: 'toast-top-right',
            showDuration: '300',
            hideDuration: '1000',
            timeOut: '3000'
        };
        Command: toastr['success']('{$_SESSION['teacher_subject_deleted']}');
    </script>";
    unset($_SESSION['teacher_subject_deleted']);
}

if(isset($_SESSION['success'])){
    echo "
    <script type='text/javascript'>
        toastr.options = {
            closeButton: true,
            positionClass: 'toast-top-right',
            showDuration: '300',
            hideDuration: '1000',
            timeOut: '3000'
        };
        Command: toastr['success']('{$_SESSION['success']}');
    </script>";
    unset($_SESSION['success']);
}
?>

<div class="container-fluid">
    <div class="row g-0 my-2">
        <div class="col-lg-4 col-md-4 col-sm-12">
            <a href="<?php echo SITEURL ?>addteachersub.php" class="btn btn-success text-white fs-6 text-capitalize">
                Manage Staff Load <i class="fa-solid fa-pen-to-square"></i>
            </a>
        </div>
        <div class="col-12 mt-2">
            <h4 class="text-uppercase fw-bold text-center mb-3 bg-primary text-white py-2 rounded">STAFF LOAD AT <?php echo htmlspecialchars($school_name); ?> PRIMARY SCHOOL</h4>
        </div>
        <div class="col-12 mt-2">
            <table id="example" class="table table-hover display" style="width: 100%;">
                <thead>
                    <tr>
                        <th>Sn</th>
                        <th class="text-capitalize">fullname</th>
                        <th class="text-capitalize">initials</th>
                        <th class="text-capitalize">class</th>
                        <th class="text-capitalize">stream</th>
                        <th class="text-capitalize">subject</th>
                        <th class="text-capitalize">term</th>
                        <th class="text-capitalize">year</th>
                        <th class="text-capitalize">action</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    $select = "
                        SELECT tsa.*, u.fullname, t.term_name AS termname, 
                               c.class_name AS classname, s.stream_name AS streamname, 
                               sub.subject_name AS subjectname
                        FROM teacher_subject_assignments tsa
                        JOIN users u ON u.user_id = tsa.teacher_id
                        JOIN terms t ON t.term_id = tsa.term_id
                        JOIN classes c ON c.id = tsa.class_id
                        JOIN streams s ON s.id = tsa.stream_id
                        JOIN subjects sub ON sub.subject_id = tsa.subject_id
                        ORDER BY u.fullname, c.class_name, s.stream_name
                    ";
                    $execute = mysqli_query($conn, $select);
                    if($execute && mysqli_num_rows($execute) > 0){
                        $sn = 1;
                        while($row = mysqli_fetch_assoc($execute)){
                            $assign_id = $row['id'];
                            $fullname = $row['fullname'];
                            $initials = $row['initials'];
                            $class = $row['classname'];
                            $stream = $row['streamname'];
                            $subject = $row['subjectname'];
                            $term = $row['termname'];
                            $year = $row['academic_year'];
                            ?>
                            <tr>
                                <td><?php echo $sn++; ?></td>
                                <td class="text-capitalize"><?php echo htmlspecialchars($fullname); ?></td>
                                <td class="text-capitalize"><?php echo htmlspecialchars($initials); ?></td>
                                <td class="text-capitalize"><?php echo htmlspecialchars($class); ?></td>
                                <td class="text-capitalize"><?php echo htmlspecialchars($stream); ?></td>
                                <td class="text-capitalize"><?php echo htmlspecialchars($subject); ?></td>
                                <td class="text-capitalize"><?php echo htmlspecialchars($term); ?></td>
                                <td class="text-capitalize"><?php echo htmlspecialchars($year); ?></td>
                                <td>
                                    <div class="d-flex gap-2">
                                        <a href="<?php echo SITEURL ?>update_teacherSubject.php?id=<?php echo $assign_id ?>" class="btn btn-primary btn-sm">
                                            <i class="fa-solid fa-pen-to-square"></i>
                                        </a>
                                        <a href="<?php echo SITEURL ?>delete_teacherSubject.php?id=<?php echo $assign_id ?>" class="btn btn-danger btn-sm">
                                            <i class="fa-solid fa-trash"></i>
                                        </a>
                                    </div>
                                </td>
                            </tr>
                            <?php
                        }
                    }
                    ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<?php include("partials/footer.php"); ?>