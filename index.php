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

// ====================== FETCH COUNTS ======================

// Total students
$stdnts = 0;
$students = "SELECT COUNT(*) AS TOTAL FROM students";
if($executeStudents = mysqli_query($conn, $students)){
  $studentRow = mysqli_fetch_assoc($executeStudents);
  $stdnts = $studentRow['TOTAL'];
}

// Total staff
$count = 0;
$users = "SELECT COUNT(*) AS COUNTUSER FROM users";
if($executequery = mysqli_query($conn, $users)){
  $usersCount = mysqli_fetch_assoc($executequery);
  $count = $usersCount['COUNTUSER'];
}

// Female students
$femaleNum = 0;
$female = "SELECT COUNT(*) AS FEMALECOUNT FROM students WHERE gender='female'";
if($femaleQuery = mysqli_query($conn, $female)){
  $femaleCount = mysqli_fetch_assoc($femaleQuery);
  $femaleNum = $femaleCount['FEMALECOUNT'];
}

// Male students
$maleNum = 0;
$male = "SELECT COUNT(*) AS MALECOUNT FROM students WHERE gender='male'";
if($Querymale = mysqli_query($conn, $male)){
  $maleCount = mysqli_fetch_assoc($Querymale);
  $maleNum = $maleCount['MALECOUNT'];
}
//day students
$dayStu = 0;
$day = "SELECT COUNT(*) AS DAYCOUNT FROM students WHERE status='day'";
$dayQuery = mysqli_query($conn, $day);
$dayStudent =  mysqli_fetch_assoc($dayQuery);
$dayStu = $dayStudent['DAYCOUNT'];

//day students
$boardingStu = 0;
$boarding = "SELECT COUNT(*) AS BOARDINGCOUNT FROM students WHERE status='boarding'";
$boardingQuery = mysqli_query($conn, $boarding);
$boardingStudent =  mysqli_fetch_assoc($boardingQuery);
$boardingStu = $boardingStudent['BOARDINGCOUNT'];


// ====================== FETCH DATA FOR GRAPH ======================
$class_labels = [];
$class_counts = [];

$class_query = "
  SELECT c.prefix, COUNT(s.student_id) AS total_students
  FROM classes c
  LEFT JOIN students s ON s.class_id = c.id
  GROUP BY c.id, c.prefix
  ORDER BY c.prefix
";

$res = mysqli_query($conn, $class_query);
if($res){
  while($row = mysqli_fetch_assoc($res)){
    $class_labels[] = $row['prefix'];
    $class_counts[] = $row['total_students'];
  }
}
?>

