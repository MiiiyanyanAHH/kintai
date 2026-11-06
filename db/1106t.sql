
CREATE TABLE attendance_details (
    detail_id INT AUTO_INCREMENT PRIMARY KEY,
    employee_id CHAR(6) NOT NULL,
    work_date DATE NOT NULL,

    start_time TIME NULL,
    end_time TIME NULL,
    work_type VARCHAR(30) NULL,

    original_start_timestamp DATETIME NULL,
    original_end_timestamp DATETIME NULL,

    operation_type VARCHAR(30) NOT NULL,
    admin_id CHAR(7) NOT NULL,
    changed_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,

    start_time_changed TINYINT(1) NOT NULL DEFAULT 0,
    end_time_changed TINYINT(1) NOT NULL DEFAULT 0,
    work_type_changed TINYINT(1) NOT NULL DEFAULT 0,

    is_deleted TINYINT(1) NOT NULL DEFAULT 0,

    FOREIGN KEY (employee_id)
        REFERENCES employees(employee_id)
);
