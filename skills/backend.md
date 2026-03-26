# Backend Skill - Gym Attendance System

## Overview
Backend for a gym attendance and membership management system built with Laravel 11, providing RESTful APIs and business logic for member management, attendance tracking, memberships, and payments.

## Tech Stack
- **Framework**: Laravel 11
- **Database**: MySQL/PostgreSQL
- **API**: RESTful with JSON responses
- **Authentication**: Laravel Sanctum (JWT-like tokens)
- **Testing**: PHPUnit
- **Code Generation**: Artisan commands

## Project Structure
```
app/
├── Models/                     # Eloquent models for 13 entities
│   ├── User.php               # Authentication
│   ├── Member.php
│   ├── MembershipPlan.php
│   ├── Trainer.php
│   ├── Certification.php
│   ├── TrainerCertification.php
│   ├── FitnessClass.php
│   ├── ClassSchedule.php
│   ├── Attendance.php
│   ├── Payment.php
│   ├── Equipment.php
│   ├── ClassEquipment.php
│   ├── EquipmentUsage.php
│   └── MembershipUpgrade.php
├── Http/
│   └── Controllers/            # API controllers by resource
│       ├── MemberController.php
│       ├── TrainerController.php
│       ├── FitnessClassController.php
│       ├── ClassScheduleController.php
│       ├── AttendanceController.php
│       ├── PaymentController.php
│       ├── EquipmentController.php
│       ├── MembershipPlanController.php
│       └── ReportController.php
├── Services/                   # Business logic layer
│   ├── MemberService.php
│   ├── AttendanceService.php
│   ├── PaymentService.php
│   ├── MembershipService.php
│   ├── ClassScheduleService.php
│   └── ReportService.php
├── Events/
│   ├── MembershipExpired.php
│   ├── MembershipUpgraded.php
│   ├── AttendanceRecorded.php
│   ├── PaymentProcessed.php
│   └── ClassCapacityReached.php
└── Listeners/
    ├── SendMembershipExpiryReminder.php
    ├── ValidatePaymentCoverage.php
    └── UpdateMembershipStatus.php

database/
├── migrations/                 # One migration per table
│   ├── create_membership_plans_table.php
│   ├── create_members_table.php
│   ├── create_trainers_table.php
│   ├── create_certifications_table.php
│   ├── create_trainer_certifications_table.php
│   ├── create_fitness_classes_table.php
│   ├── create_class_schedules_table.php
│   ├── create_attendance_table.php
│   ├── create_payments_table.php
│   ├── create_equipment_table.php
│   ├── create_class_equipment_table.php
│   ├── create_equipment_usage_table.php
│   ├── create_membership_upgrades_table.php
│   └── create_triggers_and_procedures.php
├── seeders/
│   ├── MembershipPlanSeeder.php
│   ├── TrainerSeeder.php
│   ├── CertificationSeeder.php
│   ├── FitnessClassSeeder.php
│   └── EquipmentSeeder.php
└── factories/
    ├── MemberFactory.php
    ├── TrainerFactory.php
    └── AttendanceFactory.php
```

## Core Entities & Database Schema

The system implements 13 normalized tables designed to 3NF:

### 1. MEMBERSHIP_PLANS
**Purpose**: Define gym subscription options
```php
- plan_id (PK)
- plan_name (string): "Monthly", "Annual", "Quarterly"
- price (decimal): Cost of plan
- duration_months (int): How long plan lasts
- description (text): Plan features/details
- created_at, updated_at
```
**Relationships**: One-to-many with Members
**Key Validation**: Unique plan names, price > 0

### 2. MEMBERS
**Purpose**: Store gym member information
```php
- member_id (PK)
- first_name, last_name (string)
- email (string): Unique email
- phone (string)
- date_of_birth (date)
- membership_start (date)
- membership_end (date)
- membership_status (enum): Active, Expired, Suspended
- plan_id (FK → MEMBERSHIP_PLANS)
- created_at, updated_at
```
**Relationships**: 
  - Belongs to one membership plan
  - Has many payments
  - Has many attendance records
  - Can have multiple membership upgrades
**Triggers**:
  - Auto-update membership_status based on end date
  - Trigger ON INSERT/UPDATE to validate date logic

### 3. TRAINERS
**Purpose**: Store gym trainer information
```php
- trainer_id (PK)
- first_name, last_name (string)
- specialization (string): E.g., "Cardio", "Strength"
- phone (string)
- email (string): Unique
- created_at, updated_at
```
**Relationships**: 
  - One trainer → many fitness classes
  - Many-to-many with certifications

### 4. CERTIFICATIONS
**Purpose**: Store professional certifications
```php
- certification_id (PK)
- certification_name (string): E.g., "CPR", "NASM-CPT"
- issuing_organization (string)
- created_at, updated_at
```
**Relationships**: Many-to-many with trainers

