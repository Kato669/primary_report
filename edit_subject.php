<?php 
ob_start();
include("partials/header.php"); 

if(isset($_GET['subject_id'])){
    $subject_id = $_GET['subject_id'];
}
?>

<!-- update table -->
<?php 
if(isset($_POST['updatesubject'])){
    $subject = strtoupper(trim(mysqli_real_escape_string($conn, $_POST['subject'])));
    $short_code = strtoupper(trim(mysqli_real_escape_string($conn, $_POST['short_code'] ?? '')));
    $onlystrng = preg_match('/^[a-zA-Z0-9 .]+$/', $subject); // allow letters, numbers, space, dot

    //check whether data exist
    $selectTocheck = "SELECT * FROM subjects WHERE subject_name='$subject' AND subject_id != $subject_id";
    $execute = mysqli_query($conn, $selectTocheck);

    if($execute && mysqli_num_rows($execute) > 0){
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
                Command: toastr["error"]("Subject already exists");
            </script>
        ';
    } else {
        if($onlystrng){
            $update = "UPDATE subjects SET
                subject_name = '$subject',
                short_code = '$short_code'
                WHERE subject_id = $subject_id
            ";
            $res = mysqli_query($conn, $update);
            if($res){
                $_SESSION['update_subject'] = "Subject updated successfully";
                header("Location:".SITEURL."subject.php");
                exit;
            }else{
                $_SESSION['failed'] = "Failed to update subject";
                exit;
            }
        } else {
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
                    Command: toastr["error"]("Subject name can only contain letters and numbers");
                </script>
            ';
        }
    }
}
?>

<!-- fetch data from table to populate -->
<?php 
$selectToupdate = "SELECT * FROM subjects WHERE subject_id=$subject_id";
$res = mysqli_query($conn, $selectToupdate);
if($res && mysqli_num_rows($res) > 0){
    $row = mysqli_fetch_assoc($res);
}
?>

<div class="container-fluid my-3">
    <div class="row">
        <h3 class="text-capitalize fs-6 text-dark py-2">update subject</h3>
        <div class="col-lg-6 col-sm-12 col-md-12 shadow rounded p-5">
            <form method="POST" action="">
                <div class="mb-3">
                    <label for="subject" class="form-label text-capitalize fw-bold">subject name</label>
                    <input type="text" class="form-control shadow-none" name="subject" id="subject" 
                           value="<?php echo htmlspecialchars($row['subject_name']); ?>" autocomplete="off" required>
                </div>

                <div class="mb-3">
                    <label for="short_code" class="form-label text-capitalize fw-bold">short code</label>
                    <input type="text" class="form-control shadow-none" name="short_code" id="short_code"
                           value="<?php echo htmlspecialchars($row['short_code'] ?? ''); ?>" 
                           placeholder="e.g. ENG" maxlength="10">
                </div>

                <button type="submit" name="updatesubject" class="btn btn-primary text-capitalize">
                    update subject
                </button>
            </form>
        </div>
    </div>
</div>

<?php include("partials/footer.php"); ?>
