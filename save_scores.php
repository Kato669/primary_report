<?php
require_once 'partials/header.php';

if (!isset($_SESSION['logged_in']) || !in_array($_SESSION['role'], ['admin', 'class_teacher', 'teacher'])) {
    $_SESSION['must_login'] = 'Please log in first.';
    header('Location: login.php');
    exit;
}

$role = $_SESSION['role'];
$user_id = intval($_SESSION['user_id']);
$class_id = intval($_SESSION['class_id'] ?? 0);
$stream_id = intval($_SESSION['stream_id'] ?? 0);

$allowed_subjects = [];
if ($role === 'teacher') {
    $assign_query = "
        SELECT subject_id
        FROM teacher_subject_assignments
        WHERE teacher_id = ?
          AND academic_year = (
              SELECT MAX(academic_year)
              FROM teacher_subject_assignments
              WHERE teacher_id = ?
          )
    ";

    $stmt = $conn->prepare($assign_query);
    $stmt->bind_param('ii', $user_id, $user_id);
    $stmt->execute();
    $res = $stmt->get_result();

    while ($row = $res->fetch_assoc()) {
        $allowed_subjects[] = intval($row['subject_id']);
    }
    $stmt->close();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['save_scores']) && is_array($_POST['scores'])) {
    $conn->begin_transaction();
    $error = null;

    try {
        $update_stmt = $conn->prepare('UPDATE marks SET score = ? WHERE mark_id = ?');
        $select_stmt = $conn->prepare(
            'SELECT m.mark_id, m.student_id, m.subject_id, st.class_id, st.stream_id
             FROM marks m
             JOIN students st ON st.student_id = m.student_id
             WHERE m.mark_id = ?'
        );

        foreach ($_POST['scores'] as $mark_id => $score_value) {
            $mark_id = intval($mark_id);
            if ($mark_id <= 0) {
                continue;
            }

            $score = trim($score_value);
            if ($score === '') {
                continue;
            }

            if (!is_numeric($score)) {
                throw new Exception('All scores must be numeric values.');
            }

            $score = floatval($score);
            if ($score < 0 || $score > 100) {
                throw new Exception('Scores must be between 0 and 100.');
            }

            $select_stmt->bind_param('i', $mark_id);
            $select_stmt->execute();
            $result = $select_stmt->get_result();

            if ($result->num_rows === 0) {
                continue;
            }

            $record = $result->fetch_assoc();
            $subject_id = intval($record['subject_id']);
            $student_class_id = intval($record['class_id']);
            $student_stream_id = intval($record['stream_id']);

            if ($role === 'class_teacher' && ($student_class_id !== $class_id || $student_stream_id !== $stream_id)) {
                throw new Exception('Unauthorized access to one or more records.');
            }

            if ($role === 'teacher' && !in_array($subject_id, $allowed_subjects, true)) {
                throw new Exception('Unauthorized subject access.');
            }

            $update_stmt->bind_param('di', $score, $mark_id);
            $update_stmt->execute();
        }

        $conn->commit();
        $_SESSION['success_msg'] = 'Scores updated successfully.';
    } catch (Exception $e) {
        $conn->rollback();
        $_SESSION['error_msg'] = 'Failed to save scores: ' . $e->getMessage();
    }

    header('Location: declare_marks.php');
    exit;
}

header('Location: declare_marks.php');
exit;
