# DATABASE SCHEMA - QUICK REFERENCE GUIDE

## 📋 Table Quick Index

### Core Entity Tables
| # | Table | Rows | Purpose | Primary Key |
|---|-------|------|---------|-------------|
| 1 | **USERS** | ~1000 | Authentication & authorization | user_id |
| 2 | **MEMBERS** | ~950 | Member profiles | member_id |
| 3 | **MEMBERSHIP_PLANS** | ~5 | Subscription packages | plan_id |
| 4 | **TRAINERS** | ~50 | Trainer profiles | trainer_id |
| 5 | **GYM_AREAS** | ~5 | Facility zones | area_id |

### Relational Entities
| # | Table | Records | Purpose | Primary Key |
|---|-------|---------|---------|-------------|
| 6 | **MEMBERSHIPS** | ~950 | Active subscriptions | membership_id |
| 7 | **MEMBERSHIP_UPGRADES** | ~200 | Upgrade history | upgrade_id |
| 8 | **FITNESS_CLASSES** | ~20 | Class types | class_id |
| 9 | **CLASS_SCHEDULES** | ~100+ | Class instances | schedule_id |
| 10 | **CERTIFICATIONS** | ~150 | Trainer credentials | certification_id |
| 11 | **EQUIPMENT** | ~200 | Equipment inventory | equipment_id |
| 12 | **PAYMENTS** | ~5000+ | Transaction records | payment_id |

### Junction & Tracking Tables
| # | Table | Records | Purpose | Primary Key |
|---|-------|---------|---------|-------------|
| 13 | **CLASS_ENROLLMENTS** | ~3000+ | Member class enrollments (M:N) | enrollment_id |
| 14 | **CLASS_EQUIPMENT_ACCESS** | ~100 | Class-equipment mapping (M:N) | access_id |
| 15 | **ATTENDANCE** | ~10000+ | Attendance tracking | attendance_id |
| 16 | **EQUIPMENT_USAGE** | ~5000+ | Equipment usage logs | usage_id |
| 17 | **MAINTENANCE_LOGS** | ~500 | Maintenance history | maintenance_id |

**Total Tables**: 18 | **Total Columns**: 180+ | **Total Constraints**: 50+

---

## 🔍 Common SQL Queries

### 1. Get Member Information with Current Membership

```sql
SELECT 
    u.user_id,
    u.first_name,
    u.last_name,
    u.email,
    m.date_of_birth,
    mp.plan_name,
    ms.start_date,
    ms.end_date,
    DATEDIFF(ms.end_date, CURDATE()) as days_remaining
FROM users u
JOIN members m ON u.user_id = m.user_id
JOIN memberships ms ON m.member_id = ms.member_id
JOIN membership_plans mp ON ms.plan_id = mp.plan_id
WHERE u.account_status = 'active'
    AND ms.status = 'active'
    AND ms.end_date >= CURDATE()
ORDER BY ms.renewal_date ASC;
```

### 2. Get Trainer's Schedule and Class Enrollment

```sql
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
    (fc.max_capacity - cs.current_enrollment) as available_slots,
    ga.area_name
FROM trainers t
JOIN users u ON t.user_id = u.user_id
JOIN fitness_classes fc ON t.trainer_id = fc.trainer_id
JOIN class_schedules cs ON fc.class_id = cs.class_id
JOIN gym_areas ga ON fc.area_id = ga.area_id
WHERE fc.status = 'active'
    AND cs.is_cancelled = FALSE
ORDER BY t.trainer_id, cs.day_of_week, cs.start_time;
```

### 3. Get Equipment Maintenance Due (Next 30 Days)

```sql
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
    t.user_id as responsible_trainer
FROM equipment e
JOIN gym_areas ga ON e.area_id = ga.area_id
LEFT JOIN trainers t ON e.responsible_trainer_id = t.trainer_id
WHERE e.next_maintenance_date IS NOT NULL
    AND e.next_maintenance_date <= DATE_ADD(CURDATE(), INTERVAL 30 DAY)
    AND e.operational_status != 'retired'
ORDER BY e.next_maintenance_date ASC;
```

