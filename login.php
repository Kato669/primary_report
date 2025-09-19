<?php
ob_start();
include("constants/constants.php");

$errors = [];

if (isset($_POST['login'])) {
    $username = trim(mysqli_real_escape_string($conn, $_POST['username'] ?? ""));
    $password = trim(mysqli_real_escape_string($conn, $_POST['password'] ?? ""));

    if (!empty($username) && !empty($password)) {

        // Fetch user by username
        $sql = "SELECT * FROM users WHERE username='$username' LIMIT 1";
        $result = mysqli_query($conn, $sql);

        if (!$result) {
            die("Database error: " . mysqli_error($conn));
        }

        if (mysqli_num_rows($result) === 1) {
            $user = mysqli_fetch_assoc($result);

            if (password_verify($password, $user['password'])) {
                // ✅ Core session data
                $_SESSION['login'] = $username;
                $_SESSION['username'] = $user['username'];
                $_SESSION['user_id'] = $user['user_id'];
                $_SESSION['role'] = $user['role'];
                $_SESSION['logged_in'] = true;

                // ✅ Fetch teacher's class/stream assignments if teacher
                if ($user['role'] === 'class_teacher') {
                    $teacher_id = $user['user_id'];

                    $assign_query = "
                        SELECT 
                            ta.class_id, 
                            ta.stream_id, 
                            c.class_name, 
                            s.stream_name
                        FROM teacher_assignments ta
                        JOIN classes c ON ta.class_id = c.id
                        JOIN streams s ON ta.stream_id = s.id
                        WHERE ta.user_id = $teacher_id
                    ";

                    $assign_res = mysqli_query($conn, $assign_query);

                    if ($assign_res && mysqli_num_rows($assign_res) > 0) {
                        $assignments = [];
                        while ($row = mysqli_fetch_assoc($assign_res)) {
                            $assignments[] = [
                                'class_id' => $row['class_id'],
                                'stream_id' => $row['stream_id'],
                                'class_name' => $row['class_name'],
                                'stream_name' => $row['stream_name']
                            ];
                        }

                        $_SESSION['assignments'] = $assignments;

                        // Set first assignment as default
                        $_SESSION['class_id'] = $assignments[0]['class_id'];
                        $_SESSION['stream_id'] = $assignments[0]['stream_id'];

                    } else {
                        $_SESSION['assignments'] = [];
                        $_SESSION['class_id'] = null;
                        $_SESSION['stream_id'] = null;
                        $errors[] = "No class/stream assignments found for this teacher.";
                    }
                }

                // ✅ Role-based redirect (admins & teachers)
                header("Location: " . SITEURL);
                exit;

            } else {
                $errors[] = "Incorrect password.";
            }

        } else {
            $errors[] = "No user found with that username.";
        }

    } else {
        $errors[] = "Username and password can't be empty.";
    }
}
?>



<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Login</title>
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.2.3/dist/css/bootstrap.min.css">
   <script src="https://kit.fontawesome.com/78e0d6a352.js" crossorigin="anonymous"></script>
   <!-- toast code -->
     <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.css">
     <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.6.0/jquery.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.js"></script>
  <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gradient-to-r from-blue-400 to-purple-500 flex items-center justify-center min-h-screen">
  <div class="bg-white rounded-2xl shadow-lg p-8 w-full max-w-sm">
    <h2 class="text-2xl font-bold text-center text-gray-700 mb-6">Login</h2>
    <!-- error handing -->
    <?php if(!empty($errors)): ?>
        <div class="alert alert-danger" role="alert" id="errors">
            <?php 
                foreach ($errors as $error) {
                    echo $error;
                }
            ?>
        </div>
    <?php endif ?>
    
    <form class="space-y-4" method="POST">
      <!-- Username -->
      <div class="flex items-center border-b border-gray-300 py-2">
        <i class="fas fa-user text-gray-400 mr-3"></i>
        <input 
          class="appearance-none bg-transparent border-none w-full text-gray-700 mr-3 py-1 px-2 leading-tight focus:outline-none" 
          type="text" 
          autocomplete="off"
          name="username"
          placeholder="Username" 
          aria-label="Username">
          
      </div>
      <!-- Password -->
      <div class="flex items-center border-b border-gray-300 py-2">
        <i class="fas fa-lock text-gray-400 mr-3"></i>
        <input 
          class="appearance-none bg-transparent border-none w-full text-gray-700 mr-3 py-1 px-2 leading-tight focus:outline-none" 
          type="password" 
          name="password"
          placeholder="Password" 
          aria-label="Password">
      </div>
      <!-- Remember Me + Forgot -->
      <div class="flex items-center justify-between">
        <label class="flex items-center text-sm text-gray-600">
          <input type="checkbox" class="form-checkbox h-4 w-4 text-blue-600">
          <span class="ml-2">Remember me</span>
        </label>
        <a href="<?php echo SITEURL ?>forgotuser.php" class="text-blue-500 text-sm hover:underline">Forgot?</a>
      </div>
      <!-- Login Button -->
      <button type="submit" name="login"
        class="w-full bg-blue-500 hover:bg-blue-600 text-white font-bold py-2 px-4 rounded-lg transition-all duration-300">
        Login
      </button>
    </form>
    <!-- <p class="text-center text-gray-500 text-sm mt-4">
      Don't have an account? <a href="#" class="text-blue-500 hover:underline">Sign Up</a>
    </p> -->
  </div>
</body>
</html>
<?php 
    if(isset($_SESSION['must_login'])){
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
            Command: toastr["error"]("'.$_SESSION['must_login'].'");
        </script>
        ';
        unset($_SESSION['must_login']);
     }
    if(isset($_SESSION['password_reset'])){
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
            Command: toastr["success"]("'.$_SESSION['password_reset'].'");
        </script>
        ';
        unset($_SESSION['password_reset']);
     }
?>
<script>
    const errors = document.getElementById('errors');
    if (errors) {
        setTimeout(() => {
            errors.remove();
        }, 3000);
    }
</script>

