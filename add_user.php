<?php 
ob_start();
    include("partials/header.php");
    include("partials/adminOnly.php");
// include("partials/db_connect.php"); // Make sure you have this connection

// Initialize variables for sticky data
$fullname = "";
$username = "";
$role = "";
$errors = [];

if (isset($_POST['user'])) {
    // Collect form data
    $fullname = trim($_POST['fullname'] ?? "");
    $username = strtolower(trim($_POST['username'] ?? ""));
    $password = trim($_POST['password'] ?? "");
    $role = $_POST['role'] ?? "";

    // ✅ Validations
    if (empty($fullname)) {
        $errors[] = "Fullname is required.";
    } elseif (!preg_match('/^[a-zA-Z\s]+$/', $fullname)) {
        $errors[] = "Fullname must contain only letters and spaces.";
    }
    if (empty($username)) {
        $errors[] = "Username is required.";
    }
    if (empty($password)) {
        $errors[] = "Password is required.";
    } elseif (strlen($password) < 6) {
        $errors[] = "Password must be at least 6 characters.";
    }
    if (empty($role)) {
        $errors[] = "Role is required.";
    }

    // ✅ Check if username already exists
    if (empty($errors)) {
        $checkQuery = "SELECT user_id FROM users WHERE username = ?";
        $stmt = $conn->prepare($checkQuery);
        $stmt->bind_param("s", $username);
        $stmt->execute();
        $stmt->store_result();
        if ($stmt->num_rows > 0) {
            $errors[] = "Username already taken.";
        }
        $stmt->close();
    }

    // ✅ Insert into DB if no errors
    if (empty($errors)) {
        $hashedPassword = password_hash($password, PASSWORD_DEFAULT);

        $insertQuery = "INSERT INTO users (fullname, username, password, role) VALUES (?, ?, ?, ?)";
        $stmt = $conn->prepare($insertQuery);
        $stmt->bind_param("ssss", $fullname, $username, $hashedPassword, $role);

        if ($stmt->execute()) {
            $successMsg = "User added successfully!";
            // Clear form values after successful insert
            $fullname = "";
            $username = "";
            $role = "";
        } else {
            $errors[] = "Something went wrong. Try again.";
        }
        $stmt->close();
    }
}
?>

<div class="container-fluid my-3">
    <div class="row">
        <div class="col-lg-6 col-sm-12 col-md-12 shadow rounded p-3">

            <!-- Display Errors -->
            <?php if (!empty($errors)): ?>
                <div class="alert alert-danger">
                    <ul class="mb-0">
                        <?php foreach ($errors as $error): ?>
                            <li><?= htmlspecialchars($error) ?></li>
                        <?php endforeach; ?>
                    </ul>
                </div>
            <?php endif; ?>

            <!-- Display Success Message -->
            <?php if (!empty($successMsg)): ?>
                <div class="alert alert-success">
                    <?= htmlspecialchars($successMsg) ?>
                </div>
            <?php endif; ?>

            <form method="POST" action="">
                <div class="mb-3">
                    <label for="fullname" class="form-label text-capitalize fw-bold">fullname</label>
                    <input 
                        type="text" 
                        class="form-control shadow-none" 
                        name="fullname" 
                        id="fullname" 
                        placeholder="enter fullname as fullname" 
                        autocomplete="off"
                        value="<?= htmlspecialchars($fullname) ?>"
                    >
                </div>
                <div class="mb-3">
                    <label for="username" class="form-label text-capitalize fw-bold">username</label>
                    <input 
                        type="text" 
                        class="form-control shadow-none" 
                        name="username" 
                        id="username" 
                        placeholder="enter fullname as username" 
                        autocomplete="off"
                        value="<?= htmlspecialchars($username) ?>"
                    >
                </div>
                <div class="mb-3">
                    <label for="password" class="form-label text-capitalize fw-bold">password</label>
                    <input 
                        type="password" 
                        class="form-control shadow-none" 
                        name="password" 
                        id="password" 
                        placeholder="enter password" 
                        autocomplete="off"
                    >
                </div>
                <div class="form-check mb-3">
                    <input class="form-check-input shadow-none" type="checkbox" id="check">
                    <label class="form-check-label" for="check">Show password</label>
                </div>
                <div class="mb-3">
                    <label for="role" class="form-label text-capitalize fw-bold">role</label>
                    <select class="form-select shadow-none" name="role" id="role">
                        <option selected disabled>Choose role</option>
                        <option value="admin" <?= ($role === "admin") ? "selected" : "" ?>>Admin</option>
                        <option value="class_teacher" <?= ($role === "class_teacher") ? "selected" : "" ?>>Class Teacher</option>
                        <option value="teacher" <?= ($role === "teacher") ? "selected" : "" ?>>Teacher</option>
                    </select>
                </div>
                <button type="submit" name="user" class="btn btn-primary text-capitalize">Add user</button>
            </form>
        </div>
    </div>
</div>

<?php include("partials/footer.php"); ?>
<script>
    const check = document.getElementById("check");
    const password = document.getElementById("password");
    check.addEventListener('click', ()=>{
        password.type = password.type === "password" ? "text" : "password";
    })
</script>
