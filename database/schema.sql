-- ============================================================================
-- GYM MANAGEMENT SYSTEM - COMPLETE MYSQL DATABASE SCHEMA
-- ============================================================================
-- Database: gym_management
-- DBMS: MySQL 8.0+
-- Normalization: Third Normal Form (3NF)
-- Last Updated: 2024-03-14
-- ============================================================================

-- Drop existing database if exists (use with caution)
-- DROP DATABASE IF EXISTS gym_management;

-- Create Database
CREATE DATABASE IF NOT EXISTS gym_management 
CHARACTER SET utf8mb4 
COLLATE utf8mb4_unicode_ci;

USE gym_management;

-- ============================================================================
-- TABLE 1: USERS (Authentication & Authorization)
-- ============================================================================
-- Primary Entity: Core user authentication for all user types
-- Normalization: 1NF (all atomic values)
-- Dependencies: None (root entity)
-- ============================================================================

CREATE TABLE users (
    user_id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    email VARCHAR(255) NOT NULL UNIQUE,
    phone_number VARCHAR(20) NOT NULL UNIQUE,
    password_hash VARCHAR(255) NOT NULL,
    first_name VARCHAR(100) NOT NULL,
    last_name VARCHAR(100) NOT NULL,
    user_type ENUM('member', 'trainer', 'admin', 'manager') NOT NULL DEFAULT 'member',
    account_status ENUM('active', 'inactive', 'suspended', 'banned') NOT NULL DEFAULT 'active',
    email_verified BOOLEAN DEFAULT FALSE,
    email_verified_at TIMESTAMP NULL,
    last_login_at TIMESTAMP NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    deleted_at TIMESTAMP NULL,
    
    -- Indexes for frequent queries
    INDEX idx_email (email),
    INDEX idx_user_type (user_type),
    INDEX idx_account_status (account_status),
    INDEX idx_created_at (created_at),
    
    -- Check constraints
    CONSTRAINT chk_email_format CHECK (email LIKE '%@%')
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
COMMENT='Core user authentication table for all user types';

-- ============================================================================
-- TABLE 2: MEMBERS (Member-Specific Information)
-- ============================================================================
-- Primary Entity: Extended member profile information
-- Normalization: 3NF (depends on users via FK, no transitive dependencies)
-- Dependencies: users (1:1 relationship)
-- ============================================================================

CREATE TABLE members (
    member_id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    user_id BIGINT UNSIGNED NOT NULL UNIQUE,
    date_of_birth DATE NOT NULL,
    gender ENUM('male', 'female', 'other', 'prefer_not_to_say') NOT NULL,
    address VARCHAR(500),
    city VARCHAR(100),
    state VARCHAR(100),
    postal_code VARCHAR(20),
    country VARCHAR(100),
    emergency_contact_name VARCHAR(200),
    emergency_contact_phone VARCHAR(20),
    medical_conditions TEXT,
    membership_start_date DATE NOT NULL,
    profile_photo_url VARCHAR(500),
    total_classes_attended INT DEFAULT 0,
    preferred_class_time ENUM('morning', 'afternoon', 'evening', 'night') DEFAULT NULL,
    fitness_goals TEXT,
    trainer_id BIGINT UNSIGNED,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    
    -- Foreign Keys
    CONSTRAINT fk_members_users FOREIGN KEY (user_id) 
        REFERENCES users(user_id) ON DELETE CASCADE ON UPDATE CASCADE,
    CONSTRAINT fk_members_trainer FOREIGN KEY (trainer_id) 
        REFERENCES trainers(trainer_id) ON DELETE SET NULL ON UPDATE CASCADE,
    
    -- Indexes
    INDEX idx_user_id (user_id),
    INDEX idx_trainer_id (trainer_id),
    INDEX idx_city (city),
    INDEX idx_membership_start (membership_start_date),
    INDEX idx_gender (gender),
    
    -- Constraints
    CONSTRAINT chk_member_dob CHECK (date_of_birth <= CURDATE()),
    CONSTRAINT chk_membership_start CHECK (membership_start_date <= CURDATE()),
    CONSTRAINT chk_classes_attended CHECK (total_classes_attended >= 0)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
COMMENT='Member-specific profile information';

-- ============================================================================
-- TABLE 3: MEMBERSHIP_PLANS (Subscription Tiers)
-- ============================================================================
-- Primary Entity: Predefined membership subscription packages
-- Normalization: 1NF (all atomic values, no repeating groups)
-- Dependencies: None (root entity)
-- ============================================================================

CREATE TABLE membership_plans (
    plan_id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    plan_name VARCHAR(100) NOT NULL UNIQUE,
    description TEXT,
    price_per_month DECIMAL(10, 2) NOT NULL,
    price_per_year DECIMAL(10, 2),
    duration_months INT NOT NULL,
    max_classes_per_week INT,
    max_class_capacity INT DEFAULT NULL,
    access_to_gym BOOLEAN DEFAULT TRUE,
    personal_training_sessions INT DEFAULT 0,
    includes_nutrition_plan BOOLEAN DEFAULT FALSE,
    includes_recovery_program BOOLEAN DEFAULT FALSE,
    cancellation_notice_days INT DEFAULT 30,
    is_active BOOLEAN DEFAULT TRUE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE UPDATE CURRENT_TIMESTAMP,
    
    -- Indexes
    INDEX idx_is_active (is_active),
    INDEX idx_plan_name (plan_name),
    
    -- Check Constraints
    CONSTRAINT chk_plan_price CHECK (price_per_month > 0),
    CONSTRAINT chk_plan_duration CHECK (duration_months > 0),
    CONSTRAINT chk_max_classes CHECK (max_classes_per_week IS NULL OR max_classes_per_week > 0),
    CONSTRAINT chk_cancellation_notice CHECK (cancellation_notice_days >= 0)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
COMMENT='Membership subscription plans/tiers';

-- ============================================================================
-- TABLE 4: MEMBERSHIPS (Active Subscriptions)
-- ============================================================================
-- Primary Entity: Current and historical membership records
-- Normalization: 3NF (depends on member and plan, no transitive dependencies)
-- Dependencies: members (N:1), membership_plans (N:1)
-- ============================================================================

CREATE TABLE memberships (
    membership_id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    member_id BIGINT UNSIGNED NOT NULL,
    plan_id BIGINT UNSIGNED NOT NULL,
    start_date DATE NOT NULL,
    end_date DATE NOT NULL,
    renewal_date DATE,
    status ENUM('active', 'expired', 'cancelled', 'paused', 'pending') NOT NULL DEFAULT 'active',
    cancellation_reason VARCHAR(500),
    cancelled_by_user BOOLEAN DEFAULT FALSE,
    auto_renewal BOOLEAN DEFAULT TRUE,
    total_price DECIMAL(10, 2) NOT NULL,
    amount_paid DECIMAL(10, 2) DEFAULT 0,
    classes_used_this_month INT DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    
    -- Foreign Keys
    CONSTRAINT fk_memberships_member FOREIGN KEY (member_id) 
        REFERENCES members(member_id) ON DELETE CASCADE ON UPDATE CASCADE,
    CONSTRAINT fk_memberships_plan FOREIGN KEY (plan_id) 
        REFERENCES membership_plans(plan_id) ON DELETE RESTRICT ON UPDATE CASCADE,
    
    -- Indexes
    INDEX idx_member_id (member_id),
    INDEX idx_plan_id (plan_id),
    INDEX idx_status (status),
    INDEX idx_start_date (start_date),
    INDEX idx_end_date (end_date),
    INDEX idx_renewal_date (renewal_date),
    
    -- Constraints
    CONSTRAINT chk_membership_dates CHECK (start_date < end_date),
    CONSTRAINT chk_membership_price CHECK (total_price > 0 AND amount_paid >= 0),
    CONSTRAINT chk_classes_used CHECK (classes_used_this_month >= 0)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
COMMENT='Active and historical membership subscriptions for members';

-- ============================================================================
-- TABLE 5: MEMBERSHIP_UPGRADES (Historical Upgrades/Downgrades)
-- ============================================================================
-- Secondary Entity: Track membership plan changes over time
-- Normalization: 3NF (historical record, depends on membership and plan)
-- Dependencies: memberships (N:1), membership_plans (N:1 - old and new)
-- ============================================================================

CREATE TABLE membership_upgrades (
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
    
    -- Foreign Keys
    CONSTRAINT fk_upgrades_membership FOREIGN KEY (membership_id) 
        REFERENCES memberships(membership_id) ON DELETE CASCADE ON UPDATE CASCADE,
    CONSTRAINT fk_upgrades_old_plan FOREIGN KEY (old_plan_id) 
        REFERENCES membership_plans(plan_id) ON DELETE RESTRICT ON UPDATE CASCADE,
    CONSTRAINT fk_upgrades_new_plan FOREIGN KEY (new_plan_id) 
        REFERENCES membership_plans(plan_id) ON DELETE RESTRICT ON UPDATE CASCADE,
    
    -- Indexes
    INDEX idx_membership_id (membership_id),
    INDEX idx_upgrade_date (upgrade_date),
    INDEX idx_upgrade_type (upgrade_type),
    
    -- Constraint
    CONSTRAINT chk_different_plans CHECK (old_plan_id != new_plan_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
COMMENT='Historical record of membership plan upgrades and downgrades';

-- ============================================================================
-- TABLE 6: TRAINERS (Trainer Profiles)
-- ============================================================================
-- Primary Entity: Trainer-specific information
-- Normalization: 3NF (depends on users via FK)
-- Dependencies: users (1:1 relationship)
-- ============================================================================

CREATE TABLE trainers (
    trainer_id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    user_id BIGINT UNSIGNED NOT NULL UNIQUE,
    specialization VARCHAR(200) NOT NULL,
    years_of_experience INT NOT NULL,
    bio TEXT,
    hourly_rate DECIMAL(10, 2) NOT NULL,
    availability_status ENUM('available', 'unavailable', 'on_leave') NOT NULL DEFAULT 'available',
    total_clients INT DEFAULT 0,
    max_clients INT DEFAULT 20,
    qualification_summary VARCHAR(500),
    profile_photo_url VARCHAR(500),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    
    -- Foreign Keys
    CONSTRAINT fk_trainers_users FOREIGN KEY (user_id) 
        REFERENCES users(user_id) ON DELETE CASCADE ON UPDATE CASCADE,
    
    -- Indexes
    INDEX idx_user_id (user_id),
    INDEX idx_specialization (specialization),
    INDEX idx_availability (availability_status),
    
    -- Constraints
    CONSTRAINT chk_experience CHECK (years_of_experience >= 0),
    CONSTRAINT chk_hourly_rate CHECK (hourly_rate > 0),
    CONSTRAINT chk_clients CHECK (total_clients >= 0 AND max_clients > 0)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
COMMENT='Trainer profile information and qualifications';

-- ============================================================================
-- TABLE 7: CERTIFICATIONS (Trainer Certifications)
-- ============================================================================
-- Secondary Entity: Trainer qualifications and certifications
-- Normalization: 3NF (depends on trainers, no transitive dependencies)
-- Dependencies: trainers (N:1)
-- ============================================================================

CREATE TABLE certifications (
    certification_id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    trainer_id BIGINT UNSIGNED NOT NULL,
    certification_name VARCHAR(200) NOT NULL,
    issuing_organization VARCHAR(200) NOT NULL,
    issue_date DATE NOT NULL,
    expiration_date DATE,
    certification_number VARCHAR(100) UNIQUE,
    document_url VARCHAR(500),
    is_active BOOLEAN DEFAULT TRUE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    
    -- Foreign Keys
    CONSTRAINT fk_certifications_trainer FOREIGN KEY (trainer_id) 
        REFERENCES trainers(trainer_id) ON DELETE CASCADE ON UPDATE CASCADE,
    
    -- Indexes
    INDEX idx_trainer_id (trainer_id),
    INDEX idx_certification_name (certification_name),
    INDEX idx_expiration_date (expiration_date),
    INDEX idx_is_active (is_active),
    
    -- Constraints
    CONSTRAINT chk_cert_dates CHECK (issue_date <= CURDATE() AND (expiration_date IS NULL OR expiration_date >= issue_date))
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
COMMENT='Trainer certifications and qualifications';

-- ============================================================================
-- TABLE 8: GYM_AREAS (Physical Zones Within Gym)
-- ============================================================================
-- Primary Entity: Physical locations/zones in the gym
-- Normalization: 1NF (all atomic values)
-- Dependencies: None (root entity)
-- ============================================================================

CREATE TABLE gym_areas (
    area_id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    area_name VARCHAR(100) NOT NULL UNIQUE,
    description TEXT,
    capacity INT NOT NULL,
    equipment_count INT DEFAULT 0,
    is_active BOOLEAN DEFAULT TRUE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    
    -- Indexes
    INDEX idx_area_name (area_name),
    INDEX idx_is_active (is_active),
    
    -- Constraints
    CONSTRAINT chk_area_capacity CHECK (capacity > 0)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
COMMENT='Physical gym areas/zones (e.g., Cardio Room, Weight Room)';

-- ============================================================================
-- TABLE 9: FITNESS_CLASSES (Fitness Classes)
-- ============================================================================
-- Primary Entity: Fitness classes offered by the gym
-- Normalization: 3NF (depends on trainer and area)
-- Dependencies: trainers (N:1), gym_areas (N:1)
-- ============================================================================

CREATE TABLE fitness_classes (
    class_id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    trainer_id BIGINT UNSIGNED NOT NULL,
    area_id BIGINT UNSIGNED NOT NULL,
    class_name VARCHAR(150) NOT NULL,
    description TEXT,
    category ENUM('yoga', 'pilates', 'cardio', 'strength', 'boxing', 'dance', 'aqua', 'other') NOT NULL,
    difficulty_level ENUM('beginner', 'intermediate', 'advanced', 'mixed') NOT NULL DEFAULT 'beginner',
    max_capacity INT NOT NULL,
    current_enrollment INT DEFAULT 0,
    duration_minutes INT NOT NULL,
    class_type ENUM('recurring', 'one_time', 'special') NOT NULL DEFAULT 'recurring',
    status ENUM('active', 'suspended', 'cancelled', 'completed') NOT NULL DEFAULT 'active',
    price_per_session DECIMAL(10, 2) DEFAULT NULL,
    min_age INT DEFAULT NULL,
    max_age INT DEFAULT NULL,
    requires_equipment BOOLEAN DEFAULT FALSE,
    notes TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    
    -- Foreign Keys
    CONSTRAINT fk_class_trainer FOREIGN KEY (trainer_id) 
        REFERENCES trainers(trainer_id) ON DELETE RESTRICT ON UPDATE CASCADE,
    CONSTRAINT fk_class_area FOREIGN KEY (area_id) 
        REFERENCES gym_areas(area_id) ON DELETE RESTRICT ON UPDATE CASCADE,
    
    -- Indexes
    INDEX idx_trainer_id (trainer_id),
    INDEX idx_area_id (area_id),
    INDEX idx_category (category),
    INDEX idx_difficulty (difficulty_level),
    INDEX idx_status (status),
    INDEX idx_class_type (class_type),
    
    -- Constraints
    CONSTRAINT chk_class_capacity CHECK (max_capacity > 0),
    CONSTRAINT chk_current_enrollment CHECK (current_enrollment >= 0 AND current_enrollment <= max_capacity),
    CONSTRAINT chk_duration CHECK (duration_minutes > 0),
    CONSTRAINT chk_price CHECK (price_per_session IS NULL OR price_per_session > 0),
    CONSTRAINT chk_age_range CHECK (min_age IS NULL OR max_age IS NULL OR min_age <= max_age)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
COMMENT='Fitness classes offered by the gym';

-- ============================================================================
-- TABLE 10: CLASS_SCHEDULES (Class Schedule Instances)
-- ============================================================================
-- Secondary Entity: Specific instances/sessions of fitness classes
-- Normalization: 3NF (depends on fitness_classes)
-- Dependencies: fitness_classes (N:1)
-- ============================================================================

CREATE TABLE class_schedules (
    schedule_id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    class_id BIGINT UNSIGNED NOT NULL,
    day_of_week ENUM('monday', 'tuesday', 'wednesday', 'thursday', 'friday', 'saturday', 'sunday'),
    start_time TIME NOT NULL,
    end_time TIME NOT NULL,
    scheduled_date DATE,
    is_cancelled BOOLEAN DEFAULT FALSE,
    cancellation_reason VARCHAR(500),
    current_enrollment INT DEFAULT 0,
    waiting_list_count INT DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    
    -- Foreign Keys
    CONSTRAINT fk_schedule_class FOREIGN KEY (class_id) 
        REFERENCES fitness_classes(class_id) ON DELETE CASCADE ON UPDATE CASCADE,
    
    -- Indexes
    INDEX idx_class_id (class_id),
    INDEX idx_day_of_week (day_of_week),
    INDEX idx_scheduled_date (scheduled_date),
    INDEX idx_start_time (start_time),
    
    -- Constraints
    CONSTRAINT chk_schedule_times CHECK (start_time < end_time),
    CONSTRAINT chk_enrollment CHECK (current_enrollment >= 0),
    CONSTRAINT chk_waiting_list CHECK (waiting_list_count >= 0)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
COMMENT='Specific schedule instances for fitness classes';

-- ============================================================================
-- TABLE 11: CLASS_EQUIPMENT_ACCESS (Many-to-Many: Classes & Equipment)
-- ============================================================================
-- Junction Table: Resolve M:M relationship between fitness_classes and equipment
-- Normalization: 3NF (decomposed into junction table)
-- Dependencies: fitness_classes (N:M), equipment (M:N)
-- ============================================================================

CREATE TABLE class_equipment_access (
    access_id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    class_id BIGINT UNSIGNED NOT NULL,
    equipment_id BIGINT UNSIGNED NOT NULL,
    equipment_required BOOLEAN DEFAULT FALSE,
    quantity_needed INT DEFAULT 1,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    
    -- Foreign Keys
    CONSTRAINT fk_access_class FOREIGN KEY (class_id) 
        REFERENCES fitness_classes(class_id) ON DELETE CASCADE ON UPDATE CASCADE,
    CONSTRAINT fk_access_equipment FOREIGN KEY (equipment_id) 
        REFERENCES equipment(equipment_id) ON DELETE CASCADE ON UPDATE CASCADE,
    
    -- Unique constraint to prevent duplicate entries
    UNIQUE KEY uk_class_equipment (class_id, equipment_id),
    
    -- Indexes
    INDEX idx_equipment_id (equipment_id),
    
    -- Constraints
    CONSTRAINT chk_quantity CHECK (quantity_needed > 0)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
COMMENT='Junction table: Many-to-Many relationship between classes and equipment';

-- ============================================================================
-- TABLE 12: ATTENDANCE (Class Attendance Records)
-- ============================================================================
-- Secondary Entity: Member attendance tracking for classes
-- Normalization: 3NF (depends on members and class_schedules)
-- Dependencies: members (N:1), class_schedules (N:1)
-- ============================================================================

CREATE TABLE attendance (
    attendance_id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    member_id BIGINT UNSIGNED NOT NULL,
    schedule_id BIGINT UNSIGNED NOT NULL,
    check_in_time TIMESTAMP NOT NULL,
    check_out_time TIMESTAMP,
    attendance_status ENUM('present', 'absent', 'late', 'cancelled') NOT NULL DEFAULT 'present',
    duration_minutes INT,
    marked_by_trainer BOOLEAN DEFAULT FALSE,
    notes TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    
    -- Foreign Keys
    CONSTRAINT fk_attendance_member FOREIGN KEY (member_id) 
        REFERENCES members(member_id) ON DELETE CASCADE ON UPDATE CASCADE,
    CONSTRAINT fk_attendance_schedule FOREIGN KEY (schedule_id) 
        REFERENCES class_schedules(schedule_id) ON DELETE CASCADE ON UPDATE CASCADE,
    
    -- Indexes
    INDEX idx_member_id (member_id),
    INDEX idx_schedule_id (schedule_id),
    INDEX idx_check_in_time (check_in_time),
    INDEX idx_status (attendance_status),
    INDEX idx_member_schedule (member_id, schedule_id),
    
    -- Unique constraint: One attendance record per member per schedule
    UNIQUE KEY uk_member_schedule (member_id, schedule_id),
    
    -- Constraints
    CONSTRAINT chk_times CHECK (check_out_time IS NULL OR check_in_time < check_out_time),
    CONSTRAINT chk_duration CHECK (duration_minutes IS NULL OR duration_minutes > 0)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
COMMENT='Member attendance records for class sessions';

-- ============================================================================
-- TABLE 13: EQUIPMENT (Gym Equipment Inventory)
-- ============================================================================
-- Primary Entity: Gym equipment inventory
-- Normalization: 3NF (depends on gym_areas)
-- Dependencies: gym_areas (N:1)
-- ============================================================================

CREATE TABLE equipment (
    equipment_id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    area_id BIGINT UNSIGNED NOT NULL,
    equipment_name VARCHAR(200) NOT NULL,
    equipment_type VARCHAR(100) NOT NULL,
    description TEXT,
    serial_number VARCHAR(100) UNIQUE,
    purchase_date DATE NOT NULL,
    purchase_cost DECIMAL(10, 2),
    warranty_expiry_date DATE,
    condition_status ENUM('excellent', 'good', 'fair', 'poor', 'damaged') NOT NULL DEFAULT 'good',
    operational_status ENUM('operational', 'maintenance', 'repair', 'retired', 'damaged') NOT NULL DEFAULT 'operational',
    last_maintenance_date DATE,
    next_maintenance_date DATE,
    maintenance_interval_days INT DEFAULT 30,
    max_weight_capacity INT,
    usage_count INT DEFAULT 0,
    responsible_trainer_id BIGINT UNSIGNED,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    
    -- Foreign Keys
    CONSTRAINT fk_equipment_area FOREIGN KEY (area_id) 
        REFERENCES gym_areas(area_id) ON DELETE RESTRICT ON UPDATE CASCADE,
    CONSTRAINT fk_equipment_trainer FOREIGN KEY (responsible_trainer_id) 
        REFERENCES trainers(trainer_id) ON DELETE SET NULL ON UPDATE CASCADE,
    
    -- Indexes
    INDEX idx_area_id (area_id),
    INDEX idx_equipment_type (equipment_type),
    INDEX idx_condition_status (condition_status),
    INDEX idx_operational_status (operational_status),
    INDEX idx_next_maintenance (next_maintenance_date),
    
    -- Constraints
    CONSTRAINT chk_purchase_date CHECK (purchase_date <= CURDATE()),
    CONSTRAINT chk_maintenance_dates CHECK (last_maintenance_date IS NULL OR next_maintenance_date IS NULL OR 
                                           (last_maintenance_date <= CURDATE() AND next_maintenance_date > CURDATE())),
    CONSTRAINT chk_interval CHECK (maintenance_interval_days > 0),
    CONSTRAINT chk_usage_count CHECK (usage_count >= 0)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
COMMENT='Gym equipment inventory and maintenance tracking';

-- ============================================================================
-- TABLE 14: EQUIPMENT_USAGE (Equipment Usage/Maintenance Logs)
-- ============================================================================
-- Secondary Entity: Track equipment usage and maintenance
-- Normalization: 3NF (depends on equipment and members)
-- Dependencies: equipment (N:1), members (N:1 optional)
-- ============================================================================

CREATE TABLE equipment_usage (
    usage_id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    equipment_id BIGINT UNSIGNED NOT NULL,
    member_id BIGINT UNSIGNED,
    usage_type ENUM('training', 'maintenance', 'repair', 'inspection', 'cleaning') NOT NULL,
    start_time TIMESTAMP NOT NULL,
    end_time TIMESTAMP,
    duration_minutes INT,
    notes TEXT,
    usage_status ENUM('in_progress', 'completed', 'cancelled') NOT NULL DEFAULT 'in_progress',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    
    -- Foreign Keys
    CONSTRAINT fk_usage_equipment FOREIGN KEY (equipment_id) 
        REFERENCES equipment(equipment_id) ON DELETE CASCADE ON UPDATE CASCADE,
    CONSTRAINT fk_usage_member FOREIGN KEY (member_id) 
        REFERENCES members(member_id) ON DELETE SET NULL ON UPDATE CASCADE,
    
    -- Indexes
    INDEX idx_equipment_id (equipment_id),
    INDEX idx_member_id (member_id),
    INDEX idx_start_time (start_time),
    INDEX idx_usage_type (usage_type),
    INDEX idx_usage_status (usage_status),
    
    -- Constraints
    CONSTRAINT chk_usage_times CHECK (end_time IS NULL OR start_time < end_time),
    CONSTRAINT chk_usage_duration CHECK (duration_minutes IS NULL OR duration_minutes > 0)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
COMMENT='Equipment usage and maintenance logs';

-- ============================================================================
-- TABLE 15: PAYMENTS (Payment Transactions)
-- ============================================================================
-- Secondary Entity: Financial transaction records
-- Normalization: 3NF (depends on members and memberships)
-- Dependencies: members (N:1), memberships (N:1)
-- ============================================================================

CREATE TABLE payments (
    payment_id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    member_id BIGINT UNSIGNED NOT NULL,
    membership_id BIGINT UNSIGNED,
    payment_type ENUM('membership_fee', 'renewal_fee', 'class_fee', 'training_session', 'other') NOT NULL,
    amount DECIMAL(10, 2) NOT NULL,
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
    
    -- Foreign Keys
    CONSTRAINT fk_payment_member FOREIGN KEY (member_id) 
        REFERENCES members(member_id) ON DELETE CASCADE ON UPDATE CASCADE,
    CONSTRAINT fk_payment_membership FOREIGN KEY (membership_id) 
        REFERENCES memberships(membership_id) ON DELETE SET NULL ON UPDATE CASCADE,
    
    -- Indexes
    INDEX idx_member_id (member_id),
    INDEX idx_membership_id (membership_id),
    INDEX idx_payment_date (payment_date),
    INDEX idx_payment_status (payment_status),
    INDEX idx_payment_type (payment_type),
    INDEX idx_transaction_id (transaction_id),
    
    -- Constraints
    CONSTRAINT chk_payment_amount CHECK (amount > 0),
    CONSTRAINT chk_refund_logic CHECK ((refund_amount IS NULL AND refund_date IS NULL) OR 
                                        (refund_amount IS NOT NULL AND refund_date IS NOT NULL AND 
                                         refund_amount <= amount AND refund_amount > 0))
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
COMMENT='Payment transactions and financial records';

-- ============================================================================
-- TABLE 16: MAINTENANCE_LOGS (Equipment Maintenance History)
-- ============================================================================
-- Secondary Entity: Detailed maintenance history for equipment
-- Normalization: 3NF (depends on equipment and trainers)
-- Dependencies: equipment (N:1), trainers (N:1)
-- ============================================================================

CREATE TABLE maintenance_logs (
    maintenance_id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    equipment_id BIGINT UNSIGNED NOT NULL,
    trainer_id BIGINT UNSIGNED,
    maintenance_type ENUM('preventive', 'corrective', 'emergency', 'inspection') NOT NULL,
    maintenance_description TEXT NOT NULL,
    parts_replaced TEXT,
    maintenance_cost DECIMAL(10, 2),
    maintenance_date DATE NOT NULL,
    next_scheduled_date DATE,
    completion_status ENUM('pending', 'in_progress', 'completed', 'cancelled') NOT NULL DEFAULT 'pending',
    notes TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    
    -- Foreign Keys
    CONSTRAINT fk_maintenance_equipment FOREIGN KEY (equipment_id) 
        REFERENCES equipment(equipment_id) ON DELETE CASCADE ON UPDATE CASCADE,
    CONSTRAINT fk_maintenance_trainer FOREIGN KEY (trainer_id) 
        REFERENCES trainers(trainer_id) ON DELETE SET NULL ON UPDATE CASCADE,
    
    -- Indexes
    INDEX idx_equipment_id (equipment_id),
    INDEX idx_trainer_id (trainer_id),
    INDEX idx_maintenance_date (maintenance_date),
    INDEX idx_maintenance_type (maintenance_type),
    INDEX idx_completion_status (completion_status),
    
    -- Constraints
    CONSTRAINT chk_maint_cost CHECK (maintenance_cost IS NULL OR maintenance_cost > 0),
    CONSTRAINT chk_maint_date CHECK (maintenance_date <= CURDATE())
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
COMMENT='Equipment maintenance history and logs';

-- ============================================================================
-- TABLE 17: CLASS_ENROLLMENTS (Member Class Enrollment - Many-to-Many)
-- ============================================================================
-- Junction Table: Resolve M:M relationship between members and class_schedules
-- Normalization: 3NF (decomposed into junction table)
-- Dependencies: members (N:M), class_schedules (M:N)
-- ============================================================================

CREATE TABLE class_enrollments (
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
    
    -- Foreign Keys
    CONSTRAINT fk_enrollment_member FOREIGN KEY (member_id) 
        REFERENCES members(member_id) ON DELETE CASCADE ON UPDATE CASCADE,
    CONSTRAINT fk_enrollment_schedule FOREIGN KEY (schedule_id) 
        REFERENCES class_schedules(schedule_id) ON DELETE CASCADE ON UPDATE CASCADE,
    
    -- Unique constraint: Prevent duplicate enrollments
    UNIQUE KEY uk_member_schedule (member_id, schedule_id),
    
    -- Indexes
    INDEX idx_member_id (member_id),
    INDEX idx_schedule_id (schedule_id),
    INDEX idx_enrollment_status (enrollment_status),
    INDEX idx_enrollment_date (enrollment_date),
    
    -- Constraints
    CONSTRAINT chk_cancel_logic CHECK ((cancellation_reason IS NULL AND cancelled_date IS NULL) OR 
                                       (cancellation_reason IS NOT NULL AND cancelled_date IS NOT NULL))
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
COMMENT='Junction table for member class enrollments (Many-to-Many relationship)';

-- ============================================================================
-- TABLE 18: TRAINER_CERTIFICATIONS (Trainer & Certification - Many-to-Many)
-- ============================================================================
-- Note: This can be used as an alternative or supplement to CERTIFICATIONS table
-- Junction Table: Explicit M:M for advanced scenarios
-- Normalization: 3NF (decomposed junction table)
-- ============================================================================

-- Already handled via CERTIFICATIONS table with trainer_id FK (1:N relationship)
-- This junction would only be needed if certifications can belong to multiple trainers
-- For this system, CERTIFICATIONS table is sufficient

-- ============================================================================
-- CREATE VIEWS FOR COMMON QUERIES (Performance Optimization)
-- ============================================================================

-- View 1: Current Active Memberships
CREATE OR REPLACE VIEW v_active_memberships AS
SELECT 
    m.membership_id,
    m.member_id,
    u.email,
    u.first_name,
    u.last_name,
    mp.plan_name,
    m.start_date,
    m.end_date,
    m.renewal_date,
    m.auto_renewal,
    m.classes_used_this_month,
    mp.max_classes_per_week,
    DATEDIFF(m.end_date, CURDATE()) as days_until_expiry,
    m.status
FROM memberships m
JOIN members mb ON m.member_id = mb.member_id
JOIN users u ON mb.user_id = u.user_id
JOIN membership_plans mp ON m.plan_id = mp.plan_id
WHERE m.status = 'active' 
  AND m.end_date >= CURDATE()
ORDER BY m.renewal_date ASC;

-- View 2: Trainer Schedule with Class Details
CREATE OR REPLACE VIEW v_trainer_schedule AS
SELECT 
    t.trainer_id,
    u.first_name,
    u.last_name,
    fc.class_name,
    cs.day_of_week,
    cs.start_time,
    cs.end_time,
    cs.current_enrollment,
    fc.max_capacity,
    fc.max_capacity - cs.current_enrollment as available_slots,
    ga.area_name,
    fc.status
FROM trainers t
JOIN users u ON t.user_id = u.user_id
JOIN fitness_classes fc ON t.trainer_id = fc.trainer_id
JOIN class_schedules cs ON fc.class_id = cs.class_id
JOIN gym_areas ga ON fc.area_id = ga.area_id
WHERE fc.status = 'active'
ORDER BY t.trainer_id, cs.day_of_week, cs.start_time;

-- View 3: Member Attendance Statistics
CREATE OR REPLACE VIEW v_member_attendance_stats AS
SELECT 
    m.member_id,
    u.email,
    u.first_name,
    u.last_name,
    COUNT(a.attendance_id) as total_attended,
    COUNT(CASE WHEN a.attendance_status = 'present' THEN 1 END) as classes_attended,
    COUNT(CASE WHEN a.attendance_status = 'absent' THEN 1 END) as classes_absent,
    COUNT(CASE WHEN a.attendance_status = 'late' THEN 1 END) as classes_late,
    AVG(a.duration_minutes) as avg_class_duration,
    MAX(a.check_in_time) as last_class_date
FROM members m
JOIN users u ON m.user_id = u.user_id
LEFT JOIN attendance a ON m.member_id = a.member_id
GROUP BY m.member_id, u.email, u.first_name, u.last_name;

-- View 4: Equipment Maintenance Schedule
CREATE OR REPLACE VIEW v_equipment_maintenance_due AS
SELECT 
    e.equipment_id,
    e.equipment_name,
    e.equipment_type,
    ga.area_name,
    e.next_maintenance_date,
    DATEDIFF(e.next_maintenance_date, CURDATE()) as days_until_due,
    e.last_maintenance_date,
    e.operational_status,
    e.condition_status,
    t.user_id
FROM equipment e
JOIN gym_areas ga ON e.area_id = ga.area_id
LEFT JOIN trainers t ON e.responsible_trainer_id = t.trainer_id
WHERE e.next_maintenance_date IS NOT NULL
  AND e.next_maintenance_date <= DATE_ADD(CURDATE(), INTERVAL 30 DAY)
  AND e.operational_status != 'retired'
ORDER BY e.next_maintenance_date ASC;

-- View 5: Revenue Report by Payment Type
CREATE OR REPLACE VIEW v_revenue_summary AS
SELECT 
    DATE_FORMAT(p.payment_date, '%Y-%m') as month,
    p.payment_type,
    COUNT(p.payment_id) as transaction_count,
    SUM(p.amount) as total_amount,
    AVG(p.amount) as avg_amount,
    MIN(p.amount) as min_amount,
    MAX(p.amount) as max_amount
FROM payments p
WHERE p.payment_status IN ('completed', 'refunded')
GROUP BY DATE_FORMAT(p.payment_date, '%Y-%m'), p.payment_type
ORDER BY month DESC, total_amount DESC;

-- ============================================================================
-- INDEXES FOR OPTIMIZATION (Additional Performance Indexes)
-- ============================================================================

-- Composite indexes for common filter combinations
CREATE INDEX idx_membership_status_date ON memberships(status, end_date);
CREATE INDEX idx_class_trainer_status ON fitness_classes(trainer_id, status);
CREATE INDEX idx_equipment_area_status ON equipment(area_id, operational_status);
CREATE INDEX idx_payment_member_date ON payments(member_id, payment_date);
CREATE INDEX idx_attendance_member_date ON attendance(member_id, check_in_time);

-- ============================================================================
-- INSERT SAMPLE DATA (OPTIONAL - For Testing)
-- ============================================================================

-- Sample Gym Areas
INSERT INTO gym_areas (area_name, description, capacity, equipment_count, is_active) VALUES
('Cardio Room', 'Treadmills, stair climbers, and stationary bikes', 30, 15, TRUE),
('Weight Room', 'Free weights, squat racks, and benches', 25, 45, TRUE),
('Yoga Studio', 'Yoga and pilates classes', 40, 5, TRUE),
('Swimming Pool', 'Olympic-size swimming pool', 50, 2, TRUE),
('Spin Studio', 'Spin bikes and classes', 35, 25, TRUE);

-- ============================================================================
-- END OF DATABASE SCHEMA
-- ============================================================================

-- Display completion message
SELECT '✓ Database schema created successfully!' as 'Status';
