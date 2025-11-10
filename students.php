<?php 
ob_start();
include("partials/header.php");
// include("partials/adminOnly.php");
?>

<div class="container-fluid">
    <div class="row g-0 my-2">
        <div class="col-lg-4 col-md-4 col-sm-12">
            <a href="<?php echo SITEURL ?>add_student.php" class="btn text-capitalize text-white btn-success fs-6">
                add student <i class="fa-solid fa-pen-to-square"></i>
            </a>
        </div>
    </div>

    <h4 class="text-uppercase fw-bold text-center mb-3 bg-primary text-white py-2 rounded">STUDENTS OF <?php echo htmlspecialchars($school_name); ?> PRIMARY SCHOOL</h4>

    <!-- FILTERS -->
    <div class="row mb-3">
        <div class="col-md-4">
            <select id="classSelect" class="form-select">
                <option value="">-- Select Class --</option>
                <?php
                $classQuery = mysqli_query($conn, "SELECT id, class_name FROM classes");
                while ($class = mysqli_fetch_assoc($classQuery)) {
                    echo "<option value='{$class['id']}'>{$class['class_name']}</option>";
                }
                ?>
            </select>
        </div>

        <div class="col-md-4">
            <select id="streamSelect" class="form-select" disabled>
                <option value="">-- Select Stream --</option>
            </select>
        </div>

        <div class="col-md-4 text-end">
            <button id="downloadCSV" class="btn btn-outline-success">
                <i class="fa-solid fa-file-csv"></i> Download CSV
            </button>
        </div>
    </div>

    <!-- TABLE -->
    <div class="row">
        <table id="studentsTable" class="table table-hover display" style="width:100%">
            <thead>
                <tr>
                    <th>Sn</th>
                    <th>First Name</th>
                    <th>Last Name</th>
                    <th>Gender</th>
                    <th>DOB</th>
                    <th>Reg No.</th>
                    <th>Class</th>
                    <th>Stream</th>
                    <th>Image</th>
                    <th>Status</th>
                    <th>Action</th>
                </tr>
            </thead>
            <tbody id="studentsBody">
                <?php
                $selectData = "
                    SELECT students.*, 
                        streams.stream_name AS streamName, 
                        classes.class_name AS className
                    FROM students
                    JOIN classes ON classes.id = students.class_id
                    JOIN streams ON streams.id = students.stream_id
                ";
                $executeData = mysqli_query($conn, $selectData);
                $sn = 1;
                while($row = mysqli_fetch_assoc($executeData)){
                    ?>
                    <tr>
                        <td><?= $sn++ ?></td>
                        <td><?= $row['first_name'] ?></td>
                        <td><?= $row['last_name'] ?></td>
                        <td><?= $row['gender'] ?></td>
                        <td><?= $row['dob'] ?></td>
                        <td><?= $row['LIN'] ?></td>
                        <td><?= $row['className'] ?></td>
                        <td><?= $row['streamName'] ?></td>
                        <td>
                            <?php if(!empty($row['image'])): ?>
                                <img src="<?= SITEURL ?>img/stdent_image/<?= $row['image'] ?>" style="height:40px;width:40px;border-radius:50%">
                            <?php else: ?>
                                No Image
                            <?php endif; ?>
                        </td>
                        <td class="text-uppercase"><?= $row['status'] ?></td>
                        <td class="d-flex gap-2">
                            <a href="<?= SITEURL ?>edit_stdnt.php?student_id=<?= $row['student_id'] ?>" class="btn btn-success btn-small">
                                <i class="fa-solid fa-pencil"></i>
                            </a>
                            <a href="<?= SITEURL ?>delete_stdnt.php?student_id=<?= $row['student_id'] ?>&image=<?= $row['image'] ?>" 
                               class="btn btn-danger btn-small"
                               onclick="return confirm('Do you want to delete this student?')">
                                <i class="fa-solid fa-trash-can"></i>
                            </a>
                        </td>
                    </tr>
                    <?php
                }
                ?>
            </tbody>
        </table>
    </div>
</div>

<?php include("partials/footer.php"); ?>

<script>
document.addEventListener("DOMContentLoaded", function() {
    const classSelect = document.getElementById("classSelect");
    const streamSelect = document.getElementById("streamSelect");
    const studentsBody = document.getElementById("studentsBody");

    function fetchStudents() {
        const classId = classSelect.value;
        const streamId = streamSelect.value;
        let url = "get_students.php?";
        if (classId) url += "class_id=" + classId + "&";
        if (streamId) url += "stream_id=" + streamId;
        fetch(url)
            .then(response => response.text())
            .then(data => {
                studentsBody.innerHTML = data;
            });
    }

    classSelect.addEventListener("change", function() {
        const classId = this.value;
        if (classId) {
            fetch("get_streams.php?class_id=" + classId)
                .then(response => response.text())
                .then(data => {
                    streamSelect.innerHTML = "<option value=''>-- Select Stream --</option>" + data;
                    streamSelect.disabled = false;
                });
        } else {
            streamSelect.innerHTML = "<option value=''>-- Select Stream --</option>";
            streamSelect.disabled = true;
        }
        fetchStudents();
    });

    streamSelect.addEventListener("change", fetchStudents);

    // Initial load
    fetchStudents();

    // CSV Download (unchanged)
    document.getElementById("downloadCSV").addEventListener("click", function() {
        let table = document.getElementById("studentsTable");
        let csv = [];
        for (let row of table.rows) {
            let cols = [...row.cells].map(cell => `"${cell.innerText}"`);
            csv.push(cols.join(","));
        }

        let blob = new Blob([csv.join("\n")], { type: "text/csv" });
        let link = document.createElement("a");
        link.href = URL.createObjectURL(blob);
        link.download = "students.csv";
        link.click();
    });
});
</script>
