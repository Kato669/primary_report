<?php
ob_start();
include("partials/header.php");
include("partials/adminOnly.php");

// ---------------- Handle fees update (when Save Fees is submitted) ----------------
if (isset($_POST['save_fees'])) {
    // sanitize inputs
    $class_id = intval($_POST['class_id'] ?? 0);
    $term = mysqli_real_escape_string($conn, $_POST['term'] ?? '');
    $year = intval($_POST['year'] ?? date('Y'));
    $student_ids = $_POST['student_id'] ?? [];
    $set_fees = $_POST['set_fees'] ?? [];
    $paid_fees = $_POST['paid_fees'] ?? [];

    if ($class_id && $term && $year && is_array($student_ids)) {
        foreach ($student_ids as $index => $sid) {
            $sid = intval($sid);
            $total = floatval($set_fees[$index] ?? 0);
            $paid = floatval($paid_fees[$index] ?? 0);
            $balance = $total - $paid;

            $query = "
                INSERT INTO student_fees (student_id, class_id, term, year, total_fees, amount_paid, balance)
                VALUES ('$sid', '$class_id', '$term', '$year', '$total', '$paid', '$balance')
                ON DUPLICATE KEY UPDATE 
                    total_fees = VALUES(total_fees),
                    amount_paid = VALUES(amount_paid),
                    balance = VALUES(balance)
            ";
            mysqli_query($conn, $query);
        }

        // success message
        echo '<div class="alert alert-success">Fees updated successfully!</div>';
    } else {
        echo '<div class="alert alert-danger">Invalid submission. Please reload and try again.</div>';
    }
}
?>

<div class="container mt-4">
    <h4 class="mb-3">Fees Management</h4>

    <div class="row mb-3">
        <div class="col-md-3">
            <label>Class:</label>
            <select id="classSelect" class="form-control">
                <option value="">Select Class</option>
                <?php
                $classes = mysqli_query($conn, "SELECT * FROM classes");
                while ($row = mysqli_fetch_assoc($classes)) {
                    echo '<option value="' . intval($row['id']) . '">' . htmlspecialchars($row['class_name']) . '</option>';
                }
                ?>
            </select>
        </div>

        <div class="col-md-3">
            <label>Term:</label>
            <select id="termSelect" class="form-control">
                <option value="">Select Term</option>
                <option value="Term 1">Term 1</option>
                <option value="Term 2">Term 2</option>
                <option value="Term 3">Term 3</option>
            </select>
        </div>

        <div class="col-md-3">
            <label>Year:</label>
            <input type="number" id="yearSelect" class="form-control" value="<?php echo date('Y'); ?>">
        </div>

        <div class="col-md-3 d-flex align-items-end">
            <button id="loadBtn" class="btn btn-primary w-100">Load Students</button>
        </div>
    </div>

    <!-- MAIN FORM: injected fields from load_students_for_fees.php will be placed inside this form -->
    <form method="POST" id="feesForm">
        <div id="studentTable" class="table-responsive"></div>

        <!-- Save button placed inside the form so submission posts to this page -->
        <div class="mt-3 d-flex justify-content-end">
            <input type="hidden" name="class_id" id="hiddenClassId" value="">
            <input type="hidden" name="term" id="hiddenTerm" value="">
            <input type="hidden" name="year" id="hiddenYear" value="">
            <button type="submit" name="save_fees" id="saveFeesBtn" class="btn btn-primary">Save Fees</button>
        </div>
    </form>

    <!-- Sticky Summary Bar -->
    <div id="summaryBar" class="sticky-summary shadow-sm">
        <div class="row text-center gx-2">
            <div class="col-md-3 fw-bold text-primary">
                Total Fees: <span id="sumTotal">0</span> UGX
            </div>
            <div class="col-md-3 fw-bold text-success">
                Total Paid: <span id="sumPaid">0</span> UGX
            </div>
            <div class="col-md-3 fw-bold text-danger">
                Total Balance: <span id="sumBalance">0</span> UGX
            </div>
            
        </div>
    </div>
</div>

<style>
    /* Sticky summary bar at bottom */
    .sticky-summary {
        position: fixed;
        bottom: 0;
        left: 0;
        right: 0;
        background: #fff;
        border-top: 2px solid #007bff;
        padding: 10px 12px;
        z-index: 1050;
    }
    .sticky-summary span {
        font-weight: bold;
        font-size: 1rem;
    }
    body {
        padding-bottom: 90px; /* Avoid overlap with sticky bar */
    }
</style>

<script>
document.getElementById('loadBtn').addEventListener('click', (e) => {
    e.preventDefault();
    const classId = document.getElementById('classSelect').value;
    const term = document.getElementById('termSelect').value;
    const year = document.getElementById('yearSelect').value;

    if (!classId || !term || !year) {
        alert("Please select class, term, and year!");
        return;
    }

    // update hidden fields (these will be submitted with the fees form)
    document.getElementById('hiddenClassId').value = classId;
    document.getElementById('hiddenTerm').value = term;
    document.getElementById('hiddenYear').value = year;

    fetch(`load_students_for_fees.php?class_id=${encodeURIComponent(classId)}&term=${encodeURIComponent(term)}&year=${encodeURIComponent(year)}`)
        .then(res => res.text())
        .then(html => {
            document.getElementById('studentTable').innerHTML = html;

            // --- Bind calculation after table loads ---
            const updateTotals = () => {
                let sumTotal = 0, sumPaid = 0, sumBalance = 0;
                document.querySelectorAll('#studentTable tbody tr').forEach(row => {
                    const total = parseFloat(row.querySelector('.total')?.value || 0);
                    const paid = parseFloat(row.querySelector('.paid')?.value || 0);
                    const balance = total - paid;
                    const balanceField = row.querySelector('.balance');
                    if (balanceField) balanceField.value = balance.toFixed(2);
                    sumTotal += total;
                    sumPaid += paid;
                    sumBalance += balance;
                });

                // show numbers nicely formatted with toLocaleString
                document.getElementById('sumTotal').textContent = sumTotal.toLocaleString();
                document.getElementById('sumPaid').textContent = sumPaid.toLocaleString();
                document.getElementById('sumBalance').textContent = sumBalance.toLocaleString();
            };

            // attach event listeners to inputs
            document.querySelectorAll('.total, .paid').forEach(input => {
                input.addEventListener('input', updateTotals);
            });

            // initial calculation
            updateTotals();
        })
        .catch(err => {
            console.error('Error loading students:', err);
            alert('Failed to load students. See console for details.');
        });
});

// View Fees Sheet button opens printable sheet
document.getElementById('viewFeesBtn').addEventListener('click', () => {
    const classId = document.getElementById('classSelect').value;
    const term = document.getElementById('termSelect').value;
    const year = document.getElementById('yearSelect').value;

    if (!classId || !term || !year) {
        alert("Please select class, term, and year first!");
        return;
    }

    window.open(`view_fees_sheet.php?class_id=${encodeURIComponent(classId)}&term=${encodeURIComponent(term)}&year=${encodeURIComponent(year)}`, '_blank');
});
</script>

<?php include("partials/footer.php"); ?>
