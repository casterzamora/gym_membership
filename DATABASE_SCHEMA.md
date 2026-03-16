# Database Schema Design & ER Diagram

## System Architecture Overview

```
┌─────────────────────────────────────────────────────────────┐
│                    Gym Management System                    │
├─────────────────────────────────────────────────────────────┤
│                   Frontend Layer                            │
│              (Web App / Mobile App)                         │
├─────────────────────────────────────────────────────────────┤
│                   API Layer (RESTful)                       │
│        (Laravel Routes & Controllers)                      │
├─────────────────────────────────────────────────────────────┤
│              Business Logic Layer                           │
│        (Services & Domain Logic)                           │
├─────────────────────────────────────────────────────────────┤
│              Data Access Layer                             │
│        (Models & Repositories)                            │
├─────────────────────────────────────────────────────────────┤
│          Database Layer (MySQL)                            │
│    (Tables, Views, Stored Procedures)                     │
└─────────────────────────────────────────────────────────────┘
```

## Database Schema - Core Entities

### Entity-Relationship Overview

**Primary Entities:**
1. Users (Core authentication)
2. Members (Member-specific data)
3. Trainers (Trainer-specific data)
4. Membership Plans (Subscription plans)
5. Memberships (Active subscriptions)
6. Classes (Fitness classes)
7. Class Enrollments (Member-Class mapping)
8. Attendance (Class attendance records)
9. Equipment (Gym equipment)
10. Equipment Usage (Equipment usage tracking)
11. Payments (Payment transactions)
12. Areas (Physical gym areas)

### Entity Definitions

#### 1. USERS Table
```
Purpose: Core authentication and authorization
Relationships: 1-to-many with Members, Trainers, Payments
```

| Column | Type | Constraint | Description |
|--------|------|-----------|-------------|
| id | BIGINT | PK, AI | Unique user identifier |
| name | VARCHAR(255) | NOT NULL | User full name |
| email | VARCHAR(255) | UNIQUE, NOT NULL | Email address |
| email_verified_at | TIMESTAMP | NULL | Email verification timestamp |
| phone | VARCHAR(20) | UNIQUE, NOT NULL | Phone number |
| password | VARCHAR(255) | NOT NULL | Hashed password |
| role | ENUM | NOT NULL | admin/manager/trainer/member |
| status | ENUM | DEFAULT active | active/inactive/suspended |
| last_login_at | TIMESTAMP | NULL | Last login timestamp |
| created_at | TIMESTAMP | DEFAULT CURRENT | Creation timestamp |
| updated_at | TIMESTAMP | ON UPDATE | Last update timestamp |
| deleted_at | TIMESTAMP | NULL | Soft delete timestamp |

---

#### 2. MEMBERS Table
```
Purpose: Extended member information
Relationships: 1-to-many with Memberships, Attendance, Class_Enrollments, Payments
```

| Column | Type | Constraint | Description |
|--------|------|-----------|-------------|
| id | BIGINT | PK, AI | Unique member identifier |
| user_id | BIGINT | FK USERS, UNIQUE | Reference to users table |
| date_of_birth | DATE | NOT NULL | Date of birth |
| gender | ENUM | NOT NULL | male/female/other |
| address | VARCHAR(500) | | Home address |
| city | VARCHAR(100) | | City |
| state | VARCHAR(100) | | State/Province |
| postal_code | VARCHAR(20) | | Postal code |
| country | VARCHAR(100) | | Country |
| emergency_contact | VARCHAR(255) | | Emergency contact name |
| emergency_phone | VARCHAR(20) | | Emergency contact phone |
| medical_conditions | TEXT | | Medical allergies/conditions |
| member_since | DATE | NOT NULL | Membership start date |
| profile_photo | VARCHAR(255) | | Profile image URL |
| created_at | TIMESTAMP | DEFAULT CURRENT | Creation timestamp |
| updated_at | TIMESTAMP | ON UPDATE | Last update timestamp |

---

#### 3. TRAINERS Table
```
Purpose: Trainer-specific information
Relationships: 1-to-many with Classes
```

| Column | Type | Constraint | Description |
|--------|------|-----------|-------------|
| id | BIGINT | PK, AI | Unique trainer identifier |
| user_id | BIGINT | FK USERS, UNIQUE | Reference to users table |
| specialization | VARCHAR(255) | NOT NULL | Area of specialization |
| certification | VARCHAR(255) | NOT NULL | Certification details |
| certification_expiry | DATE | NOT NULL | Certification expiry date |
| years_experience | INT | | Years of experience |
| hourly_rate | DECIMAL(10,2) | | Training rate per hour |
| bio | TEXT | | Trainer biography |
| is_available | BOOLEAN | DEFAULT true | Availability status |
| created_at | TIMESTAMP | DEFAULT CURRENT | Creation timestamp |
| updated_at | TIMESTAMP | ON UPDATE | Last update timestamp |

