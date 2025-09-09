<?php

include("constants/constants.php");

$class_id = intval($_GET['class_id'] ?? 0);
$stream_id = intval($_GET['stream_id'] ?? 0);

$where = [];
if ($class_id) $where[] = "students.class_id = $class_id";
if ($stream_id) $where[] = "students.stream_id = $stream_id";
$where_sql = $where ? "WHERE " . implode(" AND ", $where) : "";

$sql = "
    SELECT students.*, 
        streams.stream_name AS streamName, 
        classes.class_name AS className
    FROM students
    JOIN classes ON classes.id = students.class_id
    JOIN streams ON streams.id = students.stream_id
    $where_sql
";
$res = mysqli_query($conn, $sql);
$sn = 1;
while($row = mysqli_fetch_assoc($res)){
    ?>
    <tr>
        <td><?= $sn++ ?></td>
        <td><?= htmlspecialchars($row['first_name']) ?></td>
        <td><?= htmlspecialchars($row['last_name']) ?></td>
        <td><?= htmlspecialchars($row['gender']) ?></td>
        <td><?= htmlspecialchars($row['dob']) ?></td>
        <td><?= htmlspecialchars($row['LIN']) ?></td>
        <td><?= htmlspecialchars($row['className']) ?></td>
        <td><?= htmlspecialchars($row['streamName']) ?></td>
        <td>
            <?php if(!empty($row['image'])): ?>
                <img src="<?= SITEURL ?>img/stdent_image/<?= htmlspecialchars($row['image']) ?>" style="height:40px;width:40px;border-radius:50%">
            <?php else: ?>
                No Image
            <?php endif; ?>
        </td>
        <td><?= htmlspecialchars($row['status']) ?></td>
        <td class="d-flex gap-2">
            <a href="<?= SITEURL ?>edit_stdnt.php?student_id=<?= $row['student_id'] ?>" class="btn btn-success btn-small">
                <i class="fa-solid fa-pencil"></i>
            </a>
            <a href="<?= SITEURL ?>delete_stdnt.php?student_id=<?= $row['student_id'] ?>&image=<?= htmlspecialchars($row['image']) ?>" 
               class="btn btn-danger btn-small"
               onclick="return confirm('Do you want to delete this student?')">
                <i class="fa-solid fa-trash-can"></i>
            </a>
        </td>
    </tr>
    <?php
}
?>