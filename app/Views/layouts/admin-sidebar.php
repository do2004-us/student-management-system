<?php

$currentPage = basename($_SERVER['SCRIPT_NAME']);

function admin_nav_class(string $page, string $currentPage): string
{
    return $page === $currentPage ? 'active' : '';
}
?>
<aside class="sidebar">
    <div class="sidebar-brand">
        <span class="brand-mark">SMS</span>
        <strong>School Admin</strong>
    </div>

    <nav class="sidebar-nav">
        <a href="dashboard.php" class="<?= e(admin_nav_class('dashboard.php', $currentPage)); ?>"><span>▣</span>Dashboard</a>
        <a href="students.php" class="<?= e(admin_nav_class('students.php', $currentPage)); ?>"><span>◎</span>Students</a>
        <a href="parents.php" class="<?= e(admin_nav_class('parents.php', $currentPage)); ?>"><span>◉</span>Parents</a>
        <a href="teachers.php" class="<?= e(admin_nav_class('teachers.php', $currentPage)); ?>"><span>◈</span>Teachers</a>
        <a href="classes.php" class="<?= e(admin_nav_class('classes.php', $currentPage)); ?>"><span>▤</span>Classes</a>
        <a href="subjects.php" class="<?= e(admin_nav_class('subjects.php', $currentPage)); ?>"><span>◇</span>Subjects</a>
        <a href="class-subjects.php" class="<?= e(admin_nav_class('class-subjects.php', $currentPage)); ?>"><span>□</span>Class Subjects</a>
        <a href="teacher-subjects.php" class="<?= e(admin_nav_class('teacher-subjects.php', $currentPage)); ?>"><span>◌</span>Teacher Subjects</a>
        <a href="terms.php" class="<?= e(admin_nav_class('terms.php', $currentPage)); ?>"><span>◷</span>Terms</a>
        <a href="attendance.php" class="<?= e(admin_nav_class('attendance.php', $currentPage)); ?>"><span>✓</span>Attendance</a>
        <a href="results.php" class="<?= e(admin_nav_class('results.php', $currentPage)); ?>"><span>⌁</span>Results</a>
        <a href="fees.php" class="<?= e(admin_nav_class('fees.php', $currentPage)); ?>"><span>₵</span>Fees</a>
        <a href="reports.php" class="<?= e(admin_nav_class('reports.php', $currentPage)); ?>"><span>▧</span>Reports</a>
        <a href="notifications.php" class="<?= e(admin_nav_class('notifications.php', $currentPage)); ?>"><span>●</span>Notifications</a>
        <a href="../change-password.php"><span>◐</span>Change Password</a>
    </nav>
</aside>
