-- Student Management System Database
-- Step 1: Create the database
CREATE DATABASE IF NOT EXISTS student_management_system
  CHARACTER SET utf8mb4
  COLLATE utf8mb4_unicode_ci;

USE student_management_system;

-- Stores user roles such as Admin, Teacher, and Student.
CREATE TABLE roles (
  id INT AUTO_INCREMENT PRIMARY KEY,
  name VARCHAR(50) NOT NULL UNIQUE,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

-- Stores login information for every system user.
CREATE TABLE users (
  id INT AUTO_INCREMENT PRIMARY KEY,
  role_id INT NOT NULL,
  full_name VARCHAR(120) NOT NULL,
  email VARCHAR(150) NOT NULL UNIQUE,
  password VARCHAR(255) NOT NULL,
  phone VARCHAR(30) NULL,
  status ENUM('active', 'inactive', 'suspended') NOT NULL DEFAULT 'active',
  last_login_at DATETIME NULL,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  CONSTRAINT fk_users_role
    FOREIGN KEY (role_id) REFERENCES roles(id)
    ON UPDATE CASCADE
    ON DELETE RESTRICT
) ENGINE=InnoDB;

-- Stores school class groups, for example JHS 1, Grade 5, or Form 2 Science.
CREATE TABLE classes (
  id INT AUTO_INCREMENT PRIMARY KEY,
  class_name VARCHAR(80) NOT NULL UNIQUE,
  class_teacher_id INT NULL,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB;

-- Stores teacher profile information.
CREATE TABLE teachers (
  id INT AUTO_INCREMENT PRIMARY KEY,
  user_id INT NOT NULL UNIQUE,
  staff_number VARCHAR(50) NOT NULL UNIQUE,
  gender ENUM('male', 'female', 'other') NULL,
  date_of_birth DATE NULL,
  address VARCHAR(255) NULL,
  qualification VARCHAR(120) NULL,
  employment_date DATE NULL,
  photo VARCHAR(255) NULL,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  CONSTRAINT fk_teachers_user
    FOREIGN KEY (user_id) REFERENCES users(id)
    ON UPDATE CASCADE
    ON DELETE CASCADE
) ENGINE=InnoDB;

-- Adds the optional class teacher relationship after teachers has been created.
ALTER TABLE classes
  ADD CONSTRAINT fk_classes_class_teacher
  FOREIGN KEY (class_teacher_id) REFERENCES teachers(id)
  ON UPDATE CASCADE
  ON DELETE SET NULL;

-- Stores student profile information.
CREATE TABLE students (
  id INT AUTO_INCREMENT PRIMARY KEY,
  user_id INT NOT NULL UNIQUE,
  class_id INT NOT NULL,
  admission_number VARCHAR(50) NOT NULL UNIQUE,
  gender ENUM('male', 'female', 'other') NULL,
  date_of_birth DATE NULL,
  address VARCHAR(255) NULL,
  guardian_name VARCHAR(120) NULL,
  guardian_phone VARCHAR(30) NULL,
  admission_date DATE NULL,
  photo VARCHAR(255) NULL,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  CONSTRAINT fk_students_user
    FOREIGN KEY (user_id) REFERENCES users(id)
    ON UPDATE CASCADE
    ON DELETE CASCADE,
  CONSTRAINT fk_students_class
    FOREIGN KEY (class_id) REFERENCES classes(id)
    ON UPDATE CASCADE
    ON DELETE RESTRICT
) ENGINE=InnoDB;

-- Stores subjects taught in the school.
CREATE TABLE subjects (
  id INT AUTO_INCREMENT PRIMARY KEY,
  subject_name VARCHAR(100) NOT NULL UNIQUE,
  subject_code VARCHAR(30) NOT NULL UNIQUE,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB;

-- Connects classes to subjects.
CREATE TABLE class_subjects (
  id INT AUTO_INCREMENT PRIMARY KEY,
  class_id INT NOT NULL,
  subject_id INT NOT NULL,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  UNIQUE KEY unique_class_subject (class_id, subject_id),
  CONSTRAINT fk_class_subjects_class
    FOREIGN KEY (class_id) REFERENCES classes(id)
    ON UPDATE CASCADE
    ON DELETE CASCADE,
  CONSTRAINT fk_class_subjects_subject
    FOREIGN KEY (subject_id) REFERENCES subjects(id)
    ON UPDATE CASCADE
    ON DELETE CASCADE
) ENGINE=InnoDB;

-- Connects teachers to the subjects and classes they teach.
CREATE TABLE teacher_subjects (
  id INT AUTO_INCREMENT PRIMARY KEY,
  teacher_id INT NOT NULL,
  subject_id INT NOT NULL,
  class_id INT NOT NULL,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  UNIQUE KEY unique_teacher_assignment (teacher_id, subject_id, class_id),
  CONSTRAINT fk_teacher_subjects_teacher
    FOREIGN KEY (teacher_id) REFERENCES teachers(id)
    ON UPDATE CASCADE
    ON DELETE CASCADE,
  CONSTRAINT fk_teacher_subjects_subject
    FOREIGN KEY (subject_id) REFERENCES subjects(id)
    ON UPDATE CASCADE
    ON DELETE CASCADE,
  CONSTRAINT fk_teacher_subjects_class
    FOREIGN KEY (class_id) REFERENCES classes(id)
    ON UPDATE CASCADE
    ON DELETE CASCADE
) ENGINE=InnoDB;

-- Stores daily student attendance.
CREATE TABLE attendance (
  id INT AUTO_INCREMENT PRIMARY KEY,
  student_id INT NOT NULL,
  class_id INT NOT NULL,
  marked_by INT NOT NULL,
  attendance_date DATE NOT NULL,
  status ENUM('present', 'absent', 'late', 'excused') NOT NULL,
  remarks VARCHAR(255) NULL,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  UNIQUE KEY unique_student_attendance_date (student_id, attendance_date),
  CONSTRAINT fk_attendance_student
    FOREIGN KEY (student_id) REFERENCES students(id)
    ON UPDATE CASCADE
    ON DELETE CASCADE,
  CONSTRAINT fk_attendance_class
    FOREIGN KEY (class_id) REFERENCES classes(id)
    ON UPDATE CASCADE
    ON DELETE CASCADE,
  CONSTRAINT fk_attendance_marked_by
    FOREIGN KEY (marked_by) REFERENCES users(id)
    ON UPDATE CASCADE
    ON DELETE RESTRICT
) ENGINE=InnoDB;

-- Stores academic terms such as First Term, Second Term, and Third Term.
CREATE TABLE academic_terms (
  id INT AUTO_INCREMENT PRIMARY KEY,
  term_name VARCHAR(50) NOT NULL,
  academic_year VARCHAR(20) NOT NULL,
  start_date DATE NULL,
  end_date DATE NULL,
  is_active TINYINT(1) NOT NULL DEFAULT 0,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  UNIQUE KEY unique_term_year (term_name, academic_year)
) ENGINE=InnoDB;

-- Stores students' scores and grades.
CREATE TABLE results (
  id INT AUTO_INCREMENT PRIMARY KEY,
  student_id INT NOT NULL,
  class_id INT NOT NULL,
  subject_id INT NOT NULL,
  teacher_id INT NOT NULL,
  term_id INT NOT NULL,
  class_score DECIMAL(5,2) NOT NULL DEFAULT 0.00,
  exam_score DECIMAL(5,2) NOT NULL DEFAULT 0.00,
  total_score DECIMAL(5,2) GENERATED ALWAYS AS (class_score + exam_score) STORED,
  grade VARCHAR(5) NULL,
  remark VARCHAR(120) NULL,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  UNIQUE KEY unique_student_subject_term (student_id, subject_id, term_id),
  CONSTRAINT fk_results_student
    FOREIGN KEY (student_id) REFERENCES students(id)
    ON UPDATE CASCADE
    ON DELETE CASCADE,
  CONSTRAINT fk_results_class
    FOREIGN KEY (class_id) REFERENCES classes(id)
    ON UPDATE CASCADE
    ON DELETE CASCADE,
  CONSTRAINT fk_results_subject
    FOREIGN KEY (subject_id) REFERENCES subjects(id)
    ON UPDATE CASCADE
    ON DELETE CASCADE,
  CONSTRAINT fk_results_teacher
    FOREIGN KEY (teacher_id) REFERENCES teachers(id)
    ON UPDATE CASCADE
    ON DELETE RESTRICT,
  CONSTRAINT fk_results_term
    FOREIGN KEY (term_id) REFERENCES academic_terms(id)
    ON UPDATE CASCADE
    ON DELETE RESTRICT
) ENGINE=InnoDB;

-- Stores fee bills assigned to students.
CREATE TABLE fees (
  id INT AUTO_INCREMENT PRIMARY KEY,
  student_id INT NOT NULL,
  term_id INT NOT NULL,
  amount_due DECIMAL(10,2) NOT NULL,
  due_date DATE NULL,
  status ENUM('unpaid', 'partly_paid', 'paid') NOT NULL DEFAULT 'unpaid',
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  CONSTRAINT fk_fees_student
    FOREIGN KEY (student_id) REFERENCES students(id)
    ON UPDATE CASCADE
    ON DELETE CASCADE,
  CONSTRAINT fk_fees_term
    FOREIGN KEY (term_id) REFERENCES academic_terms(id)
    ON UPDATE CASCADE
    ON DELETE RESTRICT
) ENGINE=InnoDB;

-- Stores actual payments made by students or guardians.
CREATE TABLE payments (
  id INT AUTO_INCREMENT PRIMARY KEY,
  fee_id INT NOT NULL,
  student_id INT NOT NULL,
  amount_paid DECIMAL(10,2) NOT NULL,
  payment_method ENUM('cash', 'mobile_money', 'bank_transfer', 'card') NOT NULL DEFAULT 'cash',
  reference_number VARCHAR(100) NULL,
  payment_date DATE NOT NULL,
  received_by INT NOT NULL,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  CONSTRAINT fk_payments_fee
    FOREIGN KEY (fee_id) REFERENCES fees(id)
    ON UPDATE CASCADE
    ON DELETE CASCADE,
  CONSTRAINT fk_payments_student
    FOREIGN KEY (student_id) REFERENCES students(id)
    ON UPDATE CASCADE
    ON DELETE CASCADE,
  CONSTRAINT fk_payments_received_by
    FOREIGN KEY (received_by) REFERENCES users(id)
    ON UPDATE CASCADE
    ON DELETE RESTRICT
) ENGINE=InnoDB;

-- Stores messages shown to users.
CREATE TABLE notifications (
  id INT AUTO_INCREMENT PRIMARY KEY,
  user_id INT NOT NULL,
  title VARCHAR(120) NOT NULL,
  message TEXT NOT NULL,
  is_read TINYINT(1) NOT NULL DEFAULT 0,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  CONSTRAINT fk_notifications_user
    FOREIGN KEY (user_id) REFERENCES users(id)
    ON UPDATE CASCADE
    ON DELETE CASCADE
) ENGINE=InnoDB;

-- Stores secure tokens for forgot password.
CREATE TABLE password_resets (
  id INT AUTO_INCREMENT PRIMARY KEY,
  user_id INT NOT NULL,
  token VARCHAR(255) NOT NULL UNIQUE,
  expires_at DATETIME NOT NULL,
  used_at DATETIME NULL,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  CONSTRAINT fk_password_resets_user
    FOREIGN KEY (user_id) REFERENCES users(id)
    ON UPDATE CASCADE
    ON DELETE CASCADE
) ENGINE=InnoDB;

-- Stores important system actions for accountability.
CREATE TABLE audit_logs (
  id INT AUTO_INCREMENT PRIMARY KEY,
  user_id INT NULL,
  action VARCHAR(120) NOT NULL,
  table_name VARCHAR(80) NULL,
  record_id INT NULL,
  ip_address VARCHAR(45) NULL,
  user_agent VARCHAR(255) NULL,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  CONSTRAINT fk_audit_logs_user
    FOREIGN KEY (user_id) REFERENCES users(id)
    ON UPDATE CASCADE
    ON DELETE SET NULL
) ENGINE=InnoDB;

-- Default roles required by the system.
INSERT INTO roles (name) VALUES
  ('admin'),
  ('teacher'),
  ('student');

