# GYM MANAGEMENT SYSTEM - DATABASE ARCHITECTURE DOCUMENTATION

## Table of Contents
1. [Overview](#overview)
2. [Normalization Analysis](#normalization-analysis)
3. [Entity-Relationship Diagram](#entity-relationship-diagram)
4. [Complete Table Structure](#complete-table-structure)
5. [Relationships & Constraints](#relationships--constraints)
6. [Indexes & Performance](#indexes--performance)
7. [Design Decisions](#design-decisions)
8. [Data Flow](#data-flow)
9. [Implementation Guide](#implementation-guide)

---

## Overview

### Database Purpose
The Gym Management System database is designed to manage all aspects of a fitness facility including:
- Member management and memberships
- Trainer and certification tracking
- Fitness classes and scheduling
- Equipment inventory and maintenance
- Attendance tracking
- Payment processing
- Class enrollments

### Key Statistics
- **Total Tables**: 18
- **Total Entities**: 11 (core business entities)
- **Junction Tables**: 3 (for many-to-many relationships)
- **Total Columns**: 180+
- **Foreign Key Relationships**: 25+
- **Unique Constraints**: 8+
- **Check Constraints**: 25+
- **Views**: 5 (for common queries)
- **Indexes**: 50+ (including composite indexes)

### Technology Stack
- **DBMS**: MySQL 8.0+
- **Character Set**: UTF8MB4 (Unicode support for international characters)
- **Collation**: utf8mb4_unicode_ci (case-insensitive, unicode-aware)
- **Engine**: InnoDB (ACID compliance, transactions, foreign keys)
- **Normalization Level**: Third Normal Form (3NF)

---

## Normalization Analysis

### What is Database Normalization?
Normalization is a systematic process of organizing data to eliminate redundancy, improve data integrity, and ensure efficient data retrieval. The goal is to organize data in multiple related tables while maintaining data relationships.

### Normalization Levels

#### **1. First Normal Form (1NF)**
**Rule**: Every column must contain atomic (indivisible) values only. No repeating groups.

**Tables in 1NF**:
- `users` - Only atomic values (email, phone, names, status)
- `membership_plans` - Direct membership subscription data
- `gym_areas` - Physical location information
- `trainers` - Trainer profile information

**Example - NOT in 1NF** ❌:
```sql
-- BAD DESIGN - Violates 1NF
phone_numbers VARCHAR(200) -- "555-1234, 555-5678, 555-9999"
certifications TEXT         -- "CPT, ACE, NASM"
```

**Example - In 1NF** ✅:
```sql
-- GOOD DESIGN - Follows 1NF
-- Store each phone in separate row in CERTIFICATIONS or PHONE_NUMBERS table
-- Each certification gets its own row in CERTIFICATIONS table
```

#### **2. Second Normal Form (2NF)**
**Rules**: 
- Must be in 1NF
- All non-key columns must depend on the ENTIRE primary key (no partial dependencies)

**Tables in 2NF**:
All tables satisfy this because:
- Single-column primary keys (no composite PKs requiring multi-column dependencies)
- All non-key columns depend fully on the primary key

**Example - NOT in 2NF** ❌:
```sql
-- BAD DESIGN
CREATE TABLE class_sessions (
    class_id INT PRIMARY KEY,
    trainer_id INT,              -- Depends only on class_id
    trainer_name VARCHAR(100),   -- PARTIAL DEPENDENCY: Depends on trainer_id, not class_id
    session_date DATE
);
```

**Example - In 2NF** ✅:
```sql
-- GOOD DESIGN - Separate trainer info to trainers table
CREATE TABLE fitness_classes (
    class_id INT PRIMARY KEY,
    trainer_id INT,              -- Foreign key
    session_date DATE
);

CREATE TABLE trainers (
    trainer_id INT PRIMARY KEY,
    trainer_name VARCHAR(100)    -- Depends fully on trainer_id
);
```

#### **3. Third Normal Form (3NF)**
**Rules**:
- Must be in 2NF
- No transitive dependencies (non-key columns must not depend on other non-key columns)

**Tables in 3NF**:

✅ **MEMBERS Table** (3NF):
```
PK: member_id
├─ Depends on member_id:
│  ├── user_id (FK)
│  ├── date_of_birth
│  ├── address
│  ├── emergency_contact_name
│  └── total_classes_attended
└─ NO transitive dependencies!
   (city doesn't depend on address, etc.)
```

✅ **MEMBERSHIPS Table** (3NF):
```
PK: membership_id
├─ Depends on membership_id:
│  ├── member_id (FK)
│  ├── plan_id (FK)
│  ├── start_date
│  ├── end_date
│  ├── status
│  └── total_price
└─ NO transitive dependencies!
   (plan details stored in MEMBERSHIP_PLANS table)
```

❌ **NOT in 3NF - Example** (Transitive Dependency):
```sql
-- BAD DESIGN - Violates 3NF
CREATE TABLE memberships_bad (
    membership_id INT PRIMARY KEY,
    member_id INT,
    plan_id INT,
    plan_name VARCHAR(100),      -- Transitive: membership → plan_id → plan_name
    plan_price DECIMAL(10,2),    -- Transitive: membership → plan_id → plan_price
    start_date DATE
);
```

✅ **CORRECT Design** (3NF):
```sql
-- GOOD DESIGN - Plan details in separate table
CREATE TABLE memberships (
    membership_id INT PRIMARY KEY,
    member_id INT,
    plan_id INT,                 -- FK to plans
    start_date DATE
);

CREATE TABLE membership_plans (
    plan_id INT PRIMARY KEY,
    plan_name VARCHAR(100),
    plan_price DECIMAL(10,2)
);
```

### Our Database: 3NF Compliance

**Summary of Normalization Approach**:
| Level | Compliance | Evidence |
|-------|-----------|----------|
| **1NF** | ✅ FULL | All columns contain atomic values; no repeating groups |
| **2NF** | ✅ FULL | Single-column PKs; no partial dependencies |
| **3NF** | ✅ FULL | No transitive dependencies; related data in separate tables |

**Benefits Achieved**:
1. **Data Integrity**: No redundancy, single source of truth
2. **Update Efficiency**: Changes in one place affect entire system
3. **Query Performance**: Proper indexing on normalized structure
4. **Consistency**: Related data stays synchronized via foreign keys
5. **Scalability**: Structure supports growth without redesign

---

## Entity-Relationship Diagram

### Visual ER Diagram (Mermaid Format)

```
erDiagram
    USERS ||--o| MEMBERS : "1:1"
    USERS ||--o| TRAINERS : "1:1"
    MEMBERS ||--o{ MEMBERSHIPS : "1:N"
    MEMBERSHIP_PLANS ||--o{ MEMBERSHIPS : "1:N"
    MEMBERS ||--o{ PAYMENTS : "1:N"
    MEMBERSHIPS ||--o{ PAYMENTS : "1:N"
    TRAINERS ||--o{ FITNESS_CLASSES : "1:N"
    TRAINERS ||--o{ CERTIFICATIONS : "1:N"
    TRAINERS ||--o{ MAINTENANCE_LOGS : "1:N"
    GYM_AREAS ||--o{ FITNESS_CLASSES : "1:N"
    GYM_AREAS ||--o{ EQUIPMENT : "1:N"
    FITNESS_CLASSES ||--o{ CLASS_SCHEDULES : "1:N"
    FITNESS_CLASSES }o--o{ EQUIPMENT : "M:N"
    CLASS_SCHEDULES ||--o{ CLASS_ENROLLMENTS : "1:N"
    CLASS_SCHEDULES ||--o{ ATTENDANCE : "1:N"
    MEMBERS ||--o{ CLASS_ENROLLMENTS : "1:N"
    MEMBERS ||--o{ EQUIPMENT_USAGE : "1:N"
    EQUIPMENT ||--o{ EQUIPMENT_USAGE : "1:N"
    EQUIPMENT ||--o{ MAINTENANCE_LOGS : "1:N"
    MEMBERSHIPS ||--o{ MEMBERSHIP_UPGRADES : "1:N"
    MEMBERSHIP_PLANS ||--o{ MEMBERSHIP_UPGRADES : "1:N"
```

### Relationship Legend
- **1:1** (One-to-One): User ↔ Member, User ↔ Trainer
- **1:N** (One-to-Many): Trainer → Classes, Members → Memberships
- **N:M** (Many-to-Many): Members ↔ Classes (via CLASS_ENROLLMENTS)

---

## Complete Table Structure

### Core Entity Tables (Root Entities)

#### **TABLE 1: USERS**
**Purpose**: Central authentication and user management table
**Scope**: All users (members, trainers, admins, managers)

| Column | Type | Constraints | Purpose |
|--------|------|-----------|---------|
| user_id | BIGINT UNSIGNED | PK, AUTO_INCREMENT | Unique user identifier |
| email | VARCHAR(255) | NOT NULL, UNIQUE | Login credential, communication |
| phone_number | VARCHAR(20) | NOT NULL, UNIQUE | Contact information |
| password_hash | VARCHAR(255) | NOT NULL | Secure password storage (bcrypt/argon2) |
| first_name | VARCHAR(100) | NOT NULL | User's first name |
| last_name | VARCHAR(100) | NOT NULL | User's last name |
| user_type | ENUM | NOT NULL, DEFAULT 'member' | Role classification: member/trainer/admin/manager |
| account_status | ENUM | NOT NULL, DEFAULT 'active' | Account state: active/inactive/suspended/banned |
| email_verified | BOOLEAN | DEFAULT FALSE | Email verification status |
| email_verified_at | TIMESTAMP | NULL | Timestamp of verification |
| last_login_at | TIMESTAMP | NULL | Last successful login |
| created_at | TIMESTAMP | DEFAULT CURRENT_TIMESTAMP | Record creation time |
| updated_at | TIMESTAMP | AUTO UPDATE | Record modification time |
| deleted_at | TIMESTAMP | NULL | Soft delete timestamp (for GDPR compliance) |

**Indexes**:
- `email` - For login queries
- `user_type` - For role-based filtering
- `account_status` - For status filtering
- `created_at` - For timerange queries

**Constraints**:
- Email format validation: `email LIKE '%@%'`

---

#### **TABLE 2: MEMBERS**
**Purpose**: Extended member profile with gym-specific information
**Scope**: All gym members
**Relationship**: 1:1 with users (FK user_id), N:1 with trainers

| Column | Type | Constraints | Purpose |
|--------|------|-----------|---------|
| member_id | BIGINT UNSIGNED | PK, AUTO_INCREMENT | Unique member identifier |
| user_id | BIGINT UNSIGNED | NOT NULL, UNIQUE, FK | Link to users table |
| date_of_birth | DATE | NOT NULL, CHECK | Member age calculation |
| gender | ENUM | NOT NULL | Demographic data |
| address | VARCHAR(500) | NULL | Physical address |
| city | VARCHAR(100) | NULL | City for member management |
| state | VARCHAR(100) | NULL | State/province |
| postal_code | VARCHAR(20) | NULL | Postal code for registration |
| country | VARCHAR(100) | NULL | Country information |
| emergency_contact_name | VARCHAR(200) | NULL | Emergency contact person |
| emergency_contact_phone | VARCHAR(20) | NULL | Emergency contact number |
| medical_conditions | TEXT | NULL | Health considerations |
| membership_start_date | DATE | NOT NULL, CHECK | Member tenure tracking |
| profile_photo_url | VARCHAR(500) | NULL | Member profile picture |
| total_classes_attended | INT | DEFAULT 0, CHECK | Attendance statistics |
| preferred_class_time | ENUM | NULL | Scheduling preference |
| fitness_goals | TEXT | NULL | Member's personal goals |
| trainer_id | BIGINT UNSIGNED | NULL, FK | Assigned personal trainer |
| created_at | TIMESTAMP | DEFAULT CURRENT_TIMESTAMP | Record creation |
| updated_at | TIMESTAMP | AUTO UPDATE | Record modification |

**Foreign Keys**:
- `user_id` → users.user_id (CASCADE DELETE)
- `trainer_id` → trainers.trainer_id (SET NULL on delete)

**Indexes**:
- `user_id` - For authentication lookups
- `trainer_id` - For trainer-member relationships
- `city` - For member filtering by location
- `membership_start_date` - For tenure analysis

---

#### **TABLE 3: MEMBERSHIP_PLANS**
**Purpose**: Predefined membership subscription tiers
**Scope**: Different subscription packages offered by gym

| Column | Type | Constraints | Purpose |
|--------|------|-----------|---------|
| plan_id | BIGINT UNSIGNED | PK, AUTO_INCREMENT | Unique plan identifier |
| plan_name | VARCHAR(100) | NOT NULL, UNIQUE | Plan display name (e.g., "Gold", "Platinum") |
| description | TEXT | NULL | Plan details and features |
| price_per_month | DECIMAL(10,2) | NOT NULL, CHECK > 0 | Monthly subscription cost |
| price_per_year | DECIMAL(10,2) | NULL | Annual subscription cost (if offered) |
| duration_months | INT | NOT NULL, CHECK > 0 | Subscription duration |
| max_classes_per_week | INT | NULL | Weekly class attendance limit |
| max_class_capacity | INT | NULL | Maximum class size for this plan |
| access_to_gym | BOOLEAN | DEFAULT TRUE | 24/7 gym access flag |
| personal_training_sessions | INT | DEFAULT 0 | Included trainer sessions |
| includes_nutrition_plan | BOOLEAN | DEFAULT FALSE | Nutrition guidance included |
| includes_recovery_program | BOOLEAN | DEFAULT FALSE | Recovery/rehab program access |
| cancellation_notice_days | INT | DEFAULT 30 | Notice period for cancellation |
| is_active | BOOLEAN | DEFAULT TRUE | Plan availability status |
| created_at | TIMESTAMP | DEFAULT CURRENT_TIMESTAMP | Plan creation time |
| updated_at | TIMESTAMP | AUTO UPDATE | Plan modification time |

**Indexes**:
- `plan_name` - For plan lookup
- `is_active` - For active plans filtering

**Constraints**:
- Price must be > 0
- Duration must be > 0

---

#### **TABLE 4: TRAINERS**
**Purpose**: Professional trainer profiles and qualifications
**Scope**: All gym trainers and instructors
**Relationship**: 1:1 with users (FK user_id)

| Column | Type | Constraints | Purpose |
|--------|------|-----------|---------|
| trainer_id | BIGINT UNSIGNED | PK, AUTO_INCREMENT | Unique trainer identifier |
| user_id | BIGINT UNSIGNED | NOT NULL, UNIQUE, FK | Link to users table |
| specialization | VARCHAR(200) | NOT NULL | Training specialty (e.g., "Weight Training", "Yoga") |
| years_of_experience | INT | NOT NULL, CHECK >= 0 | Professional experience |
| bio | TEXT | NULL | Trainer biography |
| hourly_rate | DECIMAL(10,2) | NOT NULL, CHECK > 0 | Personal training rate |
| availability_status | ENUM | NOT NULL, DEFAULT 'available' | Availability: available/unavailable/on_leave |
| total_clients | INT | DEFAULT 0, CHECK >= 0 | Current client count |
| max_clients | INT | DEFAULT 20, CHECK > 0 | Client capacity limit |
| qualification_summary | VARCHAR(500) | NULL | Brief qualification overview |
| profile_photo_url | VARCHAR(500) | NULL | Trainer profile picture |
| created_at | TIMESTAMP | DEFAULT CURRENT_TIMESTAMP | Record creation |
| updated_at | TIMESTAMP | AUTO UPDATE | Record modification |

**Foreign Keys**:
- `user_id` → users.user_id (CASCADE DELETE)

**Indexes**:
- `specialization` - For skill-based trainer search
- `availability_status` - For available trainer lookup

---

#### **TABLE 5: GYM_AREAS**
**Purpose**: Physical zones/locations within the gym facility
**Scope**: Different areas/rooms in the gym

| Column | Type | Constraints | Purpose |
|--------|------|-----------|---------|
| area_id | BIGINT UNSIGNED | PK, AUTO_INCREMENT | Unique area identifier |
| area_name | VARCHAR(100) | NOT NULL, UNIQUE | Area name (e.g., "Cardio Room", "Weight Room") |
| description | TEXT | NULL | Area description and features |
| capacity | INT | NOT NULL, CHECK > 0 | Maximum occupancy |
| equipment_count | INT | DEFAULT 0 | Equipment inventory in area |
| is_active | BOOLEAN | DEFAULT TRUE | Area operational status |
| created_at | TIMESTAMP | DEFAULT CURRENT_TIMESTAMP | Record creation |
| updated_at | TIMESTAMP | AUTO UPDATE | Record modification |

**Indexes**:
- `area_name` - For area lookup
- `is_active` - For active areas filtering

---

#### **TABLE 6: FITNESS_CLASSES**
**Purpose**: Fitness class offerings (not individual sessions)
**Scope**: Types and categories of classes offered
**Relationship**: N:1 with trainers, N:1 with gym_areas, M:N with equipment

| Column | Type | Constraints | Purpose |
|--------|------|-----------|---------|
| class_id | BIGINT UNSIGNED | PK, AUTO_INCREMENT | Unique class identifier |
| trainer_id | BIGINT UNSIGNED | NOT NULL, FK | Instructor for the class |
| area_id | BIGINT UNSIGNED | NOT NULL, FK | Physical location of class |
| class_name | VARCHAR(150) | NOT NULL | Class name (e.g., "Morning Yoga") |
| description | TEXT | NULL | Class description |
| category | ENUM | NOT NULL | Category: yoga/pilates/cardio/strength/boxing/dance/aqua/other |
| difficulty_level | ENUM | NOT NULL, DEFAULT 'beginner' | Skill requirement: beginner/intermediate/advanced/mixed |
| max_capacity | INT | NOT NULL, CHECK > 0 | Class size limit |
| current_enrollment | INT | DEFAULT 0, CHECK >= 0 | Current member count |
| duration_minutes | INT | NOT NULL, CHECK > 0 | Class duration in minutes |
| class_type | ENUM | NOT NULL, DEFAULT 'recurring' | Type: recurring/one_time/special |
| status | ENUM | NOT NULL, DEFAULT 'active' | Status: active/suspended/cancelled/completed |
| price_per_session | DECIMAL(10,2) | NULL | Per-session cost (if applicable) |
| min_age | INT | NULL | Minimum participant age |
| max_age | INT | NULL | Maximum participant age |
| requires_equipment | BOOLEAN | DEFAULT FALSE | Equipment requirement flag |
| notes | TEXT | NULL | Additional notes |
| created_at | TIMESTAMP | DEFAULT CURRENT_TIMESTAMP | Record creation |
| updated_at | TIMESTAMP | AUTO UPDATE | Record modification |

**Foreign Keys**:
- `trainer_id` → trainers.trainer_id (RESTRICT DELETE)
- `area_id` → gym_areas.area_id (RESTRICT DELETE)

**Indexes**:
- `trainer_id` - For trainer's classes lookup
- `category` - For class type filtering
- `status` - For active classes filtering
- `class_type` - For class type filtering

---

#### **TABLE 7: EQUIPMENT**
**Purpose**: Gym equipment inventory tracking
**Scope**: All equipment in the facility
**Relationship**: N:1 with gym_areas

| Column | Type | Constraints | Purpose |
|--------|------|-----------|---------|
| equipment_id | BIGINT UNSIGNED | PK, AUTO_INCREMENT | Unique equipment identifier |
| area_id | BIGINT UNSIGNED | NOT NULL, FK | Location in gym |
| equipment_name | VARCHAR(200) | NOT NULL | Equipment name |
| equipment_type | VARCHAR(100) | NOT NULL | Type classification |
| description | TEXT | NULL | Equipment description |
| serial_number | VARCHAR(100) | UNIQUE | Manufacturer serial number |
| purchase_date | DATE | NOT NULL, CHECK <= TODAY | Acquisition date |
| purchase_cost | DECIMAL(10,2) | NULL | Original purchase cost |
| warranty_expiry_date | DATE | NULL | Warranty expiration |
| condition_status | ENUM | NOT NULL, DEFAULT 'good' | Condition: excellent/good/fair/poor/damaged |
| operational_status | ENUM | NOT NULL, DEFAULT 'operational' | Status: operational/maintenance/repair/retired/damaged |
| last_maintenance_date | DATE | NULL | Last maintenance date |
| next_maintenance_date | DATE | NULL | Scheduled next maintenance |
| maintenance_interval_days | INT | DEFAULT 30, CHECK > 0 | Days between maintenance |
| max_weight_capacity | INT | NULL | Weight limit (for applicable equipment) |
| usage_count | INT | DEFAULT 0, CHECK >= 0 | Total usage count |
| responsible_trainer_id | BIGINT UNSIGNED | NULL, FK | Assigned trainer managing equipment |
| created_at | TIMESTAMP | DEFAULT CURRENT_TIMESTAMP | Record creation |
| updated_at | TIMESTAMP | AUTO UPDATE | Record modification |

**Foreign Keys**:
- `area_id` → gym_areas.area_id (RESTRICT DELETE)
- `responsible_trainer_id` → trainers.trainer_id (SET NULL on delete)

**Indexes**:
- `equipment_type` - For equipment filtering
- `operational_status` - For status-based queries
- `next_maintenance_date` - For maintenance scheduling
- Composite: `(area_id, operational_status)` - For area-specific equipment status

---

### Relational Tables (Dependent Entities)

#### **TABLE 8: CERTIFICATIONS**
**Purpose**: Track trainer certifications and qualifications
**Scope**: All trainer certifications and credentials
**Relationship**: N:1 with trainers

| Column | Type | Constraints | Purpose |
|--------|------|-----------|---------|
| certification_id | BIGINT UNSIGNED | PK, AUTO_INCREMENT | Unique certification record ID |
| trainer_id | BIGINT UNSIGNED | NOT NULL, FK | Reference to trainer |
| certification_name | VARCHAR(200) | NOT NULL | Certification title |
| issuing_organization | VARCHAR(200) | NOT NULL | Organization that issued cert |
| issue_date | DATE | NOT NULL, CHECK <= TODAY | Certification issue date |
| expiration_date | DATE | NULL | Expiry date (if applicable) |
| certification_number | VARCHAR(100) | UNIQUE | Official certification number |
| document_url | VARCHAR(500) | NULL | URL to certificate document |
| is_active | BOOLEAN | DEFAULT TRUE | Active status |
| created_at | TIMESTAMP | DEFAULT CURRENT_TIMESTAMP | Record creation |
| updated_at | TIMESTAMP | AUTO UPDATE | Record modification |

**Foreign Keys**:
- `trainer_id` → trainers.trainer_id (CASCADE DELETE)

**Indexes**:
- `trainer_id` - For trainer's certifications
- `certification_name` - For certification type lookup
- `expiration_date` - For expiry monitoring
- `is_active` - For active certifications filtering

---

#### **TABLE 9: CLASS_SCHEDULES**
**Purpose**: Scheduled instances of fitness classes
**Scope**: Individual class sessions
**Relationship**: N:1 with fitness_classes

| Column | Type | Constraints | Purpose |
|--------|------|-----------|---------|
| schedule_id | BIGINT UNSIGNED | PK, AUTO_INCREMENT | Unique schedule instance ID |
| class_id | BIGINT UNSIGNED | NOT NULL, FK | Reference to fitness class |
| day_of_week | ENUM | NULL | Recurring day: monday/tuesday/wednesday/thursday/friday/saturday/sunday |
| start_time | TIME | NOT NULL | Session start time |
| end_time | TIME | NOT NULL, CHECK > start_time | Session end time |
| scheduled_date | DATE | NULL | Specific date for one-time classes |
| is_cancelled | BOOLEAN | DEFAULT FALSE | Cancellation status |
| cancellation_reason | VARCHAR(500) | NULL | Reason for cancellation |
| current_enrollment | INT | DEFAULT 0 | Current participants |
| waiting_list_count | INT | DEFAULT 0 | People on waiting list |
| created_at | TIMESTAMP | DEFAULT CURRENT_TIMESTAMP | Record creation |
| updated_at | TIMESTAMP | AUTO UPDATE | Record modification |

**Foreign Keys**:
- `class_id` → fitness_classes.class_id (CASCADE DELETE)

**Indexes**:
- `class_id` - For class's schedules
- `scheduled_date` - For date-based queries
- `start_time` - For time-based filtering

---

#### **TABLE 10: MEMBERSHIPS**
**Purpose**: Track active and historical member subscriptions
**Scope**: All member memberships
**Relationship**: N:1 with members, N:1 with membership_plans

| Column | Type | Constraints | Purpose |
|--------|------|-----------|---------|
| membership_id | BIGINT UNSIGNED | PK, AUTO_INCREMENT | Unique membership record ID |
| member_id | BIGINT UNSIGNED | NOT NULL, FK | Member reference |
| plan_id | BIGINT UNSIGNED | NOT NULL, FK | Membership plan reference |
| start_date | DATE | NOT NULL | Subscription start |
| end_date | DATE | NOT NULL, CHECK > start_date | Subscription expiration |
| renewal_date | DATE | NULL | Scheduled renewal date |
| status | ENUM | NOT NULL, DEFAULT 'active' | Status: active/expired/cancelled/paused/pending |
| cancellation_reason | VARCHAR(500) | NULL | Reason for cancellation |
| cancelled_by_user | BOOLEAN | DEFAULT FALSE | Self-cancellation flag |
| auto_renewal | BOOLEAN | DEFAULT TRUE | Automatic renewal setting |
| total_price | DECIMAL(10,2) | NOT NULL, CHECK > 0 | Total membership cost |
| amount_paid | DECIMAL(10,2) | DEFAULT 0, CHECK >= 0 | Amount already paid |
| classes_used_this_month | INT | DEFAULT 0, CHECK >= 0 | Current month's class usage |
| created_at | TIMESTAMP | DEFAULT CURRENT_TIMESTAMP | Record creation |
| updated_at | TIMESTAMP | AUTO UPDATE | Record modification |

**Foreign Keys**:
- `member_id` → members.member_id (CASCADE DELETE)
- `plan_id` → membership_plans.plan_id (RESTRICT DELETE)

**Indexes**:
- `member_id` - For member's memberships
- `status` - For status filtering
- `end_date` - For expiry alerts
- `renewal_date` - For renewal scheduling
- Composite: `(status, end_date)` - For active membership queries

---

#### **TABLE 11: MEMBERSHIP_UPGRADES**
**Purpose**: Historical record of membership plan changes
**Scope**: Track all membership modifications
**Relationship**: N:1 with memberships, N:1 with membership_plans (old & new)

| Column | Type | Constraints | Purpose |
|--------|------|-----------|---------|
| upgrade_id | BIGINT UNSIGNED | PK, AUTO_INCREMENT | Unique upgrade record ID |
| membership_id | BIGINT UNSIGNED | NOT NULL, FK | Membership being upgraded |
| old_plan_id | BIGINT UNSIGNED | NOT NULL, FK | Previous plan |
| new_plan_id | BIGINT UNSIGNED | NOT NULL, FK, CHECK != old_plan_id | New plan |
| upgrade_date | TIMESTAMP | NOT NULL, DEFAULT NOW | When upgrade occurred |
| old_price_monthly | DECIMAL(10,2) | NOT NULL | Old plan's monthly price |
| new_price_monthly | DECIMAL(10,2) | NOT NULL | New plan's monthly price |
| price_difference | DECIMAL(10,2) | NOT NULL | Price change |
| adjustment_amount | DECIMAL(10,2) | NULL | Prorated credit/charge |
| upgrade_type | ENUM | NOT NULL | Type: upgrade/downgrade/lateral |
| created_at | TIMESTAMP | DEFAULT CURRENT_TIMESTAMP | Record creation |

**Foreign Keys**:
- `membership_id` → memberships.membership_id (CASCADE DELETE)
- `old_plan_id` → membership_plans.plan_id (RESTRICT DELETE)
- `new_plan_id` → membership_plans.plan_id (RESTRICT DELETE)

**Indexes**:
- `membership_id` - For membership's upgrade history
- `upgrade_date` - For timeline analysis
- `upgrade_type` - For upgrade type filtering

---

#### **TABLE 12: PAYMENTS**
**Purpose**: Financial transaction records
**Scope**: All payments and revenue tracking
**Relationship**: N:1 with members, N:1 with memberships

| Column | Type | Constraints | Purpose |
|--------|------|-----------|---------|
| payment_id | BIGINT UNSIGNED | PK, AUTO_INCREMENT | Unique payment record ID |
| member_id | BIGINT UNSIGNED | NOT NULL, FK | Member who made payment |
| membership_id | BIGINT UNSIGNED | NULL, FK | Related membership (if applicable) |
| payment_type | ENUM | NOT NULL | Type: membership_fee/renewal_fee/class_fee/training_session/other |
| amount | DECIMAL(10,2) | NOT NULL, CHECK > 0 | Payment amount |
| payment_method | ENUM | NOT NULL | Method: credit_card/debit_card/bank_transfer/cash/digital_wallet |
| payment_status | ENUM | NOT NULL, DEFAULT 'completed' | Status: pending/completed/failed/refunded/cancelled |
| transaction_id | VARCHAR(255) | UNIQUE | External transaction reference |
| reference_number | VARCHAR(100) | UNIQUE | Internal payment reference |
| payment_date | TIMESTAMP | NOT NULL | When payment was made |
| due_date | DATE | NULL | When payment was due |
| refund_date | TIMESTAMP | NULL | When refund was processed |
| refund_amount | DECIMAL(10,2) | NULL | Amount refunded |
| refund_reason | VARCHAR(500) | NULL | Reason for refund |
| notes | TEXT | NULL | Payment notes |
| created_at | TIMESTAMP | DEFAULT CURRENT_TIMESTAMP | Record creation |
| updated_at | TIMESTAMP | AUTO UPDATE | Record modification |

**Foreign Keys**:
- `member_id` → members.member_id (CASCADE DELETE)
- `membership_id` → memberships.membership_id (SET NULL on delete)

**Indexes**:
- `member_id` - For member's payment history
- `payment_date` - For payment timeline
- `payment_status` - For status filtering
- `payment_type` - For transaction type analysis
- Composite: `(member_id, payment_date)` - For member's payment history

---

### Junction Tables (Many-to-Many)

#### **TABLE 13: CLASS_ENROLLMENTS**
**Purpose**: Track member enrollment in class schedules
**Scope**: Resolve M:N relationship between members and class_schedules
**Type**: Junction Table

| Column | Type | Constraints | Purpose |
|--------|------|-----------|---------|
| enrollment_id | BIGINT UNSIGNED | PK, AUTO_INCREMENT | Unique enrollment record ID |
| member_id | BIGINT UNSIGNED | NOT NULL, FK | Member enrolling |
| schedule_id | BIGINT UNSIGNED | NOT NULL, FK | Class schedule instance |
| enrollment_date | TIMESTAMP | NOT NULL | When member enrolled |
| enrollment_status | ENUM | NOT NULL, DEFAULT 'enrolled' | Status: enrolled/waiting_list/cancelled/completed |
| enrolled_by_trainer | BOOLEAN | DEFAULT FALSE | Trainer-initiated enrollment |
| cancellation_reason | VARCHAR(500) | NULL | Reason for cancellation |
| cancelled_date | TIMESTAMP | NULL | When cancellation occurred |
| created_at | TIMESTAMP | DEFAULT CURRENT_TIMESTAMP | Record creation |
| updated_at | TIMESTAMP | AUTO UPDATE | Record modification |

**Foreign Keys**:
- `member_id` → members.member_id (CASCADE DELETE)
- `schedule_id` → class_schedules.schedule_id (CASCADE DELETE)

**Indexes**:
- `member_id` - For member's class enrollments
- `schedule_id` - For schedule's enrollments
- Unique: `(member_id, schedule_id)` - Prevent duplicate enrollments
- `enrollment_status` - For enrollment filtering

**Constraint Logic**:
```sql
-- Cancellation data must either both exist or both be NULL
CHECK ((cancellation_reason IS NULL AND cancelled_date IS NULL) OR 
       (cancellation_reason IS NOT NULL AND cancelled_date IS NOT NULL))
```

---

#### **TABLE 14: CLASS_EQUIPMENT_ACCESS**
**Purpose**: Track which equipment is used/available in each class
**Scope**: Resolve M:N relationship between fitness_classes and equipment
**Type**: Junction Table

| Column | Type | Constraints | Purpose |
|--------|------|-----------|---------|
| access_id | BIGINT UNSIGNED | PK, AUTO_INCREMENT | Unique access record ID |
| class_id | BIGINT UNSIGNED | NOT NULL, FK | Fitness class |
| equipment_id | BIGINT UNSIGNED | NOT NULL, FK | Equipment used/available |
| equipment_required | BOOLEAN | DEFAULT FALSE | Whether equipment is required |
| quantity_needed | INT | DEFAULT 1, CHECK > 0 | Number of units needed |
| created_at | TIMESTAMP | DEFAULT CURRENT_TIMESTAMP | Record creation |

**Foreign Keys**:
- `class_id` → fitness_classes.class_id (CASCADE DELETE)
- `equipment_id` → equipment.equipment_id (CASCADE DELETE)

**Indexes**:
- `equipment_id` - For equipment's class assignments
- Unique: `(class_id, equipment_id)` - Prevent duplicate assignments

---

#### **TABLE 15: ATTENDANCE**
**Purpose**: Track member attendance in class sessions
**Scope**: Attendance records for monitoring participation
**Relationship**: N:1 with members, N:1 with class_schedules

| Column | Type | Constraints | Purpose |
|--------|------|-----------|---------|
| attendance_id | BIGINT UNSIGNED | PK, AUTO_INCREMENT | Unique attendance record ID |
| member_id | BIGINT UNSIGNED | NOT NULL, FK | Member attending |
| schedule_id | BIGINT UNSIGNED | NOT NULL, FK | Class schedule attended |
| check_in_time | TIMESTAMP | NOT NULL | When member arrived |
| check_out_time | TIMESTAMP | NULL | When member left |
| attendance_status | ENUM | NOT NULL, DEFAULT 'present' | Status: present/absent/late/cancelled |
| duration_minutes | INT | NULL, CHECK > 0 | Time spent in class |
| marked_by_trainer | BOOLEAN | DEFAULT FALSE | Trainer-marked attendance |
| notes | TEXT | NULL | Attendance notes |
| created_at | TIMESTAMP | DEFAULT CURRENT_TIMESTAMP | Record creation |
| updated_at | TIMESTAMP | AUTO UPDATE | Record modification |

**Foreign Keys**:
- `member_id` → members.member_id (CASCADE DELETE)
- `schedule_id` → class_schedules.schedule_id (CASCADE DELETE)

**Indexes**:
- `member_id` - For member's attendance history
- `check_in_time` - For attendance timeline
- `attendance_status` - For status-based analysis
- Unique: `(member_id, schedule_id)` - One attendance per member per session
- Composite: `(member_id, check_in_time)` - For member's attendance timeline

---

#### **TABLE 16: EQUIPMENT_USAGE**
**Purpose**: Log equipment usage and maintenance events
**Scope**: Track when equipment is used or serviced
**Relationship**: N:1 with equipment, N:1 with members (optional)

| Column | Type | Constraints | Purpose |
|--------|------|-----------|---------|
| usage_id | BIGINT UNSIGNED | PK, AUTO_INCREMENT | Unique usage record ID |
| equipment_id | BIGINT UNSIGNED | NOT NULL, FK | Equipment used |
| member_id | BIGINT UNSIGNED | NULL, FK | Member using (optional for maintenance) |
| usage_type | ENUM | NOT NULL | Type: training/maintenance/repair/inspection/cleaning |
| start_time | TIMESTAMP | NOT NULL | When equipment use started |
| end_time | TIMESTAMP | NULL | When equipment use ended |
| duration_minutes | INT | NULL, CHECK > 0 | Duration of use |
| notes | TEXT | NULL | Usage notes |
| usage_status | ENUM | NOT NULL, DEFAULT 'in_progress' | Status: in_progress/completed/cancelled |
| created_at | TIMESTAMP | DEFAULT CURRENT_TIMESTAMP | Record creation |
| updated_at | TIMESTAMP | AUTO UPDATE | Record modification |

**Foreign Keys**:
- `equipment_id` → equipment.equipment_id (CASCADE DELETE)
- `member_id` → members.member_id (SET NULL on delete)

**Indexes**:
- `equipment_id` - For equipment's usage history
- `start_time` - For timeline queries
- `usage_type` - For usage type filtering

---

#### **TABLE 17: MAINTENANCE_LOGS**
**Purpose**: Detailed equipment maintenance history
**Scope**: Track all maintenance activities
**Relationship**: N:1 with equipment, N:1 with trainers

| Column | Type | Constraints | Purpose |
|--------|------|-----------|---------|
| maintenance_id | BIGINT UNSIGNED | PK, AUTO_INCREMENT | Unique maintenance record ID |
| equipment_id | BIGINT UNSIGNED | NOT NULL, FK | Equipment serviced |
| trainer_id | BIGINT UNSIGNED | NULL, FK | Trainer performing maintenance |
| maintenance_type | ENUM | NOT NULL | Type: preventive/corrective/emergency/inspection |
| maintenance_description | TEXT | NOT NULL | What was done |
| parts_replaced | TEXT | NULL | Parts replaced |
| maintenance_cost | DECIMAL(10,2) | NULL, CHECK > 0 | Cost of maintenance |
| maintenance_date | DATE | NOT NULL, CHECK <= TODAY | When maintenance occurred |
| next_scheduled_date | DATE | NULL | Next scheduled maintenance |
| completion_status | ENUM | NOT NULL, DEFAULT 'pending' | Status: pending/in_progress/completed/cancelled |
| notes | TEXT | NULL | Additional notes |
| created_at | TIMESTAMP | DEFAULT CURRENT_TIMESTAMP | Record creation |
| updated_at | TIMESTAMP | AUTO UPDATE | Record modification |

**Foreign Keys**:
- `equipment_id` → equipment.equipment_id (CASCADE DELETE)
- `trainer_id` → trainers.trainer_id (SET NULL on delete)

**Indexes**:
- `equipment_id` - For equipment's maintenance history
- `maintenance_date` - For timeline analysis
- `maintenance_type` - For maintenance type filtering
- `completion_status` - For status-based queries

---

## Relationships & Constraints

### Foreign Key Cascade Rules

#### **CASCADE DELETE**
Used when child records should be deleted when parent is deleted:
- users → members (1:1)
- users → trainers (1:1)
- members → memberships (1:N)
- memberships → membership_upgrades (1:N)
- trainers → certifications (1:N)
- fitness_classes → class_schedules (1:N)
- class_schedules → class_enrollments (N:M)
- class_schedules → attendance (1:N)
- members → class_enrollments (N:1)
- members → attendance (1:N)
- members → payments (1:N)
- members → equipment_usage (1:N)
- equipment → equipment_usage (1:N)
- equipment → maintenance_logs (1:N)

#### **RESTRICT DELETE**
Used when parent cannot be deleted if child records exist:
- membership_plans → memberships (cannot delete if members using)
- membership_plans → membership_upgrades (cannot delete if historical records exist)
- trainers → fitness_classes (cannot delete if conducting classes)
- gym_areas → fitness_classes (cannot delete if area has active classes)
- gym_areas → equipment (cannot delete if equipment located there)

#### **SET NULL**
Used when parent deletion should only nullify references:
- trainers (trainer_id) → members (personal trainer assignment)
- trainers (responsible_trainer_id) → equipment (equipment manager)
- members (member_id) → equipment_usage (member using equipment)
- trainers (trainer_id) → maintenance_logs (trainer performing maintenance)
- memberships (membership_id) → payments (payment reference)

### Data Integrity Constraints

#### **CHECK Constraints** (Sample)
```sql
-- Pricing validation
CHECK (price > 0)
CHECK (amount_paid >= 0 AND amount_paid <= total_price)

-- Date validation
CHECK (start_date < end_date)
CHECK (issue_date <= CURDATE())
CHECK (CHECK_IN_TIME < CHECK_OUT_TIME)

-- Range validation
CHECK (current_enrollment >= 0 AND current_enrollment <= max_capacity)
CHECK (years_of_experience >= 0)
CHECK (maintenance_interval_days > 0)

-- Business logic validation
CHECK ((refund_amount IS NULL AND refund_date IS NULL) OR 
       (refund_amount IS NOT NULL AND refund_date IS NOT NULL))
```

#### **UNIQUE Constraints**
```sql
user_id UNIQUE          -- One user record per user
email UNIQUE            -- No duplicate emails
phone_number UNIQUE     -- No duplicate phone numbers
plan_name UNIQUE        -- Plan names are unique
serial_number UNIQUE    -- Equipment serial numbers unique
certification_number UNIQUE  -- Cert numbers unique
transaction_id UNIQUE   -- Payment transaction IDs unique
reference_number UNIQUE -- Payment reference numbers unique

-- Composite unique constraint
UNIQUE KEY uk_member_schedule (member_id, schedule_id)  -- One enrollment per session
UNIQUE KEY uk_class_equipment (class_id, equipment_id)  -- Prevent duplicate equipment assignments
```

---

## Indexes & Performance

### Indexing Strategy

#### **1. Primary Key Indexes (Automatic)**
- All `xxxxx_id` columns are automatically covered

#### **2. Foreign Key Indexes (Required)**
MySQL automatically creates indexes on all FK columns for:
- Join performance
- Referential integrity checks

#### **3. Search Indexes**
Columns frequently used in WHERE clauses:

| Index | Table | Purpose |
|-------|-------|---------|
| `idx_email` | users | Login queries |
| `idx_user_type` | users | Role-based filtering |
| `idx_member_id` | members | Member lookups |
| `idx_trainer_id` | trainers | Trainer lookups |
| `idx_plan_id` | membership_plans | Plan queries |
| `idx_status` | (multiple) | Status-based filtering |
| `idx_category` | fitness_classes | Class type filtering |
| `idx_equipment_type` | equipment | Equipment filtering |
| `idx_operational_status` | equipment | Equipment availability |
| `idx_payment_type` | payments | Payment categorization |

#### **4. Composite (Multi-Column) Indexes**
For common compound queries:

| Index | Columns | Query | Benefit |
|-------|---------|-------|---------|
| `idx_membership_status_date` | memberships(status, end_date) | Active memberships expiring soon | Covers entire query |
| `idx_attendance_member_date` | attendance(member_id, check_in_time) | Member attendance history | Covers entire query |
| `idx_payment_member_date` | payments(member_id, payment_date) | Payment history (chrono order) | Covers entire query |
| `idx_class_trainer_status` | fitness_classes(trainer_id, status) | Trainer's active classes | Covers entire query |
| `idx_equipment_area_status` | equipment(area_id, operational_status) | Operational equipment by area | Covers entire query |

#### **5. Date Range Indexes**
For common temporal queries:

```sql
CREATE INDEX idx_created_at ON users(created_at);          -- Time-based user queries
CREATE INDEX idx_start_date ON memberships(start_date);    -- Date range queries
CREATE INDEX idx_end_date ON memberships(end_date);        -- Expiry date queries
CREATE INDEX idx_scheduled_date ON class_schedules(scheduled_date);
CREATE INDEX idx_check_in_time ON attendance(check_in_time);
CREATE INDEX idx_next_maintenance ON equipment(next_maintenance_date);
```

### Performance Optimization Techniques

#### **1. Index Covering** (Query covers all needed data from index)
```sql
-- Query
SELECT member_id, status, end_date 
FROM memberships 
WHERE status = 'active' AND end_date < DATE_ADD(CURDATE(), INTERVAL 30 DAY);

-- Covering index
CREATE INDEX idx_membership_status_date ON memberships(status, end_date, member_id);
-- Query uses only index, no table access needed!
```

#### **2. Avoiding Full Table Scans**
```sql
-- GOOD: Indexed search
SELECT * FROM users WHERE email = 'user@example.com';  -- Uses idx_email

-- BAD: Full table scan
SELECT * FROM users WHERE SUBSTRING(email, 1, 5) = 'user@';  -- Cannot use index
```

#### **3. Join Optimization**
```sql
-- All FK columns have automatic indexes
-- Ensures efficient joins:
SELECT m.*, u.first_name 
FROM members m 
JOIN users u ON m.user_id = u.user_id;  -- Fast due to FK index
```

#### **4. Avoiding N+1 Queries**
```sql
-- GOOD: Single query with JOIN
SELECT m.*, COUNT(a.attendance_id) as attendance_count
FROM members m
LEFT JOIN attendance a ON m.member_id = a.member_id
GROUP BY m.member_id;

-- BAD: N+1 (multiple queries)
SELECT * FROM members;  -- Query 1
-- Then for each member:
SELECT * FROM attendance WHERE member_id = ?;  -- Query 2, 3, 4, ...
```

---

## Design Decisions

### 1. **Single USER Table vs. Multiple User Type Tables**

**Decision**: ✅ Single USERS table with user_type enum

**Rationale**:
- Common authentication needs (email, password, login tracking)
- Simplified permission system
- Single login credential store
- Better GDPR compliance (centralized user tracking)

**Alternative Considered** ❌:
Separate tables (MEMBERS, TRAINERS, ADMINS) with user fields duplicated
- Problem: Duplicate user credentials
- Problem: Difficult to validate email uniqueness across types
- Problem: Complex authentication logic

---

### 2. **CASCADE DELETE vs. RESTRICT DELETE**

**Decisions**:
- **CASCADE**: Used for hierarchical owned relationships (User → Member, Classes → Schedules)
- **RESTRICT**: Used for shared resource relationships (Plan → Membership, Area → Equipment)

**Rationale**:
- CASCADE appropriate when child is "owned" by parent
- RESTRICT protects historical/shared data integrity
- Prevents accidental data loss of important records

---

### 3. **ENROLLMENT Status vs. Separate Cancellation Recording**

**Decision**: ✅ Single status field with optional cancellation reason

**Advantages**:
- Status enum prevents invalid state combinations
- Cancellation data included in same record
- Simpler queries (no joins for cancellation data)

```sql
-- Good design
enrollment_status ENUM('enrolled', 'waiting_list', 'cancelled', 'completed')
cancellation_reason VARCHAR(500)
cancelled_date TIMESTAMP

-- Can query:
SELECT * FROM enrollments WHERE enrollment_status = 'cancelled' 
  AND cancelled_date > DATE_SUB(CURDATE(), INTERVAL 30 DAY);
```

---

### 4. **Equipment Maintenance as Separate Table vs. Field**

**Decision**: ✅ Separate MAINTENANCE_LOGS table (N:1 with equipment)

**Advantages**:
- Full audit trail of maintenance history
- Ability to track maintenance patterns over time
- Support for scheduled maintenance dates
- Easy to query maintenance statistics

**Alternative Considered** ❌:
Store last_maintenance_date in EQUIPMENT table
- Problem: Loses historical data
- Problem: Cannot track multiple maintenance events
- Problem: No schedule prediction capability

---

### 5. **Attendance as Separate Table vs. CLASS_ENROLLMENTS Field**

**Decision**: ✅ Separate ATTENDANCE table (1:1 with class instance)

**Advantages**:
- Tracks actual attendance vs. enrollment intent
- Separates "intent to attend" from "actually attended"
- Supports check-in/check-out times
- Enables time tracking and statistics

```
Relationship:
CLASS_ENROLLMENTS → Member can enroll in class
ATTENDANCE       → Member actually attended (with times)
```

---

### 6. **PAYMENT vs. TRANSACTION Terminology**

**Decision**: ✅ PAYMENTS table (not TRANSACTIONS)

**Rationale**:
- Domain-specific (gym context)
- Clearer intent (payment vs. transaction could be ambiguous)
- Includes business context (payment_type, membership_id)
- Better for reporting and auditing

---

### 7. **Enum vs. Lookup Tables for Status/Types**

**Decision**: ✅ Enum for relatively static values (status, role, category)

**Rationale**:
- Application defines possible values
- No performance difference in MySQL 8.0+
- Simpler schema (No lookup table joins)
- Type safety in application code

```sql
-- Enums used for:
- user_type (member/trainer/admin/manager)
- account_status (active/inactive/suspended/banned)
- payment_status (pending/completed/failed/refunded)
- equipment_status (operational/maintenance/repair/retired)

-- This can change, user needs to update application
-- But these are rarely added/removed
```

---

### 8. **Self-Referencing vs. Separate Trainer Field in Members**

**Decision**: ✅ Separate trainer_id field in MEMBERS table (N:1)

**Advantages**:
- Clear personal trainer assignment
- Easy to query members of a trainer
- Single FK relationship

**Alternative Considered** ❌:
MEMBER_TRAINER table (N:M)
- Problem: Gym typically assigns one primary trainer
- Problem: Overcomplicates common queries
- Better approach: MEMBER_TRAINER could be added later if gym supports multiple trainers

---

### 9. **Soft Delete (deleted_at) vs. Hard Delete**

**Decision**: ✅ Soft delete on USERS table

**Rationale**:
- GDPR compliance (audit trail of user existence)
- Maintain referential integrity for historical records
- Can reactivate deleted accounts
- Preserves financial/attendance history

**Applied To**:
- users table only (soft delete via deleted_at)
- Other tables: CASCADE/RESTRICT as documented

---

### 10. **Normalization Level Decision: Stop at 3NF (not BCNF)**

**Decision**: ✅ Third Normal Form (3NF)

**Why not higher?**
- Boyce-Codd Normal Form (BCNF) adds unnecessary complexity
- Additional normalization would require more tables
- Performance trade-off for minimal benefit
- 3NF eliminates > 99% of practical anomalies

**3NF satisfies our goals**:
- No redundancy
- Data integrity
- Acceptable performance
- Maintainability

---

## Data Flow

### Typical User Journeys & Related Data Flow

#### **Journey 1: New Member Registration**

```
1. User creates account in USERS table
   ├─ email, phone_number, password_hash
   ├─ user_type = 'member'
   └─ account_status = 'active'

2. System creates MEMBERS record
   ├─ Links to user via user_id FK
   ├─ Initializes: date_of_birth, emergency_contact
   └─ membership_start_date = TODAY

3. Member chooses membership plan
   └─ Reads from MEMBERSHIP_PLANS table

4. System creates MEMBERSHIPS record
   ├─ member_id (FK) → MEMBERS
   ├─ plan_id (FK) → MEMBERSHIP_PLANS
   ├─ start_date = TODAY
   ├─ end_date = TODAY + (duration_months)
   └─ status = 'active'

5. Payment processed
   └─ Creates PAYMENTS record
       ├─ member_id (FK)
       ├─ membership_id (FK)
       ├─ amount = plan.price
       └─ status = 'completed'
```

#### **Journey 2: Member Enrolls in Class**

```
1. Member views available classes
   └─ Query FITNESS_CLASSES with status = 'active'

2. System shows class schedules
   └─ Query CLASS_SCHEDULES for specific class_id

3. Member enrolls in class session
   └─ Creates CLASS_ENROLLMENTS record
       ├─ member_id (FK)
       ├─ schedule_id (FK)
       ├─ enrollment_status = 'enrolled'
       └─ enrollment_date = NOW

4. Member attends class (check-in)
   └─ Creates ATTENDANCE record
       ├─ member_id (FK)
       ├─ schedule_id (FK)
       ├─ check_in_time = NOW
       └─ attendance_status = 'present'

5. Member leaves class (check-out)
   └─ Updates ATTENDANCE record
       ├─ check_out_time = NOW
       ├─ duration_minutes = check_out - check_in
       └─ attendance_status = 'present'
```

#### **Journey 3: Equipment Maintenance Scheduling**

```
1. System detects equipment maintenance due
   └─ Query EQUIPMENT where next_maintenance_date <= TODAY + 7 days

2. Manager schedules maintenance
   └─ Creates MAINTENANCE_LOGS record
       ├─ equipment_id (FK)
       ├─ trainer_id (FK) → assigned trainer
       ├─ maintenance_type = 'preventive'
       └─ completion_status = 'pending'

3. Trainer performs maintenance
   └─ Updates MAINTENANCE_LOGS
       ├─ completion_status = 'completed'
       ├─ maintenance_date = TODAY
       └─ next_scheduled_date = TODAY + interval

4. System updates equipment status
   └─ Updates EQUIPMENT
       ├─ operational_status = 'operational'
       ├─ last_maintenance_date = TODAY
       └─ next_maintenance_date = calculated date
```

---

## Implementation Guide

### Step 1: Create Database
```sql
CREATE DATABASE gym_management 
CHARACTER SET utf8mb4 
COLLATE utf8mb4_unicode_ci;
USE gym_management;
```

### Step 2: Execute Schema File
```bash
mysql -u root -p gym_management < schema.sql
```

### Step 3: Verify Tables Created
```sql
SHOW TABLES;  -- Should display 18 tables
DESCRIBE users;  -- Verify table structure
```

### Step 4: Test Relationships
```sql
-- Insert a user
INSERT INTO users (email, phone_number, password_hash, first_name, last_name, user_type) 
VALUES ('john@example.com', '555-1234', UNHEX(SHA2('password123', 256)), 'John', 'Doe', 'member');

-- Insert related member
INSERT INTO members (user_id, date_of_birth, gender, membership_start_date) 
VALUES (LAST_INSERT_ID(), '1990-05-15', 'male', CURDATE());

-- Query joined data
SELECT u.first_name, u.email, m.date_of_birth 
FROM users u 
JOIN members m ON u.user_id = m.user_id 
WHERE u.user_id = 1;
```

### Step 5: Test Referential Integrity
```sql
-- This should fail (FK violation)
INSERT INTO members (user_id, ...) VALUES (9999, ...);  -- ❌ user_id 9999 doesn't exist

-- This should succeed (valid FK)
INSERT INTO members (user_id, ...) 
SELECT user_id FROM users WHERE user_type = 'member' LIMIT 1;  -- ✅
```

### Step 6: Verify Constraints
```sql
-- Check all tables
SELECT TABLE_NAME, COLUMN_NAME, COLUMN_KEY, CONSTRAINT_NAME, REFERENCED_TABLE_NAME
FROM INFORMATION_SCHEMA.KEY_COLUMN_USAGE
WHERE TABLE_SCHEMA = 'gym_management'
ORDER BY TABLE_NAME;

-- Check indexes
SELECT TABLE_NAME, INDEX_NAME, COLUMN_NAME, SEQ_IN_INDEX
FROM INFORMATION_SCHEMA.STATISTICS
WHERE TABLE_SCHEMA = 'gym_management'
ORDER BY TABLE_NAME, INDEX_NAME, SEQ_IN_INDEX;
```

---

## Conclusion

This database schema provides:
- ✅ **Complete normalization** (3NF) eliminating redundancy
- ✅ **Full referential integrity** with proper FK relationships
- ✅ **Data validation** through check constraints and enums
- ✅ **Performance optimization** with strategic indexing
- ✅ **Scalability** supporting 1000+ members, trainers, classes
- ✅ **Auditability** with timestamps and soft deletes
- ✅ **Business logic support** through appropriate constraints

The schema is production-ready and supports all requirements of the Gym Management System.

---

**Document Version**: 1.0  
**Last Updated**: March 14, 2024  
**Status**: ✅ Complete & Production-Ready
