-- ============================================================================
-- GYM MANAGEMENT SYSTEM - COMPLETE MYSQL IMPLEMENTATION
-- ============================================================================
-- Database: gym_management
-- DBMS: MySQL 8.0+
-- Components: Tables, Triggers, Stored Procedures, Views
-- Last Updated: 2024-03-14
-- ============================================================================

USE gym_management;

-- ============================================================================
-- PART 1: SIMPLIFIED TABLE STRUCTURE FOR DEVELOPMENT
-- ============================================================================
-- Note: Enhanced core tables with all required constraints

-- Drop existing tables if migrating (CASCADE option)
SET FOREIGN_KEY_CHECKS = 0;

-- ============================================================================
-- TABLE 1: USERS (Core Authentication)
-- ============================================================================

CREATE TABLE IF NOT EXISTS users (
    user_id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    email VARCHAR(255) NOT NULL UNIQUE,
    phone_number VARCHAR(20) NOT NULL UNIQUE,
    password_hash VARCHAR(255) NOT NULL,
    first_name VARCHAR(100) NOT NULL,
    last_name VARCHAR(100) NOT NULL,
    user_type ENUM('member', 'trainer', 'admin', 'manager') NOT NULL DEFAULT 'member',
    account_status ENUM('active', 'inactive', 'suspended', 'banned') NOT NULL DEFAULT 'active',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    
    INDEX idx_email (email),
    INDEX idx_user_type (user_type),
    INDEX idx_account_status (account_status)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
COMMENT='Core user authentication and profile table';

-- ============================================================================
-- TABLE 2: MEMBERS
-- ============================================================================

CREATE TABLE IF NOT EXISTS members (
    member_id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    user_id BIGINT UNSIGNED NOT NULL UNIQUE,
    date_of_birth DATE NOT NULL,
    gender ENUM('male', 'female', 'other') NOT NULL,
    address VARCHAR(500),
    city VARCHAR(100),
    state VARCHAR(100),
    postal_code VARCHAR(20),
    country VARCHAR(100),
    emergency_contact_name VARCHAR(200),
    emergency_contact_phone VARCHAR(20),
    medical_conditions TEXT,
    membership_start_date DATE NOT NULL,
    total_classes_attended INT DEFAULT 0 CHECK (total_classes_attended >= 0),
    trainer_id BIGINT UNSIGNED,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    
    CONSTRAINT fk_members_users FOREIGN KEY (user_id) 
        REFERENCES users(user_id) ON DELETE CASCADE ON UPDATE CASCADE,
    CONSTRAINT fk_members_trainer FOREIGN KEY (trainer_id) 
        REFERENCES trainers(trainer_id) ON DELETE SET NULL ON UPDATE CASCADE,
    
    INDEX idx_user_id (user_id),
    INDEX idx_trainer_id (trainer_id),
    INDEX idx_city (city),
    
    CHECK (date_of_birth <= CURDATE()),
    CHECK (membership_start_date <= CURDATE())
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
COMMENT='Member demographic and profile information';

-- ============================================================================
-- TABLE 3: MEMBERSHIP_PLANS
-- ============================================================================

CREATE TABLE IF NOT EXISTS membership_plans (
    plan_id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    plan_name VARCHAR(100) NOT NULL UNIQUE,
    description TEXT,
    price_per_month DECIMAL(10, 2) NOT NULL CHECK (price_per_month > 0),
    price_per_year DECIMAL(10, 2),
    duration_months INT NOT NULL CHECK (duration_months > 0),
    max_classes_per_week INT,
    access_to_gym BOOLEAN DEFAULT TRUE,
    personal_training_sessions INT DEFAULT 0,
    includes_nutrition_plan BOOLEAN DEFAULT FALSE,
    cancellation_notice_days INT DEFAULT 30,
    is_active BOOLEAN DEFAULT TRUE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    
    INDEX idx_is_active (is_active),
    INDEX idx_plan_name (plan_name)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
COMMENT='Membership subscription plans/tiers';

-- ============================================================================
-- TABLE 4: MEMBERSHIPS
-- ============================================================================

CREATE TABLE IF NOT EXISTS memberships (
    membership_id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    member_id BIGINT UNSIGNED NOT NULL,
    plan_id BIGINT UNSIGNED NOT NULL,
    start_date DATE NOT NULL,
    end_date DATE NOT NULL,
    renewal_date DATE,
    status ENUM('active', 'expired', 'cancelled', 'paused', 'pending') NOT NULL DEFAULT 'active',
    cancellation_reason VARCHAR(500),
    auto_renewal BOOLEAN DEFAULT TRUE,
    total_price DECIMAL(10, 2) NOT NULL CHECK (total_price > 0),
    amount_paid DECIMAL(10, 2) DEFAULT 0 CHECK (amount_paid >= 0),
    classes_used_this_month INT DEFAULT 0 CHECK (classes_used_this_month >= 0),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    
    CONSTRAINT fk_memberships_member FOREIGN KEY (member_id) 
        REFERENCES members(member_id) ON DELETE CASCADE ON UPDATE CASCADE,
    CONSTRAINT fk_memberships_plan FOREIGN KEY (plan_id) 
        REFERENCES membership_plans(plan_id) ON DELETE RESTRICT ON UPDATE CASCADE,
    
    INDEX idx_member_id (member_id),
    INDEX idx_plan_id (plan_id),
    INDEX idx_status (status),
    INDEX idx_end_date (end_date),
    INDEX idx_renewal_date (renewal_date),
    
    CHECK (start_date < end_date)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
COMMENT='Active membership subscriptions for members';

-- ============================================================================
-- TABLE 5: MEMBERSHIP_UPGRADES
-- ============================================================================

CREATE TABLE IF NOT EXISTS membership_upgrades (
    upgrade_id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    membership_id BIGINT UNSIGNED NOT NULL,
    old_plan_id BIGINT UNSIGNED NOT NULL,
    new_plan_id BIGINT UNSIGNED NOT NULL,
    upgrade_date TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    old_price_monthly DECIMAL(10, 2) NOT NULL,
    new_price_monthly DECIMAL(10, 2) NOT NULL,
    price_difference DECIMAL(10, 2) NOT NULL,
    adjustment_amount DECIMAL(10, 2),
    upgrade_type ENUM('upgrade', 'downgrade', 'lateral') NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    
    CONSTRAINT fk_upgrades_membership FOREIGN KEY (membership_id) 
        REFERENCES memberships(membership_id) ON DELETE CASCADE ON UPDATE CASCADE,
    CONSTRAINT fk_upgrades_old_plan FOREIGN KEY (old_plan_id) 
        REFERENCES membership_plans(plan_id) ON DELETE RESTRICT ON UPDATE CASCADE,
    CONSTRAINT fk_upgrades_new_plan FOREIGN KEY (new_plan_id) 
        REFERENCES membership_plans(plan_id) ON DELETE RESTRICT ON UPDATE CASCADE,
    
    INDEX idx_membership_id (membership_id),
    INDEX idx_upgrade_date (upgrade_date),
    
    CHECK (old_plan_id != new_plan_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
COMMENT='Historical record of membership plan changes';

-- ============================================================================
-- TABLE 6: TRAINERS
-- ============================================================================

CREATE TABLE IF NOT EXISTS trainers (
    trainer_id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    user_id BIGINT UNSIGNED NOT NULL UNIQUE,
    specialization VARCHAR(200) NOT NULL,
    years_of_experience INT NOT NULL CHECK (years_of_experience >= 0),
    bio TEXT,
    hourly_rate DECIMAL(10, 2) NOT NULL CHECK (hourly_rate > 0),
    availability_status ENUM('available', 'unavailable', 'on_leave') NOT NULL DEFAULT 'available',
    total_clients INT DEFAULT 0 CHECK (total_clients >= 0),
    max_clients INT DEFAULT 20 CHECK (max_clients > 0),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    
    CONSTRAINT fk_trainers_users FOREIGN KEY (user_id) 
        REFERENCES users(user_id) ON DELETE CASCADE ON UPDATE CASCADE,
    
    INDEX idx_user_id (user_id),
    INDEX idx_specialization (specialization),
    INDEX idx_availability_status (availability_status)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
COMMENT='Trainer professional profiles and qualifications';

-- ============================================================================
-- TABLE 7: CERTIFICATIONS (Trainer Certifications)
-- ============================================================================

CREATE TABLE IF NOT EXISTS certifications (
    certification_id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    trainer_id BIGINT UNSIGNED NOT NULL,
    certification_name VARCHAR(200) NOT NULL,
    issuing_organization VARCHAR(200) NOT NULL,
    issue_date DATE NOT NULL,
    expiration_date DATE,
    certification_number VARCHAR(100) UNIQUE,
    is_active BOOLEAN DEFAULT TRUE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    
    CONSTRAINT fk_certifications_trainer FOREIGN KEY (trainer_id) 
        REFERENCES trainers(trainer_id) ON DELETE CASCADE ON UPDATE CASCADE,
    
    INDEX idx_trainer_id (trainer_id),
    INDEX idx_certification_name (certification_name),
    INDEX idx_expiration_date (expiration_date),
    INDEX idx_is_active (is_active),
    
    CHECK (issue_date <= CURDATE() AND (expiration_date IS NULL OR expiration_date >= issue_date))
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
COMMENT='Trainer certifications and professional qualifications';

-- ============================================================================
-- TABLE 8: GYM_AREAS
-- ============================================================================

CREATE TABLE IF NOT EXISTS gym_areas (
    area_id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    area_name VARCHAR(100) NOT NULL UNIQUE,
    description TEXT,
    capacity INT NOT NULL CHECK (capacity > 0),
    equipment_count INT DEFAULT 0,
    is_active BOOLEAN DEFAULT TRUE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    
    INDEX idx_area_name (area_name),
    INDEX idx_is_active (is_active)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
COMMENT='Physical gym areas/zones';

-- ============================================================================
-- TABLE 9: FITNESS_CLASSES
-- ============================================================================

CREATE TABLE IF NOT EXISTS fitness_classes (
    class_id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    trainer_id BIGINT UNSIGNED NOT NULL,
    area_id BIGINT UNSIGNED NOT NULL,
    class_name VARCHAR(150) NOT NULL,
    description TEXT,
    category ENUM('yoga', 'pilates', 'cardio', 'strength', 'boxing', 'dance', 'aqua', 'other') NOT NULL,
    difficulty_level ENUM('beginner', 'intermediate', 'advanced', 'mixed') NOT NULL DEFAULT 'beginner',
    max_capacity INT NOT NULL CHECK (max_capacity > 0),
    current_enrollment INT DEFAULT 0 CHECK (current_enrollment >= 0),
    duration_minutes INT NOT NULL CHECK (duration_minutes > 0),
    class_type ENUM('recurring', 'one_time', 'special') NOT NULL DEFAULT 'recurring',
    status ENUM('active', 'suspended', 'cancelled', 'completed') NOT NULL DEFAULT 'active',
    price_per_session DECIMAL(10, 2),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    
    CONSTRAINT fk_class_trainer FOREIGN KEY (trainer_id) 
        REFERENCES trainers(trainer_id) ON DELETE RESTRICT ON UPDATE CASCADE,
    CONSTRAINT fk_class_area FOREIGN KEY (area_id) 
        REFERENCES gym_areas(area_id) ON DELETE RESTRICT ON UPDATE CASCADE,
    
    INDEX idx_trainer_id (trainer_id),
    INDEX idx_area_id (area_id),
    INDEX idx_category (category),
    INDEX idx_status (status),
    
    CHECK (current_enrollment <= max_capacity)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
COMMENT='Fitness classes offered by the gym';

-- ============================================================================
-- TABLE 10: CLASS_SCHEDULES
-- ============================================================================

CREATE TABLE IF NOT EXISTS class_schedules (
    schedule_id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    class_id BIGINT UNSIGNED NOT NULL,
    day_of_week ENUM('monday', 'tuesday', 'wednesday', 'thursday', 'friday', 'saturday', 'sunday'),
    start_time TIME NOT NULL,
    end_time TIME NOT NULL,
    scheduled_date DATE,
    is_cancelled BOOLEAN DEFAULT FALSE,
    cancellation_reason VARCHAR(500),
    current_enrollment INT DEFAULT 0 CHECK (current_enrollment >= 0),
    waiting_list_count INT DEFAULT 0 CHECK (waiting_list_count >= 0),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    
    CONSTRAINT fk_schedule_class FOREIGN KEY (class_id) 
        REFERENCES fitness_classes(class_id) ON DELETE CASCADE ON UPDATE CASCADE,
    
    INDEX idx_class_id (class_id),
    INDEX idx_day_of_week (day_of_week),
    INDEX idx_scheduled_date (scheduled_date),
    
    CHECK (start_time < end_time)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
COMMENT='Scheduled instances of fitness classes';

-- ============================================================================
-- TABLE 11: CLASS_ENROLLMENTS (M:N Junction Table)
-- ============================================================================

CREATE TABLE IF NOT EXISTS class_enrollments (
    enrollment_id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    member_id BIGINT UNSIGNED NOT NULL,
    schedule_id BIGINT UNSIGNED NOT NULL,
    enrollment_date TIMESTAMP NOT NULL,
    enrollment_status ENUM('enrolled', 'waiting_list', 'cancelled', 'completed') NOT NULL DEFAULT 'enrolled',
    enrolled_by_trainer BOOLEAN DEFAULT FALSE,
    cancellation_reason VARCHAR(500),
    cancelled_date TIMESTAMP,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    
    CONSTRAINT fk_enrollment_member FOREIGN KEY (member_id) 
        REFERENCES members(member_id) ON DELETE CASCADE ON UPDATE CASCADE,
    CONSTRAINT fk_enrollment_schedule FOREIGN KEY (schedule_id) 
        REFERENCES class_schedules(schedule_id) ON DELETE CASCADE ON UPDATE CASCADE,
    
    UNIQUE KEY uk_member_schedule (member_id, schedule_id),
    
    INDEX idx_member_id (member_id),
    INDEX idx_schedule_id (schedule_id),
    INDEX idx_enrollment_status (enrollment_status),
    
    CHECK ((cancellation_reason IS NULL AND cancelled_date IS NULL) OR 
           (cancellation_reason IS NOT NULL AND cancelled_date IS NOT NULL))
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
COMMENT='Member class enrollments (M:N relationship)';

-- ============================================================================
-- TABLE 12: ATTENDANCE
-- ============================================================================

CREATE TABLE IF NOT EXISTS attendance (
    attendance_id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    member_id BIGINT UNSIGNED NOT NULL,
    schedule_id BIGINT UNSIGNED NOT NULL,
    check_in_time TIMESTAMP NOT NULL,
    check_out_time TIMESTAMP,
    attendance_status ENUM('present', 'absent', 'late', 'cancelled') NOT NULL DEFAULT 'present',
    duration_minutes INT CHECK (duration_minutes > 0),
    marked_by_trainer BOOLEAN DEFAULT FALSE,
    notes TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    
    CONSTRAINT fk_attendance_member FOREIGN KEY (member_id) 
        REFERENCES members(member_id) ON DELETE CASCADE ON UPDATE CASCADE,
    CONSTRAINT fk_attendance_schedule FOREIGN KEY (schedule_id) 
        REFERENCES class_schedules(schedule_id) ON DELETE CASCADE ON UPDATE CASCADE,
    
    UNIQUE KEY uk_member_schedule_attendance (member_id, schedule_id),
    
    INDEX idx_member_id (member_id),
    INDEX idx_check_in_time (check_in_time),
    INDEX idx_attendance_status (attendance_status),
    
    CHECK (check_out_time IS NULL OR check_in_time < check_out_time)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
COMMENT='Member attendance records for class sessions';

-- ============================================================================
-- TABLE 13: EQUIPMENT
-- ============================================================================

CREATE TABLE IF NOT EXISTS equipment (
    equipment_id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    area_id BIGINT UNSIGNED NOT NULL,
    equipment_name VARCHAR(200) NOT NULL,
    equipment_type VARCHAR(100) NOT NULL,
    description TEXT,
    serial_number VARCHAR(100) UNIQUE,
    purchase_date DATE NOT NULL CHECK (purchase_date <= CURDATE()),
    purchase_cost DECIMAL(10, 2),
    warranty_expiry_date DATE,
    condition_status ENUM('excellent', 'good', 'fair', 'poor', 'damaged') NOT NULL DEFAULT 'good',
    operational_status ENUM('operational', 'maintenance', 'repair', 'retired', 'damaged') NOT NULL DEFAULT 'operational',
    last_maintenance_date DATE,
    next_maintenance_date DATE,
    maintenance_interval_days INT DEFAULT 30 CHECK (maintenance_interval_days > 0),
    max_weight_capacity INT,
    usage_count INT DEFAULT 0 CHECK (usage_count >= 0),
    responsible_trainer_id BIGINT UNSIGNED,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    
    CONSTRAINT fk_equipment_area FOREIGN KEY (area_id) 
        REFERENCES gym_areas(area_id) ON DELETE RESTRICT ON UPDATE CASCADE,
    CONSTRAINT fk_equipment_trainer FOREIGN KEY (responsible_trainer_id) 
        REFERENCES trainers(trainer_id) ON DELETE SET NULL ON UPDATE CASCADE,
    
    INDEX idx_area_id (area_id),
    INDEX idx_equipment_type (equipment_type),
    INDEX idx_operational_status (operational_status),
    INDEX idx_next_maintenance_date (next_maintenance_date)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
COMMENT='Gym equipment inventory and maintenance tracking';

-- ============================================================================
-- TABLE 14: EQUIPMENT_USAGE
-- ============================================================================

CREATE TABLE IF NOT EXISTS equipment_usage (
    usage_id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    equipment_id BIGINT UNSIGNED NOT NULL,
    member_id BIGINT UNSIGNED,
    usage_type ENUM('training', 'maintenance', 'repair', 'inspection', 'cleaning') NOT NULL,
    start_time TIMESTAMP NOT NULL,
    end_time TIMESTAMP,
    duration_minutes INT CHECK (duration_minutes > 0),
    notes TEXT,
    usage_status ENUM('in_progress', 'completed', 'cancelled') NOT NULL DEFAULT 'in_progress',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    
    CONSTRAINT fk_usage_equipment FOREIGN KEY (equipment_id) 
        REFERENCES equipment(equipment_id) ON DELETE CASCADE ON UPDATE CASCADE,
    CONSTRAINT fk_usage_member FOREIGN KEY (member_id) 
        REFERENCES members(member_id) ON DELETE SET NULL ON UPDATE CASCADE,
    
    INDEX idx_equipment_id (equipment_id),
    INDEX idx_member_id (member_id),
    INDEX idx_start_time (start_time),
    INDEX idx_usage_type (usage_type),
    
    CHECK (end_time IS NULL OR start_time < end_time)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
COMMENT='Equipment usage and maintenance logs';

-- ============================================================================
-- TABLE 15: MAINTENANCE_LOGS
-- ============================================================================

CREATE TABLE IF NOT EXISTS maintenance_logs (
    maintenance_id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    equipment_id BIGINT UNSIGNED NOT NULL,
    trainer_id BIGINT UNSIGNED,
    maintenance_type ENUM('preventive', 'corrective', 'emergency', 'inspection') NOT NULL,
    maintenance_description TEXT NOT NULL,
    parts_replaced TEXT,
    maintenance_cost DECIMAL(10, 2) CHECK (maintenance_cost IS NULL OR maintenance_cost > 0),
    maintenance_date DATE NOT NULL CHECK (maintenance_date <= CURDATE()),
    next_scheduled_date DATE,
    completion_status ENUM('pending', 'in_progress', 'completed', 'cancelled') NOT NULL DEFAULT 'pending',
    notes TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    
    CONSTRAINT fk_maintenance_equipment FOREIGN KEY (equipment_id) 
        REFERENCES equipment(equipment_id) ON DELETE CASCADE ON UPDATE CASCADE,
    CONSTRAINT fk_maintenance_trainer FOREIGN KEY (trainer_id) 
        REFERENCES trainers(trainer_id) ON DELETE SET NULL ON UPDATE CASCADE,
    
    INDEX idx_equipment_id (equipment_id),
    INDEX idx_trainer_id (trainer_id),
    INDEX idx_maintenance_date (maintenance_date),
    INDEX idx_maintenance_type (maintenance_type)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
COMMENT='Equipment maintenance history and logs';

-- ============================================================================
-- TABLE 16: PAYMENTS
-- ============================================================================

CREATE TABLE IF NOT EXISTS payments (
    payment_id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    member_id BIGINT UNSIGNED NOT NULL,
    membership_id BIGINT UNSIGNED,
    payment_type ENUM('membership_fee', 'renewal_fee', 'class_fee', 'training_session', 'other') NOT NULL,
    amount DECIMAL(10, 2) NOT NULL CHECK (amount > 0),
    payment_method ENUM('credit_card', 'debit_card', 'bank_transfer', 'cash', 'digital_wallet') NOT NULL,
    payment_status ENUM('pending', 'completed', 'failed', 'refunded', 'cancelled') NOT NULL DEFAULT 'completed',
    transaction_id VARCHAR(255) UNIQUE,
    reference_number VARCHAR(100) UNIQUE,
    payment_date TIMESTAMP NOT NULL,
    due_date DATE,
    refund_date TIMESTAMP,
    refund_amount DECIMAL(10, 2),
    refund_reason VARCHAR(500),
    notes TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    
    CONSTRAINT fk_payment_member FOREIGN KEY (member_id) 
        REFERENCES members(member_id) ON DELETE CASCADE ON UPDATE CASCADE,
    CONSTRAINT fk_payment_membership FOREIGN KEY (membership_id) 
        REFERENCES memberships(membership_id) ON DELETE SET NULL ON UPDATE CASCADE,
    
    INDEX idx_member_id (member_id),
    INDEX idx_membership_id (membership_id),
    INDEX idx_payment_date (payment_date),
    INDEX idx_payment_status (payment_status),
    
    CHECK ((refund_amount IS NULL AND refund_date IS NULL) OR 
           (refund_amount IS NOT NULL AND refund_date IS NOT NULL AND refund_amount > 0))
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
COMMENT='Payment transactions and financial records';

-- ============================================================================
-- TABLE 17: CLASS_EQUIPMENT_ACCESS (M:N Junction Table)
-- ============================================================================

CREATE TABLE IF NOT EXISTS class_equipment_access (
    access_id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    class_id BIGINT UNSIGNED NOT NULL,
    equipment_id BIGINT UNSIGNED NOT NULL,
    equipment_required BOOLEAN DEFAULT FALSE,
    quantity_needed INT DEFAULT 1 CHECK (quantity_needed > 0),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    
    CONSTRAINT fk_access_class FOREIGN KEY (class_id) 
        REFERENCES fitness_classes(class_id) ON DELETE CASCADE ON UPDATE CASCADE,
    CONSTRAINT fk_access_equipment FOREIGN KEY (equipment_id) 
        REFERENCES equipment(equipment_id) ON DELETE CASCADE ON UPDATE CASCADE,
    
    UNIQUE KEY uk_class_equipment (class_id, equipment_id),
    
    INDEX idx_equipment_id (equipment_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
COMMENT='Junction table: Classes and Equipment (M:N relationship)';

SET FOREIGN_KEY_CHECKS = 1;

-- ============================================================================
-- PART 2: BUSINESS LOGIC TRIGGERS
-- ============================================================================

-- ============================================================================
-- TRIGGER 1: Prevent Enrollment if Class is Full
-- ============================================================================

DELIMITER $$

CREATE TRIGGER trg_prevent_full_class_enrollment
BEFORE INSERT ON class_enrollments
FOR EACH ROW
BEGIN
    DECLARE class_max_capacity INT;
    DECLARE current_enrolled INT;
    DECLARE error_message VARCHAR(255);
    
    -- Get class max capacity
    SELECT fc.max_capacity INTO class_max_capacity
    FROM fitness_classes fc
    WHERE fc.class_id = (SELECT class_id FROM class_schedules WHERE schedule_id = NEW.schedule_id);
    
    -- Get current enrollment
    SELECT COUNT(*) INTO current_enrolled
    FROM class_enrollments ce
    WHERE ce.schedule_id = NEW.schedule_id 
    AND ce.enrollment_status IN ('enrolled', 'waiting_list');
    
    -- Check if full (but allow wait list)
    IF current_enrolled >= class_max_capacity AND NEW.enrollment_status = 'enrolled' THEN
        SET NEW.enrollment_status = 'waiting_list';
    END IF;
END$$

DELIMITER ;

-- ============================================================================
-- TRIGGER 2: Update Membership Status After Payment
-- ============================================================================

DELIMITER $$

CREATE TRIGGER trg_update_membership_after_payment
AFTER INSERT ON payments
FOR EACH ROW
BEGIN
    DECLARE full_amount DECIMAL(10, 2);
    
    IF NEW.payment_status = 'completed' AND NEW.membership_id IS NOT NULL THEN
        -- Update amount paid in membership
        UPDATE memberships
        SET amount_paid = amount_paid + NEW.amount
        WHERE membership_id = NEW.membership_id;
        
        -- Check if fully paid, then mark as active
        SELECT total_price INTO full_amount
        FROM memberships
        WHERE membership_id = NEW.membership_id;
        
        UPDATE memberships
        SET status = 'active',
            renewal_date = DATE_ADD(end_date, INTERVAL 1 DAY)
        WHERE membership_id = NEW.membership_id
        AND amount_paid >= full_amount;
    END IF;
END$$

DELIMITER ;

-- ============================================================================
-- TRIGGER 3: Update Current Enrollment in Class Schedule
-- ============================================================================

DELIMITER $$

CREATE TRIGGER trg_update_enrollment_on_insert
AFTER INSERT ON class_enrollments
FOR EACH ROW
BEGIN
    IF NEW.enrollment_status = 'enrolled' THEN
        UPDATE class_schedules
        SET current_enrollment = current_enrollment + 1
        WHERE schedule_id = NEW.schedule_id;
    END IF;
END$$

DELIMITER ;

-- ============================================================================
-- TRIGGER 4: Update Current Enrollment on Cancellation
-- ============================================================================

DELIMITER $$

CREATE TRIGGER trg_update_enrollment_on_cancel
AFTER UPDATE ON class_enrollments
FOR EACH ROW
BEGIN
    IF OLD.enrollment_status = 'enrolled' AND NEW.enrollment_status = 'cancelled' THEN
        UPDATE class_schedules
        SET current_enrollment = current_enrollment - 1
        WHERE schedule_id = NEW.schedule_id
        AND current_enrollment > 0;
    END IF;
END$$

DELIMITER ;

-- ============================================================================
-- TRIGGER 5: Increment Equipment Usage Count
-- ============================================================================

DELIMITER $$

CREATE TRIGGER trg_increment_equipment_usage
AFTER UPDATE ON equipment_usage
FOR EACH ROW
BEGIN
    IF OLD.usage_status != 'completed' AND NEW.usage_status = 'completed' THEN
        UPDATE equipment
        SET usage_count = usage_count + 1
        WHERE equipment_id = NEW.equipment_id;
    END IF;
END$$

DELIMITER ;

-- ============================================================================
-- TRIGGER 6: Update Total Classes Attended
-- ============================================================================

DELIMITER $$

CREATE TRIGGER trg_update_classes_attended
AFTER INSERT ON attendance
FOR EACH ROW
BEGIN
    IF NEW.attendance_status = 'present' THEN
        UPDATE members
        SET total_classes_attended = total_classes_attended + 1
        WHERE member_id = NEW.member_id;
    END IF;
END$$

DELIMITER ;

-- ============================================================================
-- PART 3: STORED PROCEDURES
-- ============================================================================

-- ============================================================================
-- PROCEDURE 1: Register New Member
-- ============================================================================

DELIMITER $$

CREATE PROCEDURE sp_register_new_member(
    IN p_email VARCHAR(255),
    IN p_phone VARCHAR(20),
    IN p_password VARCHAR(255),
    IN p_first_name VARCHAR(100),
    IN p_last_name VARCHAR(100),
    IN p_date_of_birth DATE,
    IN p_gender VARCHAR(20),
    IN p_plan_id BIGINT,
    OUT p_member_id BIGINT
)
BEGIN
    DECLARE p_user_id BIGINT;
    DECLARE p_membership_id BIGINT;
    DECLARE plan_price DECIMAL(10, 2);
    DECLARE plan_duration INT;
    DECLARE EXIT HANDLER FOR SQLEXCEPTION
    BEGIN
        ROLLBACK;
        RESIGNAL;
    END;
    
    START TRANSACTION;
    
    -- Create user account
    INSERT INTO users (email, phone_number, password_hash, first_name, last_name, user_type, account_status)
    VALUES (p_email, p_phone, p_password, p_first_name, p_last_name, 'member', 'active');
    
    SET p_user_id = LAST_INSERT_ID();
    
    -- Create member profile
    INSERT INTO members (user_id, date_of_birth, gender, membership_start_date)
    VALUES (p_user_id, p_date_of_birth, p_gender, CURDATE());
    
    SET p_member_id = LAST_INSERT_ID();
    
    -- Get plan details
    SELECT price_per_month, duration_months INTO plan_price, plan_duration
    FROM membership_plans
    WHERE plan_id = p_plan_id;
    
    -- Create membership
    INSERT INTO memberships (member_id, plan_id, start_date, end_date, total_price, status)
    VALUES (p_member_id, p_plan_id, CURDATE(), DATE_ADD(CURDATE(), INTERVAL plan_duration MONTH), plan_price, 'pending');
    
    SET p_membership_id = LAST_INSERT_ID();
    
    COMMIT;
END$$

DELIMITER ;

-- ============================================================================
-- PROCEDURE 2: Process Membership Payment
-- ============================================================================

DELIMITER $$

CREATE PROCEDURE sp_process_membership_payment(
    IN p_member_id BIGINT,
    IN p_membership_id BIGINT,
    IN p_amount DECIMAL(10, 2),
    IN p_payment_method VARCHAR(50),
    IN p_transaction_id VARCHAR(255),
    OUT p_payment_id BIGINT,
    OUT p_success BOOLEAN
)
BEGIN
    DECLARE membership_status VARCHAR(20);
    DECLARE EXIT HANDLER FOR SQLEXCEPTION
    BEGIN
        SET p_success = FALSE;
        ROLLBACK;
        RESIGNAL;
    END;
    
    START TRANSACTION;
    
    -- Check membership exists
    SELECT status INTO membership_status
    FROM memberships
    WHERE membership_id = p_membership_id AND member_id = p_member_id;
    
    IF membership_status IS NULL THEN
        SET p_success = FALSE;
        ROLLBACK;
    ELSE
        -- Insert payment record
        INSERT INTO payments 
        (member_id, membership_id, payment_type, amount, payment_method, 
         payment_status, transaction_id, payment_date)
        VALUES (p_member_id, p_membership_id, 'membership_fee', p_amount, 
                p_payment_method, 'completed', p_transaction_id, NOW());
        
        SET p_payment_id = LAST_INSERT_ID();
        SET p_success = TRUE;
        
        COMMIT;
    END IF;
END$$

DELIMITER ;

-- ============================================================================
-- PROCEDURE 3: Enroll Member in Class
-- ============================================================================

DELIMITER $$

CREATE PROCEDURE sp_enroll_member_in_class(
    IN p_member_id BIGINT,
    IN p_schedule_id BIGINT,
    OUT p_enrollment_id BIGINT,
    OUT p_status VARCHAR(50)
)
BEGIN
    DECLARE class_max_capacity INT;
    DECLARE current_enrollment INT;
    DECLARE has_active_membership BOOLEAN;
    DECLARE EXIT HANDLER FOR SQLEXCEPTION
    BEGIN
        SET p_status = 'ERROR';
        ROLLBACK;
        RESIGNAL;
    END;
    
    START TRANSACTION;
    
    -- Check active membership
    SELECT COUNT(*) > 0 INTO has_active_membership
    FROM memberships
    WHERE member_id = p_member_id AND status = 'active' AND end_date >= CURDATE();
    
    IF NOT has_active_membership THEN
        SET p_status = 'NO_ACTIVE_MEMBERSHIP';
        ROLLBACK;
    ELSE
        -- Get class capacity
        SELECT fc.max_capacity INTO class_max_capacity
        FROM fitness_classes fc
        WHERE fc.class_id = (SELECT class_id FROM class_schedules WHERE schedule_id = p_schedule_id);
        
        -- Get current enrollment
        SELECT current_enrollment INTO current_enrollment
        FROM class_schedules
        WHERE schedule_id = p_schedule_id;
        
        -- Determine enrollment status
        IF current_enrollment < class_max_capacity THEN
            INSERT INTO class_enrollments 
            (member_id, schedule_id, enrollment_date, enrollment_status)
            VALUES (p_member_id, p_schedule_id, NOW(), 'enrolled');
            
            SET p_status = 'ENROLLED';
        ELSE
            INSERT INTO class_enrollments 
            (member_id, schedule_id, enrollment_date, enrollment_status)
            VALUES (p_member_id, p_schedule_id, NOW(), 'waiting_list');
            
            SET p_status = 'WAITING_LIST';
        END IF;
        
        SET p_enrollment_id = LAST_INSERT_ID();
        COMMIT;
    END IF;
END$$

DELIMITER ;

-- ============================================================================
-- PROCEDURE 4: Upgrade Membership Plan
-- ============================================================================

DELIMITER $$

CREATE PROCEDURE sp_upgrade_membership_plan(
    IN p_membership_id BIGINT,
    IN p_new_plan_id BIGINT,
    OUT p_upgrade_id BIGINT,
    OUT p_new_price DECIMAL(10, 2)
)
BEGIN
    DECLARE current_plan_id BIGINT;
    DECLARE old_price DECIMAL(10, 2);
    DECLARE new_price DECIMAL(10, 2);
    DECLARE price_diff DECIMAL(10, 2);
    DECLARE upgrade_type VARCHAR(20);
    DECLARE EXIT HANDLER FOR SQLEXCEPTION
    BEGIN
        ROLLBACK;
        RESIGNAL;
    END;
    
    START TRANSACTION;
    
    -- Get current membership details
    SELECT plan_id INTO current_plan_id
    FROM memberships
    WHERE membership_id = p_membership_id;
    
    -- Get plan prices
    SELECT price_per_month INTO old_price
    FROM membership_plans
    WHERE plan_id = current_plan_id;
    
    SELECT price_per_month INTO new_price
    FROM membership_plans
    WHERE plan_id = p_new_plan_id;
    
    -- Calculate price difference
    SET price_diff = new_price - old_price;
    
    -- Determine upgrade type
    IF price_diff > 0 THEN
        SET upgrade_type = 'upgrade';
    ELSEIF price_diff < 0 THEN
        SET upgrade_type = 'downgrade';
    ELSE
        SET upgrade_type = 'lateral';
    END IF;
    
    -- Insert upgrade record
    INSERT INTO membership_upgrades 
    (membership_id, old_plan_id, new_plan_id, old_price_monthly, new_price_monthly, 
     price_difference, upgrade_type)
    VALUES (p_membership_id, current_plan_id, p_new_plan_id, old_price, new_price, price_diff, upgrade_type);
    
    SET p_upgrade_id = LAST_INSERT_ID();
    
    -- Update membership plan
    UPDATE memberships
    SET plan_id = p_new_plan_id,
        total_price = new_price
    WHERE membership_id = p_membership_id;
    
    SET p_new_price = new_price;
    
    COMMIT;
END$$

DELIMITER ;

-- ============================================================================
-- PROCEDURE 5: Record Equipment Maintenance
-- ============================================================================

DELIMITER $$

CREATE PROCEDURE sp_record_equipment_maintenance(
    IN p_equipment_id BIGINT,
    IN p_maintenance_type VARCHAR(50),
    IN p_description TEXT,
    IN p_maintenance_cost DECIMAL(10, 2),
    IN p_trainer_id BIGINT,
    OUT p_maintenance_id BIGINT
)
BEGIN
    DECLARE next_maintenance DATE;
    DECLARE EXIT HANDLER FOR SQLEXCEPTION
    BEGIN
        ROLLBACK;
        RESIGNAL;
    END;
    
    START TRANSACTION;
    
    -- Get maintenance interval
    SELECT maintenance_interval_days INTO @interval
    FROM equipment
    WHERE equipment_id = p_equipment_id;
    
    SET next_maintenance = DATE_ADD(CURDATE(), INTERVAL @interval DAY);
    
    -- Insert maintenance record
    INSERT INTO maintenance_logs 
    (equipment_id, trainer_id, maintenance_type, maintenance_description, 
     maintenance_cost, maintenance_date, next_scheduled_date, completion_status)
    VALUES (p_equipment_id, p_trainer_id, p_maintenance_type, p_description, 
            p_maintenance_cost, CURDATE(), next_maintenance, 'completed');
    
    SET p_maintenance_id = LAST_INSERT_ID();
    
    -- Update equipment status
    UPDATE equipment
    SET last_maintenance_date = CURDATE(),
        next_maintenance_date = next_maintenance,
        operational_status = 'operational',
        condition_status = 'good'
    WHERE equipment_id = p_equipment_id;
    
    COMMIT;
END$$

DELIMITER ;

-- ============================================================================
-- PROCEDURE 6: Check Member Membership Expiry
-- ============================================================================

DELIMITER $$

CREATE PROCEDURE sp_check_membership_expiry()
BEGIN
    -- Update expired memberships
    UPDATE memberships
    SET status = 'expired'
    WHERE status = 'active'
    AND end_date < CURDATE();
    
    -- Log expiry count
    SELECT ROW_COUNT() as expired_memberships;
END$$

DELIMITER ;

-- ============================================================================
-- PART 4: VIEWS FOR REPORTING
-- ============================================================================

-- ============================================================================
-- VIEW 1: Active Members with Current Membership
-- ============================================================================

CREATE OR REPLACE VIEW v_active_members_with_membership AS
SELECT 
    m.member_id,
    u.email,
    u.first_name,
    u.last_name,
    u.phone_number,
    m.date_of_birth,
    m.city,
    m.total_classes_attended,
    COALESCE(mp.plan_name, 'No Active Plan') as plan_name,
    COALESCE(ms.start_date, NULL) as membership_start,
    COALESCE(ms.end_date, NULL) as membership_end,
    COALESCE(ms.status, 'inactive') as membership_status,
    COALESCE(DATEDIFF(ms.end_date, CURDATE()), -1) as days_remaining,
    COALESCE(ms.auto_renewal, FALSE) as auto_renewal,
    u.account_status
FROM members m
JOIN users u ON m.user_id = u.user_id
LEFT JOIN memberships ms ON m.member_id = ms.member_id AND ms.status = 'active'
LEFT JOIN membership_plans mp ON ms.plan_id = mp.plan_id
WHERE u.account_status = 'active'
ORDER BY m.member_id;

-- ============================================================================
-- VIEW 2: Member Attendance Report
-- ============================================================================

CREATE OR REPLACE VIEW v_member_attendance_report AS
SELECT 
    m.member_id,
    u.first_name,
    u.last_name,
    fc.class_name,
    COUNT(DISTINCT a.attendance_id) as total_attended,
    COUNT(CASE WHEN a.attendance_status = 'present' THEN 1 END) as present,
    COUNT(CASE WHEN a.attendance_status = 'absent' THEN 1 END) as absent,
    COUNT(CASE WHEN a.attendance_status = 'late' THEN 1 END) as late,
    ROUND(AVG(a.duration_minutes), 2) as avg_duration_minutes,
    MAX(a.check_in_time) as last_attended
FROM members m
JOIN users u ON m.user_id = u.user_id
LEFT JOIN attendance a ON m.member_id = a.member_id
LEFT JOIN class_schedules cs ON a.schedule_id = cs.schedule_id
LEFT JOIN fitness_classes fc ON cs.class_id = fc.class_id
GROUP BY m.member_id, u.first_name, u.last_name, fc.class_name
ORDER BY m.member_id, last_attended DESC;

-- ============================================================================
-- VIEW 3: Trainer Schedule
-- ============================================================================

CREATE OR REPLACE VIEW v_trainer_schedule AS
SELECT 
    t.trainer_id,
    u.first_name as trainer_first_name,
    u.last_name as trainer_last_name,
    fc.class_name,
    fc.category,
    fc.difficulty_level,
    cs.day_of_week,
    cs.start_time,
    cs.end_time,
    cs.current_enrollment,
    fc.max_capacity,
    (fc.max_capacity - cs.current_enrollment) as available_slots,
    ga.area_name,
    fc.status as class_status,
    cs.is_cancelled
FROM trainers t
JOIN users u ON t.user_id = u.user_id
JOIN fitness_classes fc ON t.trainer_id = fc.trainer_id
JOIN class_schedules cs ON fc.class_id = cs.class_id
JOIN gym_areas ga ON fc.area_id = ga.area_id
WHERE u.account_status = 'active'
ORDER BY t.trainer_id, cs.day_of_week, cs.start_time;

-- ============================================================================
-- VIEW 4: Monthly Revenue Report
-- ============================================================================

CREATE OR REPLACE VIEW v_monthly_revenue_report AS
SELECT 
    DATE_FORMAT(p.payment_date, '%Y-%m') as month,
    p.payment_type,
    COUNT(p.payment_id) as transaction_count,
    SUM(p.amount) as total_revenue,
    AVG(p.amount) as avg_transaction,
    MIN(p.amount) as min_amount,
    MAX(p.amount) as max_amount,
    SUM(CASE WHEN p.payment_status = 'refunded' THEN p.refund_amount ELSE 0 END) as total_refunds
FROM payments p
WHERE p.payment_status IN ('completed', 'refunded')
GROUP BY DATE_FORMAT(p.payment_date, '%Y-%m'), p.payment_type
ORDER BY month DESC, total_revenue DESC;

-- ============================================================================
-- VIEW 5: Equipment Maintenance Schedule
-- ============================================================================

CREATE OR REPLACE VIEW v_equipment_maintenance_due AS
SELECT 
    e.equipment_id,
    e.equipment_name,
    e.equipment_type,
    ga.area_name,
    e.last_maintenance_date,
    e.next_maintenance_date,
    DATEDIFF(e.next_maintenance_date, CURDATE()) as days_until_due,
    e.operational_status,
    e.condition_status,
    COALESCE(t.user_id, NULL) as responsible_trainer_id,
    COALESCE(CONCAT(tu.first_name, ' ', tu.last_name), 'Unassigned') as responsible_trainer_name
FROM equipment e
JOIN gym_areas ga ON e.area_id = ga.area_id
LEFT JOIN trainers t ON e.responsible_trainer_id = t.trainer_id
LEFT JOIN users tu ON t.user_id = tu.user_id
WHERE e.next_maintenance_date IS NOT NULL
AND e.next_maintenance_date <= DATE_ADD(CURDATE(), INTERVAL 30 DAY)
AND e.operational_status != 'retired'
ORDER BY e.next_maintenance_date ASC;

-- ============================================================================
-- VIEW 6: Equipment Usage Summary
-- ============================================================================

CREATE OR REPLACE VIEW v_equipment_usage_summary AS
SELECT 
    e.equipment_id,
    e.equipment_name,
    e.equipment_type,
    ga.area_name,
    e.usage_count as total_usage_count,
    COUNT(DISTINCT eu.usage_id) as usage_logs,
    COUNT(DISTINCT CASE WHEN eu.usage_type = 'training' THEN eu.usage_id END) as training_sessions,
    COUNT(DISTINCT CASE WHEN eu.usage_type IN ('maintenance', 'repair', 'inspection') THEN eu.usage_id END) as maintenance_logs,
    e.operational_status,
    e.condition_status,
    MAX(eu.start_time) as last_used
FROM equipment e
JOIN gym_areas ga ON e.area_id = ga.area_id
LEFT JOIN equipment_usage eu ON e.equipment_id = eu.equipment_id
GROUP BY e.equipment_id, e.equipment_name, e.equipment_type, ga.area_name
ORDER BY e.usage_count DESC;

-- ============================================================================
-- VIEW 7: Class Enrollment Status
-- ============================================================================

CREATE OR REPLACE VIEW v_class_enrollment_status AS
SELECT 
    fc.class_id,
    fc.class_name,
    fc.category,
    fc.difficulty_level,
    cs.schedule_id,
    cs.day_of_week,
    cs.start_time,
    cs.end_time,
    fc.max_capacity,
    cs.current_enrollment,
    cs.waiting_list_count,
    ROUND((cs.current_enrollment / fc.max_capacity * 100), 2) as enrollment_percentage,
    (fc.max_capacity - cs.current_enrollment) as available_slots,
    fc.status as class_status,
    cs.is_cancelled
FROM fitness_classes fc
JOIN class_schedules cs ON fc.class_id = cs.class_id
ORDER BY fc.class_id, cs.day_of_week, cs.start_time;

-- ============================================================================
-- VIEW 8: Trainer Certifications Status
-- ============================================================================

CREATE OR REPLACE VIEW v_trainer_certifications_status AS
SELECT 
    t.trainer_id,
    u.first_name,
    u.last_name,
    t.specialization,
    c.certification_name,
    c.certification_number,
    c.issuing_organization,
    c.issue_date,
    c.expiration_date,
    DATEDIFF(c.expiration_date, CURDATE()) as days_until_expiry,
    CASE 
        WHEN c.expiration_date IS NULL THEN 'Never Expires'
        WHEN DATEDIFF(c.expiration_date, CURDATE()) < 0 THEN 'EXPIRED'
        WHEN DATEDIFF(c.expiration_date, CURDATE()) < 30 THEN 'Expiring Soon'
        ELSE 'Valid'
    END as certification_status,
    c.is_active
FROM trainers t
JOIN users u ON t.user_id = u.user_id
LEFT JOIN certifications c ON t.trainer_id = c.trainer_id
WHERE u.account_status = 'active'
ORDER BY t.trainer_id, COALESCE(c.expiration_date, CURDATE()) ASC;

-- ============================================================================
-- VIEW 9: Low Enrollment Classes
-- ============================================================================

CREATE OR REPLACE VIEW v_low_enrollment_classes AS
SELECT 
    fc.class_id,
    fc.class_name,
    fc.category,
    t.user_id as trainer_id,
    CONCAT(tu.first_name, ' ', tu.last_name) as trainer_name,
    cs.schedule_id,
    cs.day_of_week,
    cs.start_time,
    fc.max_capacity,
    cs.current_enrollment,
    ROUND((cs.current_enrollment / fc.max_capacity * 100), 2) as enrollment_percentage
FROM fitness_classes fc
JOIN class_schedules cs ON fc.class_id = cs.class_id
JOIN trainers t ON fc.trainer_id = t.trainer_id
JOIN users tu ON t.user_id = tu.user_id
WHERE fc.status = 'active'
AND cs.is_cancelled = FALSE
AND (cs.current_enrollment / fc.max_capacity) < 0.30
ORDER BY enrollment_percentage ASC;

-- ============================================================================
-- COMPLETION MESSAGE
-- ============================================================================

SELECT '✓ Complete MySQL Implementation Created Successfully!' as Status,
       '18 Tables' as Components_1,
       '6 Triggers' as Components_2,
       '6 Stored Procedures' as Components_3,
       '9 Views' as Components_4;
