<?php
include("constants/constants.php"); 

// ✅ Check if the current user is an admin
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    $_SESSION['delete_user'] = "You are not authorized to perform this action.";
    header("Location:" . SITEURL . "users.php");
    exit;
}

if (isset($_GET['user_id'])) {
    $user_id = (int) $_GET['user_id'];

    if ($user_id > 0) {
        // ✅ Check if the user exists
        $check_stmt = $conn->prepare("SELECT role, is_deleted FROM users WHERE user_id = ?");
        $check_stmt->bind_param("i", $user_id);
        $check_stmt->execute();
        $check_result = $check_stmt->get_result();
        $user = $check_result->fetch_assoc();
        $check_stmt->close();

        if (!$user) {
            $_SESSION['delete_user'] = "User not found.";
        } elseif ($user['is_deleted'] == 1) {
            $_SESSION['delete_user'] = "This user is already deleted.";
        } elseif ($user['role'] === 'admin') {
            // ✅ Prevent deleting the only admin
            $admin_count_res = mysqli_query($conn, "SELECT COUNT(*) AS total_admins FROM users WHERE role='admin' AND is_deleted=0");
            $admin_count = mysqli_fetch_assoc($admin_count_res)['total_admins'];

            if ($admin_count <= 1) {
                $_SESSION['delete_user'] = "Cannot delete the only remaining admin.";
            } elseif ($user_id == $_SESSION['user_id']) {
                $_SESSION['delete_user'] = "You cannot delete your own account while logged in.";
            } else {
                // ✅ Soft delete the admin
                $stmt = $conn->prepare("UPDATE users SET is_deleted = 1 WHERE user_id = ?");
                $stmt->bind_param("i", $user_id);
                if ($stmt->execute()) {
                    $_SESSION['delete_user'] = "Admin soft-deleted successfully.";
                } else {
                    $_SESSION['delete_user'] = "Failed to delete admin (Database error).";
                }
                $stmt->close();
            }
        } else {
            // ✅ Soft delete non-admin user
            $stmt = $conn->prepare("UPDATE users SET is_deleted = 1 WHERE user_id = ?");
            $stmt->bind_param("i", $user_id);
            if ($stmt->execute()) {
                $_SESSION['delete_user'] = "User soft-deleted successfully.";
            } else {
                $_SESSION['delete_user'] = "Failed to delete user (Database error).";
            }
            $stmt->close();
        }

    } else {
        $_SESSION['delete_user'] = "Invalid user ID.";
    }

    header("Location:" . SITEURL . "users.php");
    exit;

} else {
    header("Location:" . SITEURL . "users.php");
    exit;
}
?>
