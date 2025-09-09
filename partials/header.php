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
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>SRMS</title>
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
    <main>
      <div class="container-fluid">
        <div class="row align-items-center py-2">
          <div class="col-lg-3 d-flex align-items-center gap-3 top-left">
            <i class="fa-solid fa-bars icon-bar" id="icon"></i>
            <h3 class="text-capitalize name mb-0" id="name">
              Kato Primary School
            </h3>
          </div>
          <div class="col-lg-9 d-flex justify-content-end gap-4 top-right">
            <!-- dropdown -->
            <?php if(isset($_SESSION["role"]) && $_SESSION['role']==="admin"): ?>
              <li class="nav-item dropdown" style="list-style: none;">
                <a class="nav-link dropdown-toggle" href="#" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                  Settings
                </a>
                <ul class="dropdown-menu">
                  <li><a class="dropdown-item" href="<?php echo SITEURL ?>update_profile.php">School Profile</a></li>
                  <li><hr class="dropdown-divider"></li>
                  <li><a class="dropdown-item" href="<?php echo SITEURL ?>fees.php">Fees Structure</a></li>
                  
                  <!-- <li><a class="dropdown-item" href="#">Something else here</a></li> -->
                </ul>
              </li>
            <?php endif ?>
            <!-- Welcome + Class Selector -->
            <div class="d-flex align-items-center gap-2 sect_1">
              <i class="fa-solid fa-user"></i>
              <span>Welcome: <strong><?php echo htmlspecialchars($_SESSION['username']); ?></strong></span>

              <!-- Class/Stream Selector (Visible only if teacher has assignments) -->
              <?php if (!empty($_SESSION['assignments'])): ?>
                <select name="current_assignment" onchange="location.href='switch_class.php?index='+this.value">
                    <?php foreach ($_SESSION['assignments'] as $index => $assign): ?>
                        <option value="<?php echo $index; ?>"
                            <?php echo ($_SESSION['class_id'] == $assign['class_id']) ? 'selected' : ''; ?>>
                            Class <?php echo htmlspecialchars($assign['class_name']); ?> - Stream <?php echo htmlspecialchars($assign['stream_name']); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            <?php endif; ?>


            <!-- Date -->
            <div class="d-flex align-items-center gap-2">
              <i class="fa-solid fa-calendar-days"></i>
              <span><?php echo date('l, F j, Y'); ?></span>
            </div>

            <!-- Logout -->
            <div class="d-flex align-items-center gap-2">
              <i class="fa-solid fa-arrow-right-from-bracket"></i>
              <a href="logout.php" class="text-decoration-none text-white">Logout</a>
            </div>
          </div>
        </div>
      </div>
    </main>

    <!-- Sidebar -->
    <div class="container-fluid p-0">
      <div class="row g-0">
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
          <?php endif; ?>

          <a href="<?php 
                  echo SITEURL; 
                  // Role-based link
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
          <a href="<?php echo SITEURL ?>declare_marks.php" class="<?php echo isActive('declare_marks.php'); ?>">
            <i class="fa-solid fa-pen-to-square"></i>
            <span class="text-capitalize side-text">declare marks</span>
          </a>
          <a href="<?php echo SITEURL ?>reports.php" class="<?php echo isActive('reports.php'); ?>">
            <i class="fa-solid fa-bookmark"></i>
            <span class="text-capitalize side-text">report cards</span>
          </a>
          
        </div>

        <div class="right-bar" id="right-bar">
          <div class="container-fluid pt-1 pb-1" style="background-color: #009549;">
            <ul class="list-unstyled mb-0 dashboard">
              <li>
                <i class="fa-solid fa-house" style="color: #fff;"></i>
                <a href="<?php echo SITEURL ?>" class="text-capitalize text-white ms-2 text-decoration-none">Dashboard</a>
              </li>
              <li>
                <i class="fa-solid fa-bars toggleBar" id="icon"></i>
              </li>
            </ul>
          </div>
