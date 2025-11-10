<?php
ob_start();
include("partials/header.php");
include("partials/adminOnly.php");

if (!isset($_GET['teacher_id']) || !ctype_digit($_GET['teacher_id'])) {
    echo "<div class='alert alert-danger'>Invalid teacher selected.</div>";
    exit;
}

$teacher_id = $_GET['teacher_id'];
$teacher = mysqli_fetch_assoc(mysqli_query($conn, "SELECT fullname FROM users WHERE user_id='$teacher_id'"));
if (!$teacher) {
    echo "<div class='alert alert-danger'>Teacher not found.</div>";
    exit;
}

$teacher_name = strtoupper($teacher['fullname']);
$success = $error = "";

// Add new load
if (isset($_POST['add_load'])) {
    $class_id = $_POST['class_id'];
    $stream_id = $_POST['stream_id'] ?? "";
    $subject_id = $_POST['subject_id'];
    $term_id = $_POST['term_id'];
    $academic_year = trim($_POST['academic_year']);

    if ($class_id && $subject_id && $term_id && $academic_year) {
        $check = mysqli_query($conn, "SELECT * FROM teacher_subject_assignments 
            WHERE teacher_id='$teacher_id' AND class_id='$class_id' 
            AND stream_id='$stream_id' AND subject_id='$subject_id' 
            AND term_id='$term_id' AND academic_year='$academic_year'");
        if (mysqli_num_rows($check) == 0) {
            $insert = mysqli_query($conn, "INSERT INTO teacher_subject_assignments 
                (teacher_id, class_id, stream_id, subject_id, term_id, academic_year)
                VALUES ('$teacher_id','$class_id','$stream_id','$subject_id','$term_id','$academic_year')");
            $success = "Teaching load added successfully.";
        } else {
            $error = "This load already exists for this teacher.";
        }
    } else {
        $error = "Please fill all fields.";
    }
}

// Delete load
if (isset($_GET['delete_id']) && ctype_digit($_GET['delete_id'])) {
    $delete_id = $_GET['delete_id'];
    mysqli_query($conn, "DELETE FROM teacher_subject_assignments WHERE id='$delete_id'");
    header("Location: manage_teacher_load.php?teacher_id=$teacher_id");
    exit;
}

// Fetch all loads for this teacher
$loads = mysqli_query($conn, "SELECT tsa.id, s.subject_name, c.class_name, st.stream_name, t.term_name, tsa.academic_year
    FROM teacher_subject_assignments tsa
    JOIN subjects s ON tsa.subject_id=s.subject_id
    JOIN classes c ON tsa.class_id=c.id
    LEFT JOIN streams st ON tsa.stream_id=st.id
    JOIN terms t ON tsa.term_id=t.term_id
    WHERE tsa.teacher_id='$teacher_id'
    ORDER BY c.class_name");
?>

<div class="container-fluid my-3">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h4 class="text-capitalize fw-bold text-dark">
            👨‍🏫 STAFF LOAD FOR: <?= htmlspecialchars($teacher_name); ?>
        </h4>
        <div>
            <a href="<?php echo SITEURL ?>teacherSubject.php" class="btn btn-secondary btn-sm">← Back</a>
            <button class="btn btn-primary btn-sm" data-bs-toggle="collapse" data-bs-target="#addLoadForm">
                + Add Load
            </button>
        </div>
    </div>

    <?php if($success): ?><div class="alert alert-success"><?= $success; ?></div><?php endif; ?>
    <?php if($error): ?><div class="alert alert-danger"><?= $error; ?></div><?php endif; ?>

    <div class="collapse mb-3" id="addLoadForm">
        <div class="card card-body">
            <form method="POST">
                <div class="row g-3">
                    <div class="col-md-3">
                        <label class="form-label fw-bold">Class</label>
                        <select id="classSelect" name="class_id" class="form-select" required>
                            <option value="">Select Class</option>
                            <?php
                            $cls = mysqli_query($conn, "SELECT * FROM classes ORDER BY class_name");
                            while($row = mysqli_fetch_assoc($cls)){
                                echo "<option value='{$row['id']}'>{$row['class_name']}</option>";
                            }
                            ?>
                        </select>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label fw-bold">Stream</label>
                        <select id="streamSelect" name="stream_id" class="form-select">
                            <option value="">All Streams</option>
                        </select>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label fw-bold">Subject</label>
                        <select id="subjectSelect" name="subject_id" class="form-select" required>
                            <option value="">Select Subject</option>
                        </select>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label fw-bold">Term</label>
                        <select name="term_id" class="form-select" required>
                            <option value="">Select Term</option>
                            <?php
                            $terms = mysqli_query($conn, "SELECT * FROM terms ORDER BY term_id");
                            while($t = mysqli_fetch_assoc($terms)){
                                echo "<option value='{$t['term_id']}'>{$t['term_name']}</option>";
                            }
                            ?>
                        </select>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label fw-bold">Academic Year</label>
                        <input type="text" name="academic_year" class="form-control" placeholder="2025" required>
                    </div>
                    <div class="col-md-3 align-self-end">
                        <button type="submit" name="add_load" class="btn btn-success w-100">Save Load</button>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <div class="card shadow-sm p-3">
        <table class="table table-bordered table-striped">
            <thead class="table-light">
                <tr>
                    <th>#</th>
                    <th>Subject</th>
                    <th>Class</th>
                    <th>Stream</th>
                    <th>Term</th>
                    <th>Year</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php $i=1; while($row=mysqli_fetch_assoc($loads)): ?>
                <tr>
                    <td><?= $i++; ?></td>
                    <td><?= htmlspecialchars($row['subject_name']); ?></td>
                    <td><?= htmlspecialchars($row['class_name']); ?></td>
                    <td><?= $row['stream_name'] ?: 'All Streams'; ?></td>
                    <td><?= htmlspecialchars($row['term_name']); ?></td>
                    <td><?= htmlspecialchars($row['academic_year']); ?></td>
                    <td>
                        <a href="?teacher_id=<?= $teacher_id; ?>&delete_id=<?= $row['id']; ?>" 
                           class="btn btn-danger btn-sm" 
                           onclick="return confirm('Delete this load?')"><i class="fa-solid fa-trash"></i></a>
                    </td>
                </tr>
                <?php endwhile; ?>
            </tbody>
        </table>
    </div>
</div>

<!-- jQuery for class-stream-subject linking -->
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script>
$('#classSelect').change(function(){
    var classID = $(this).val();
    $.get('get_streams.php', {class_id: classID}, function(data){
        $('#streamSelect').html(data);
    });
    $.get('get_subjects.php', {class_id: classID}, function(data){
        $('#subjectSelect').html(data);
    });
});
</script>

<?php include("partials/footer.php"); ?>