<?php include("partials/header.php"); ?>
<?php
    if(isset($_SESSION['delete_exam'])){
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
            Command: toastr["error"]("'.$_SESSION['delete_exam'].'");
        </script>
        ';
        unset($_SESSION['delete_exam']);
    }
?>
<div class="container-fluid">
    <!-- button to add class -->
    <div class="row g-0 my-2">
        <div class="col-lg-4 col-md-4 col-sm-12">
            <a href="<?php echo SITEURL ?>addExam.php" class="btn text-capitalize text-white btn-success fs-6">
                add examination
                <i class="fa-solid fa-pen-to-square"></i>
            </a>
        </div>
        <h3 class="text-capitalize fs-6 text-dark py-2">view examination</h3>
        <div class="col-lg-12">
            <table id="example" class="display">
                <thead>
                    <tr>
                        <th>Sn</th>
                        <th class="text-capitalize">exam name</th>
                        <th class="text-capitalize">class</th>
                        <th class="text-capitalize">term</th>
                        <th class="text-capitalize">year</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                    <!-- select  -->
                    <?php
                        $select = "SELECT exams.*,
                            terms.term_name AS termName,
                            classes.class_name AS className
                            FROM exams
                            JOIN terms ON terms.term_id=exams.term_id
                            JOIN classes ON classes.id=exams.class_id
                        ";
                        $execute = mysqli_query($conn, $select);
                        if($execute && mysqli_num_rows($execute)>0){
                            $sn=1;
                            while($row = mysqli_fetch_assoc($execute)){
                                $exam_id = $row['exam_id'];
                                $exam_name = $row['exam_name'];
                                $class = $row['className'];
                                $term = $row['termName'];
                                $year = $row['academic_year'];
                                ?>
                                <tr>
                                    <td><?php echo $sn++ ?></td>
                                    <td class="text-capitalize"><?php echo $exam_name?> </td>
                                    <td class="text-capitalize"><?php echo $class ?></td>
                                    <td class="text-capitalize"><?php echo $term?></td>
                                    <td><?php echo $year ?></td>
                                    <td>
                                        <a href="<?php echo SITEURL ?>edit_exam.php?exam_id=<?php echo $exam_id ?>" class="btn btn-primary"><i class="fa-solid fa-pen-to-square"></i></a>
                                        <a href="<?php echo SITEURL ?>delete_exam.php?exam_id=<?php echo $exam_id ?>" onclick="return confirm('Do you want to delete?')" class="btn btn-danger"><i class="fa-solid fa-trash"></i></a>
                                    </td>
                                </tr>
                                <?php
                            }
                        }
                    ?>
                    
                </tbody>
            </table>
        </div>
    </div>
</div>
<?php include("partials/footer.php") ?>