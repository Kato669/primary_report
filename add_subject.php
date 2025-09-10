<?php 
    ob_start();
    include("partials/header.php");
    include("partials/adminOnly.php");
    if(isset($_POST['addsubject'])){
        $subject = strtoupper(trim(mysqli_real_escape_string($conn, $_POST['subject'] ?? "")));
        $onlyString = preg_match('/^[a-zA-Z0-9
        ]+$/', $subject);
        if(!empty($subject) && $onlyString){
            // check whether subject already submited
            $selectSbjct = "SELECT * FROM subjects WHERE subject_name = '$subject'";
            $executeSbjct = mysqli_query($conn, $selectSbjct);
            if($executeSbjct && mysqli_num_rows($executeSbjct) > 0){
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
                        Command: toastr["error"]("subject already added");
                    </script>
                    ';
            }else{
                $sql = "INSERT INTO subjects SET subject_name = '$subject'";
                $res = mysqli_query($conn, $sql);
                if($res){
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
                            Command: toastr["success"]("subject added successfully");
                        </script>
                        ';
                }else{
                    die("Failed to execute". mysqli_error($conn));
                }
            }
        }else{
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
                Command: toastr["error"]("subject can not be empty and only have strings");
            </script>
            ';
        }
        
    }
?>
<div class="container-fluid my-3">
    <div class="row">
        <div class="col-lg-6 col-sm-12 col-md-12 shadow rounded p-5">
            <form method="POST" action="">
                <div class="mb-3">
                    <label for="subject" class="form-label text-capitalize fw-bold">subject name</label>
                    <input type="text" class="form-control shadow-none" name="subject" id="subject" placeholder="mathematics, english " autocomplete="off" required>
                    <span class="text-danger fs-6">please enter subjects in full</span>
                </div>
                <button type="submit" name="addsubject" class="btn btn-primary text-capitalize">Add subject</button>
            </form>
        </div>
    </div>
</div>
<?php include("partials/footer.php") ?>