### 5. TRAINER_CERTIFICATIONS (Junction Table)
**Purpose**: Link trainers to their certifications
```php
- trainer_id (FK → TRAINERS)
- certification_id (FK → CERTIFICATIONS)
- date_obtained (date)
- expires_at (date): Nullable, for certifications that expire
```
**Key**: Composite PK (trainer_id, certification_id)
**Purpose**: Allows multiple certifications per trainer + stores extra metadata

### 6. FITNESS_CLASSES
**Purpose**: Store gym class details
```php
- class_id (PK)
- class_name (string): E.g., "Yoga", "HIIT"
- description (text): Class details
- trainer_id (FK → TRAINERS)
- max_participants (int): Class capacity
- created_at, updated_at
```
**Relationships**: 
  - One trainer → many classes
  - One class → many schedules
  - Many-to-many with equipment
**Validation**: max_participants > 0

### 7. CLASS_SCHEDULES
**Purpose**: Define when classes occur
```php
- schedule_id (PK)
- class_id (FK → FITNESS_CLASSES)
- class_date (date)
- start_time (time)
- end_time (time)
- created_at, updated_at
```
**Relationships**: 
  - Belongs to one fitness class
  - Has many attendance records
  - Can use equipment
**Constraints**: Unique (class_id, class_date, start_time)

### 8. ATTENDANCE (Junction Table)
**Purpose**: Track member participation in classes
```php
- member_id (FK → MEMBERS)
- schedule_id (FK → CLASS_SCHEDULES)
- attendance_status (enum): Present, Absent, Late
- recorded_at (timestamp)
```
**Key**: Composite PK (member_id, schedule_id)
**Connects**: MEMBERS ↔ CLASS_SCHEDULES
**Triggers**:
  - Prevent exceeding max_participants on INSERT
  - Validate member is active when checking in
**Validation**: No duplicate attendance records

### 9. PAYMENTS
**Purpose**: Track membership payments
```php
- payment_id (PK)
- member_id (FK → MEMBERS)
- amount_paid (decimal)
- payment_date (date)
- payment_method (enum): "Cash", "Card", "Bank Transfer"
- coverage_start (date): When payment coverage begins
- coverage_end (date): When payment coverage ends
- status (enum): "Completed", "Pending", "Failed"
- created_at, updated_at
```
**Relationships**: One member → many payments
**Triggers**:
  - Prevent overlapping coverage periods
  - Auto-update membership_end date
  - Validate payment_date <= today
**Constraints**: amount_paid > 0, coverage_start <= coverage_end

### 10. EQUIPMENT
**Purpose**: Track gym equipment inventory
```php
- equipment_id (PK)
- equipment_name (string)
- status (enum): "Available", "Maintenance", "Out of Service"
- acquisition_date (date)
- last_maintenance (date)
- created_at, updated_at
```
**Relationships**: Many-to-many with fitness classes

### 11. CLASS_EQUIPMENT (Junction Table)
**Purpose**: Link classes to their required equipment
```php
- class_id (FK → FITNESS_CLASSES)
- equipment_id (FK → EQUIPMENT)
```
**Key**: Composite PK (class_id, equipment_id)
**Connects**: FITNESS_CLASSES ↔ EQUIPMENT

### 12. EQUIPMENT_USAGE
**Purpose**: Track actual equipment usage during classes
```php
- usage_id (PK)
- equipment_id (FK → EQUIPMENT)
- schedule_id (FK → CLASS_SCHEDULES)
- usage_duration (int): Minutes used
- created_at, updated_at
```
**Relationships**: Links equipment to class usage

### 13. MEMBERSHIP_UPGRADES
**Purpose**: Track member plan changes
```php
- upgrade_id (PK)
- member_id (FK → MEMBERS)
- old_plan_id (FK → MEMBERSHIP_PLANS): Nullable for first upgrade
- new_plan_id (FK → MEMBERSHIP_PLANS)
- upgrade_date (date)
- created_at, updated_at
```
**Relationships**: One member → many upgrades
**Triggers**:
  - Update MEMBERS.plan_id on upgrade
  - Update membership_end date if needed
  - Record before/after plan details

### Relationship Summary
**One-to-Many**:
  - Membership Plan → Members
  - Trainer → Fitness Classes
  - Member → Payments
  - Member → Membership Upgrades
  - Fitness Class → Class Schedules

**Many-to-Many**:
  - Members ↔ Class Schedules (via Attendance)
  - Trainers ↔ Certifications (via Trainer_Certifications)
  - Fitness Classes ↔ Equipment (via Class_Equipment)
  - Equipment ↔ Class Schedules (via Equipment_Usage)

