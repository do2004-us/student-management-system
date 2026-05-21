<?php

require_once __DIR__ . '/../../config/bootstrap.php';
require_once __DIR__ . '/../../config/Connection.php';
require_once __DIR__ . '/../../app/Helpers/auth.php';

require_role('teacher');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    redirect('/student-management-system/public/teacher/attendance.php');
}

verify_csrf_token();

$classId = (int) ($_POST['class_id'] ?? 0);
$attendanceDate = $_POST['attendance_date'] ?? date('Y-m-d');
$attendanceRows = $_POST['attendance'] ?? [];

if ($classId <= 0 || !$attendanceRows) {
    $_SESSION['error'] = 'Please select a class and mark at least one student.';
    redirect('/student-management-system/public/teacher/attendance.php');
}

try {
    $database = Connection::connect();

    $statement = $database->prepare(
        'INSERT INTO attendance (student_id, class_id, marked_by, attendance_date, status, remarks)
         VALUES (:student_id, :class_id, :marked_by, :attendance_date, :status, :remarks)
         ON DUPLICATE KEY UPDATE
            status = VALUES(status),
            remarks = VALUES(remarks),
            marked_by = VALUES(marked_by)'
    );

    foreach ($attendanceRows as $studentId => $row) {
        $status = $row['status'] ?? 'present';
        $remarks = trim($row['remarks'] ?? '');

        if (!in_array($status, ['present', 'absent', 'late', 'excused'], true)) {
            $status = 'present';
        }

        $statement->execute([
            'student_id' => (int) $studentId,
            'class_id' => $classId,
            'marked_by' => current_user()['id'],
            'attendance_date' => $attendanceDate,
            'status' => $status,
            'remarks' => $remarks,
        ]);
    }

    $_SESSION['success'] = 'Attendance saved successfully.';
} catch (PDOException $exception) {
    $_SESSION['error'] = 'Unable to save attendance. Please try again.';
}

redirect('/student-management-system/public/teacher/attendance.php?class_id=' . $classId . '&attendance_date=' . urlencode($attendanceDate));
