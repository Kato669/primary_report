<?php 
include("partials/header.php");
include("partials/adminOnly.php");
?>

<div class="container-fluid">
    <h3 class="mb-3">Assign Role to Staff</h3>

    <form method="POST">
        <div class="mb-3">
            <label>Select User:</label>
            <select name="user_id" class="form-select" required>
                <option value="">-- Select Staff --</option>
                <?php 
                $users = mysqli_query($conn, "SELECT * FROM users");
                while($u = mysqli_fetch_assoc($users)){
                    echo "<option value='{$u['user_id']}'>{$u['username']}</option>";
                }
                ?>
            </select>
        </div>

        <div class="mb-3">
            <label>Select Role:</label>
            <select name="role_id" class="form-select" required>
                <option value="">-- Select Role --</option>
                <?php 
                $roles = mysqli_query($conn, "SELECT * FROM roles");
                while($r = mysqli_fetch_assoc($roles)){
                    echo "<option value='{$r['id']}'>{$r['role_name']}</option>";
                }
                ?>
            </select>
        </div>

        <button type="submit" name="assign" class="btn btn-success">Assign Role</button>
    </form>
</div>

<?php 
if(isset($_POST['assign'])){
    $user_id = $_POST['user_id'];
    $role_id = $_POST['role_id'];

    $update = mysqli_query($conn, "UPDATE users SET role_id='$role_id' WHERE user_id='$user_id'");
    if($update){
        $_SESSION['success'] = "Role assigned successfully!";
        header("Location: view_roles.php");
        exit;
    }
}
?>

<?php include('partials/footer.php'); ?>