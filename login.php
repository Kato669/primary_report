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
        // Determine suspension/active column semantics (is_suspended -> suspended when 1; is_active -> active when 1)
        $flag_col = null;
        $flag_semantics = 'suspended_when_1';
        if (array_key_exists('is_suspended', $user)) {
          $flag_col = 'is_suspended';
          $flag_semantics = 'suspended_when_1';
        } elseif (array_key_exists('is_active', $user)) {
          $flag_col = 'is_active';
          $flag_semantics = 'active_when_1';
        } else {
          // fallback to is_deleted semantics
          $flag_col = 'is_deleted';
          $flag_semantics = 'suspended_when_1';
        }

        $flag_val = isset($user[$flag_col]) ? (int)$user[$flag_col] : 0;
        $currently_suspended = ($flag_semantics === 'active_when_1') ? ($flag_val === 0) : ($flag_val === 1);

        if ($currently_suspended) {
          $errors[] = "Your account has been suspended. Please contact the administrator to reactivate it.";
        } else {
          //  Core session data
          $_SESSION['login'] = $username;
          $_SESSION['username'] = $user['username'];
          $_SESSION['user_id'] = $user['user_id'];
          $_SESSION['role'] = $user['role'];
          $_SESSION['logged_in'] = true;

          //  Fetch teacher's class/stream assignments if teacher
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

          //  Role-based redirect (admins & teachers)
          header("Location: " . SITEURL);
          exit;
        }

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
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>EduConnect</title>
  <style>
  * {margin:0;padding:0;box-sizing:border-box;}
  body {
    font-family: 'Segoe UI', Roboto, sans-serif;
    background: #e0e5ec;
    min-height: 100vh;
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
    overflow-x: hidden;
  }

  .login-container {
    width: 100%;
    max-width: 420px;
  }

  .login-card {
    background: #e0e5ec;
    border-radius: 30px;
    padding: 50px 40px;
    box-shadow: 20px 20px 60px #bec3cf, -20px -20px 60px #ffffff;
    transition: box-shadow 0.2s ease;
  }

  .login-header {
    text-align: center;
    margin-bottom: 40px;
  }

  .neu-icon {
    width: 90px;
    height: 90px;
    margin: 0 auto 24px;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    color:#6c7293;
    background: #e0e5ec;
    box-shadow: 8px 8px 20px #bec3cf, -8px -8px 20px #ffffff;
    transition: all 0.3s ease;
  }

  .neu-icon:hover {
    box-shadow: 4px 4px 10px #bec3cf, -4px -4px 10px #ffffff, inset 2px 2px 5px #bec3cf, inset -2px -2px 5px #ffffff;
  }
.neu-icon img {
    width: 70px;
    height: 70px;
    display: flex;
    align-items: center;
    justify-content: center;
  }
  .login-header h2 {
    color:#3d4468;
    font-size:2rem;
    font-weight:600;
    margin-bottom:8px;
  }

  .login-header p {
    color:#9499b7;
    font-size:15px;
  }

  .neu-input {
    position: relative;
    background: #e0e5ec;
    border-radius: 15px;
    box-shadow: inset 8px 8px 16px #bec3cf, inset -8px -8px 16px #ffffff;
    margin-bottom: 28px;
    transition: all 0.3s ease;
  }

  .neu-input:hover {
    box-shadow: inset 4px 4px 8px #bec3cf, inset -4px -4px 8px #ffffff;
  }

  .neu-input input {
    width:100%;
    background:transparent;
    border:none;
    padding:20px 24px;
    color:#3d4468;
    font-size:16px;
    outline:none;
  }

  .eye-toggle {
    position: absolute;
    right: 18px;
    top: 50%;
    transform: translateY(-50%);
    cursor: pointer;
    color: #9499b7;
    transition: color 0.3s ease, transform 0.3s ease;
  }

  .eye-toggle:hover {
    color:#3d4468;
    transform: scale(1.2);
  }

  .remember-wrapper {
    display: flex;
    align-items: center;
    cursor: pointer;
    user-select: none;
    margin-bottom: 25px;
  }

  .remember-wrapper input[type="checkbox"] {
    display: none;
  }

  .checkbox-label {
    display: flex;
    align-items: center;
    gap: 12px;
    cursor: pointer;
    color: #6c7293;
    font-size: 14px;
    font-weight: 500;
    transition: color 0.3s ease;
  }

  .neu-checkbox {
    width: 22px;
    height: 22px;
    background: #e0e5ec;
    border-radius: 6px;
    display: flex;
    align-items: center;
    justify-content: center;
    box-shadow: 3px 3px 8px #bec3cf, -3px -3px 8px #ffffff;
    transition: all 0.3s ease;
  }

  .neu-checkbox svg {
    width: 14px;
    height: 14px;
    color: #00c896;
    opacity: 0;
    transform: scale(0);
    transition: all 0.3s ease;
  }

  .remember-wrapper input[type="checkbox"]:checked + .checkbox-label .neu-checkbox {
    box-shadow: inset 2px 2px 5px #bec3cf, inset -2px -2px 5px #ffffff;
  }

  .remember-wrapper input[type="checkbox"]:checked + .checkbox-label .neu-checkbox svg {
    opacity: 1;
    transform: scale(1);
  }

  .checkbox-label:hover .neu-checkbox {
    box-shadow: 4px 4px 10px #bec3cf, -4px -4px 10px #ffffff;
    transform: translateY(-2px);
  }

  .checkbox-label:active .neu-checkbox {
    box-shadow: inset 2px 2px 5px #bec3cf, inset -2px -2px 5px #ffffff;
    transform: translateY(0);
  }

  .neu-button {
    width:100%;
    background:#e0e5ec;
    border:none;
    border-radius:15px;
    padding:18px 32px;
    color:#3d4468;
    font-size:16px;
    font-weight:600;
    cursor:pointer;
    box-shadow:8px 8px 20px #bec3cf, -8px -8px 20px #ffffff;
    transition: all 0.25s ease;
  }

  .neu-button:hover {
    box-shadow:4px 4px 10px #bec3cf, -4px -4px 10px #ffffff;
    transform: translateY(-3px);
  }

  .neu-button:active {
    transform: translateY(1px);
    box-shadow: inset 4px 4px 8px #bec3cf, inset -4px -4px 8px #ffffff;
  }

  .error {
    color: #ff3b5c;
    font-size: 14px;
    text-align: center;
    margin-bottom: 20px;
  }

  footer {
    margin-top: 40px;
    color: #6c7293;
    font-size: 14px;
    text-align: center;
    transition: opacity 0.5s ease;
  }

  footer:hover {opacity: 0.8;}
  </style>