## API Conventions
- **Base URL**: `/api/`
- **Authentication**: Bearer tokens via Sanctum
- **Response Format**: JSON with standard structure
- **Error Handling**: HTTP status codes with error messages
- **Pagination**: cursor or page-based for list endpoints

## Standard Response Format
```json
{
  "success": true,
  "data": {},
  "message": "",
  "errors": null
}
```

## Common Patterns

### Service Layer
Extract business logic from controllers into service classes:
```php
// In controller
$service = new MemberService();
$result = $service->createMember($data);
```

### Repository Pattern
Consider using repositories for complex queries:
```php
$repo = new MemberRepository();
$members = $repo->getActiveMembers();
```

### Events & Listeners
For cross-cutting concerns:
- MemberCreated → Send welcome email
- MembershipExpiring → Send reminder
- PaymentReceived → Update membership status

### Database Transactions
For multi-step operations:
```php
DB::transaction(function () {
    // Multiple operations
});
```

## Key Workflows & Business Logic

### Member Onboarding
1. Create user account (authentication)
2. Create member profile with basic info
3. Link to membership plan
4. Record initial payment
5. Send welcome email/notification
6. Set membership_start and membership_end dates

### Class Management & Trainer Assignment
1. Create fitness class with trainer assignment
2. Add equipment requirements (many-to-many)
3. Create class schedules for dates/times
4. Validate trainer availability (no conflicts)
5. Set class_date, start_time, end_time
6. Prevent duplicate schedules

### Member Check-in Process
1. Search member by ID or email
2. Verify member exists in system
3. Verify membership is active (not expired)
4. Search for today's class schedules
5. Display member and class info
6. Record attendance (Present status)
7. **Trigger**: Verify class not at max capacity
8. Show success confirmation

### Payment Processing
1. Select member
2. Enter payment amount and method
3. Specify coverage period (start_date to end_date)
4. **Trigger**: Validate no overlapping periods
5. Create payment record
6. Update MEMBERS.membership_end date
7. Check if membership was expired → reactivate if needed
8. Send payment confirmation to member

### Membership Renewal/Upgrade
1. Identify members with expiring memberships
2. Member selects new plan (same or different)
3. Process payment for new plan
4. Create MEMBERSHIP_UPGRADES record
5. Update MEMBERS.plan_id
6. Update MEMBERS.membership_end with new expiry
7. **Trigger**: Auto-update membership_status to Active
8. Send renewal confirmation

### Trainer Certification Management
1. Add certification to CERTIFICATIONS table
2. Link trainer to certification via TRAINER_CERTIFICATIONS
3. Record date_obtained
4. Track expiry date (if applicable)
5. Generate trainer qualifications report

### Equipment Usage Tracking
1. Create CLASS_EQUIPMENT junction records
2. During class, create EQUIPMENT_USAGE records
3. Track duration equipment was used
4. Generate equipment usage analytics
5. Flag equipment for maintenance based on usage

### Attendance Analytics
1. Query ATTENDANCE for date range
2. Group by CLASS_SCHEDULES
3. Calculate attendance rate per class
4. Identify members with low attendance
5. Generate trainer workload metrics
6. Flag at-risk members for follow-up

### Report Generation
- Expired memberships: Query Members where membership_end < TODAY
- Popular classes: Group Attendance by schedule_id, count records
- Trainer workload: Count active classes per trainer
- Revenue reports: Sum payments grouped by date/plan
- Attendance trends: Compare period-over-period
- Equipment usage: Sum usage_duration by equipment_id

## Migration Strategy & Database Features

### Migrations (13 Tables)
Each table gets a dedicated migration file:
1. `create_membership_plans_table` - Base reference data
2. `create_trainers_table` - Base trainer data
3. `create_certifications_table` - Base certification data
4. `create_trainer_certifications_table` - Many-to-many link
5. `create_members_table` - Members with FK to plan
6. `create_fitness_classes_table` - Classes with FK to trainer
7. `create_class_schedules_table` - Schedules with FK to class
8. `create_attendance_table` - Attendance with composite key
9. `create_equipment_table` - Equipment inventory
10. `create_class_equipment_table` - Class-equipment junction
11. `create_payments_table` - Payments with coverage tracking
12. `create_equipment_usage_table` - Usage tracking
13. `create_membership_upgrades_table` - Upgrade history
14. `create_triggers_and_procedures` - Business rule enforcement

