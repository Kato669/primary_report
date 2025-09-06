<?php ob_start();
    include("partials/header.php");
    include("partials/adminOnly.php") ?>
<?php
    if(isset($_SESSION['delete_user'])){
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
            Command: toastr["error"]("'.$_SESSION['delete_user'].'");
        </script>
        ';
        unset($_SESSION['delete_user']);
    }
    if(isset($_SESSION['update_user'])){
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
            Command: toastr["success"]("'.$_SESSION['update_user'].'");
        </script>
        ';
        unset($_SESSION['update_user']);
    }
?>
<div class="container-fluid">
    <!-- button to add class -->
    <div class="row g-0 my-2">
        <div class="col-lg-4 col-md-4 col-sm-12">
            <a href="<?php echo SITEURL ?>add_user.php" class="btn text-capitalize text-white btn-success fs-6">
                add user
                <i class="fa-solid fa-pen-to-square"></i>
            </a>
        </div>
        <h3 class="text-capitalize fs-6 text-dark py-2">view users</h3>
        <table id="example" class="display">
            <thead>
                <tr>
                    <th>Sn</th>
                    <th class="text-capitalize">fullname</th>
                    <th class="text-capitalize">username</th>
                    <!-- <th class="text-capitalize">password</th> -->
                    <th class="text-capitalize">role</th>
                    <th>Action</th>
                </tr>
            </thead>
            <tbody>
                <!-- select from users -->
                 <?php 
                    $users = "SELECT * FROM users";
                    $executeUsers = mysqli_query($conn, $users);
                    if($executeUsers && mysqli_num_rows($executeUsers)){
                        $sn =1;
                        while($fetch_users = mysqli_fetch_assoc($executeUsers)){
                            $user_id = $fetch_users['user_id'];
                            $fullname = $fetch_users['fullname'];
                            $username = $fetch_users['username'];
                            $role = $fetch_users['role'];
                            ?>
                            <tr>
                                <td><?php echo $sn++ ?></td>
                                <td><?php echo $fullname ?></td>
                                <td><?php echo $username ?></td>
                                <!-- <td>katojkalemba</td> -->
                                <td><?php echo $role ?></td>
                                <td>
                                    <a href="<?php echo SITEURL ?>update_user.php?user_id=<?php echo $user_id ?>" class="btn btn-primary"><i class="fa-solid fa-pen-to-square"></i></a>
                                    <a onclick="return confirm('Do you want to delete??')" href="<?php echo SITEURL ?>delete_user.php?user_id=<?php echo $user_id ?>" class="btn btn-danger"><i class="fa-solid fa-trash"></i></a>
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
<?php include("partials/footer.php") ?>