<div class="container-fluid my-4">
  <div class="row g-3">
    <!-- Total Students Card -->
    <div class="col-lg-3 col-sm-12 col-md-12">
      <a href="<?php echo SITEURL ?>students.php" class="dashboard-card text-decoration-none p-3 bg-primary text-white rounded shadow-sm d-flex flex-column justify-content-between">
        <div class="d-flex justify-content-between align-items-center mb-2">
          <span class="card-number fs-4 fw-bold"><?php echo $stdnts ?></span>
          <span class="card-icon fs-3"><i class="fa-solid fa-users"></i></span>
        </div>
        <span class="card-label text-white text-uppercase small">Total Students</span>
        <div class="d-flex justify-content-between align-items-center mb-2">
          <span class="text-uppercase">in school</span>
        </div>
      </a>
    </div>

    <!-- Total teachers Card -->
    <div class="col-lg-3 col-sm-12 col-md-12">
      <a href="<?php echo SITEURL ?>users.php" class="dashboard-card text-decoration-none p-3 bg-success text-white rounded shadow-sm d-flex flex-column justify-content-between">
        <div class="d-flex justify-content-between align-items-center mb-2">
          <span class="card-number fs-4 fw-bold"><?php echo $count ?></span>
          <span class="card-icon fs-3"><i class="fa-solid fa-person-chalkboard"></i></span>
        </div>
        <span class="card-label text-white text-uppercase small">staff members</span>
        <div class="d-flex justify-content-between align-items-center mb-2">
          <span class="text-uppercase">teachers</span>
        </div>
      </a>
    </div>

    <!-- Total female Card -->
    <div class="col-lg-3 col-sm-12 col-md-12">
      <a href="<?php echo SITEURL ?>students.php" class="dashboard-card text-decoration-none p-3 bg-secondary text-white rounded shadow-sm d-flex flex-column justify-content-between">
        <div class="d-flex justify-content-between align-items-center mb-2">
          <span class="card-number fs-4 fw-bold"><?php echo $femaleNum ?></span>
          <span class="card-icon fs-3"><i class="fa-solid fa-person-dress"></i></span>
        </div>
        <span class="card-label text-white text-uppercase small">female Students</span>
        <div class="d-flex justify-content-between align-items-center mb-2">
          <span class="text-uppercase">female</span>
        </div>
      </a>
    </div>

    <!-- Total male Card -->
    <div class="col-lg-3 col-sm-12 col-md-12">
      <a href="<?php echo SITEURL ?>students.php" class="dashboard-card text-decoration-none p-3 bg-danger text-white rounded shadow-sm d-flex flex-column justify-content-between">
        <div class="d-flex justify-content-between align-items-center mb-2">
          <span class="card-number fs-4 fw-bold"><?php echo $maleNum ?></span>
          <span class="card-icon fs-3"><i class="fa-solid fa-person"></i></span>
        </div>
        <span class="card-label text-white text-uppercase small">male Students</span>
        <div class="d-flex justify-content-between align-items-center mb-2">
          <span class="text-uppercase">male</span>
        </div>
      </a>
    </div>
  </div>
</div>

<!-- Dynamic Chart -->
<div class="container-fluid my-4">
  <div class="row">
    <div class="col-6">
      <div class="col-lg-12 col-sm-12 col-md-12">
      <a href="" class="dashboard-card text-decoration-none p-3 bg-dark text-white rounded shadow-sm d-flex flex-column justify-content-between">
        <div class="d-flex justify-content-between align-items-center mb-2">
          <span class="card-number fs-4 fw-bold"><?php echo $dayStu ?></span>
          <span class="card-icon fs-3"><i class="fa-solid fa-bicycle"></i></span>
        </div>
        <span class="card-label text-white text-uppercase small">day Students</span>
        <div class="d-flex justify-content-between align-items-center mb-2">
          <span class="text-uppercase">day</span>
        </div>
      </a>
      <br>
      <a href="" class="dashboard-card text-decoration-none p-3 bg-success text-white rounded shadow-sm d-flex flex-column justify-content-between">
        <div class="d-flex justify-content-between align-items-center mb-2">
          <span class="card-number fs-4 fw-bold"><?php echo $boardingStu ?></span>
          <span class="card-icon fs-3"><i class="fa-solid fa-bed"></i></span>
        </div>
        <span class="card-label text-white text-uppercase small">boarding Students</span>
        <div class="d-flex justify-content-between align-items-center mb-2">
          <span class="text-uppercase">boarding</span>
        </div>
      </a>
    </div>
    </div>
    <div class="col-lg-6 col-12 bg-white py-3 rounded shadow-sm">
      <div class="h3 fs-5 text-uppercase text-dark">STUDENTS PER CLASS</div>
      <canvas id="studentsChart" style="max-height: 350px;"></canvas>
    </div>
  </div>
</div>

<!-- Include Chart.js -->
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
  const ctx = document.getElementById('studentsChart').getContext('2d');
  const studentsChart = new Chart(ctx, {
    type: 'bar',
    data: {
      labels: <?php echo json_encode($class_labels); ?>,
      datasets: [{
        label: 'Number of Students',
        data: <?php echo json_encode($class_counts); ?>,
        backgroundColor: '#009549',
        borderColor: '#009549',
        borderWidth: 1,
        borderRadius: 5
      }]
    },
    options: {
      responsive: true,
      scales: {
        y: { beginAtZero: true, ticks: { stepSize: 1 } }
      },
      plugins: {
        legend: { display: false },
        tooltip: { enabled: true }
      }
    }
  });
</script>

<?php include("partials/footer.php"); ?>
