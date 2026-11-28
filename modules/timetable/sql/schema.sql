-- ==============================================================================
-- Timetable Module Schema (`coaching_timetable`)
-- ==============================================================================

CREATE TABLE IF NOT EXISTS coaching_timetable (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,

    academic_session_id INT NOT NULL,
    course_id INT NOT NULL,
    batch_id INT NOT NULL,
    section_id INT DEFAULT NULL,
    subject_id INT NOT NULL,
    employee_id INT NOT NULL,

    day_of_week TINYINT NOT NULL
        COMMENT '1=Monday, 2=Tuesday, 3=Wednesday, 4=Thursday, 5=Friday, 6=Saturday, 7=Sunday',

    start_time TIME NOT NULL,
    end_time TIME NOT NULL,

    room_id INT DEFAULT NULL,

    class_type ENUM(
        'Theory',
        'Practical',
        'Revision',
        'Test',
        'Doubt Clearing'
    ) DEFAULT 'Theory',

    effective_from DATE NOT NULL,
    effective_to DATE DEFAULT NULL,

    status TINYINT NOT NULL DEFAULT 1
        COMMENT '1=Active, 0=Inactive',

    created_by INT DEFAULT NULL,
    updated_by INT DEFAULT NULL,

    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
        ON UPDATE CURRENT_TIMESTAMP,

    CHECK (day_of_week BETWEEN 1 AND 7),
    CHECK (start_time < end_time),
    CHECK (effective_to IS NULL OR effective_to >= effective_from),

    INDEX idx_batch_day (batch_id, day_of_week),
    INDEX idx_employee_day (employee_id, day_of_week)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