### Database Triggers (Enforce Business Rules)
**Triggers in MySQL** (created via migration):
- **BEFORE INSERT on ATTENDANCE**: Check class max capacity not exceeded
- **BEFORE INSERT on PAYMENTS**: Validate no overlapping coverage periods
- **AFTER INSERT on PAYMENTS**: Update MEMBERS.membership_end
- **AFTER INSERT on PAYMENTS**: Update MEMBERS.membership_status if needed
- **AFTER INSERT on MEMBERSHIP_UPGRADES**: Update MEMBERS.plan_id
- **BEFORE INSERT on CLASS_SCHEDULES**: Validate trainer not double-booked
- **DAILY EVENT** (scheduled job): Update MEMBERS.membership_status (Active/Expired)

### Stored Procedures (Complex Operations)
Example procedures for common operations:
```sql
-- Check-in a member
PROCEDURE check_in_member(
  IN member_id INT,
  IN schedule_id INT,
  OUT result VARCHAR(255)
)

-- Process payment and renew membership
PROCEDURE renew_membership(
  IN member_id INT,
  IN payment_amount DECIMAL,
  IN new_plan_id INT,
  OUT result VARCHAR(255)
)

-- Get attendance report
PROCEDURE get_attendance_report(
  IN date_start DATE,
  IN date_end DATE
)
```

### Database Views (Analytics & Reporting)
```sql
VIEW v_active_memberships:
  - Shows members with active memberships
  - Includes plan details and expiry info

VIEW v_trainer_workload:
  - Shows trainers with class counts
  - Useful for workload analysis

VIEW v_popular_classes:
  - Ranks classes by attendance count
  - Useful for capacity planning

VIEW v_member_attendance_summary:
  - Shows attendance metrics per member
  - Identifies inactive members

VIEW v_revenue_by_plan:
  - Aggregates revenue by membership plan
  - Period-based filtering

VIEW v_equipment_usage_stats:
  - Equipment usage frequency
  - Maintenance requirements
```

### Indexing Strategy
**Primary Indexes** (on primary keys):
- All 13 tables indexed by their PK

**Foreign Key Indexes** (performance):
- Members.plan_id
- Classes.trainer_id
- Attendance.member_id, schedule_id
- Payments.member_id
- Class_Schedules.class_id
- Equipment_Usage.equipment_id, schedule_id
- Membership_Upgrades.member_id

**Search/Filter Indexes**:
- Members.email (unique + frequent lookup)
- Members.membership_status (frequent filtering)
- ClassSchedules.class_date (date range queries)
- Payments.payment_date (date range queries)
- Equipment.status (availability filtering)

**Composite Indexes** (for common query patterns):
- (schedule_id, class_date) on CLASS_SCHEDULES
- (member_id, membership_end) on MEMBERS (expiry checks)

### Normalization to 3NF
**First Normal Form (1NF)**:
- All attributes are atomic (no multi-valued)
- All rows are unique via Primary Key
- All columns follow domain constraints

**Second Normal Form (2NF)**:
- All 1NF requirements met
- All non-key attributes depend on entire primary key
- No partial dependencies (no column depends on just part of composite key)
- Example: ATTENDANCE has (member_id, schedule_id) - no column depends on just member_id

**Third Normal Form (3NF)**:
- All 2NF requirements met
- No transitive dependencies (non-key attributes don't depend on other non-key attributes)
- Example: MEMBERS has status determined by date, but stored for performance (acceptable)

### Data Integrity Constraints
```php
-- Primary Key Constraints
PRIMARY KEY (entity_id)

-- Foreign Key Constraints (with cascading)
FOREIGN KEY (parent_id) REFERENCES parent_table(id)
  ON DELETE CASCADE
  ON UPDATE CASCADE

-- Unique Constraints
UNIQUE (email)
UNIQUE (class_id, class_date, start_time)

-- Check Constraints
CHECK (price > 0)
CHECK (duration_months > 0)
CHECK (max_participants > 0)
CHECK (coverage_start <= coverage_end)
CHECK (membership_end >= membership_start)
CHECK (amount_paid > 0)

-- Domain Constraints (via ENUM)
ENUM('Active', 'Expired', 'Suspended')
ENUM('Present', 'Absent', 'Late')
ENUM('Available', 'Maintenance', 'Out of Service')
```

## Testing Strategy
- Unit tests for models and services
- Feature tests for API endpoints
- Database seeding for test data
- Use factories for flexible test data

## Security Considerations
- Validate all inputs
- Use Laravel's built-in security (CSRF, SQL injection protection)
- Implement rate limiting for API endpoints
- Hash passwords (Laravel handles this)
- Validate authorization on all endpoints
- Use middleware for authentication checks

## Performance Best Practices
- Eager load relationships (with/has)
- Use indexes on frequently queried columns
- Cache frequently accessed data
- Paginate large result sets
- Use database transactions for consistency

## Documentation
- Document all API endpoints with request/response examples
- Include error codes and messages
- Provide usage examples
- Keep OpenAPI/Swagger specs updated
