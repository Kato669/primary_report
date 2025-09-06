<?php 
ob_start();
include("partials/header.php");

if(!isset($_GET['user_id'])){
    header("Location:".SITEURL."users.php");
    exit;
}

$user_id = (int)$_GET['user_id'];

// Fetch current user data
$sql_user = "SELECT * FROM users WHERE user_id = $user_id LIMIT 1";
$res_user = mysqli_query($conn, $sql_user);
if(!$res_user || mysqli_num_rows($res_user) === 0){
    $_SESSION['error'] = "User not found.";
    header("Location:".SITEURL."users.php");
    exit;
}
$fetchUser = mysqli_fetch_assoc($res_user);
$current_password = $fetchUser['password'];
?>

<h3 class="text-capitalize py-3 fs-6">Update User</h3>
<div class="container-fluid">
    <div class="row m-3 shadow rounded">
        <div class="col-lg-6 p-3">
            <form action="" method="POST">
                <div class="mb-3">
                    <label class="form-label fw-bold">Username</label>
                    <input type="text" class="form-control shadow-none" name="username" value="<?php echo htmlspecialchars($fetchUser['username']); ?>" autocomplete="off">
                </div>
                <div class="mb-3">
                    <label class="form-label fw-bold">Fullname</label>
                    <input type="text" class="form-control shadow-none" name="fullname" value="<?php echo htmlspecialchars($fetchUser['fullname']); ?>" autocomplete="off">
                </div>
                <div class="mb-3">
                    <label class="form-label fw-bold">Role</label>
                    <select class="form-select shadow-none" name="role">
                        <option selected disabled>Choose role</option>
                        <option value="admin" <?php echo $fetchUser["role"] === "admin" ? "selected" : ""; ?>>Admin</option>
                        <option value="class_teacher" <?php echo $fetchUser["role"] === "class_teacher" ? "selected" : ""; ?>>Class Teacher</option>
                    </select>
                </div>
        </div>

        <div class="col-lg-6 p-3">
                <div class="mb-3">
                    <label class="form-label fw-bold">Current Password</label>
                    <input type="password" class="form-control shadow-none password" name="password" placeholder="Enter current password" autocomplete="off">
                </div>
                <div class="mb-3">
                    <label class="form-label fw-bold">New Password</label>
                    <input type="password" class="form-control shadow-none password" name="n_password" placeholder="Enter new password" autocomplete="off">
                </div>
                <div class="mb-3">
                    <label class="form-label fw-bold">Confirm Password</label>
                    <input type="password" class="form-control shadow-none password" name="c_password" placeholder="Confirm password" autocomplete="off">
                </div>
                <div class="form-check mb-3">
                    <input class="form-check-input shadow-none" type="checkbox" id="check">
                    <label class="form-check-label" for="check">Show password</label>
                </div>
                <button type="submit" name="update" class="btn btn-success text-capitalize">Update User</button>
            </form>
        </div>
    </div>
</div>

<?php
if(isset($_POST['update'])){
    $fullname = strtolower(trim($_POST['fullname'] ?? ""));
    $username = strtolower(trim($_POST['username'] ?? ""));
    $role = trim($_POST['role'] ?? "");
    $password = trim($_POST['password'] ?? "");
    $n_password = trim($_POST['n_password'] ?? "");
    $c_password = trim($_POST['c_password'] ?? "");

    // 1️⃣ Username uniqueness check
    $check_sql = "SELECT * FROM users WHERE username='".mysqli_real_escape_string($conn, $username)."' AND user_id != $user_id";
    $check_res = mysqli_query($conn, $check_sql);
    if($check_res && mysqli_num_rows($check_res) > 0){
        echo '<script>Command: toastr["error"]("Username already exists");</script>';
        exit;
    }

    // 2️⃣ Fullname validation
    if(empty($fullname) || preg_match('/\d/', $fullname)){
        echo '<script>Command: toastr["error"]("Fullname required and cannot contain digits");</script>';
        exit;
    }

    // 3️⃣ Prepare update fields
    $update_fields = "username='".mysqli_real_escape_string($conn, $username)."', fullname='".mysqli_real_escape_string($conn, $fullname)."', role='".mysqli_real_escape_string($conn, $role)."'";

    // 4️⃣ Password change (optional)
    if(!empty($n_password) || !empty($c_password)){
        if(empty($password) || !password_verify($password, $current_password)){
            echo '<script>Command: toastr["error"]("Current password is incorrect or missing");</script>';
            exit;
        }
        if(strlen($n_password) < 6){
            echo '<script>Command: toastr["error"]("New password must be at least 6 characters");</script>';
            exit;
        }
        if($n_password !== $c_password){
            echo '<script>Command: toastr["error"]("New password and confirm password do not match");</script>';
            exit;
        }
        $hashed_psw = password_hash($n_password, PASSWORD_DEFAULT);
        $update_fields .= ", password='$hashed_psw'";
    }

    // 5️⃣ Run update query
    $update_sql = "UPDATE users SET $update_fields WHERE user_id = $user_id";
    $res_update = mysqli_query($conn, $update_sql);

    if($res_update){
        // ✅ Update session role if current logged-in user updated themselves
        if(isset($_SESSION['user_id']) && $_SESSION['user_id'] == $user_id){
            $_SESSION['role'] = $role;
            $_SESSION['username'] = $username;
            $_SESSION['fullname'] = $fullname;
        }

        $_SESSION['update_user'] = "User updated successfully";
        header("Location:".SITEURL."users.php");
        exit;
    } else {
        echo '<script>Command: toastr["error"]("Failed to update user");</script>';
        exit;
    }
}
?>

<?php include("partials/footer.php"); ?>
<script>
const check = document.getElementById('check');
const passwordAll = document.querySelectorAll(".password");
passwordAll.forEach((pswd) => {
    check.addEventListener('click', () => {
        pswd.type = pswd.type === "password" ? "text" : "password";
    });
});
</script>