### 4. Member Attendance Statistics

```sql
SELECT 
    m.member_id,
    u.first_name,
    u.last_name,
    fc.class_name,
    COUNT(a.attendance_id) as total_attended,
    COUNT(CASE WHEN a.attendance_status = 'present' THEN 1 END) as classes_present,
    COUNT(CASE WHEN a.attendance_status = 'absent' THEN 1 END) as classes_absent,
    COUNT(CASE WHEN a.attendance_status = 'late' THEN 1 END) as classes_late,
    AVG(a.duration_minutes) as avg_class_duration,
    MAX(a.check_in_time) as last_attended
FROM members m
JOIN users u ON m.user_id = u.user_id
LEFT JOIN attendance a ON m.member_id = a.member_id
LEFT JOIN class_schedules cs ON a.schedule_id = cs.schedule_id
LEFT JOIN fitness_classes fc ON cs.class_id = fc.class_id
GROUP BY m.member_id, u.first_name, u.last_name, fc.class_name
ORDER BY m.member_id, fc.class_name;
```

### 5. Revenue Report by Month and Payment Type

```sql
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
    AND p.payment_date >= DATE_SUB(CURDATE(), INTERVAL 12 MONTH)
GROUP BY DATE_FORMAT(p.payment_date, '%Y-%m'), p.payment_type
ORDER BY month DESC, total_amount DESC;
```

### 6. Members with Expiring Memberships (Renewal Alerts)

```sql
SELECT 
    u.email,
    u.first_name,
    u.last_name,
    mp.plan_name,
    ms.end_date,
    DATEDIFF(ms.end_date, CURDATE()) as days_until_expiry,
    ms.auto_renewal
FROM members m
JOIN users u ON m.user_id = u.user_id
JOIN memberships ms ON m.member_id = ms.member_id
JOIN membership_plans mp ON ms.plan_id = mp.plan_id
WHERE ms.status = 'active'
    AND ms.end_date BETWEEN CURDATE() AND DATE_ADD(CURDATE(), INTERVAL 14 DAY)
ORDER BY ms.end_date ASC;
```

### 7. Member Enrollment in Classes

```sql
SELECT 
    m.member_id,
    u.first_name,
    u.last_name,
    fc.class_name,
    cs.day_of_week,
    cs.start_time,
    ce.enrollment_date,
    ce.enrollment_status,
    CASE WHEN a.attendance_id IS NOT NULL THEN 'Attended' ELSE 'Not Attended' END as attendance
FROM class_enrollments ce
JOIN members m ON ce.member_id = m.member_id
JOIN users u ON m.user_id = u.user_id
JOIN class_schedules cs ON ce.schedule_id = cs.schedule_id
JOIN fitness_classes fc ON cs.class_id = fc.class_id
LEFT JOIN attendance a ON m.member_id = a.member_id 
    AND cs.schedule_id = a.schedule_id
WHERE ce.enrollment_status = 'enrolled'
    AND cs.is_cancelled = FALSE
ORDER BY u.first_name, cs.day_of_week, cs.start_time;
```

### 8. Trainer Certifications with Expiry Status

```sql
SELECT 
    t.trainer_id,
    u.first_name,
    u.last_name,
    c.certification_name,
    c.issue_date,
    c.expiration_date,
    DATEDIFF(c.expiration_date, CURDATE()) as days_until_expiry,
    CASE 
        WHEN c.expiration_date IS NULL THEN 'Never Expires'
        WHEN DATEDIFF(c.expiration_date, CURDATE()) < 0 THEN 'EXPIRED'
        WHEN DATEDIFF(c.expiration_date, CURDATE()) < 30 THEN 'Expiring Soon'
        ELSE 'Valid'
    END as status
FROM trainers t
JOIN users u ON t.user_id = u.user_id
JOIN certifications c ON t.trainer_id = c.trainer_id
WHERE c.is_active = TRUE
ORDER BY t.trainer_id, c.expiration_date ASC;
```

