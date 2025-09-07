<?php
include("partials/header.php"); // header includes constants.php and session_start()

/* ---------------- helpers ---------------- */
function esc($v){ global $conn; return mysqli_real_escape_string($conn, trim($v)); }
function iint($v){ return intval($v ?? 0); }

/* ---------------- school info ---------------- */
$school_name    = "KATO PRIMARY SCHOOL";
$school_address = "P O BOX 100, KYOTERA";
$school_tel     = "256744683027/256700302123";
$school_email   = "info@kps.ac.ug";
$school_motto   = "'Now or Never'";

/* ---------------- role & filters ---------------- */
$role = $_SESSION['role'] ?? '';
$session_class  = $_SESSION['class_id'] ?? null;
$session_stream = $_SESSION['stream_id'] ?? null;

$sel_class  = $session_class;
$sel_stream = $session_stream;
$sel_term   = $_GET['term_id'] ?? $_POST['term_id'] ?? null;
$sel_year   = $_GET['academic_year'] ?? $_POST['academic_year'] ?? null;

if ($role === 'admin' && isset($_POST['filter_reports'])) {
    $sel_class  = iint($_POST['class_id'] ?? 0) ?: null;
    $sel_stream = iint($_POST['stream_id'] ?? 0) ?: null;
    $sel_term   = iint($_POST['term_id'] ?? 0) ?: null;
    $sel_year   = esc($_POST['academic_year'] ?? '');
}

/* require filters */
if (!$sel_class || !$sel_stream || !$sel_term || !$sel_year) {
    $classes = mysqli_query($conn, "SELECT id, class_name FROM classes ORDER BY id");
    $streams = mysqli_query($conn, "SELECT id, stream_name FROM streams ORDER BY stream_name");
    $terms   = mysqli_query($conn, "SELECT term_id, term_name FROM terms ORDER BY term_id");
    ?>
    <div class="container my-4">
      <h3>Generate Reports</h3>
  <form method="POST">
    <div class="row g-3">
      <?php if ($role === 'admin'): ?>
        <div class="col-md-3">
          <label class="form-label">Class</label>
          <select id="classSelect" name="class_id" class="form-select" required>
            <option value="">Choose class</option>
            <?php while($c = mysqli_fetch_assoc($classes)): ?>
              <option value="<?php echo $c['id'] ?>" <?php echo ($sel_class==$c['id']?'selected':''); ?>>
                  <?php echo htmlspecialchars($c['class_name']) ?>
              </option>
            <?php endwhile; ?>
          </select>
        </div>

        <div class="col-md-3">
          <label class="form-label">Stream</label>
          <select id="streamSelect" name="stream_id" class="form-select" required>
            <option value="">Choose stream</option>
            <!-- Streams will be loaded via AJAX -->
          </select>
        </div>
      <?php else: ?>
        <div class="col-md-3">
          <label class="form-label">Class</label>
          <input class="form-control" readonly value="<?php echo htmlspecialchars(mysqli_fetch_assoc(mysqli_query($conn, "SELECT class_name FROM classes WHERE id=".iint($sel_class)." LIMIT 1"))['class_name'] ?? '') ?>">
        </div>
        <div class="col-md-3">
          <label class="form-label">Stream</label>
          <input class="form-control" readonly value="<?php echo htmlspecialchars(mysqli_fetch_assoc(mysqli_query($conn, "SELECT stream_name FROM streams WHERE id=".iint($sel_stream)." LIMIT 1"))['stream_name'] ?? '') ?>">
        </div>
      <?php endif; ?>

      <div class="col-md-3">
        <label class="form-label">Term</label>
        <select name="term_id" class="form-select" required>
          <option value="">Choose term</option>
          <?php while($t = mysqli_fetch_assoc($terms)): ?>
            <option value="<?php echo $t['term_id'] ?>" <?php echo ($sel_term==$t['term_id']?'selected':''); ?>>
                <?php echo htmlspecialchars($t['term_name']) ?>
            </option>
          <?php endwhile; ?>
        </select>
      </div>

      <div class="col-md-3">
        <label class="form-label">Academic Year</label>
        <input name="academic_year" class="form-control" placeholder="e.g. 2025" required value="<?php echo htmlspecialchars($sel_year); ?>">
      </div>
    </div>

    <div class="mt-3">
      <button class="btn btn-primary" name="filter_reports">Generate All Reports</button>
    </div>
  </form>
</div>

<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script>
$(document).ready(function(){
    function loadStreams(classID, selectedStream = null){
        if(!classID) return;
        $.get('get_streams.php', { class_id: classID })
         .done(function(data){
            $('#streamSelect').html(data);
            if(selectedStream) $('#streamSelect').val(selectedStream);
         })
         .fail(function(xhr){ console.error("Stream AJAX Error:", xhr.responseText); });
    }

    // Load streams when class changes
    $('#classSelect').change(function(){
        loadStreams($(this).val());
    });

    // Preload streams if a class is already selected
    <?php if($sel_class): ?>
        loadStreams(<?php echo iint($sel_class); ?>, <?php echo iint($sel_stream) ?: 'null'; ?>);
    <?php endif; ?>
});
</script>

    </div>
    <?php
    include("partials/footer.php");
    exit;
}

