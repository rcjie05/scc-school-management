-- Migration: Add subject_type to subjects table and class_type to section_schedules
-- Run this SQL on your database

-- 1. Add subject_type column to subjects table
ALTER TABLE subjects
    ADD COLUMN subject_type ENUM('major', 'minor') NOT NULL DEFAULT 'major'
    AFTER units;

-- 2. Add class_type column to section_schedules table
ALTER TABLE section_schedules
    ADD COLUMN class_type ENUM('LEC', 'LAB') NOT NULL DEFAULT 'LEC'
    AFTER section_subject_id;

-- Verify
-- SELECT subject_code, subject_name, subject_type FROM subjects LIMIT 10;
-- SELECT id, class_type, start_time, end_time FROM section_schedules LIMIT 10;
