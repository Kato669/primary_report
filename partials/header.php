<?php
  include("./constants/constants.php");
  include("NotLoggedin.php");

  // Helper function to check active link
  function isActive($page) {
      $currentPage = basename($_SERVER['PHP_SELF']);
      return $currentPage === $page ? 'active-link' : '';
  }
?>

<!doctype html>
<html lang="en">
  <head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>EduConnect Uganda</title>
    <!-- fontawesome  -->
    <script src="https://kit.fontawesome.com/78e0d6a352.js" crossorigin="anonymous"></script>
    <link rel="shortcut icon" href="img/icon.png" type="image/x-icon">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.2.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- data table -->
    <link rel="stylesheet" href="https://cdn.datatables.net/2.3.3/css/dataTables.dataTables.css">
    <!-- toast code -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.css">
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.6.0/jquery.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.js"></script>
    <link rel="stylesheet" href="css/style.css">
  </head>
  <body>
    <!-- Mobile sidebar overlay -->
    <div class="sidebar-overlay" id="sidebarOverlay"></div>

    <main>
      <div class="container-fluid px-2 px-md-3">
        <div class="d-flex align-items-center justify-content-between gap-2">

          <!-- Left: hamburger + school name -->
          <div class="d-flex align-items-center gap-2 flex-grow-1 overflow-hidden">
            <i class="fa-solid fa-bars icon-bar" id="icon" title="Toggle sidebar"></i>
            <h3 class="text-capitalize name mb-0" id="name">
              <?php
                $school_profile = "SELECT * FROM school_profile";
                $execute = mysqli_query($conn, $school_profile);
                $row = mysqli_fetch_assoc($execute);
                $school_name = $row['school_name'];
                echo htmlspecialchars($school_name);
              ?> PRIMARY SCHOOL
            </h3>
          </div>

          <!-- Right: nav actions -->
          <div class="d-flex align-items-center gap-2 gap-md-3 flex-shrink-0">

            <!-- Settings dropdown (admin only) -->
            <?php if(isset($_SESSION["role"]) && $_SESSION['role']==="admin"): ?>
              <div class="nav-item dropdown" style="list-style: none;">
                <a class="nav-link dropdown-toggle text-white p-0" href="#" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                  <i class="fa-solid fa-gear"></i><span class="d-none d-md-inline ms-1">Settings</span>
                </a>
                <ul class="dropdown-menu dropdown-menu-end">
                  <li><a class="dropdown-item" href="<?php echo SITEURL ?>update_profile.php">School Profile</a></li>
                  <li><hr class="dropdown-divider"></li>
                  <li><a class="dropdown-item" href="<?php echo SITEURL ?>fees.php">Fees Structure</a></li>
                  <li><hr class="dropdown-divider"></li>
                  <li><a class="dropdown-item" href="<?php echo SITEURL ?>promote_students.php">Promote</a></li>
                </ul>
              </div>
            <?php endif ?>

            <!-- Welcome + username (hidden on xs) -->
            <div class="nav-welcome-text d-none d-md-flex align-items-center gap-1">
              <i class="fa-solid fa-user"></i>
              <span>Welcome: <strong><?php echo htmlspecialchars($_SESSION['username']); ?></strong></span>
            </div>

            <!-- Class/Stream Selector (hidden on mobile, teachers only) -->
            <?php if (!empty($_SESSION['assignments'])): ?>
              <div class="nav-class-select d-none d-lg-block">
                <select name="current_assignment" class="form-select form-select-sm text-dark" onchange="location.href='switch_class.php?index='+this.value">
                  <?php foreach ($_SESSION['assignments'] as $index => $assign): ?>
                    <option value="<?php echo $index; ?>"
                        <?php echo ($_SESSION['class_id'] == $assign['class_id']) ? 'selected' : ''; ?>>
                        <?php echo htmlspecialchars($assign['class_name']); ?> -
                        <?php echo htmlspecialchars($assign['stream_name']); ?>
                    </option>
                  <?php endforeach; ?>
                </select>
              </div>
            <?php endif; ?>

            <!-- Date (hidden on mobile) -->
            <div class="nav-date d-none d-lg-flex align-items-center gap-1">
              <i class="fa-solid fa-calendar-days"></i>
              <span><?php echo date('D, M j, Y'); ?></span>
            </div>

            <!-- Logout (always visible) -->
            <div class="d-flex align-items-center gap-1">
              <i class="fa-solid fa-arrow-right-from-bracket"></i>
              <a href="logout.php" class="text-decoration-none text-white d-none d-md-inline">Logout</a>
              <a href="logout.php" class="text-decoration-none text-white d-md-none" title="Logout">
                <span class="visually-hidden">Logout</span>
              </a>
            </div>

          </div><!-- /right -->
        </div>
      </div>
    </main>

    <!-- Sidebar -->
    <div class="left-bar" id="left-bar">
      <a href="<?php echo SITEURL ?>" class="lists gap-3 <?php echo isActive('index.php'); ?>">
        <i class="fa-solid fa-house"></i>
        <span class="text-capitalize side-text">dashboard</span>
      </a>

      <?php if (isset($_SESSION['role']) && $_SESSION['role'] === 'admin'): ?>
        <a href="<?php echo SITEURL ?>class.php" class="<?php echo isActive('class.php'); ?>">
          <i class="fa-solid fa-landmark"></i>
          <span class="text-capitalize side-text">class</span>
        </a>
        <a href="<?php echo SITEURL ?>streams.php" class="<?php echo isActive('streams.php'); ?>">
          <i class="fa-solid fa-clipboard"></i>
          <span class="text-capitalize side-text">stream</span>
        </a>
        <a href="<?php echo SITEURL ?>students.php" class="<?php echo isActive('students.php'); ?>">
          <i class="fa-solid fa-users"></i>
          <span class="text-capitalize side-text">students</span>
        </a>
        <a href="<?php echo SITEURL ?>subject.php" class="<?php echo isActive('subject.php'); ?>">
          <i class="fa-solid fa-book"></i>
          <span class="text-capitalize side-text">subjects</span>
        </a>
        <a href="<?php echo SITEURL ?>class_subjects.php" class="<?php echo isActive('class_subjects.php'); ?>">
          <i class="fa-solid fa-book-atlas"></i>
          <span class="text-capitalize side-text">class subjects</span>
        </a>
        <a href="<?php echo SITEURL ?>users.php" class="<?php echo isActive('users.php'); ?>">
          <i class="fa-solid fa-lock"></i>
          <span class="text-capitalize side-text">user</span>
        </a>
        <a href="<?php echo SITEURL ?>teacherSubject.php" class="<?php echo isActive('teacherSubject.php'); ?>">
          <i class="fa-solid fa-terminal"></i>
          <span class="text-capitalize side-text">Teacher subject assignment</span>
        </a>

        <a href="<?php echo SITEURL ?>assign_roles.php" class="<?php echo isActive('assign_roles.php'); ?>">
          <i class="fa-solid fa-suitcase-rolling"></i>
          <span class="text-capitalize side-text">assign roles</span>
        </a>
        <a href="<?php echo SITEURL ?>fees_management.php" class="<?php echo isActive('fees_management.php'); ?>">
          <i class="fa-solid fa-money-bill-wave"></i>
          <span class="text-capitalize side-text">fees management</span>
        </a>
      <?php endif; ?>

      <a href="<?php
              echo SITEURL;
              echo $_SESSION['role'] === 'admin' ? 'select_class_stream.php' : 'selectcomment_exam.php';
            ?>"
        class="<?php echo isActive($_SESSION['role'] === 'admin' ? 'select_class_stream.php' : 'selectcomment_exam.php'); ?>">
        <i class="fa-solid fa-comments"></i>
        <span class="text-capitalize side-text">comments</span>
      </a>

      <a href="<?php echo SITEURL ?>examination.php" class="<?php echo isActive('examination.php'); ?>">
        <i class="fa-solid fa-file-pen"></i>
        <span class="text-capitalize side-text">examination</span>
      </a>
      <a href="<?php echo SITEURL ?>addScore.php" class="<?php echo isActive('addScore.php'); ?>">
        <i class="fa-solid fa-pen-to-square"></i>
        <span class="text-capitalize side-text">declare marks</span>
      </a>
      <a href="<?php echo SITEURL ?>declare_marks.php" class="<?php echo isActive('declare_marks.php'); ?>">
        <i class="fa-solid fa-table-list"></i>
        <span class="text-capitalize side-text">view/edit marks</span>
      </a>
      <a href="<?php echo SITEURL ?>reports.php" class="<?php echo isActive('reports.php'); ?>">
        <i class="fa-solid fa-bookmark"></i>
        <span class="text-capitalize side-text">report cards</span>
      </a>
      <?php if (isset($_SESSION['role']) && $_SESSION['role'] === 'admin'): ?>
        <a onclick="return confirm('Do you want to graduate P.7 students?')" href="<?php echo SITEURL ?>graduate_students.php" class="<?php echo isActive('graduate_students.php'); ?>">
          <i class="fa-solid fa-user-graduate"></i>
          <span class="text-capitalize side-text">Graduate Students</span>
        </a>
      <?php endif ?>
      <?php if (isset($_SESSION['role']) && $_SESSION['role'] === 'class_teacher'): ?>
        <a href="<?php echo SITEURL ?>promote_students.php" class="<?php echo isActive('promote_students.php'); ?>">
          <i class="fa-solid fa-arrow-up"></i>
          <span class="text-capitalize side-text">Promote students</span>
        </a>
      <?php endif; ?>

    </div>

    <div class="right-bar" id="right-bar">
      