### 9. Classes with Low Enrollment

```sql
SELECT 
    fc.class_id,
    fc.class_name,
    t.user_id as trainer_name,
    cs.day_of_week,
    cs.start_time,
    cs.current_enrollment,
    fc.max_capacity,
    ROUND((cs.current_enrollment / fc.max_capacity * 100), 2) as enrollment_percentage
FROM fitness_classes fc
JOIN class_schedules cs ON fc.class_id = cs.class_id
JOIN trainers t ON fc.trainer_id = t.trainer_id
WHERE fc.status = 'active'
    AND cs.is_cancelled = FALSE
    AND cs.current_enrollment < (fc.max_capacity * 0.3)
ORDER BY enrollment_percentage ASC;
```

### 10. Equipment Utilization Report

```sql
SELECT 
    e.equipment_id,
    e.equipment_name,
    e.equipment_type,
    ga.area_name,
    e.usage_count,
    COUNT(DISTINCT eu.usage_id) as usage_logs,
    COUNT(DISTINCT ce.class_id) as classes_using,
    e.operational_status,
    e.condition_status
FROM equipment e
JOIN gym_areas ga ON e.area_id = ga.area_id
LEFT JOIN equipment_usage eu ON e.equipment_id = eu.equipment_id
LEFT JOIN class_equipment_access cea ON e.equipment_id = cea.equipment_id
LEFT JOIN fitness_classes ce ON cea.class_id = ce.class_id
GROUP BY e.equipment_id, e.equipment_name, ga.area_name
ORDER BY e.usage_count DESC;
```

---

## ✅ Normalization Verification Checklist

### First Normal Form (1NF) - Atomic Values Only

**✓ Compliance Check**:
```sql
-- Verify no text fields contain multiple values
-- Example: phone_numbers should be in separate rows
SELECT * FROM users WHERE phone_number LIKE '%,%';  -- Should return 0 rows

-- Verify certifications are properly separated
SELECT * FROM certifications;  -- Each cert in separate row, not comma-separated
```

**Status**: ✅ **1NF COMPLIANT** - All columns contain single atomic values

---

### Second Normal Form (2NF) - No Partial Dependencies

**✓ Compliance Check**:

All tables have:
- Single-column primary key OR
- All non-key attributes depend on entire PK

**Example - MEMBERS Table** (2NF Compliant):
```
PK: member_id (single column)
├─ user_id          → Depends on member_id ✓
├─ date_of_birth    → Depends on member_id ✓
├─ emergency_contact → Depends on member_id ✓
└─ All attrs depend on FULL PK ✓
```

**Status**: ✅ **2NF COMPLIANT** - No partial dependencies

---

### Third Normal Form (3NF) - No Transitive Dependencies

**✗ Non-3NF Example (What We Avoided)**:
```sql
-- BAD: Transitive dependency
CREATE TABLE memberships_bad (
    membership_id INT,
    member_id INT,
    plan_id INT,
    plan_name VARCHAR(100),      -- Depends on plan_id, not membership_id!
    plan_price DECIMAL(10,2)     -- Same issue
);
-- membership_id → plan_id → plan_name (TRANSITIVE)
```

**✓ 3NF Solution (What We Have)**:
```sql
-- GOOD: Separate plan table
CREATE TABLE memberships (
    membership_id INT PRIMARY KEY,
    member_id INT,
    plan_id INT FK              -- Reference only
);

CREATE TABLE membership_plans (
    plan_id INT PRIMARY KEY,
    plan_name VARCHAR(100),     -- Depends directly on plan_id
    plan_price DECIMAL(10,2)
);
```