---

#### 4. AREAS Table
```
Purpose: Physical zones within the gym
Relationships: 1-to-many with Equipment, Classes
```

| Column | Type | Constraint | Description |
|--------|------|-----------|-------------|
| id | BIGINT | PK, AI | Unique area identifier |
| name | VARCHAR(255) | NOT NULL | Area name |
| description | TEXT | | Area description |
| capacity | INT | | Max capacity |
| created_at | TIMESTAMP | DEFAULT CURRENT | Creation timestamp |
| updated_at | TIMESTAMP | ON UPDATE | Last update timestamp |

---

#### 5. MEMBERSHIP_PLANS Table
```
Purpose: Predefined membership subscription tiers
Relationships: 1-to-many with Memberships
```

| Column | Type | Constraint | Description |
|--------|------|-----------|-------------|
| id | BIGINT | PK, AI | Unique plan identifier |
| name | VARCHAR(255) | NOT NULL, UNIQUE | Plan name (e.g., Basic, Premium) |
| description | TEXT | | Plan description |
| price | DECIMAL(10,2) | NOT NULL | Monthly/yearly price |
| duration_months | INT | NOT NULL | Duration in months |
| max_classes_per_month | INT | | Max classes allowed (-1 = unlimited) |
| features | JSON | | Plan features as JSON array |
| is_active | BOOLEAN | DEFAULT true | Plan active status |
| created_at | TIMESTAMP | DEFAULT CURRENT | Creation timestamp |
| updated_at | TIMESTAMP | ON UPDATE | Last update timestamp |

---

#### 6. MEMBERSHIPS Table
```
Purpose: Active member subscriptions
Relationships: Many-to-many bridge with Members and Plans
```

| Column | Type | Constraint | Description |
|--------|------|-----------|-------------|
| id | BIGINT | PK, AI | Unique membership identifier |
| member_id | BIGINT | FK MEMBERS, NOT NULL | Reference to member |
| plan_id | BIGINT | FK MEMBERSHIP_PLANS, NOT NULL | Reference to plan |
| status | ENUM | NOT NULL | active/expired/cancelled/paused |
| start_date | DATE | NOT NULL | Subscription start date |
| end_date | DATE | NOT NULL | Subscription end date |
| auto_renew | BOOLEAN | DEFAULT true | Auto-renewal enabled |
| renewal_date | DATE | | Next renewal date |
| classes_used_this_month | INT | DEFAULT 0 | Classes used in current month |
| created_at | TIMESTAMP | DEFAULT CURRENT | Creation timestamp |
| updated_at | TIMESTAMP | ON UPDATE | Last update timestamp |

---

#### 7. CLASSES Table
```
Purpose: Fitness classes offered by gym
Relationships: Many-to-one with Trainers, Areas; 1-to-many with Class_Enrollments
```

| Column | Type | Constraint | Description |
|--------|------|-----------|-------------|
| id | BIGINT | PK, AI | Unique class identifier |
| name | VARCHAR(255) | NOT NULL | Class name |
| description | TEXT | | Class description |
| trainer_id | BIGINT | FK TRAINERS, NOT NULL | Assigned trainer ID |
| area_id | BIGINT | FK AREAS, NOT NULL | Physical location |
| category | VARCHAR(100) | NOT NULL | Yoga, Pilates, Boxing, etc. |
| capacity | INT | NOT NULL | Maximum participants |
| duration_minutes | INT | NOT NULL | Class duration |
| difficulty_level | ENUM | DEFAULT beginner | beginner/intermediate/advanced |
| schedule_type | ENUM | DEFAULT recurring | recurring/one-time |
| status | ENUM | DEFAULT active | active/cancelled/suspended |
| created_at | TIMESTAMP | DEFAULT CURRENT | Creation timestamp |
| updated_at | TIMESTAMP | ON UPDATE | Last update timestamp |

---

#### 8. CLASS_SCHEDULES Table
```
Purpose: Schedule instances for classes
Relationships: Many-to-one with Classes
```

