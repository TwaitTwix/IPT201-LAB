-- Schema update for departments and teacher-subject relationships
-- Run this to add new tables and update existing structure

-- Create departments table
CREATE TABLE IF NOT EXISTS `departments` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(100) NOT NULL,
  `description` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `name` (`name`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Create teacher_subjects table for teacher-subject assignments
CREATE TABLE IF NOT EXISTS `teacher_subjects` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `teacher_id` int(11) NOT NULL,
  `subject_id` int(11) NOT NULL,
  `assigned_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `teacher_subject` (`teacher_id`, `subject_id`),
  KEY `teacher_id` (`teacher_id`),
  KEY `subject_id` (`subject_id`),
  CONSTRAINT `fk_teacher_subjects_teacher` FOREIGN KEY (`teacher_id`) REFERENCES `teachers` (`id`) ON DELETE CASCADE,
  CONSTRAINT `fk_teacher_subjects_subject` FOREIGN KEY (`subject_id`) REFERENCES `subjects` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Update teachers table to reference departments table
ALTER TABLE `teachers` 
ADD COLUMN `department_id` int(11) DEFAULT NULL AFTER `user_id`,
ADD KEY `department_id` (`department_id`),
ADD CONSTRAINT `fk_teachers_department` FOREIGN KEY (`department_id`) REFERENCES `departments` (`id`) ON DELETE SET NULL;

-- Migrate existing department names to departments table
INSERT IGNORE INTO `departments` (`name`, `description`) 
SELECT DISTINCT `department`, `Department for ' || department` 
FROM `teachers` 
WHERE `department` IS NOT NULL AND `department` != '';

-- Update teachers to use department_id
UPDATE `teachers` t 
JOIN `departments` d ON t.department = d.name 
SET t.department_id = d.id 
WHERE t.department = d.name;

-- (Optional) After migration, you can drop the old department column
-- ALTER TABLE `teachers` DROP COLUMN `department`;