/* sanitize */
$sel_class  = iint($sel_class);
$sel_stream = iint($sel_stream);
$sel_term   = iint($sel_term);
$sel_year   = esc($sel_year);

/* ---------------- Fetch subjects for class ---------------- */
$subjects = [];
$qr = "SELECT cs.subject_id, s.subject_name
       FROM class_subjects cs
       JOIN subjects s ON cs.subject_id = s.subject_id
       WHERE cs.class_id = $sel_class
       ORDER BY s.subject_name";
$res = mysqli_query($conn, $qr);
if (!$res) { die("Subjects query failed: ".mysqli_error($conn)); }
while($r = mysqli_fetch_assoc($res)) $subjects[] = $r;
$subject_ids = array_column($subjects,'subject_id');

/* ---------------- Fetch students (stream) and class students ---------------- */
$students_stream = [];
$res = mysqli_query($conn, "SELECT student_id, first_name, last_name, gender, image, COALESCE(lin,'') AS lin FROM students WHERE class_id=$sel_class AND stream_id=$sel_stream ORDER BY first_name, last_name");
if (!$res) { die("Students stream query failed: ".mysqli_error($conn)); }
while($r = mysqli_fetch_assoc($res)) $students_stream[$r['student_id']] = $r;

$students_class = [];
$res = mysqli_query($conn, "SELECT student_id, first_name, last_name FROM students WHERE class_id=$sel_class ORDER BY first_name, last_name");
if (!$res) { die("Students class query failed: ".mysqli_error($conn)); }
while($r = mysqli_fetch_assoc($res)) $students_class[$r['student_id']] = $r;

/* ---------------- Fetch exams that have marks for students in this class/stream & term/year ----------------
   We choose exams that are:
    - in exams table for the class+term+year AND
    - have marks for students in this class (so columns will exist only if some student has marks)
*/
$exam_rows = [];
$student_ids_for_marks = !empty($students_class) ? implode(',', array_map('intval', array_keys($students_class))) : '0';
$exam_q = "
    SELECT DISTINCT e.exam_id, e.exam_name
    FROM marks m
    JOIN exams e ON m.exam_id = e.exam_id
    WHERE e.class_id = $sel_class
      AND e.term_id = $sel_term
      AND e.academic_year = '{$sel_year}'
      AND m.student_id IN ($student_ids_for_marks)
    ORDER BY e.exam_id
";
$er = mysqli_query($conn, $exam_q);
if (!$er) { die("Exam fetch failed: ".mysqli_error($conn)." | SQL: $exam_q"); }
while($r = mysqli_fetch_assoc($er)) $exam_rows[] = $r;

/* if no exams found via marks, fallback to exams table (maybe marks stored differently) */
if (empty($exam_rows)) {
    $er = mysqli_query($conn, "SELECT exam_id, exam_name FROM exams WHERE class_id=$sel_class AND term_id=$sel_term AND academic_year='{$sel_year}' ORDER BY exam_id");
    while($r = mysqli_fetch_assoc($er)) $exam_rows[] = $r;
}

$exam_ids = array_column($exam_rows,'exam_id');

/* ---------------- Preload marks in one go ---------------- */
$marks_map = []; // marks_map[student_id][exam_id][subject_id] = score
if (!empty($exam_ids) && (!empty($students_class) || !empty($students_stream)) && !empty($subject_ids)) {
    $ex_in = implode(',', array_map('intval',$exam_ids));
    $sub_in = implode(',', array_map('intval',$subject_ids));
    $all_students_ids = array_unique(array_merge(array_keys($students_stream), array_keys($students_class)));
    $stu_in = implode(',', array_map('intval',$all_students_ids));
    if ($stu_in === '') $stu_in = '0';
    $mq = "SELECT student_id, exam_id, subject_id, score, mark_id FROM marks WHERE exam_id IN ($ex_in) AND subject_id IN ($sub_in) AND student_id IN ($stu_in)";
    $mr = mysqli_query($conn, $mq);
    if (!$mr) { die("Marks preload failed: ".mysqli_error($conn)); }
    while($m = mysqli_fetch_assoc($mr)){
        $marks_map[$m['student_id']][$m['exam_id']][$m['subject_id']] = $m['score'];
    }
}

