<?php 
ob_start();
include("partials/header.php"); 
include("partials/adminOnly.php"); 

if(!isset($_GET['role_id'])){
    header("Location: view_roles.php");
    exit;
}

$role_id = intval($_GET['role_id']);
$role_sql = mysqli_query($conn, "SELECT role_name FROM roles WHERE id=$role_id");
$role_data = mysqli_fetch_assoc($role_sql);
$role_name = $role_data['role_name'];
?>

<div class="container-fluid">
    <a href="view_roles.php" class="btn btn-secondary mb-3">← Back</a>
    <h4 class="mb-3">Academic Permissions for <b><?= htmlspecialchars($role_name) ?></b></h4>

    <form method="POST">
        <table class="table table-bordered">
            <thead>
                <tr>
                    <th>S/N</th>
                    <th>Permission</th>
                    <th><?= htmlspecialchars($role_name) ?></th>
                </tr>
            </thead>
            <tbody>
                <?php 
                $perm_sql = mysqli_query($conn, "SELECT * FROM permissions WHERE module='Academics'");
                $sn = 1;
                while($perm = mysqli_fetch_assoc($perm_sql)){
                    $permission_id = $perm['id'];
                    $permission_name = $perm['permission_name'];

                    $check_sql = "SELECT * FROM role_permissions WHERE role_id=$role_id AND permission_id=$permission_id";
                    $has_permission = mysqli_num_rows(mysqli_query($conn, $check_sql)) > 0;
                    ?>
                    <tr>
                        <td><?= $sn++ ?></td>
                        <td><?= htmlspecialchars($permission_name) ?></td>
                        <td>
                            <input type="checkbox" name="permissions[]" value="<?= $permission_id ?>" 
                                <?= $has_permission ? 'checked' : '' ?>>
                        </td>
                    </tr>
                <?php } ?>
            </tbody>
        </table>

        <button type="submit" name="save" class="btn btn-success">Save Changes</button>
    </form>
</div>

<?php 
if(isset($_POST['save'])){
    $selected_permissions = $_POST['permissions'] ?? [];

    // Remove all current permissions first
    mysqli_query($conn, "DELETE FROM role_permissions WHERE role_id=$role_id");

    // Add new selected permissions
    foreach($selected_permissions as $perm_id){
        mysqli_query($conn, "INSERT INTO role_permissions(role_id, permission_id) VALUES($role_id, $perm_id)");
    }

    $_SESSION['success'] = "Permissions updated successfully!";
    header("Location: view_permissions.php?role_id=$role_id");
    exit;
}
?>

<?php include('partials/footer.php'); ?>