**All Tables Verified for 3NF Compliance**:
- ✅ USERS - Direct attributes only
- ✅ MEMBERS - All depend on member_id
- ✅ MEMBERSHIP_PLANS - All depend on plan_id
- ✅ MEMBERSHIPS - References via FKs only
- ✅ TRAINERS - All depend on trainer_id
- ✅ FITNESS_CLASSES - All depend on class_id
- ✅ EQUIPMENT - All depend on equipment_id
- ✅ PAYMENTS - All depend on payment_id

**Status**: ✅ **3NF COMPLIANT** - No transitive dependencies

---

## 🔐 Constraint Reference

### Unique Constraints (UNIQUE)

| Column | Table | Purpose | Business Rule |
|--------|-------|---------|---------------|
| email | USERS | Login credential | One email per account |
| phone_number | USERS | Contact | One phone per account |
| user_id | MEMBERS | 1:1 relationship | One member profile per user |
| user_id | TRAINERS | 1:1 relationship | One trainer profile per user |
| plan_name | MEMBERSHIP_PLANS | Plan identification | One plan with each name |
| serial_number | EQUIPMENT | Equipment tracking | Unique equipment identifier |
| certification_number | CERTIFICATIONS | Credential tracking | Each cert has unique number |
| transaction_id | PAYMENTS | Payment reference | No duplicate transactions |
| reference_number | PAYMENTS | Internal reference | No duplicate references |
| (member_id, schedule_id) | CLASS_ENROLLMENTS | Prevent duplicates | One enrollment per session |
| (member_id, schedule_id) | ATTENDANCE | Prevent duplicates | One attendance record per session |
| (class_id, equipment_id) | CLASS_EQUIPMENT_ACCESS | Prevent duplicates | Equipment assigned once per class |

---

### Foreign Key Constraints with Cascade Rules

#### CASCADE DELETE (Child deleted with parent)
```sql
-- Example: If user deleted, member automatically deleted
ALTER TABLE members
ADD CONSTRAINT fk_members_users 
FOREIGN KEY (user_id) REFERENCES users(user_id) 
ON DELETE CASCADE ON UPDATE CASCADE;
```

**Applied To** (Owned child records):
- users → members (member "owned" by user)
- users → trainers (trainer "owned" by user)
- members → memberships (memberships belong to member)
- memberships → membership_upgrades (upgrades belong to membership)
- trainers → certifications (certs belong to trainer)
- fitness_classes → class_schedules (schedules of specific class)
- class_schedules → class_enrollments (enrollments of specific session)
- class_schedules → attendance (attendance of specific session)
- equipment → equipment_usage (usage of specific equipment)
- equipment → maintenance_logs (maintenance of specific equipment)

#### RESTRICT DELETE (Parent cannot delete if children exist)
```sql
-- Example: Cannot delete membership plan if members are using it
ALTER TABLE memberships
ADD CONSTRAINT fk_memberships_plan 
FOREIGN KEY (plan_id) REFERENCES membership_plans(plan_id) 
ON DELETE RESTRICT ON UPDATE CASCADE;
```

**Applied To** (Shared resources):
- membership_plans (protect plan history)
- trainers (protect class history)
- gym_areas (protect facility structure)

#### SET NULL (Reference nullified on parent delete)
```sql
-- Example: If trainer deleted, member's trainer assignment cleared
ALTER TABLE members
ADD CONSTRAINT fk_members_trainer 
FOREIGN KEY (trainer_id) REFERENCES trainers(trainer_id) 
ON DELETE SET NULL ON UPDATE CASCADE;
```

**Applied To** (Optional assignments):
- members.trainer_id (personal trainer)
- equipment.responsible_trainer_id (equipment manager)
- members.trainer_id in equip_usage (user of equipment)
- maintenance_logs.trainer_id (performing maintenance)
- payments.membership_id (related membership)

---

### Check Constraints (Business Rules)

