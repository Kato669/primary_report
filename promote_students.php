<?php
include("partials/header.php");
// include("db_connect.php");

$role = $_SESSION['role'] ?? '';
$teacher_class_id = $_SESSION['class_id'] ?? null;

// Get the highest class id (last class)
$last_class_row = mysqli_fetch_assoc(mysqli_query($conn, "SELECT id FROM classes ORDER BY id DESC LIMIT 1"));
$last_class_id = $last_class_row['id'];

// For class_teacher
if ($role === 'class_teacher' && $teacher_class_id == $last_class_id) {
    echo "<div class='alert alert-info'>This is the final class. Students cannot be promoted further.</div>";
    include("partials/footer.php");
    exit;
}

// For admin
if ($role === 'admin' && isset($_GET['class_id']) && $_GET['class_id'] == $last_class_id) {
    echo "<div class='alert alert-info'>This is the final class. Students cannot be promoted further.</div>";
    include("partials/footer.php");
    exit;
}

// Handle promotion form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['promote_students'])) {
    $present_students = $_POST['present_students'] ?? [];
    $class_id = ($role === 'class_teacher') ? $teacher_class_id : $_POST['class_id'];

    // Get next class id
    $next_class_sql = "SELECT id FROM classes WHERE id > ? ORDER BY id ASC LIMIT 1";
    $stmt = $conn->prepare($next_class_sql);
    $stmt->bind_param("i", $class_id);
    $stmt->execute();
    $next_class = $stmt->get_result()->fetch_assoc();

    if ($next_class) {
        $next_class_id = $next_class['id'];
        foreach ($present_students as $lin) {
            $update_sql = "UPDATE students SET class_id = ? WHERE LIN = ?";
            $update_stmt = $conn->prepare($update_sql);
            $update_stmt->bind_param("is", $next_class_id, $lin);
            $update_stmt->execute();
        }
        $message = "<div class='alert alert-success'>Present students promoted successfully!</div>";
    } else {
        $message = "<div class='alert alert-warning'>No next class found. Promotion not possible.</div>";
    }
}
?>

<div class="container mt-4">
    <h3>Promote Students</h3>
    <?php if (isset($message)) echo $message; ?>

    <?php if ($role === 'class_teacher' && $teacher_class_id): ?>
        <?php
        $class_name = mysqli_fetch_assoc(mysqli_query($conn, "SELECT class_name FROM classes WHERE id=$teacher_class_id LIMIT 1"))['class_name'] ?? '';
        ?>
        <form method="post">
            <input type="hidden" name="class_id" value="<?= $teacher_class_id ?>">
            <div class="mb-3">
                <label class="form-label">Class:</label>
                <input type="text" class="form-control" value="<?= htmlspecialchars($class_name) ?>" readonly>
            </div>
            <table class="table table-bordered">
                <thead>
                    <tr>
                        <th>Present</th>
                        <th>First Name</th>
                        <th>Last Name</th>
                        <th>LIN</th>
                        <th>Gender</th>
                        <th>DOB</th>
                        <th>Stream</th>
                        <th>Status</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    $students_sql = "
                        SELECT students.*, streams.stream_name AS streamName
                        FROM students
                        JOIN streams ON streams.id = students.stream_id
                        WHERE students.class_id = ?
                    ";
                    $stmt = $conn->prepare($students_sql);
                    $stmt->bind_param("i", $teacher_class_id);
                    $stmt->execute();
                    $students = $stmt->get_result();
                    while ($row = $students->fetch_assoc()) {
                        echo "<tr>
                            <td><input type='checkbox' name='present_students[]' value='{$row['LIN']}'></td>
                            <td>{$row['first_name']}</td>
                            <td>{$row['last_name']}</td>
                            <td>{$row['LIN']}</td>
                            <td>{$row['gender']}</td>
                            <td>{$row['dob']}</td>
                            <td>{$row['streamName']}</td>
                            <td>{$row['status']}</td>
                        </tr>";
                    }
                    ?>
                </tbody>
            </table>
            <button type="submit" name="promote_students" class="btn btn-primary">Promote Available Students</button>
        </form>
    <?php elseif ($role === 'admin'): ?>
        <form method="get" class="mb-3">
            <label for="class_id" class="form-label">Select Class:</label>
            <select name="class_id" id="class_id" class="form-select" required onchange="this.form.submit()">
                <option value="">-- Select Class --</option>
                <?php
                $class_query = mysqli_query($conn, "SELECT id, class_name FROM classes ORDER BY class_name");
                while ($class = mysqli_fetch_assoc($class_query)) {
                    $selected = (isset($_GET['class_id']) && $_GET['class_id'] == $class['id']) ? "selected" : "";
                    echo "<option value='{$class['id']}' $selected>{$class['class_name']}</option>";
                }
                ?>
            </select>
        </form>

        <?php if (isset($_GET['class_id']) && $_GET['class_id']): ?>
            <form method="post">
                <input type="hidden" name="class_id" value="<?= $_GET['class_id'] ?>">
                <table class="table table-bordered">
                    <thead>
                        <tr>
                            <th>Present</th>
                            <th>First Name</th>
                            <th>Last Name</th>
                            <th>LIN</th>
                            <th>Gender</th>
                            <th>DOB</th>
                            <th>Stream</th>
                            <th>Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        $students_sql = "
                            SELECT students.*, streams.stream_name AS streamName
                            FROM students
                            JOIN streams ON streams.id = students.stream_id
                            WHERE students.class_id = ?
                        ";
                        $stmt = $conn->prepare($students_sql);
                        $stmt->bind_param("i", $_GET['class_id']);
                        $stmt->execute();
                        $students = $stmt->get_result();
                        while ($row = $students->fetch_assoc()) {
                            echo "<tr>
                                <td><input type='checkbox' name='present_students[]' value='{$row['LIN']}'></td>
                                <td>{$row['first_name']}</td>
                                <td>{$row['last_name']}</td>
                                <td>{$row['LIN']}</td>
                                <td>{$row['gender']}</td>
                                <td>{$row['dob']}</td>
                                <td>{$row['streamName']}</td>
                                <td>{$row['status']}</td>
                            </tr>";
                        }
                        ?>
                    </tbody>
                </table>
                <button type="submit" name="promote_students" class="btn btn-primary">Promote Available Students</button>
            </form>
        <?php endif; ?>
    <?php endif; ?>
</div>

<?php include("partials/footer.php"); ?>