</head>
<body>
  <div class="login-container">
    <div class="login-card" id="loginCard">
      <div class="login-header">
        <div class="neu-icon">
          <img src="img/IT Logo-02.png" alt="Company Logo" style="width: 75px; height: 75px; object-fit: contain;">
        </div>
        <h2>Welcome back</h2>
        <p>Please sign in to continue</p>
      </div>

      <?php if(!empty($errors)): ?>
        <div class="error">
          <?php foreach($errors as $error) echo htmlspecialchars($error) . "<br>"; ?>
        </div>
      <?php endif; ?>

      <form method="POST" autocomplete="off">
        <div class="neu-input">
          <input type="text" name="username" placeholder="Username" 
                 value="<?php echo isset($_COOKIE['remember_user']) ? htmlspecialchars($_COOKIE['remember_user']) : ''; ?>" required />
        </div>
        <div class="neu-input">
          <input type="password" name="password" id="password" placeholder="Password" required />
          <span class="eye-toggle" id="togglePassword">👁️</span>
        </div>

        <div class="remember-wrapper">
          <input type="checkbox" name="remember" id="remember"
                 <?php echo isset($_COOKIE['remember_user']) ? 'checked' : ''; ?>>
          <label for="remember" class="checkbox-label">
            <div class="neu-checkbox">
              <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3">
                <path d="M5 13l4 4L19 7"/>
              </svg>
            </div>
            Remember me
          </label>
        </div>

        <button type="submit" name="login" class="neu-button">LOGIN</button>
      </form>
    </div>
  </div>

  <footer>© <?php echo date("Y"); ?> EduMaster Uganda. All Rights Reserved. Expires December 2026</footer>

  <script>
  // Password toggle
  const togglePassword = document.getElementById('togglePassword');
  const passwordInput = document.getElementById('password');
  togglePassword.addEventListener('click', () => {
    const type = passwordInput.getAttribute('type') === 'password' ? 'text' : 'password';
    passwordInput.setAttribute('type', type);
    togglePassword.textContent = type === 'password' ? '👁️' : '🙈';
  });

  // Dynamic opposite shadow direction effect
  const card = document.getElementById('loginCard');
  document.addEventListener('mousemove', e => {
    const rect = card.getBoundingClientRect();
    const x = e.clientX - rect.left;
    const mid = rect.width / 2;
    const offset = (x - mid) / mid; // -1 to 1 range

    // when moving right, shadow darkens on right and lightens on left (and vice versa)
    const darkOffset = offset * 15;
    const lightOffset = -offset * 15;

    card.style.boxShadow = `${darkOffset}px 20px 50px rgba(190,195,207,0.8),
                            ${lightOffset}px -20px 50px rgba(255,255,255,0.9)`;
  });
  </script>
</body>
</html>