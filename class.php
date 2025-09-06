<?php 
    ob_start();
    include("partials/header.php");
    include("partials/adminOnly.php")
?>
<div class="container-fluid">
    <!-- button to add class -->
    <div class="row g-0 my-2">
        <div class="col-lg-4 col-md-4 col-sm-12">
            <a href="<?php echo SITEURL ?>addClass.php" class="btn text-capitalize text-white btn-success fs-6">
                add class
                <i class="fa-solid fa-pen-to-square"></i>
            </a>
        </div>
    </div>
    <!-- successfully submision -->
     <?php
     if(isset($_SESSION['added_class'])){
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
            Command: toastr["success"]("class added successfully");
        </script>
        ';
        unset($_SESSION['added_class']);
     }
    //  displaying delete message
    if(isset($_SESSION['delete_class'])){
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
            Command: toastr["success"]("'.$_SESSION['delete_class'].'");
        </script>
        ';
        unset($_SESSION['delete_class']);
     }
     if(isset($_SESSION['added_class'])){
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
            Command: toastr["success"]("class added successfully");
        </script>
        ';
        unset($_SESSION['added_class']);
     }
     ?>
    <!-- table of content -->
     <h3 class="text-capitalize fs-6 text-dark py-2">view classes</h3>
     <div class="row">
        <div class="col-12">
            <table id="example" class="display">
                <thead>
                    <tr>
                        <th>Sn</th>
                        <th>Class Name</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                        $selectData = "SELECT * FROM classes";
                        $executeData = mysqli_query($conn, $selectData);
                        if(!$executeData){
                            die("Failed execution". mysqli_error($conn));
                        }
                        $countRows = mysqli_num_rows($executeData);
                        if($countRows>0){
                            $sn=1;
                            while($rows = mysqli_fetch_assoc($executeData)){
                                $class_id = $rows['id'];
                                $className = $rows['class_name'];
                                ?>
                                <tr>
                                    <td><?php echo $sn++ ?></td>
                                    <td class="text-uppercase fw-bold"><?php echo $className ?></td>
                                    <td>
                                        <!-- delete class -->
                                        <a href="<?php echo SITEURL ?>deleteClass.php?id=<?php echo $class_id ?>" 
                                            class="btn btn-danger" 
                                            onclick="return confirm('Do you want to delete this class?')">
                                            <i class="fa-solid fa-trash-can"></i>
                                        </a>

                                        
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
