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
        <?php 
            // Show a descriptive heading for this page with the school name (school name provided by header.php)
            // Keep it bold and uppercase as requested
        ?>
        <div>
        <h4 class="text-uppercase fw-bold text-center mb-3 bg-primary text-white py-2 rounded">LOGIN DETAILS FOR STAFF MEMBERS OF <?php echo htmlspecialchars($school_name); ?> PRIMARY SCHOOL</h4>
        </div>
        <table id="example" class="display">
            <thead>
                <tr>
                    <th>Sn</th>
                    <th class="text-capitalize">fullname</th>
                    <th class="text-capitalize">username</th>
                    <th class="text-capitalize">password</th>
                    <th class="text-capitalize">role</th>
                    <th>Action</th>
                </tr>
            </thead>
            <tbody>
                <!-- select from users -->
                 <?php 
                    // Detect suspension/active column
                    $flag_col = null;
                    $flag_semantics = 'suspended_when_1';
                    $res = mysqli_query($conn, "SHOW COLUMNS FROM users LIKE 'is_suspended'");
                    if ($res && mysqli_num_rows($res) > 0) {
                        $flag_col = 'is_suspended';
                        $flag_semantics = 'suspended_when_1';
                    } else {
                        $res = mysqli_query($conn, "SHOW COLUMNS FROM users LIKE 'is_active'");
                        if ($res && mysqli_num_rows($res) > 0) {
                            $flag_col = 'is_active';
                            $flag_semantics = 'active_when_1';
                        } else {
                            $flag_col = 'is_deleted';
                            $flag_semantics = 'suspended_when_1';
                        }
                    }

                    // Show all users (active and suspended). We'll mark suspended users in the UI and
                    // provide Suspend/Unsuspend action links that toggle the flag in delete_user.php
                    $users_sql = "SELECT * FROM users";
                    $executeUsers = mysqli_query($conn, $users_sql);
                    if($executeUsers && mysqli_num_rows($executeUsers)){
                        $sn =1;
                        while($fetch_users = mysqli_fetch_assoc($executeUsers)){
                            $user_id = $fetch_users['user_id'];
                            $fullname = $fetch_users['fullname'];
                            $username = $fetch_users['username'];
                            $password = $fetch_users['password'];
                            $role = $fetch_users['role'];
                            $flag_val = isset($fetch_users[$flag_col]) ? (int)$fetch_users[$flag_col] : 0;

                            // Determine if the user is suspended based on detected semantics
                            if ($flag_semantics === 'active_when_1') {
                                $is_suspended = ($flag_val === 0);
                            } else {
                                $is_suspended = ($flag_val === 1);
                            }
                            ?>
                            <tr>
                                <td><?php echo $sn++ ?></td>
                                <td class="text-capitalize text-bold"><?php echo $fullname ?></td>
                                <td><?php echo $username ?></td>
                                <td><?php echo password_verify($fetch_users['password'], $password) ?></td>
                                <!-- <td>katojkalemba</td> -->
                                <td class="text-capitalize"><?php echo $role ?>
                                    <?php if ($is_suspended): ?>
                                        <span class="badge bg-danger ms-2">Suspended</span>
                                    <?php else: ?>
                                        <span class="badge bg-success ms-2">Active</span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <a href="<?php echo SITEURL ?>update_user.php?user_id=<?php echo $user_id ?>" class="btn btn-primary"><i class="fa-solid fa-pen-to-square"></i></a>
                                    <?php if ($is_suspended): ?>
                                        <a onclick="return confirm('Do you want to unsuspend this user?')" href="<?php echo SITEURL ?>delete_user.php?user_id=<?php echo $user_id ?>" class="btn btn-success"><i class="fa-solid fa-unlock"></i></a>
                                    <?php else: ?>
                                        <a onclick="return confirm('Do you want to suspend this user?')" href="<?php echo SITEURL ?>delete_user.php?user_id=<?php echo $user_id ?>" class="btn btn-warning text-white"><i class="fa-solid fa-user-slash"></i></a>
                                    <?php endif; ?>
                                    <a href="" class="btn btn-outline-danger text-small text-capitalize">permissions</a>
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