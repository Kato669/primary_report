<?php ob_start();
    include("partials/header.php");
    include("partials/adminOnly.php") ?>
<div class="container-fluid">
    <div class="row g-0 my-2">
        <div class="col-lg-4 col-md-4 col-sm-12">
            <a href="<?php echo SITEURL ?>add_student.php" class="btn text-capitalize text-white btn-success fs-6">
                add student
                <i class="fa-solid fa-pen-to-square"></i>
            </a>
        </div>
    </div>
    <h3 class="text-capitalize fs-6 text-dark py-2">view students</h3>
    <div class="row">
        <table id="example" class="display" style="overflow-x: hidden;">
            <thead>
                <tr>
                    <th class="text-capitalize">Sn</th>
                    <th class="text-capitalize">first name</th>
                   
                    <th class="text-capitalize">last name</th>
                    <th class="text-capitalize">gender</th>
                    <th class="text-capitalize">DOB</th>
                    <th class="text-capitalize">admission no.</th>
                    <th class="text-capitalize">class</th>
                    <th class="text-capitalize">stream</th>
                    <th class="text-capitalize">image</th>
                     <th class="text-capitalize">status</th>
                    <th class="text-capitalize">action</th>
                   
                </tr>
            </thead>
            <tbody>
                <!-- selecting data from database -->
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
                    if(!$executeData){
                        die("Failed execution". mysqli_error($conn));
                    } 
                    $sn= 1;
                    while($row = mysqli_fetch_assoc($executeData)){
                        $student_id = $row['student_id'];
                        $first_name = $row['first_name'];
                        $last_name = $row['last_name'];
                        $gender = $row['gender'];
                        $dob = $row['dob'];
                        $LIN = $row['LIN'];
                        $image = $row['image'];
                        $status = $row['status'];
                        ?>
                            <tr>
                                <td><?php echo $sn++ ?></td>
                                <td><?php echo $first_name ?></td>
                                <td><?php echo $last_name ?></td>
                                <td><?php echo $gender ?></td>
                                <td><?php echo $dob ?></td>
                                <td><?php echo $LIN ?></td>
                                <td><?php echo $row['className'] ?></td>
                                <td><?php echo $row['streamName'] ?></td>
                                <td>
                                    <?php if(!empty($image)): ?>
                                        <img class="" src="<?php echo SITEURL ?>img/stdent_image/<?php echo $image ?>" alt="" srcset="" style="height: 40px; width: 40px; border-radius: 100%">
                                    <?php else: ?>
                                        <?php echo "No Image"; ?>
                                    <?php endif; ?>
                                </td>
                                <td><?php echo $status ?></td>
                                <td class="d-flex gap-2">
                                    <!-- <a href="" class="btn btn-secondary btn-small">
                                        <i class="fa-solid fa-eye"></i>
                                    </a> -->
                                    <a href="<?php echo SITEURL ?>edit_stdnt.php?student_id=<?php echo $student_id ?>" class="btn btn-success btn-small" >
                                        <i class="fa-solid fa-pencil"></i>
                                    </a>
                                    <a href="<?php echo SITEURL ?>delete_stdnt.php?student_id=<?php echo $student_id ?>&&image=<?php echo $image ?>" 
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