<?php 
    ob_start();
    include("partials/header.php");
    include("partials/adminOnly.php")
 ?>
<div class="container-fluid">
    <!-- button to add class -->
    <div class="row g-0 my-2">
        <div class="col-lg-4 col-md-4 col-sm-12">
            <a href="<?php echo SITEURL ?>addStream.php" class="btn text-capitalize text-white btn-success fs-6">
                add stream
                <i class="fa-solid fa-pen-to-square"></i>
            </a>
        </div>
    </div>

    <?php
        if (isset($_SESSION['added_stream'])) {
            $message = addslashes($_SESSION['added_stream']); // escape quotes
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
                "hideMethod": "fadeOut"
            }
            Command: toastr["success"]("' . $message . '");
            </script>
            ';
            unset($_SESSION['added_stream']);
        }
        if (isset($_SESSION['delete_stream'])) {
            $message = addslashes($_SESSION['delete_stream']); // escape quotes
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
                "hideMethod": "fadeOut"
            }
            Command: toastr["error"]("'.$_SESSION['delete_stream'].'");
            </script>
            ';
            unset($_SESSION['delete_stream']);
        }
    ?>

    <h3 class="text-capitalize fs-6 text-dark py-2">view streams</h3>
    <h4 class="text-uppercase fw-bold text-center mb-3 bg-primary text-white py-2 rounded">STREAMS FOR <?php echo htmlspecialchars($school_name); ?> PRIMARY SCHOOL</h4>
    <div class="row">
        <div class="col-lg-12">
            <table id="example" class="display">
                <thead>
                    <tr>
                        <th>Sn</th>
                        <th>Class Name</th>
                        <th class="text-capitalize">Stream Name</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                    <!-- fetching data from streams table -->
                    <?php 
                        $selectStream = "SELECT streams.*, classes.class_name AS className 
                                         FROM streams 
                                         JOIN classes ON classes.id = streams.class_id";
                        $res = mysqli_query($conn, $selectStream);

                        if (!$res) {
                            die("Failed execution: " . mysqli_error($conn));
                        }

                        $count = mysqli_num_rows($res);
                        if ($count > 0) {
                            $sn = 1;
                            while ($row = mysqli_fetch_assoc($res)) {
                                $stream_id = $row['id'];
                                $streamName = $row['stream_name'];
                                ?>
                                <tr>
                                    <td><?php echo $sn++; ?></td>
                                    <td><?php echo $row['className']; ?></td>
                                    <td class="text-uppercase"><?php echo $streamName; ?></td>
                                    <td>
                                        
                                        <a href="<?php echo SITEURL ?>delete_stream.php?id=<?php echo $stream_id ?>" 
                                           onclick="return confirm('Are you sure you want to delete this stream?');" 
                                           class="btn btn-danger">
                                            <i class="fa-solid fa-trash-can"></i>
                                        </a>
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
<?php include('partials/footer.php'); ?>