| Column | Type | Constraint | Description |
|--------|------|-----------|-------------|
| id | BIGINT | PK, AI | Unique schedule identifier |
| class_id | BIGINT | FK CLASSES, NOT NULL | Reference to class |
| day_of_week | ENUM | | mon/tue/wed/thu/fri/sat/sun |
| start_time | TIME | NOT NULL | Class start time |
| end_time | TIME | NOT NULL | Class end time |
| scheduled_date | DATE | | Specific date for one-time classes |
| is_cancelled | BOOLEAN | DEFAULT false | Cancellation status |
| cancellation_reason | TEXT | | Reason for cancellation |
| current_enrollment | INT | DEFAULT 0 | Current number of enrollments |
| created_at | TIMESTAMP | DEFAULT CURRENT | Creation timestamp |
| updated_at | TIMESTAMP | ON UPDATE | Last update timestamp |

---

#### 9. CLASS_ENROLLMENTS Table
```
Purpose: Member enrollment in classes
Relationships: Many-to-one with Members and Classes
```

| Column | Type | Constraint | Description |
|--------|------|-----------|-------------|
| id | BIGINT | PK, AI | Unique enrollment identifier |
| member_id | BIGINT | FK MEMBERS, NOT NULL | Member reference |
| class_id | BIGINT | FK CLASSES, NOT NULL | Class reference |
| schedule_id | BIGINT | FK CLASS_SCHEDULES | Schedule reference |
| enrollment_date | TIMESTAMP | NOT NULL | Enrollment timestamp |
| status | ENUM | DEFAULT active | active/completed/cancelled |
| cancellation_reason | VARCHAR(255) | | Reason for cancellation |
| attended | BOOLEAN | DEFAULT false | Attendance status |
| created_at | TIMESTAMP | DEFAULT CURRENT | Creation timestamp |
| updated_at | TIMESTAMP | ON UPDATE | Last update timestamp |
| UNIQUE(member_id, schedule_id) | | | Prevent duplicate enrollments |

---

#### 10. ATTENDANCE Table
```
Purpose: Class attendance tracking
Relationships: Many-to-one with Members and Classes
```

| Column | Type | Constraint | Description |
|--------|------|-----------|-------------|
| id | BIGINT | PK, AI | Unique attendance record |
| member_id | BIGINT | FK MEMBERS, NOT NULL | Member reference |
| class_id | BIGINT | FK CLASSES, NOT NULL | Class reference |
| schedule_id | BIGINT | FK CLASS_SCHEDULES | Schedule reference |
| check_in_time | TIMESTAMP | NOT NULL | Check-in timestamp |
| check_out_time | TIMESTAMP | | Check-out timestamp |
| duration_minutes | INT | | Actual duration attended |
| marked_by_trainer | VARCHAR(255) | | Trainer who marked attendance |
| created_at | TIMESTAMP | DEFAULT CURRENT | Creation timestamp |

---

#### 11. EQUIPMENT Table
```
Purpose: Gym equipment inventory
Relationships: Many-to-one with Areas; 1-to-many with Equipment_Usage
```

| Column | Type | Constraint | Description |
|--------|------|-----------|-------------|
| id | BIGINT | PK, AI | Unique equipment identifier |
| name | VARCHAR(255) | NOT NULL | Equipment name |
| category | VARCHAR(100) | NOT NULL | Category (cardio, strength, etc.) |
| area_id | BIGINT | FK AREAS, NOT NULL | Physical location |
| serial_number | VARCHAR(255) | UNIQUE | Equipment serial number |
| purchase_date | DATE | NOT NULL | Purchase date |
| purchase_cost | DECIMAL(10,2) | | Purchase cost |
| warranty_expiry | DATE | | Warranty expiration date |
| status | ENUM | DEFAULT available | available/maintenance/damaged/retired |
| last_maintenance_date | DATE | | Last maintenance date |
| next_maintenance_date | DATE | | Scheduled next maintenance |
| maintenance_interval_days | INT | DEFAULT 30 | Maintenance frequency |
| condition | ENUM | DEFAULT good | good/fair/poor |
| created_at | TIMESTAMP | DEFAULT CURRENT | Creation timestamp |
| updated_at | TIMESTAMP | ON UPDATE | Last update timestamp |

---

#### 12. EQUIPMENT_USAGE Table
```
Purpose: Track equipment usage by members
Relationships: Many-to-one with Equipment and Members
```

