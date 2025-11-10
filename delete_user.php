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
        // Determine which column to use for suspension
        // Prefer is_suspended (1 = suspended), then is_active (1 = active), else fall back to is_deleted (1 = deleted/suspended)
        $flag_col = null;
        $flag_semantics = 'suspended_when_1'; // or 'active_when_1'

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
                // fallback to existing column used previously
                $flag_col = 'is_deleted';
                $flag_semantics = 'suspended_when_1';
            }
        }

        // Check if the user exists and get current flag value
        $check_sql = "SELECT role, " . $flag_col . " AS flag_val FROM users WHERE user_id = ?";
        $check_stmt = $conn->prepare($check_sql);
        $check_stmt->bind_param("i", $user_id);
        $check_stmt->execute();
        $check_result = $check_stmt->get_result();
        $user = $check_result->fetch_assoc();
        $check_stmt->close();

        if (!$user) {
            $_SESSION['delete_user'] = "User not found.";
        } else {
            // Determine whether the user is currently suspended
            $flag_val = (int) $user['flag_val'];
            $currently_suspended = false;
            if ($flag_semantics === 'suspended_when_1') {
                $currently_suspended = ($flag_val === 1);
            } else { // active_when_1
                $currently_suspended = ($flag_val === 0);
            }

            // If target is admin, ensure we don't suspend the only active admin
            if ($user['role'] === 'admin') {
                if ($flag_semantics === 'active_when_1') {
                    $admin_count_res = mysqli_query($conn, "SELECT COUNT(*) AS total_admins FROM users WHERE role='admin' AND " . $flag_col . "=1");
                    $admin_count = mysqli_fetch_assoc($admin_count_res)['total_admins'];
                    // If we're about to suspend (set active=0) and there's only 1 active admin, block it
                    if (!$currently_suspended && $admin_count <= 1) {
                        $_SESSION['delete_user'] = "Cannot suspend the only remaining admin.";
                        header("Location:" . SITEURL . "users.php");
                        exit;
                    }
                } else {
                    // suspended_when_1 semantics: count admins where flag = 0 (not suspended)
                    $admin_count_res = mysqli_query($conn, "SELECT COUNT(*) AS total_admins FROM users WHERE role='admin' AND " . $flag_col . "=0");
                    $admin_count = mysqli_fetch_assoc($admin_count_res)['total_admins'];
                    if (!$currently_suspended && $admin_count <= 1) {
                        $_SESSION['delete_user'] = "Cannot suspend the only remaining admin.";
                        header("Location:" . SITEURL . "users.php");
                        exit;
                    }
                }

                if ($user_id == $_SESSION['user_id']) {
                    $_SESSION['delete_user'] = "You cannot suspend your own account while logged in.";
                    header("Location:" . SITEURL . "users.php");
                    exit;
                }
            }

            // Toggle suspension
            if ($currently_suspended) {
                // Unsuspend
                if ($flag_semantics === 'active_when_1') {
                    $update_sql = "UPDATE users SET " . $flag_col . " = 1 WHERE user_id = ?";
                } else {
                    $update_sql = "UPDATE users SET " . $flag_col . " = 0 WHERE user_id = ?";
                }
                $action_msg = "unsuspended";
            } else {
                // Suspend
                if ($flag_semantics === 'active_when_1') {
                    $update_sql = "UPDATE users SET " . $flag_col . " = 0 WHERE user_id = ?";
                } else {
                    $update_sql = "UPDATE users SET " . $flag_col . " = 1 WHERE user_id = ?";
                }
                $action_msg = "suspended";
            }

            $stmt = $conn->prepare($update_sql);
            $stmt->bind_param("i", $user_id);
            if ($stmt->execute()) {
                $_SESSION['delete_user'] = "User successfully " . $action_msg . ".";
            } else {
                $_SESSION['delete_user'] = "Failed to update user status (Database error).";
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
