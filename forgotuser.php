<?php
include("constants/constants.php"); // session already started
$user_id = '';
$error = '';

if (isset($_POST['next'])) {
    $username = trim(mysqli_real_escape_string($conn, $_POST['username']));
    $select = "SELECT * FROM users WHERE username='$username'";
    $execute = mysqli_query($conn, $select);

    if ($execute && mysqli_num_rows($execute) > 0) {
        $fetch = mysqli_fetch_assoc($execute);
        $user_id = $fetch['user_id'];

        // store user ID in session
        $_SESSION['user_id'] = $user_id;

        // redirect to password reset page
        header("Location: " . SITEURL . "pswd_reset.php");
        exit();
    } else {
        $error = "Username not found";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>SRMS</title>
    <script src="https://kit.fontawesome.com/a076d05399.js" crossorigin="anonymous"></script>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gradient-to-r from-blue-400 to-purple-500 flex items-center justify-center min-h-screen">

<div class="bg-white rounded-2xl shadow-lg p-8 w-full max-w-sm">
    <h2 class="text-2xl font-bold text-center text-gray-700 mb-6">Enter Username</h2>

    <?php if ($error): ?>
        <div class="bg-red-100 text-red-700 p-2 mb-4 rounded text-sm text-center">
            <?php echo $error; ?>
        </div>
    <?php endif; ?>

    <form method="POST" class="space-y-4">
        <div class="flex items-center border-b border-gray-300 py-2">
            <i class="fas fa-user text-gray-400 mr-3"></i>
            <input 
                class="appearance-none bg-transparent border-none w-full text-gray-700 py-1 px-2 leading-tight focus:outline-none" 
                type="text" 
                name="username"
                placeholder="Username" 
                aria-label="Username"
                required>
        </div>

        <button type="submit" name="next" 
            class="w-full bg-blue-500 hover:bg-blue-600 text-white font-bold py-2 px-4 rounded-lg transition-all duration-300">
            Next
        </button>
    </form>
</div>

</body>
</html>
