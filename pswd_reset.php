<?php
    ob_start();
    include("constants/constants.php"); // session already started
// check if user_id exists in session
    if (isset($_SESSION['user_id'])) {
        $user_id = $_SESSION['user_id'];
        // echo $user_id;
    } else {
        // redirect back if user_id is missing
        header("Location: " . SITEURL . "login.php");
        exit();
    }

    // update password
    $errors = [];
    if(isset($_POST['update'])){
        $password = trim($_POST['password'] ?? ""); // current password
        $n_password = trim($_POST['n_password'] ?? "");
        $c_password = trim($_POST['c_password'] ?? "");
        
        $select = "SELECT * FROM users WHERE user_id=$user_id LIMIT 1";
        $execute = mysqli_query($conn, $select);
        $fetch = mysqli_fetch_assoc($execute);
        $user_username = $fetch['username'];
        $g_password = $fetch['password'];

        if(password_verify($password,$g_password)){
            if(!empty($n_password) && !empty($c_password)){
                // do something
                if(strlen($n_password)>6){
                    //do something
                    $h_password = password_hash($n_password, PASSWORD_DEFAULT);
                    $update = "UPDATE users SET
                        password = '$h_password'
                        WHERE
                        user_id = $user_id
                    ";
                    $execute = mysqli_query($conn, $update);
                    if($execute){
                        $_SESSION['password_reset'] = "password reset";
                        header("Location: " . SITEURL . "login.php");

                    }else{
                        $errors = "failed to execute";
                        exit;
                    }
                }else{
                    $errors[] = "password should be more than 6 characters";
                }
            }else{
                $errors[] = "fill all required fields";
            }
        }else{
            $errors[] = "password dont match";
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
    <h4 class="text-2xl font-bold text-center text-gray-700 mb-6">Reset password</h4>

    
    <form method="POST" class="space-y-4">
        <div class="flex items-center border-b border-gray-300 py-2">
            <!-- <i class="fas fa-user text-gray-400 mr-3"></i> -->
            <input 
                class="appearance-none bg-transparent border-none w-full text-gray-700 py-1 px-2 leading-tight focus:outline-none password" 
                type="password" 
                name="password"
                placeholder="enter current password" 
                id="password"
            >
        </div>
        <div class="flex items-center border-b border-gray-300 py-2">
            <!-- <i class="fas fa-user text-gray-400 mr-3"></i> -->
            <input 
                class="appearance-none bg-transparent border-none w-full text-gray-700 py-1 px-2 leading-tight focus:outline-none password" 
                type="password" 
                name="n_password"
                placeholder="enter new password" 
                id="password"
            >
        </div>
        <div class="flex items-center border-b border-gray-300 py-2">
            <!-- <i class="fas fa-user text-gray-400 mr-3"></i> -->
            <input 
                class="appearance-none bg-transparent border-none w-full text-gray-700 py-1 px-2 leading-tight focus:outline-none password" 
                type="password" 
                name="c_password"
                placeholder="confirm password" 
                id="password"
            >
        </div>
        <div class="flex items-center border-b border-gray-300 py-2">
            <input class="mr-2 accent-blue-500" type="checkbox" id="check">
            <label class="text-gray-700 capitalize" for="check">
                show password
            </label>
        </div>

        <button type="submit" name="update" 
            class="w-full bg-blue-500 hover:bg-blue-600 text-white font-bold py-2 px-4 rounded-lg transition-all duration-300">
            update password
        </button>
    </form>
</div>

</body>
</html>
<!-- show password -->
<script>
    const check = document.getElementById("check");
    const passwords =  document.querySelectorAll(".password");
    check.addEventListener("click", ()=>{
        passwords.forEach(password =>{
            password.type = password.type==="password"?"text":"password";
        })
    })

</script>