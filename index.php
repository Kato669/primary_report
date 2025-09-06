<?php include("partials/header.php"); ?>
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
?>
<div class="container-fluid my-4">
  <div class="row g-3">
    <!-- Total Students Card -->
    <div class="col-lg-6 col-sm-12 col-md-12">
      <a href="<?php echo SITEURL ?>students.php" class="dashboard-card text-decoration-none p-3 bg-primary text-white rounded shadow-sm d-flex flex-column justify-content-between">
        <div class="d-flex justify-content-between align-items-center mb-2">
          <!-- counting students from tables -->
          <?php
            $students = "SELECT COUNT(*) AS TOTAL FROM students";
            $executeStudents = mysqli_query($conn, $students);
            if($executeStudents){
              $studentRow = mysqli_fetch_assoc($executeStudents);
              $stdnts = $studentRow['TOTAL'];
            }else{
              $stdnts = 0;
            }
          ?>
          <span class="card-number fs-4 fw-bold"><?php echo $stdnts ?></span>
          <span class="card-icon fs-3"><i class="fa-solid fa-users"></i></span>
        </div>
        <span class="card-label text-white text-uppercase small">Total Students</span>
        <div class="d-flex justify-content-between align-items-center mb-2">
          <span class="text-uppercase">in school</span>
          <!-- <span>45%</span> -->
        </div>
      </a>
    </div>

    <!-- Total teachers Card -->
    <div class="col-lg-6 col-sm-12 col-md-12">
      <a href="<?php echo SITEURL ?>users.php" class="dashboard-card text-decoration-none p-3 bg-success text-white rounded shadow-sm d-flex flex-column justify-content-between">
        <div class="d-flex justify-content-between align-items-center mb-2">
          <!-- staff members around -->
          <?php 
            $users = "SELECT COUNT(*) AS COUNTUSER FROM users";
            $executequery = mysqli_query($conn, $users);
            if($executequery){
              $usersCount = mysqli_fetch_assoc($executequery);
              $count = $usersCount['COUNTUSER'];
            }else{
              $count = 0;
            }
          ?>
          <span class="card-number fs-4 fw-bold"><?php echo $count ?></span>
          <span class="card-icon fs-3"><i class="fa-solid fa-person-chalkboard"></i></span>
        </div>
        <span class="card-label text-white text-uppercase small">staff members</span>
        <div class="d-flex justify-content-between align-items-center mb-2">
          <span class="text-uppercase">teachers</span>
          <span>100%</span>
        </div>
      </a>
    </div>
    <!-- Total female Card -->
    <div class="col-lg-6 col-sm-12 col-md-12">
      <a href="<?php echo SITEURL ?>students.php" class="dashboard-card text-decoration-none p-3 bg-secondary text-white rounded shadow-sm d-flex flex-column justify-content-between">
        <div class="d-flex justify-content-between align-items-center mb-2">
          <!-- female students -->
          <?php
            $female = "SELECT COUNT(*) AS FEMALECOUNT FROM students WHERE gender='female'";
            $femaleQuery = mysqli_query($conn, $female);
            if($femaleQuery){
              $femaleCount = mysqli_fetch_assoc($femaleQuery);
              $femaleNum = $femaleCount['FEMALECOUNT'];
            }else{
              $femaleNum = 0;
            }
          ?>
          <span class="card-number fs-4 fw-bold"><?php echo $femaleNum ?></span>
          <span class="card-icon fs-3"><i class="fa-solid fa-person-dress"></i></span>
        </div>
        <span class="card-label text-white text-uppercase small">female Students</span>
        <div class="d-flex justify-content-between align-items-center mb-2">
          <span class="text-uppercase">female</span>
          <span>100%</span>
        </div>
      </a>
    </div>
    <!-- Total male Card -->
    <div class="col-lg-6 col-sm-12 col-md-12">
      <a href="<?php echo SITEURL ?>students.php" class="dashboard-card text-decoration-none p-3 bg-danger text-white rounded shadow-sm d-flex flex-column justify-content-between">
        <div class="d-flex justify-content-between align-items-center mb-2">
          <!-- male fetch -->
          <?php
            $male = "SELECT COUNT(*) AS MALECOUNT FROM students WHERE gender='male'";
            $Querymale = mysqli_query($conn, $male);
            if($Querymale){
              $maleCount = mysqli_fetch_assoc($Querymale);
              $maleNum = $maleCount['MALECOUNT'];
            }else{
              $femaleCount = 0;
            }
          ?>
          <span class="card-number fs-4 fw-bold"><?php echo $maleNum ?></span>
          <span class="card-icon fs-3"><i class="fa-solid fa-person"></i></span>
        </div>
        <span class="card-label text-white text-uppercase small">male Students</span>
        <div class="d-flex justify-content-between align-items-center mb-2">
          <span class="text-uppercase">male</span>
          <span>100%</span>
        </div>
      </a>
    </div>

  </div>
</div>
<!-- graph -->
 <div class="container-fluid">
  <div class="row">
    <div class="col-lg-6 col-12 bg-white py-3 rounded">
      <div class="h3 fs-5 text-uppercase text-dark">
        STUDENTS PER CLASS
      </div>
      <span style="border-bottom: 1px solid #000; width: 100%; display:block"></span>
      <img src="img/graph.png" alt="" class="img-fluid">
    </div>
  </div>
 </div>
<?php include("partials/footer.php"); ?>