| Constraint | SQL | Purpose |
|-----------|-----|---------|
| Price > 0 | `CHECK (price_per_month > 0)` | No free invalid plans |
| Duration > 0 | `CHECK (duration_months > 0)` | Valid duration |
| Dates ordered | `CHECK (start_date < end_date)` | Logical date range |
| Capacity > 0 | `CHECK (max_capacity > 0)` | Valid room capacity |
| Age ≥ 0 | `CHECK (years_of_experience >= 0)` | Valid age/experience |
| Enrollment valid | `CHECK (current_enrollment >= 0 AND current <= max)` | Enrollment consistency |
| Not overbilled | `CHECK (amount_paid <= total_price)` | Financial integrity |
| Time ordered | `CHECK (start_time < end_time)` | Time sanity |
| Email format | `CHECK (email LIKE '%@%')` | Basic email validation |
| Refund logic | `CHECK ((refund_amt IS NULL...) OR (...))` | Refund consistency |

---

## 📊 Index Performance Guide

### Critical Indexes for Speed

```sql
-- 1. Authentication
CREATE INDEX idx_email ON users(email);        -- Login queries

-- 2. Membership expiry alerts
CREATE INDEX idx_membership_status_date 
ON memberships(status, end_date);              -- Renewal reminders

-- 3. Attendance tracking
CREATE INDEX idx_attendance_member_date 
ON attendance(member_id, check_in_time);       -- Member history

-- 4. Payment history
CREATE INDEX idx_payment_member_date 
ON payments(member_id, payment_date);          -- Payment details

-- 5. Equipment maintenance scheduling
CREATE INDEX idx_equipment_next_maint 
ON equipment(next_maintenance_date);           -- Maintenance due

-- 6. Trainer's active classes
CREATE INDEX idx_class_trainer_status 
ON fitness_classes(trainer_id, status);        -- Schedule lookup

-- 7. Equipment area status
CREATE INDEX idx_equipment_area_status 
ON equipment(area_id, operational_status);     -- Area inventory
```

### Query Performance Tips

1. **Always filter by active status** when available
   ```sql
   WHERE account_status = 'active' AND deleted_at IS NULL
   ```

2. **Use composite indexes** for compound queries
   ```sql
   -- Good: Covered by index
   SELECT * FROM memberships 
   WHERE status = 'active' AND end_date < DATE_ADD(CURDATE(), INTERVAL 30 DAY);
   -- Uses: idx_membership_status_date
   ```

3. **Avoid functions in WHERE clause** (prevents index use)
   ```sql
   -- BAD: Cannot use index
   WHERE YEAR(payment_date) = 2024
   
   -- GOOD: Index-friendly
   WHERE payment_date >= '2024-01-01' 
     AND payment_date < '2025-01-01'
   ```

4. **Use EXPLAIN to verify** index usage
   ```sql
   EXPLAIN SELECT * FROM members WHERE date_of_birth > '2000-01-01';
   -- Check if "Using index" appears
   ```

---

## 🚀 Implementation Checklist

### Database Setup
- [ ] Create database with UTF8MB4 charset
- [ ] Execute full schema.sql file
- [ ] Verify all 18 tables created
- [ ] Test FK constraints
- [ ] Confirm all indexes exist

### Data Validation
- [ ] Test unique constraint violations
- [ ] Test check constraint violations
- [ ] Test cascade delete behavior
- [ ] Test restrict delete behavior
- [ ] Verify soft delete with deleted_at

### ER Diagram Validation
- [ ] All relationships correctly represented
- [ ] FK cardinality matches diagram
- [ ] Cascade rules documented
- [ ] No orphaned tables

### Performance Verification
- [ ] Run common queries (Index verification)
- [ ] Check execution plans
- [ ] Monitor slow query log
- [ ] Load test with sample data

### Documentation Review
- [ ] Architecture documented
- [ ] All constraints explained
- [ ] Relationships clearly mapped
- [ ] Implementation guide complete

---

## 📝 SQL Syntax Reference

### Data Type Mapping

