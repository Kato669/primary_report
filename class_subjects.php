<?php include("partials/header.php"); ?>
<?php 
    if (isset($_SESSION['subject_deleted'])) {
        $message = addslashes($_SESSION['subject_deleted']); // escape quotes
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
            "hideMethod": "fadeOut"
        }
        Command: toastr["error"]("'.$_SESSION['subject_deleted'].'");
        </script>
        ';
        unset($_SESSION['subject_deleted']);
    }
    if (isset($_SESSION['update_subject'])) {
        $message = addslashes($_SESSION['update_subject']); // escape quotes
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
            "hideMethod": "fadeOut"
        }
        Command: toastr["success"]("'.$_SESSION['update_subject'].'");
        </script>
        ';
        unset($_SESSION['update_subject']);
    }
?>
<div class="container-fluid">
    <div class="row g-0 my-2">
        <div class="col-lg-4 col-md-4 col-sm-12">
            <a href="<?php echo SITEURL ?>addClass_subject.php" class="btn text-capitalize text-white btn-success fs-6">
                add class subject
                <i class="fa-solid fa-pen-to-square"></i>
            </a>
        </div>
        <h3 class="text-capitalize fs-6 text-dark py-2">view class subject</h3>
        <div class="col-lg-12">
            <table id="example" class="display">
                <thead>
                    <tr>
                        <th>Sn</th>
                        <th>Class</th>
                        <th>Subject</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                    <!-- select data -->
                     <?php 
                        // Ensure database connection is established
                        // include("partials/db_connect.php"); // Make sure this file sets $conn

                        $sql = "SELECT class_subjects.*, classes.class_name AS className, subjects.subject_name AS subjectName FROM class_subjects JOIN classes ON classes.id = class_subjects.class_id JOIN subjects ON subjects.subject_id = class_subjects.subject_id ORDER BY id ASC";
                        $res = mysqli_query($conn, $sql);
                        if($res && mysqli_num_rows($res) > 0){
                            $sn=1;
                            while($row = mysqli_fetch_assoc($res)){
                                $subject_class_id = $row['id'];
                                $class_name = $row['className'];
                                $subject_name = $row['subjectName'];
                                ?>
                                <tr>
                                    <td><?php echo $sn++ ?></td>
                                    <td class="text-uppercase"><?php echo $class_name ?></td>
                                    <td class="text-capitalize"><?php echo $subject_name ?></td>
                                    <td>
                                        <a href="<?php echo SITEURL ?>edit_classSubjects.php?id=<?php echo $subject_class_id ?>" class="btn btn-primary"><i class="fa-solid fa-pen-to-square"></i></a>
                                        <a href="<?php echo SITEURL ?>delete_classSubject.php?id=<?php echo $subject_class_id ?>" class="btn btn-danger" onclick="return confirm('Do you want to delete?')"><i class="fa-solid fa-trash"></i></a>
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