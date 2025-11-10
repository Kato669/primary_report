<?php 
    ob_start();
    include("partials/header.php");
    include("partials/adminOnly.php")
?>
<div class="container-fluid">
    <!-- button to add class -->
    <!-- <div class="row g-0 my-2">
        <div class="col-lg-4 col-md-4 col-sm-12">
            <a href="addClass.php" class="btn text-capitalize text-white btn-success fs-6">
                add class
                <i class="fa-solid fa-pen-to-square"></i>
            </a>
        </div>
    </div> -->
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
    <h4 class="text-uppercase fw-bold text-center mb-3 bg-primary text-white py-2 rounded">CLASSES FOR <?php echo htmlspecialchars($school_name); ?> PRIMARY SCHOOL</h4>
     <div class="row">
        <div class="col-12">
            <table id="example" class="display">
                <thead>
                    <tr>
                        <th>Sn</th>
                        <th>Class Name</th>
                        <th>Prefix</th>
                        <th>Level</th>
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
                                $prefix = $rows['prefix'];
                                $level = $rows['LEVEL'];
                                ?>
                                <tr>
                                    <td><?php echo $sn++ ?></td>
                                    <td class="text-capitalize"><?php echo $className ?></td>
                                    <td class="text-capitalize"><?php echo $prefix ?></td>
                                    <td class="text-capitalize"><?php echo $level ?></td>
                                    
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
