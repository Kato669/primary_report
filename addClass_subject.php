<?php 
ob_start();
include("partials/header.php");

if (isset($_POST['addClass'])) {
    $class_id = intval($_POST['class'] ?? 0);
    $subjects = $_POST['subject'] ?? []; // this will be an array of selected subjects

    if (!empty($class_id) && !empty($subjects)) {
        $errors = [];
        foreach ($subjects as $subject_id) {
            $subject_id = intval($subject_id);
            if (!$subject_id) continue;

            // Check if this combination already exists
            $check = "SELECT * FROM class_subjects WHERE class_id=$class_id AND subject_id=$subject_id";
            $res = mysqli_query($conn, $check);

            if ($res && mysqli_num_rows($res) > 0) {
                $errors[] = $subject_id; // store duplicates
                continue;
            }

            // Insert new record
            $insert = "INSERT INTO class_subjects (class_id, subject_id) VALUES ($class_id, $subject_id)";
            mysqli_query($conn, $insert);
        }

        if (empty($errors)) {
            echo '<script>toastr.success("Subjects assigned successfully!");</script>';
        } else {
            echo '<script>toastr.warning("Some subjects were already assigned and skipped.");</script>';
        }

    } else {
        echo '<script>toastr.error("Class and at least one subject must be selected.");</script>';
    }
}
?>

<div class="container-fluid my-3">
    <div class="row">
        <div class="col-lg-6 col-sm-12 col-md-12 shadow rounded p-5">
            <h3 class="text-capitalize fs-6 text-dark py-2">Choose class and subjects taught in that class</h3>
            <form method="POST" action="">
                <!-- Class dropdown -->
                <div class="mb-3">
                    <label class="form-label text-capitalize fw-bold">Class</label>
                    <select class="form-select shadow-none" name="class" required>
                        <option selected disabled>Choose class</option>
                        <?php 
                        $selectClass = "SELECT * FROM classes";
                        $executeClass = mysqli_query($conn, $selectClass);
                        while ($fetchClass = mysqli_fetch_assoc($executeClass)) {
                            echo '<option class="text-uppercase" value="'.$fetchClass['id'].'">'.htmlspecialchars($fetchClass['class_name']).'</option>';
                        }
                        ?>
                    </select>
                </div>

                <!-- Subjects container -->
                <div id="subjects-container">
                    <div class="d-flex mb-2 subject-row">
                        <select class="form-select shadow-none" name="subject[]" required>
                            <option selected disabled>Choose subject</option>
                            <?php 
                            $selectSubject = "SELECT * FROM subjects";
                            $executeSubject = mysqli_query($conn, $selectSubject);
                            while ($fetchSubject = mysqli_fetch_assoc($executeSubject)) {
                                echo '<option value="'.$fetchSubject['subject_id'].'">'.htmlspecialchars($fetchSubject['subject_name']).'</option>';
                            }
                            ?>
                        </select>
                        <button type="button" class="btn btn-success ms-2 add-subject">+</button>
                    </div>
                </div>

                <!-- Submit button -->
                <button type="submit" name="addClass" class="btn btn-primary mt-3">Assign subjects</button>
            </form>
        </div>
    </div>
</div>

<script>
document.addEventListener("DOMContentLoaded", function() {
    const container = document.getElementById("subjects-container");

    // All subjects from PHP dropdown
    const allSubjects = Array.from(
        container.querySelector("select[name='subject[]']").options
    )
    .filter(opt => opt.value !== "" && opt.value !== "Choose subject")
    .map(opt => ({ value: opt.value, text: opt.text }));

    function getSelected() {
        return Array.from(container.querySelectorAll("select[name='subject[]']"))
            .map(sel => sel.value)
            .filter(v => v !== "");
    }

    function createDropdown() {
        const selected = getSelected();

        const select = document.createElement("select");
        select.name = "subject[]";
        select.className = "form-select shadow-none mb-2";

        // Default option
        const def = document.createElement("option");
        def.innerText = "Choose subject";
        def.disabled = true;
        def.selected = true;
        select.appendChild(def);

        // Add only unselected subjects
        allSubjects.forEach(sub => {
            if (!selected.includes(sub.value)) {
                const opt = document.createElement("option");
                opt.value = sub.value;
                opt.textContent = sub.text;
                select.appendChild(opt);
            }
        });

        return select;
    }

    container.addEventListener("click", function(e) {
        // Add new row
        if (e.target.classList.contains("add-subject")) {

            const row = document.createElement("div");
            row.className = "d-flex mb-2 subject-row";

            const select = createDropdown();
            row.appendChild(select);

            const btn = document.createElement("button");
            btn.type = "button";
            btn.className = "btn btn-danger ms-2 remove-subject";
            btn.textContent = "−";

            row.appendChild(btn);

            container.appendChild(row);
        }

        // Remove row
        if (e.target.classList.contains("remove-subject")) {
            e.target.parentElement.remove();
        }
    });

    // Rebuild dropdowns when user selects something
    container.addEventListener("change", function(e) {
        if (e.target.name === "subject[]") {
            const selects = container.querySelectorAll("select[name='subject[]']");
            const selected = getSelected();

            selects.forEach(sel => {
                const current = sel.value;
                sel.innerHTML = "";

                // Default option
                const def = document.createElement("option");
                def.innerText = "Choose subject";
                def.disabled = true;
                if (current === "") def.selected = true;
                sel.appendChild(def);

                allSubjects.forEach(sub => {
                    if (!selected.includes(sub.value) || sub.value === current) {
                        const opt = document.createElement("option");
                        opt.value = sub.value;
                        opt.textContent = sub.text;
                        if (current === sub.value) opt.selected = true;
                        sel.appendChild(opt);
                    }
                });
            });
        }
    });

});
</script>


<?php include("partials/footer.php"); ?>
