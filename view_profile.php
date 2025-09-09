<?php 
    ob_start();
    include("partials/header.php");
    include("partials/adminOnly.php");
    $select = "SELECT * FROM school_profile";
    $execute = mysqli_query($conn, $select);
    if(!$execute){
        die("Failed execution". mysqli_error($conn));
    }
    $row = mysqli_fetch_assoc($execute);
    $school_name = $row['school_name'];
    $location = $row['address'];
    $contact_1 = $row['phone_1'];
    $contact_2 = $row['phone_2'];
    $email = $row['email'];
    $motto = $row['motto'];
    $logo = $row['profile_image'];
?>
<h3 class="py-4 text-capitalize fs-6">edit school profile</h3>
<div class="container">
    <div class="row my-4">
        <div class="row">
            <div class="col-lg-6">
                <table class="table table-hover">
                    <tr>
                        <td class="fw-bold">School Name:</td>
                        <td><?php echo $school_name ?></td>
                    </tr>
                    <tr>
                        <td class="fw-bold">School Location:</td>
                        <td><?php echo $location ?></td>
                    </tr>
                    <tr>
                        <td class="fw-bold">Contacts:</td>
                        <td><?php echo $contact_1 ?></td>
                    </tr>
                    <tr>
                        <td class="fw-bold">Post Office Num:</td>
                        <td><?php echo $contact_2 ?></td>
                    </tr>
                </table>
            </div>
            <div class="col-lg-6">
                <table class="table table-hover">
                    <tr>
                        <td class="fw-bold">School Email:</td>
                        <td><?php echo $email ?></td>
                    </tr>
                    <tr>
                        <td class="fw-bold">School Motto:</td>
                        <td><?php echo $motto ?></td>
                    </tr>
                    <tr>
                        <td class="fw-bold">School Logo:</td>
                        <td>
                            <?php if(!empty($logo)): ?>
                                <img src="<?php echo htmlspecialchars($logo, ENT_QUOTES, "UTF-8") ?>" class="img img-fluid form-control" alt="" style="height: 100px; width:100px">
                            <?php endif ?>
                        </td>
                    </tr>
                    <tr>
                        <td>
                            <a href="<?php echo SITEURL ?>update_profile.php" class="btn btn-success btn-large text-capitalize w-100 shadow-none outline-none">update profile</a>
                        </td>
                    </tr>
                </table>
            </div>
        </div>
    </div>
</div>
<?php include("partials/footer.php")?>