/* ---------------- Preload comments for students (any exam in this set) ---------------- */
$ct_map = []; $ht_map = [];
if (!empty($exam_ids) && !empty($students_stream)) {
    $ex_in = implode(',', array_map('intval',$exam_ids));
    $stu_in = implode(',', array_map('intval', array_keys($students_stream)));
    $cq = "SELECT student_id, class_teacher_comment, head_teacher_comment FROM student_comments WHERE student_id IN ($stu_in) AND exam_id IN ($ex_in)";
    $cr = mysqli_query($conn, $cq);
    if ($cr) {
        while($c = mysqli_fetch_assoc($cr)){
            // if multiple rows, last one will overwrite — that's fine; or you can store arrays
            $ct_map[$c['student_id']] = $c['class_teacher_comment'];
            $ht_map[$c['student_id']] = $c['head_teacher_comment'];
        }
    }
}

/* ---------------- Preload initials from teacher_subject_assignments for this class+stream ---------------- */
$initials_map = []; // initials_map[subject_id] = initials
$ir = mysqli_query($conn, "SELECT subject_id, initials FROM teacher_subject_assignments WHERE class_id=$sel_class AND stream_id=$sel_stream");
if ($ir) {
    while($i = mysqli_fetch_assoc($ir)) $initials_map[$i['subject_id']] = $i['initials'] ?? '';
}

/* ---------------- grading ---------------- */
$grading = [];
$gr = mysqli_query($conn, "SELECT grade_name, min_score, max_score, comment FROM grading_scale ORDER BY min_score DESC");
if ($gr) while($g = mysqli_fetch_assoc($gr)) $grading[] = $g;
function get_grade($score,$grading){
    foreach($grading as $g) {
        if ($score !== '' && is_numeric($score) && $score >= $g['min_score'] && $score <= $g['max_score']) {
            return ['grade'=>$g['grade_name'],'comment'=>$g['comment']];
        }
    }
    return ['grade'=>'','comment'=>''];
}

/* ---------------- hardcoded grade => point mapping ---------------- */
$grade_points = ['D1'=>1,'D2'=>2,'C3'=>3,'C4'=>4,'C5'=>5,'C6'=>6,'P7'=>7,'P8'=>8,'F9'=>9];

/* ---------------- compute totals & ranks ---------------- */
/* helper to compute student's total (sum of subject averages across the exams that exist) */
function compute_total_for_student($sid, $subjects, $exam_rows, $marks_map){
    $sum_subject_avgs = 0; $count_subjects = 0;
    foreach($subjects as $sub){
        $sub_id = $sub['subject_id'];
        $s_sum = 0; $s_cnt = 0;
        foreach($exam_rows as $ex){
            $exid = $ex['exam_id'];
            $val = $marks_map[$sid][$exid][$sub_id] ?? '';
            if ($val !== '' && is_numeric($val)){ $s_sum += floatval($val); $s_cnt++; }
        }
        if ($s_cnt > 0) { $sum_subject_avgs += ($s_sum / $s_cnt); $count_subjects++; }
    }
    return ($count_subjects>0) ? $sum_subject_avgs : 0;
}

/* totals for class (all students in class) */
$totals_class = [];
foreach($students_class as $sid => $st) {
    $totals_class[$sid] = compute_total_for_student($sid, $subjects, $exam_rows, $marks_map);
}
$ranked = $totals_class; arsort($ranked);
$class_positions = []; $p=1; foreach($ranked as $sid=>$t){ $class_positions[$sid] = $p++; }
$class_count = count($students_class);

/* totals for stream (selected stream students only) */

$totals_stream = [];

foreach($students_stream as $sid => $st) {
    $totals_stream[$sid] = compute_total_for_student($sid, $subjects, $exam_rows, $marks_map);
}
$ranked_s = $totals_stream; arsort($ranked_s);
$stream_positions = []; $p=1; foreach($ranked_s as $sid=>$t){ $stream_positions[$sid] = $p++; }
$stream_count = count($students_stream);

