<?php

require_once __DIR__ . '/../../config/bootstrap.php';
require_once __DIR__ . '/../../config/Connection.php';
require_once __DIR__ . '/../../app/Helpers/auth.php';

require_role('teacher');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    redirect('/student-management-system/public/teacher/results.php');
}

verify_csrf_token();

$assignmentId = (int) ($_POST['assignment_id'] ?? 0);
$classId = (int) ($_POST['class_id'] ?? 0);
$subjectId = (int) ($_POST['subject_id'] ?? 0);
$termId = (int) ($_POST['term_id'] ?? 0);
$rows = $_POST['results'] ?? [];

if ($classId <= 0 || $subjectId <= 0 || $termId <= 0 || !$rows) {
    $_SESSION['error'] = 'Please load students and enter result scores.';
    redirect('/student-management-system/public/teacher/results.php');
}

function calculate_grade(float $score): string
{
    if ($score >= 80) {
        return 'A';
    }

    if ($score >= 70) {
        return 'B';
    }

    if ($score >= 60) {
        return 'C';
    }

    if ($score >= 50) {
        return 'D';
    }

    return 'F';
}

try {
    $database = Connection::connect();

    $teacherStatement = $database->prepare('SELECT id FROM teachers WHERE user_id = :user_id LIMIT 1');
    $teacherStatement->execute(['user_id' => current_user()['id']]);
    $teacherId = (int) $teacherStatement->fetchColumn();

    $statement = $database->prepare(
        'INSERT INTO results
         (student_id, class_id, subject_id, teacher_id, term_id, class_score, exam_score, grade, remark)
         VALUES
         (:student_id, :class_id, :subject_id, :teacher_id, :term_id, :class_score, :exam_score, :grade, :remark)
         ON DUPLICATE KEY UPDATE
            class_score = VALUES(class_score),
            exam_score = VALUES(exam_score),
            grade = VALUES(grade),
            remark = VALUES(remark),
            teacher_id = VALUES(teacher_id)'
    );

    foreach ($rows as $studentId => $row) {
        $classScore = max(0, min(50, (float) ($row['class_score'] ?? 0)));
        $examScore = max(0, min(50, (float) ($row['exam_score'] ?? 0)));
        $totalScore = $classScore + $examScore;

        $statement->execute([
            'student_id' => (int) $studentId,
            'class_id' => $classId,
            'subject_id' => $subjectId,
            'teacher_id' => $teacherId,
            'term_id' => $termId,
            'class_score' => $classScore,
            'exam_score' => $examScore,
            'grade' => calculate_grade($totalScore),
            'remark' => trim($row['remark'] ?? ''),
        ]);
    }

    $_SESSION['success'] = 'Results saved successfully.';
} catch (PDOException $exception) {
    $_SESSION['error'] = 'Unable to save results. Please try again.';
}

redirect('/student-management-system/public/teacher/results.php?assignment_id=' . $assignmentId);
