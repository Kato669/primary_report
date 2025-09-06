<?php ob_start();
    include("partials/header.php");
    include("partials/adminOnly.php"); ?>
<?php
if(isset($_SESSION['teacher_subject_deleted'])){
    echo '
    <script type="text/javascript">
    toastr.options = {
        "closeButton": true,
        "debug": false,
        "newestOnTop": false,
        "progressBar": false,
        "positionClass": "toast-top-right",
        "preventDuplicates": false,
        "onclick": null,
        "showDuration": "300",
        "hideDuration": "1000",
        "timeOut": "3000",
        "extendedTimeOut": "1000",
        "showEasing": "swing",
        "hideEasing": "linear",
        "showMethod": "fadeIn",
        "hideMethod": "fadeOut"}
        Command: toastr["success"]("'.$_SESSION['teacher_subject_deleted'].'");
    </script>
    ';
    unset($_SESSION['teacher_subject_deleted']);
}
if(isset($_SESSION['success'])){
    echo '
    <script type="text/javascript">
    toastr.options = {
        "closeButton": true,
        "debug": false,
        "newestOnTop": false,
        "progressBar": false,
        "positionClass": "toast-top-right",
        "preventDuplicates": false,
        "onclick": null,
        "showDuration": "300",
        "hideDuration": "1000",
        "timeOut": "3000",
        "extendedTimeOut": "1000",
        "showEasing": "swing",
        "hideEasing": "linear",
        "showMethod": "fadeIn",
        "hideMethod": "fadeOut"}
        Command: toastr["success"]("'.$_SESSION['success'].'");
    </script>
    ';
    unset($_SESSION['success']);
}
?>
<div class="container-fluid">
    <!-- button to add class -->
    <div class="row g-0 my-2">
        <div class="col-lg-4 col-md-4 col-sm-12">
            <a href="<?php echo SITEURL ?>addteachersub.php" class="btn text-capitalize text-white btn-success fs-6">
                add term
                <i class="fa-solid fa-pen-to-square"></i>
            </a>
        </div>
        <h3 class="text-capitalize fs-6 text-dark py-2">view teachers</h3>
        <div class="col-lg-12">
            <table id="example" class="display">
                <thead>
                    <tr>
                        <th>Sn</th>
                        <th class="text-capitalize">fullname</th>
                        <th class="text-capitalize">initials</th>
                        <th class="text-capitalize">class</th>
                        <th class="text-capitalize">stream</th>
                        <th class="text-capitalize">subject</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                    <!-- select from teacher_subject_assignments -->
                    <?php
                        $select = "SELECT teacher_subject_assignments.*,
                        users.fullname AS fullname,
                        classes.class_name AS classname,
                        streams.stream_name AS streamname,
                        subjects.subject_name AS subjectname
                        FROM teacher_subject_assignments
                        JOIN users ON users.user_id=teacher_subject_assignments.teacher_id
                        JOIN classes ON classes.id=teacher_subject_assignments.class_id
                        JOIN streams ON streams.id=teacher_subject_assignments.stream_id
                        JOIN subjects ON subjects.subject_id=teacher_subject_assignments.subject_id
                        ";
                        $execute = mysqli_query($conn, $select);
                        if($execute && mysqli_num_rows($execute)){
                            $sn=1;
                            while($fetchInitials = mysqli_fetch_assoc($execute)){
                                $assign_id = $fetchInitials['id'];
                                $fullname = $fetchInitials['fullname'];
                                $initials = $fetchInitials['initials'];
                                $classes = $fetchInitials['classname'];
                                $streams = $fetchInitials['streamname'];
                                $subjects = $fetchInitials['subjectname'];
                                ?>
                                    <tr>
                                        <td><?php echo $sn++ ?></td>
                                        <td class="text-capitalize"><?php echo $fullname ?></td>
                                        <td class="text-capitalize"><?php echo $initials ?></td>
                                        <td class="text-capitalize"><?php echo $classes?></td>
                                        <td class="text-capitalize"><?php echo $streams ?></td>
                                        <td class="text-capitalize"><?php echo $subjects ?></td>
                                        <td>
                                            <a href="<?php echo SITEURL ?>update_teacherSubject.php?id=<?php echo $assign_id ?>" class="btn btn-primary"><i class="fa-solid fa-pen-to-square"></i></a>
                                            <a href="<?php echo SITEURL ?>delete_teacherSubject.php?id=<?php echo $assign_id ?>" class="btn btn-danger"><i class="fa-solid fa-trash"></i></a>
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
<?php include("partials/footer.php") ?>