/* fetch term name */
$term_name = mysqli_fetch_assoc(mysqli_query($conn, "SELECT term_name FROM terms WHERE term_id=$sel_term LIMIT 1"))['term_name'] ?? 'Term';

/* ---------------- render ---------------- */
?>
<style>
  .report-card{ 
    padding:20px; 
    /* margin:10px;  */
    border: 2px solid #000000ff; 
    background:#fff; 
    position:relative; 
    width: 90%;
    margin: 50px auto 10px;
  }
  .report-watermark{ 
    position:absolute; 
    left:0; 
    right:0; 
    top:20px; 
    bottom:0; 
    opacity:0.09; 
    background:url('img/logo.png') center center / 500px no-repeat; 
    pointer-events:none; 
  }
  .report-header{ 
    display:flex; 
    flex-direction: row; 
    align-items:center; 
    justify-content:space-between; 
    gap:10px; 
  }
  .report-school{ 
    text-align:center; 
    flex:1; 
  }
  .student-photo{ 
    width:80px; 
    height:80px; 
    border-radius:50%; 
    object-fit:cover; 
    border:1px solid #ccc; 
  }
  .subject-table, .grading-horizontal{ 
    width:100%; 
    border-collapse:collapse; 
    margin-top:12px; 
  }
  .subject-table th, .subject-table td, .grading-horizontal td, .grading-horizontal th {
    border:1px solid #ddd; 
    padding:6px; 
    text-align:center; 
    font-size:13px; 
  }
  .meta-row{ 
    display:flex; 
    justify-content:space-between; 
    margin-top:10px; 
  }
  .signature{ 
    border-bottom:1px dotted #333; 
    width: 50%;
  } 
  .page-break{ 
    page-break-after:always; 
  }
  .meta-row span{
    color: #0b00acff;
    font-weight: 700;
  }
  @media print {
    body * {
        visibility: hidden; /* hide everything */
    }
    .report-card {
        visibility: visible; /* show report cards */
        position: relative; /* allow stacking vertically */
        width: 100%;
        page-break-after: always; /* ensure each card prints on separate page */
    }
    .report-card * { visibility: visible; }
    .btn { display: none; } /* hide buttons on print */

}

/* button starts */
</style>
<div class="my-3  d-flex gap-2">
    <!-- <button class="btn btn-primary" onclick="window.print()">
        <i class="fa fa-print"></i> Print All
    </button> -->
    <button class="btn btn-success" id="downloadPdf">
        <i class="fa fa-file-pdf"></i> Download PDF
    </button>