| Column | Type | Constraint | Description |
|--------|------|-----------|-------------|
| id | BIGINT | PK, AI | Unique usage record |
| equipment_id | BIGINT | FK EQUIPMENT, NOT NULL | Equipment reference |
| member_id | BIGINT | FK MEMBERS | Member who used equipment |
| start_time | TIMESTAMP | NOT NULL | Usage start time |
| end_time | TIMESTAMP | | Usage end time |
| duration_minutes | INT | | Duration of usage |
| created_at | TIMESTAMP | DEFAULT CURRENT | Creation timestamp |

---

#### 13. PAYMENTS Table
```
Purpose: Transaction tracking
Relationships: Many-to-one with Members and Memberships
```

| Column | Type | Constraint | Description |
|--------|------|-----------|-------------|
| id | BIGINT | PK, AI | Unique payment identifier |
| member_id | BIGINT | FK MEMBERS, NOT NULL | Member reference |
| membership_id | BIGINT | FK MEMBERSHIPS | Membership reference |
| amount | DECIMAL(10,2) | NOT NULL | Payment amount |
| payment_type | ENUM | NOT NULL | membership/renewal/additional_service |
| payment_method | ENUM | NOT NULL | credit_card/debit_card/bank_transfer/cash |
| payment_status | ENUM | NOT NULL | pending/completed/failed/refunded |
| transaction_id | VARCHAR(255) | UNIQUE | Payment gateway transaction ID |
| payment_date | TIMESTAMP | NOT NULL | Payment timestamp |
| due_date | DATE | | Due date for payment |
| receipt_url | VARCHAR(255) | | Generated receipt URL |
| notes | TEXT | | Additional notes |
| created_at | TIMESTAMP | DEFAULT CURRENT | Creation timestamp |
| updated_at | TIMESTAMP | ON UPDATE | Last update timestamp |

---

#### 14. MAINTENANCE_LOGS Table
```
Purpose: Equipment maintenance records
Relationships: Many-to-one with Equipment
```

| Column | Type | Constraint | Description |
|--------|------|-----------|-------------|
| id | BIGINT | PK, AI | Unique maintenance record |
| equipment_id | BIGINT | FK EQUIPMENT, NOT NULL | Equipment reference |
| maintenance_date | DATE | NOT NULL | Maintenance date |
| maintenance_type | ENUM | NOT NULL | preventive/corrective/emergency |
| description | TEXT | NOT NULL | What was done |
| cost | DECIMAL(10,2) | | Maintenance cost |
| performed_by | VARCHAR(255) | | Technician name |
| next_due_date | DATE | | Next maintenance due date |
| created_at | TIMESTAMP | DEFAULT CURRENT | Creation timestamp |

---

## Key Relationships Summary

```
Users (1) ──→ (1) Members
Users (1) ──→ (1) Trainers
Users (1) ──→ (∞) Payments

Trainers (1) ──→ (∞) Classes
Classes (1) ──→ (∞) Class_Schedules
Classes (1) ──→ (∞) Class_Enrollments
Class_Schedules (1) ──→ (∞) Class_Enrollments
Class_Schedules (1) ──→ (∞) Attendance

Members (1) ──→ (∞) Memberships
Membership_Plans (1) ──→ (∞) Memberships
Members (1) ──→ (∞) Class_Enrollments
Members (1) ──→ (∞) Attendance
Members (1) ──→ (∞) Equipment_Usage

Equipment (1) ──→ (∞) Equipment_Usage
Equipment (1) ──→ (∞) Maintenance_Logs
Areas (1) ──→ (∞) Equipment
Areas (1) ──→ (∞) Classes
```

## Indexing Strategy

**Primary Indexes (on Foreign Keys):**
- users.email
- members.user_id
- trainers.user_id
- classes.trainer_id
- classes.area_id
- class_enrollments.member_id, class_enrollments.schedule_id
- attendance.member_id, attendance.schedule_id
- payments.member_id
- equipment_usage.equipment_id, equipment_usage.member_id

**Secondary Indexes (for common queries):**
- memberships.member_id, memberships.status
- memberships.end_date
- class_schedules.class_id, class_schedules.scheduled_date
- payments.payment_date, payments.payment_status
- equipment_usage.start_time, equipment_usage.equipment_id
- maintanence_logs.equipment_id, maintenance_logs.maintenance_date

## Constraints & Validation

- Email must be unique and valid format
- Phone number must be valid format
- Prices must be >= 0
- Dates must be valid and logical (end_date > start_date)
- Class capacity must be > 0
- Duration must be > 0
- Status enums limited to predefined values
- Soft deletes on users, members, trainers
- Cascade delete for related records where appropriate
