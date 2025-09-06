<?php ob_start();
    include("partials/header.php");
    include("partials/adminOnly.php")?>
    <!-- notification -->
<?php 
    if(isset($_SESSION['delete_subject'])){
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
            Command: toastr["error"]("'.$_SESSION['delete_subject'].'");
        </script>
        ';
        unset($_SESSION['delete_subject']);
    }
    if(isset($_SESSION['update_subject'])){
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
            Command: toastr["success"]("'.$_SESSION['update_subject'].'");
        </script>
        ';
        unset($_SESSION['update_subject']);
    }
?>
<div class="container-fluid">
    <div class="row g-0 my-2">
        <div class="col-lg-4 col-md-4 col-sm-12">
            <a href="<?php echo SITEURL ?>add_subject.php" class="btn text-capitalize text-white btn-success fs-6">
                add subject
                <i class="fa-solid fa-pen-to-square"></i>
            </a>
        </div>
    </div>
    <h3 class="text-capitalize fs-6 text-dark py-2">view subjects</h3>
    <div class="row">
        <div class="col-lg-12">
            <table id="example" class="display">
                <thead>
                    <tr>
                        <th>Sn</th>
                        <th>Subject Name</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                    <!-- select data from subejcts -->
                     <?php 
                        $sql = "SELECT * FROM subjects";
                        $res = mysqli_query($conn, $sql);
                        if($res && mysqli_num_rows($res) > 0){
                            $sn=1;
                            while($row = mysqli_fetch_assoc($res)){
                                $subject_id = $row['subject_id'];
                                $subject_name = $row['subject_name'];
                                ?>
                                <tr>
                                    <td><?php echo $sn++ ?></td>
                                    <td class="text-capitalize"><?php echo $subject_name ?></td>
                                    <td>
                                        <a href="<?php echo SITEURL ?>edit_subject.php?subject_id=<?php echo $subject_id ?>" class="btn btn-primary"><i class="fa-solid fa-pen-to-square"></i></a>
                                        <a href="<?php echo SITEURL ?>delete_subject.php?subject_id=<?php echo $subject_id ?>" class="btn btn-danger" onclick="return confirm('Do you want to delete subject?')"><i class="fa-solid fa-trash"></i></a>
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