| Business Need | SQL Type | Range/Details |
|---------------|----------|---------------|
| ID | BIGINT UNSIGNED | 0 to 18,446,744,073,709,551,615 |
| Counter | INT | -2,147,483,648 to 2,147,483,647 |
| Money | DECIMAL(10,2) | Up to 99,999,999.99 |
| Email | VARCHAR(255) | RFC 5321 compliant |
| Names | VARCHAR(100-200) | Unicode support (UTF8MB4) |
| Descriptions | TEXT | Long text (max 65KB) |
| Dates | DATE | YYYY-MM-DD format |
| Times | TIME | HH:MM:SS format |
| Timestamps | TIMESTAMP | YYYY-MM-DD HH:MM:SS |
| Status | ENUM | Predefined values only |
| Booleans | BOOLEAN | 0 (false) or 1 (true) |

### Temporal Functions

```sql
-- Current date/time
CURDATE()                           -- Today's date
CURTIME()                           -- Current time
CURRENT_TIMESTAMP                   -- Now (date + time)

-- Date arithmetic
DATE_ADD(date, INTERVAL n DAY)      -- Add days
DATE_SUB(date, INTERVAL n MONTH)    -- Subtract months
DATEDIFF(date1, date2)              -- Days between dates
DATE_FORMAT(date, format)           -- Format date string

-- Examples
DATE_ADD(CURDATE(), INTERVAL 30 DAY)        -- 30 days from today
DATE_SUB(CURDATE(), INTERVAL 1 MONTH)       -- First day of last month
DATEDIFF(end_date, CURDATE())               -- Days until expiry
DATE_FORMAT(payment_date, '%Y-%m')          -- "2024-03"
```

---

## 🔍 Debugging Common Issues

### Issue 1: Foreign Key Constraint Fails

**Error**: `ERROR 1452: Cannot add or update a child row: foreign key constraint fails`

**Cause**: Trying to insert FK value that doesn't exist in parent table

**Solution**:
```sql
-- Check if parent record exists
SELECT * FROM membership_plans WHERE plan_id = 999;

-- Insert parent first
INSERT INTO membership_plans (...) VALUES (...);

-- Then child
INSERT INTO memberships (plan_id, ...) VALUES (NEW_PLAN_ID, ...);
```

### Issue 2: Duplicate Entry Unique Constraint

**Error**: `ERROR 1062: Duplicate entry 'john@example.com' for key 'email'`

**Cause**: Email already exists in USERS table

**Solution**:
```sql
-- Check existing
SELECT * FROM users WHERE email = 'john@example.com';

-- Use ON DUPLICATE KEY UPDATE (if update allowed)
INSERT INTO users (..., email) 
VALUES (..., 'john@example.com') 
ON DUPLICATE KEY UPDATE last_login_at = NOW();
```

### Issue 3: Soft Delete Shows Deleted Records

**Problem**: Queries returning deleted users (deleted_at IS NOT NULL)

**Solution**: Always include active filter
```sql
-- Always add to WHERE clause
WHERE deleted_at IS NULL
  AND account_status = 'active'
```

### Issue 4: Slow Queries

**Problem**: Query taking > 1 second

**Solution**:
```sql
-- Check which indexes are used
EXPLAIN SELECT ... FROM ...;
-- Should see "Using index" or "Using index condition"

-- Create missing index if needed
CREATE INDEX idx_name ON table_name(columns);

-- Rebuild statistics
ANALYZE TABLE table_name;
```

---

## 📚 Related Documentation

- **SCHEMA_DOCUMENTATION.md** - Detailed table definitions and normalization analysis
- **ER_DIAGRAM.md** - Visual relationship mapping
- **schema.sql** - Executable SQL schema creation

---

## 📞 Support & References

For questions about:
- **3NF Normalization**: See SCHEMA_DOCUMENTATION.md → Normalization Analysis
- **Relationships**: See ER_DIAGRAM.md → Relationship Mapping
- **SQL Syntax**: See schema.sql → CREATE TABLE statements
- **Constraints**: See this document → Constraint Reference

---

**Quick Reference Version**: 1.0  
**Last Updated**: March 14, 2024  
**Status**: ✅ Complete & Ready for Use