</div>
<!-- button ends -->
<div id="reportCardsContainer">
<?php
foreach($students_stream as $sid => $stu){
    $student_name = $stu['first_name'].' '.$stu['last_name'];
    $student_img = !empty($stu['image']) ? "img/stdent_image/".htmlspecialchars($stu['image']) : "img/stdent_image/default.png";
    $lin = $stu['lin'] ?? '';
    $gender = $stu['gender'] ?? '';
    $class_pos = $class_positions[$sid] ?? '-';
    $stream_pos = $stream_positions[$sid] ?? '-';
    ?>
    <!-- <button onclick="window.print()" class="btn btn-primary">Print Report</button> -->

    <div class="report-card">
      <div class="report-watermark"></div>
        <div class="container">
          <div class="row align-items-center text-uppercase" style="font-weight:700;">
            <div class="col-lg-4 col-4 text-center text-lg-start">
              <img src="img/logo.png" style="width:160px; height:auto;" alt="logo">
            </div>
            <div class="col-lg-4 col-4 text-center">
              <div class="fw-bold fs-6"><?php echo htmlspecialchars($school_name) ?></div>
              <?php echo htmlspecialchars($school_address) . "<br>" . 
                    htmlspecialchars("Tel: $school_tel") . "<br>" . 
                    htmlspecialchars("Email: $school_email"); 
              ?>
              <div class="text-uppercase"><?php echo htmlspecialchars($school_motto) ?></div>
              <div class="mt-2 fw-bold"><?php echo htmlspecialchars("END OF $term_name $sel_year REPORT") ?></div>
            </div>
            <div class="col-lg-4 col-4 text-center text-lg-end">
              <img style="width: 160px; height:auto" src="<?php echo $student_img?>" alt="student" class="student-photo" onerror="this.src='img/stdent_image/default.png'">
            </div>
          </div>
        </div>

        <hr style="height: 3px; background-color: #000000 !important;">

      <div class="meta-row">
        <div>
          <strong>Student Name:</strong> <span class="text-uppercase"><?php echo htmlspecialchars($student_name) ?></span><br>
          <strong>Class:</strong> <span class="text-uppercase"><?php 
              echo htmlspecialchars(mysqli_fetch_assoc(mysqli_query($conn,"SELECT class_name FROM classes WHERE id=$sel_class LIMIT 1"))['class_name'] ?? '') 
                 . ' ' . 
                 htmlspecialchars(mysqli_fetch_assoc(mysqli_query($conn,"SELECT stream_name FROM streams WHERE id=$sel_stream LIMIT 1"))['stream_name'] ?? '') 
              ?></span>
        </div>
        <div style="text-align:left;">
          <strong>LIN:</strong> <span class="text-uppercase"><?php echo htmlspecialchars($lin ?: '-') ?></span><br>
          <strong>Stream Pos:</strong><span class="text-uppercase"> <?php echo ($stream_pos !== '-' ? "$stream_pos out of $stream_count" : '-') ?></span>
        </div>
        <div style="text-align:right;">
          <strong>Gender:</strong><span class="text-uppercase"> <?php echo htmlspecialchars($gender) ?></span><br>
          <strong>Class Pos:</strong><span class="text-uppercase"> <?php echo ($class_pos !== '-' ? "$class_pos out of $class_count" : '-') ?></span>
        </div>
      </div>

      <hr style="height: 3px; background-color: #000000 !important; border: none;">

      <div>
        <table class="subject-table">
          <thead>
            <tr>
              <th class="text-uppercase">Subject</th>
              <?php foreach($exam_rows as $ex): ?>
              <th class="fw-bold text-uppercase">
                  <?php echo htmlspecialchars(strtoupper($ex['exam_name'])); ?>
              </th>
              <?php endforeach; ?>
              <th class="text-uppercase">Average</th>
              <th class="text-uppercase">Grade</th>
              <th class="text-uppercase">Comment</th>
              <th class="text-uppercase">Initials</th>
            </tr>
          </thead>
          <tbody>
            <?php
            // per-student accumulators
            $grand_total = 0;            // sum of subject averages
            $aggregate_total = 0;        // sum of grade points
            $exam_avgs = [];             // per-exam average (across subjects) for this student

            // initialize exam_avgs counters
            foreach($exam_rows as $ex) { $exam_avgs[$ex['exam_id']] = ['sum'=>0,'cnt'=>0]; }

            foreach($subjects as $sub):
                $sub_id = $sub['subject_id'];
                $sum=0; $cnt=0; $cells=[];
                foreach($exam_rows as $ex){
                    $exid = $ex['exam_id'];
                    $val = $marks_map[$sid][$exid][$sub_id] ?? '';
                    $cells[] = ($val === '' ? '' : htmlspecialchars($val));
                    if ($val !== '' && is_numeric($val)){
                        $sum += floatval($val);
                        $cnt++;
                        // accumulate to per-exam avg
                        $exam_avgs[$exid]['sum'] += floatval($val);
                        $exam_avgs[$exid]['cnt'] += 1;
                    }
                }
                $avg = $cnt? round($sum/$cnt,0) : '';
                $ginfo = get_grade($avg,$grading);
                $grade = $ginfo['grade'];
                $grand_total += ($avg !== '' ? $avg : 0);
                if (isset($grade_points[$grade])) $aggregate_total += $grade_points[$grade];
            ?>
              <tr>
                <td><?php echo htmlspecialchars($sub['subject_name']) ?></td>
                <?php foreach($cells as $c): ?><td><?php echo $c; ?></td><?php endforeach; ?>
                <td><?php echo ($avg === ''? '': htmlspecialchars($avg)); ?></td>
                <td><?php echo htmlspecialchars($grade); ?></td>
                <td><?php echo htmlspecialchars($ginfo['comment']); ?></td>
                <td><?php echo htmlspecialchars($initials_map[$sub_id] ?? ''); ?></td>
              </tr>
            <?php endforeach; ?>

            <?php
            // compute per-exam averages (only subjects with marks are counted)
            $per_exam_avg_values = [];
            foreach($exam_rows as $ex){
                $exid = $ex['exam_id'];
                $esum = $exam_avgs[$exid]['sum'];
                $ecnt = $exam_avgs[$exid]['cnt'];
                $per_exam_avg_values[] = ($ecnt>0) ? ($esum / $ecnt) : null;
            }
            // average of exam averages (ignore nulls)
            $valid_exam_avgs = array_filter($per_exam_avg_values, function($v){ return $v !== null; });
            $merged_exam_average = !empty($valid_exam_avgs) ? round(array_sum($valid_exam_avgs)/count($valid_exam_avgs), 0) : 0;

            // grand_total currently is sum of subject averages (each subject avg is already rounded).
            // per your request, Average column in summary row will show TOTAL (sum of those subject averages)
            $total_of_subject_averages = $grand_total;

            // determine division from aggregate_total
            if ($aggregate_total >= 4 && $aggregate_total <= 12) $division = "1";
            elseif ($aggregate_total >= 13 && $aggregate_total <= 23) $division = "2";
            elseif ($aggregate_total >= 24 && $aggregate_total <= 29) $division = "3";
            elseif ($aggregate_total >= 30 && $aggregate_total <= 34) $division = "4";
            else $division = "-";
            ?>

            <!-- SUMMARY ROW -->
            <tr style="font-weight:bold; background:#f8f9fa; text-align:center;">
              <td></td>

              <!-- merged exam averages cell -->
              <td colspan="<?php echo max(1, count($exam_rows)); ?>">
                AVERAGE: <?php echo htmlspecialchars($merged_exam_average); ?>
              </td>

              <!-- TOTAL (sum of subject averages) -->
              <td><?php echo htmlspecialchars($total_of_subject_averages); ?></td>

              <!-- Grade + Comment merged -->
              <td colspan="2">AGGREGATES: <?php echo htmlspecialchars($aggregate_total); ?></td>

              <!-- Initials column used for Division -->
              <td>DIVISION: <?php echo htmlspecialchars($division === "-" ? "-" : $division); ?></td>
            </tr>

          </tbody>
        </table>
      </div>

      <!-- grading horizontal: two rows -->
      <div style="margin-top:12px;">
        <table class="grading-horizontal">
          <tr>
            <?php
            $cols = $grading;
            $maxCols = max(1, min(9, count($cols)));
            $cols = array_slice($cols,0,$maxCols);
            foreach($cols as $g) echo "<th>".htmlspecialchars($g['grade_name'])."</th>";
            for($i=count($cols); $i<9; $i++) echo "<th></th>";
            ?>
          </tr>
          <tr>
            <?php
            foreach($cols as $g) echo "<td>".htmlspecialchars($g['min_score']." - ".$g['max_score'])."</td>";
            for($i=count($cols); $i<9; $i++) echo "<td></td>";
            ?>
          </tr>
        </table>
      </div>

      <!-- comments -->
      <div style="display:flex; margin-top: 30px;">
        <div style="flex:1;">
          <strong>Class Teacher's Comment</strong>
          <div class="signature"><?php echo nl2br(htmlspecialchars($ct_map[$sid] ?? '')); ?></div><br>
          <div>Signature: ____________________</div>
        </div>
        <div style="flex:1;">
          <strong>Head Teacher's Comment</strong>
          <div class="signature"><?php echo nl2br(htmlspecialchars($ht_map[$sid] ?? '')); ?></div><br>
          <div>Signature: ____________________</div>
        </div>
      </div>

<hr style="height: 3px; background-color: #000000 !important; border: none;">
      <div style="text-align:center; margin-top:20px; font-weight:700;">
        <?php
          $term_end = '2025-08-21';
          $next_start = '2025-09-15';
          $fees_day = '450,000/=';
          $fees_boarding = '1,100,000/=';
          echo "This Term has ended On: $term_end | Next Term Begins On: $next_start<br>";
          echo "Next Term Fees: Day: $fees_day | Boarding: $fees_boarding<br>";
          echo "This Report is invalid without school Stamp";
        ?>
      </div>

    </div>

    <div class="page-break"></div>
  <?php } // end foreach students_stream ?>
</div>

</div>
<script src="https://cdnjs.cloudflare.com/ajax/libs/html2pdf.js/0.10.1/html2pdf.bundle.min.js"></script>
<script>
document.getElementById("downloadPdf").addEventListener("click", function () {
    const element = document.getElementById("reportCardsContainer");
    html2pdf()
        .set({margin:0.3, filename:'Class_Report.pdf', html2canvas:{scale:2}})
        .from(element)
        .save();
});

</script>

<?php include("partials/footer.php"); ?>
