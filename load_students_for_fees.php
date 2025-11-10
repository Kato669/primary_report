<?php
include("constants/constants.php");

if (isset($_GET['class_id'])) {
    $class_id = intval($_GET['class_id']);
    $term = mysqli_real_escape_string($conn, $_GET['term'] ?? '');
    $year = intval($_GET['year'] ?? date('Y'));

    $students = mysqli_query($conn, "
        SELECT s.student_id, s.first_name, s.last_name
        FROM students s
        WHERE s.class_id = $class_id
        ORDER BY s.first_name ASC
    ");

    if (mysqli_num_rows($students) > 0) {
        echo '
        <table class="table table-bordered table-hover">
            <thead class="table-success">
                <tr>
                    <th>Student Name</th>
                    <th style="width: 180px;">Total Fees (UGX)</th>
                    <th style="width: 180px;">Amount Paid (UGX)</th>
                    <th style="width: 160px;">Balance (UGX)</th>
                </tr>
            </thead>
            <tbody>';
        while ($row = mysqli_fetch_assoc($students)) {
            $sid = intval($row['student_id']);

            // Fetch existing fee data if available
            $feeQ = mysqli_query($conn, "
                SELECT total_fees, amount_paid, balance 
                FROM student_fees 
                WHERE student_id = '$sid' AND class_id = '$class_id' AND term = '$term' AND year = '$year'
                LIMIT 1
            ");
            $existing = mysqli_fetch_assoc($feeQ) ?: ['total_fees' => 0, 'amount_paid' => 0, 'balance' => 0];

            echo '
            <tr>
                <td>
                    <input type="hidden" name="student_id[]" value="' . $sid . '">
                    ' . htmlspecialchars(strtoupper($row['first_name'] . ' ' . $row['last_name'])) . '
                </td>
                <td><input type="number" step="0.01" class="form-control total" name="set_fees[]" value="' . htmlspecialchars($existing['total_fees']) . '" required></td>
                <td><input type="number" step="0.01" class="form-control paid" name="paid_fees[]" value="' . htmlspecialchars($existing['amount_paid']) . '" required></td>
                <td><input type="number" step="0.01" class="form-control balance" name="balance[]" value="' . htmlspecialchars($existing['balance']) . '" readonly></td>
            </tr>';
        }
        echo '
            </tbody>
        </table>

        <!-- hidden fields will be present in the main form too, but having them here is harmless -->
        <input type="hidden" name="class_id_injected" value="' . $class_id . '">
        <input type="hidden" name="term_injected" value="' . htmlspecialchars($term) . '">
        <input type="hidden" name="year_injected" value="' . $year . '">
        ';
    } else {
        echo '<div class="alert alert-warning">No students found for this class.</div>';
    }
}
?>
