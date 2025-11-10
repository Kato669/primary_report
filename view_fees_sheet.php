<?php
ob_start();
include("partials/header.php");
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
                    echo '<option value="' . $row['id'] . '">' . htmlspecialchars($row['class_name']) . '</option>';
                }
                ?>
            </select>
        </div>
        <div class="col-md-3">
            <label>Term:</label>
            <select id="termSelect" class="form-control">
                <option value="">Select Term</option>
                <option value="1">Term 1</option>
                <option value="2">Term 2</option>
                <option value="3">Term 3</option>
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

    <div id="studentTable" class="table-responsive"></div>

    <!-- Sticky Summary Bar -->
    <div id="summaryBar" class="sticky-summary shadow-sm">
        <div class="row text-center">
            <div class="col-md-4 fw-bold text-primary">
                Total Fees: <span id="sumTotal">0</span> UGX
            </div>
            <div class="col-md-4 fw-bold text-success">
                Total Paid: <span id="sumPaid">0</span> UGX
            </div>
            <div class="col-md-4 fw-bold text-danger">
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
        padding: 10px 0;
        z-index: 1050;
    }
    .sticky-summary span {
        font-weight: bold;
        font-size: 1rem;
    }
    body {
        padding-bottom: 70px; /* Prevent overlap */
    }
</style>

<script>
document.getElementById('loadBtn').addEventListener('click', () => {
    const classId = document.getElementById('classSelect').value;
    const term = document.getElementById('termSelect').value;
    const year = document.getElementById('yearSelect').value;

    if (!classId || !term || !year) {
        alert("Please select class, term, and year!");
        return;
    }

    fetch(`load_students_for_fees.php?class_id=${classId}&term=${term}&year=${year}`)
        .then(res => res.text())
        .then(html => {
            document.getElementById('studentTable').innerHTML = html;

            // --- Calculate and update totals ---
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
                document.getElementById('sumTotal').textContent = sumTotal.toLocaleString();
                document.getElementById('sumPaid').textContent = sumPaid.toLocaleString();
                document.getElementById('sumBalance').textContent = sumBalance.toLocaleString();
            };

            // Bind input changes and run initial calculation
            document.querySelectorAll('.total, .paid').forEach(input => {
                input.addEventListener('input', updateTotals);
            });
            updateTotals();
        });
});
</script>

<?php include("partials/footer.php"); ?>
