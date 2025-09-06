<?php ob_start();
    include("partials/header.php");
    include("partials/adminOnly.php") ?>
<?php
if(isset($_SESSION['delete_assign'])){
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
            Command: toastr["success"]("'.$_SESSION['delete_assign'].'");
        </script>
        ';
unset($_SESSION['delete_assign']);
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
            <a href="<?php echo SITEURL ?>addTeacher_assignment.php" class="btn text-capitalize text-white btn-success fs-6">
                assign roles
                <i class="fa-solid fa-pen-to-square"></i>
            </a>
        </div>
        <h3 class="text-capitalize fs-6 text-dark py-2">view teacher assignment</h3>
        <table id="example" class="display">
            <thead>
                <tr>
                    <th>Sn</th>
                    <th class="text-capitalize">teacher's Name</th>
                    <th class="text-capitalize">class</th>
                    <th class="text-capitalize">stream</th>
                    <th>Action</th>
                </tr>
            </thead>
            <tbody>
                <!-- select data to populate table -->
                <?php 
                $sql = "SELECT teacher_assignments.*,
                    users.username AS username,
                    classes.class_name AS className,
                    streams.stream_name AS streamName
                    FROM teacher_assignments
                    JOIN users ON users.user_id = teacher_assignments.user_id
                    JOIN classes ON classes.id = teacher_assignments.class_id
                    JOIN streams ON streams.id = teacher_assignments.stream_id
                ";
                $res = mysqli_query($conn, $sql);
                if($res && mysqli_num_rows($res) > 0){
                    $sn = 1;
                    while($row = mysqli_fetch_assoc($res)){
                        $assignment_id = $row['id'];
                        $userName = $row['username'];
                        $className = $row['className'];
                        $streamName = $row['streamName'];
                        ?>
                        <tr>
                            <td><?php echo $sn++ ?></td>
                            <td><?php echo $userName ?></td>
                            <td><?php echo $className ?></td>
                            <td><?php echo $streamName ?></td>
                            <td>
                                <a href="<?php echo SITEURL ?>update_role.php?id=<?php echo $assignment_id ?>" class="btn btn-primary"><i class="fa-solid fa-pen-to-square"></i></a>
                                <a onclick="return confirm('Do you want to delete?')" href="<?php echo SITEURL ?>delete_assign.php?id=<?php echo $assignment_id?>" class="btn btn-danger"><i class="fa-solid fa-trash"></i></a>
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
<?php include('partials/footer.php') ?>