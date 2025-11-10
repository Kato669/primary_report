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
        <div class="mt-4"></div>
        <h4 class="text-uppercase fw-bold text-center mb-3 bg-primary text-white py-2 rounded">CLASS SUBJECTS AT <?php echo htmlspecialchars($school_name); ?> PRIMARY SCHOOL</h4>
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

                        $sql = "SELECT 
                                    c.id as class_id,
                                    c.class_name AS full_class_name,
                                    CASE c.class_name 
                                        WHEN 'Primary One' THEN 'P.1'
                                        WHEN 'Primary Two' THEN 'P.2'
                                        WHEN 'Primary Three' THEN 'P.3'
                                        WHEN 'Primary Four' THEN 'P.4'
                                        WHEN 'Primary Five' THEN 'P.5'
                                        WHEN 'Primary Six' THEN 'P.6'
                                        WHEN 'Primary Seven' THEN 'P.7'
                                        ELSE c.class_name
                                    END AS className,
                                    GROUP_CONCAT(s.subject_name ORDER BY s.subject_name ASC SEPARATOR ', ') AS subjects,
                                    GROUP_CONCAT(cs.id ORDER BY s.subject_name ASC SEPARATOR ',') AS subject_ids
                                FROM classes c
                                LEFT JOIN class_subjects cs ON c.id = cs.class_id
                                LEFT JOIN subjects s ON s.subject_id = cs.subject_id
                                GROUP BY c.id, c.class_name
                                ORDER BY FIELD(c.class_name, 
                                    'Primary One', 
                                    'Primary Two', 
                                    'Primary Three', 
                                    'Primary Four', 
                                    'Primary Five', 
                                    'Primary Six', 
                                    'Primary Seven')";
                        $res = mysqli_query($conn, $sql);
                        if($res && mysqli_num_rows($res) > 0){
                            $sn=1;
                            while($row = mysqli_fetch_assoc($res)){
                                $class_name = $row['className'];
                                $subjects = $row['subjects'] ? $row['subjects'] : 'No subjects assigned';
                                $subject_ids = $row['subject_ids'];
                                ?>
                                <tr>
                                    <td><?php echo $sn++ ?></td>
                                    <td class="text-uppercase"><?php echo $class_name ?></td>
                                    <td class="text-capitalize"><?php echo $subjects ?></td>
                                    <td>
                                        <?php if($subject_ids) { ?>
                                            <a href="<?php echo SITEURL ?>edit_classSubjects.php?class_id=<?php echo $row['class_id'] ?>" class="btn btn-primary"><i class="fa-solid fa-pen-to-square"></i></a>
                                            <a href="<?php echo SITEURL ?>delete_classSubject.php?class_id=<?php echo $row['class_id'] ?>" class="btn btn-danger" onclick="return confirm('Do you want to delete all subjects for this class?')"><i class="fa-solid fa-trash"></i></a>
                                        <?php } ?>
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