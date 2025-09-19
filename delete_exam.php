<?php 
    ob_start();
    // include("constants/constants.php");
    include("partials/header.php");

    $role = $_SESSION['role'] ?? '';
    $class_id = $_SESSION['class_id'] ?? null;
    $stream_id = $_SESSION['stream_id'] ?? null;

    $exam_id = intval($_GET['exam_id'] ?? 0);

    if (!$exam_id) {
        header("Location: examination.php?msg=invalid");
        exit();
    }

    // Fetch exam details
    $stmt = $conn->prepare("SELECT class_id FROM exams WHERE exam_id = ?");
    if (!$stmt) {
        die("Prepare failed: " . $conn->error);
    }
    $stmt->bind_param("i", $exam_id);
    $stmt->execute();
    $res = $stmt->get_result();

    if ($res->num_rows === 0) {
        header("Location: examination.php?msg=notfound");
        exit();
    }

    $exam = $res->fetch_assoc();

    // Restrict class_teacher
    if ($role === 'class_teacher') {
        if ($exam['class_id'] != $class_id) {
            header("Location: examination.php?msg=forbidden");
            exit();
        }
    }

    // Proceed with deletion
    $del = $conn->prepare("DELETE FROM exams WHERE exam_id = ?");
    $del->bind_param("i", $exam_id);
    if ($del->execute()) {
        header("Location: examination.php?msg=deleted");
    } else {
        header("Location: examination.php?msg=error");
    }
    exit();
?>