# API Endpoints Documentation

## Base URL
```
http://localhost:8000/api
```

## Authentication
All API endpoints (except login/register) require Bearer token authentication:
```
Authorization: Bearer <token>
```

---

## 1. Authentication Endpoints

### Register User
```http
POST /auth/register
Content-Type: application/json

{
  "name": "string",
  "email": "string",
  "phone": "string",
  "password": "string",
  "password_confirmation": "string",
  "role": "member|trainer" // Default: member
}

Response: 201 Created
{
  "id": 1,
  "name": "John Doe",
  "email": "john@example.com",
  "role": "member",
  "token": "token_string"
}
```

### Login
```http
POST /auth/login
Content-Type: application/json

{
  "email": "string",
  "password": "string"
}

Response: 200 OK
{
  "token": "token_string",
  "user": {...}
}
```

### Logout
```http
POST /auth/logout
Authorization: Bearer <token>

Response: 200 OK
{
  "message": "Logged out successfully"
}
```

### Refresh Token
```http
POST /auth/refresh
Authorization: Bearer <token>

Response: 200 OK
{
  "token": "new_token_string"
}
```

### Upload Profile Photo
```http
POST /auth/upload-profile-photo
Content-Type: multipart/form-data
Authorization: Bearer <token>

file: <image_file>

Response: 200 OK
{
  "photo_url": "url_string"
}
```

---

## 2. Member Endpoints

### List All Members
```http
GET /members?page=1&limit=10&status=active
Authorization: Bearer <token>

Response: 200 OK
{
  "data": [...],
  "pagination": {
    "current_page": 1,
    "total": 100,
    "per_page": 10
  }
}
```

### Create Member
```http
POST /members
Content-Type: application/json
Authorization: Bearer <token>

{
  "user_id": 1,
  "date_of_birth": "1990-01-01",
  "gender": "male",
  "address": "123 Main St",
  "city": "New York",
  "state": "NY",
  "postal_code": "10001",
  "country": "USA",
  "emergency_contact": "Jane Doe",
  "emergency_phone": "+1-555-0001",
  "medical_conditions": "None"
}

Response: 201 Created
```

### Get Member Details
```http
GET /members/{id}
Authorization: Bearer <token>

Response: 200 OK
{
  "id": 1,
  "user": {...},
  "date_of_birth": "1990-01-01",
  "gender": "male",
  "active_membership": {...},
  "recent_attendance": [...],
  "payment_history": [...]
}
```

### Update Member
```http
PUT /members/{id}
Content-Type: application/json
Authorization: Bearer <token>

{
  "date_of_birth": "1990-01-01",
  "gender": "male",
  "address": "456 Oak Ave",
  ...
}

Response: 200 OK
```

### Delete Member
```http
DELETE /members/{id}
Authorization: Bearer <token>

Response: 204 No Content
```

### Get Member Dashboard
```http
GET /members/{id}/dashboard
Authorization: Bearer <token>

Response: 200 OK
{
  "active_membership": {...},
  "upcoming_classes": [...],
  "attendance_stats": {
    "total_visits": 45,
    "this_month": 8,
    "average_duration": 60
  },
  "payment_status": {...}
}
```

---

## 3. Trainer Endpoints

### List Trainers
```http
GET /trainers?page=1&limit=10
Authorization: Bearer <token>

Response: 200 OK
```

### Create Trainer
```http
POST /trainers
Content-Type: application/json
Authorization: Bearer <token>

{
  "user_id": 2,
  "specialization": "Yoga",
  "certification": "RYT-200",
  "certification_expiry": "2025-12-31",
  "years_experience": 5,
  "hourly_rate": 50.00,
  "bio": "Certified yoga instructor..."
}

Response: 201 Created
```

### Get Trainer Details
```http
GET /trainers/{id}
Authorization: Bearer <token>

Response: 200 OK
{
  "id": 1,
  "user": {...},
  "specialization": "Yoga",
  "assigned_classes": [...],
  "performance_stats": {...}
}
```

### Update Trainer
```http
PUT /trainers/{id}
Authorization: Bearer <token>
```

### Get Trainer Classes
```http
GET /trainers/{id}/classes
Authorization: Bearer <token>

Response: 200 OK
{
  "data": [...],
  "total_classes": 12
}
```

### Get Trainer Schedule
```http
GET /trainers/{id}/schedule?from_date=2024-03-14&to_date=2024-03-21
Authorization: Bearer <token>
```

---

## 4. Membership Plan Endpoints

### List Membership Plans
```http
GET /membership-plans?is_active=true
Authorization: Bearer <token>

Response: 200 OK
{
  "data": [
    {
      "id": 1,
      "name": "Basic",
      "price": 29.99,
      "duration_months": 1,
      "max_classes_per_month": 8,
      "features": ["Gym access", "2 classes/week"]
    }
  ]
}
```

### Create Membership Plan (Admin)
```http
POST /membership-plans
Content-Type: application/json
Authorization: Bearer <admin_token>

{
  "name": "Premium",
  "description": "Full access plan",
  "price": 59.99,
  "duration_months": 1,
  "max_classes_per_month": -1,
  "features": ["Unlimited gym access", "Unlimited classes"]
}

Response: 201 Created
```

### Get Plan Details
```http
GET /membership-plans/{id}
```

### Update Plan (Admin)
```http
PUT /membership-plans/{id}
Authorization: Bearer <admin_token>
```

### Delete Plan (Admin)
```http
DELETE /membership-plans/{id}
Authorization: Bearer <admin_token>
```

---

## 5. Membership Endpoints

### List Member's Memberships
```http
GET /members/{id}/memberships
Authorization: Bearer <token>

Response: 200 OK
{
  "data": [...],
  "active_membership": {...},
  "renewal_date": "2024-04-14"
}
```

### Create Membership
```http
POST /memberships
Content-Type: application/json
Authorization: Bearer <token>

{
  "member_id": 1,
  "plan_id": 1,
  "duration_months": 1,
  "auto_renew": true
}

Response: 201 Created
{
  "id": 1,
  "member_id": 1,
  "plan_id": 1,
  "start_date": "2024-03-14",
  "end_date": "2024-04-14",
  "status": "active",
  "renewal_date": "2024-04-14"
}
```

### Get Membership Details
```http
GET /memberships/{id}
Authorization: Bearer <token>
```

### Renew Membership
```http
POST /memberships/{id}/renew
Content-Type: application/json
Authorization: Bearer <token>

{
  "plan_id": 1,
  "duration_months": 1
}

Response: 201 Created
```

### Cancel Membership
```http
POST /memberships/{id}/cancel
Content-Type: application/json
Authorization: Bearer <token>

{
  "reason": "Moving out of city"
}

Response: 200 OK
```

### Pause Membership
```http
POST /memberships/{id}/pause
Authorization: Bearer <token>
```

### Get Active Memberships
```http
GET /memberships/active/all
Authorization: Bearer <token>
```

### Get Expiring Memberships
```http
GET /memberships/expiring/soon?days=14
Authorization: Bearer <admin_token>
```

---

## 6. Class Endpoints

### List Classes
```http
GET /classes?page=1&category=yoga&difficulty=beginner&status=active
Authorization: Bearer <token>

Response: 200 OK
{
  "data": [
    {
      "id": 1,
      "name": "Morning Yoga",
      "category": "Yoga",
      "trainer": {...},
      "capacity": 30,
      "enrolled_count": 25,
      "difficulty_level": "beginner",
      "schedules": [...]
    }
  ]
}
```

### Create Class (Admin/Trainer)
```http
POST /classes
Content-Type: application/json
Authorization: Bearer <admin_token>

{
  "name": "Evening Pilates",
  "description": "Evening pilates class",
  "trainer_id": 1,
  "area_id": 1,
  "category": "Pilates",
  "capacity": 20,
  "duration_minutes": 60,
  "difficulty_level": "intermediate",
  "schedule_type": "recurring"
}

Response: 201 Created
```

### Get Class Details
```http
GET /classes/{id}
Authorization: Bearer <token>

Response: 200 OK
{
  "id": 1,
  "name": "Morning Yoga",
  "trainer": {...},
  "schedules": [...],
  "enrolled_members": [...],
  "capacity": 30,
  "available_slots": 5
}
```

### Update Class
```http
PUT /classes/{id}
Authorization: Bearer <trainer_token>
```

### Delete Class
```http
DELETE /classes/{id}
Authorization: Bearer <admin_token>
```

### Get Available Classes
```http
GET /classes/available/today
Authorization: Bearer <token>

Response: 200 OK
```

---

## 7. Class Schedule Endpoints

### Add Class Schedule
```http
POST /classes/{id}/schedules
Content-Type: application/json
Authorization: Bearer <trainer_token>

{
  "day_of_week": "mon",
  "start_time": "10:00",
  "end_time": "11:00"
}
or
{
  "scheduled_date": "2024-03-20",
  "start_time": "10:00",
  "end_time": "11:00"
}

Response: 201 Created
```

### Get Class Schedules
```http
GET /classes/{id}/schedules?from_date=2024-03-14&to_date=2024-03-21
Authorization: Bearer <token>
```

### Cancel Schedule
```http
POST /class-schedules/{schedule_id}/cancel
Content-Type: application/json
Authorization: Bearer <trainer_token>

{
  "reason": "Trainer illness"
}

Response: 200 OK
```

---

## 8. Class Enrollment Endpoints

### Enroll in Class
```http
POST /class-enrollments
Content-Type: application/json
Authorization: Bearer <member_token>

{
  "schedule_id": 1
}

Response: 201 Created
{
  "id": 1,
  "member_id": 1,
  "schedule_id": 1,
  "status": "active",
  "enrollment_date": "2024-03-14"
}
```

### Cancel Enrollment
```http
POST /class-enrollments/{id}/cancel
Authorization: Bearer <member_token>

Response: 200 OK
```

### Get Member's Enrollments
```http
GET /members/{id}/enrollments?status=active
Authorization: Bearer <token>
```

### Get Class Enrollments (Trainer/Admin)
```http
GET /classes/{id}/enrollments
Authorization: Bearer <trainer_token>
```

### Update Attendance
```http
PUT /class-enrollments/{id}/attendance
Content-Type: application/json
Authorization: Bearer <trainer_token>

{
  "attended": true
}

Response: 200 OK
```

---

## 9. Attendance Endpoints

### Check-in to Class
```http
POST /attendance/check-in
Content-Type: application/json
Authorization: Bearer <member_token>

{
  "schedule_id": 1
}

Response: 201 Created
{
  "id": 1,
  "member_id": 1,
  "check_in_time": "2024-03-14T10:05:00Z"
}
```

### Check-out from Class
```http
POST /attendance/check-out
Content-Type: application/json
Authorization: Bearer <member_token>

{
  "attendance_id": 1
}

Response: 200 OK
{
  "id": 1,
  "duration_minutes": 58,
  "check_out_time": "2024-03-14T11:03:00Z"
}
```

### Get Member's Attendance
```http
GET /members/{id}/attendance?from_date=2024-01-01&to_date=2024-03-14&limit=50
Authorization: Bearer <token>

Response: 200 OK
{
  "data": [{...}],
  "stats": {
    "total_visits": 45,
    "this_month": 8,
    "average_duration": 58
  }
}
```

### Get Class Attendance Report
```http
GET /classes/{id}/attendance?from_date=2024-01-01&to_date=2024-03-14
Authorization: Bearer <trainer_token>
```

### Get Today's Attendance
```http
GET /attendance/today
Authorization: Bearer <admin_token>

Response: 200 OK
{
  "checked_in": 45,
  "checked_out": 30,
  "records": [...]
}
```

### Get Attendance Statistics
```http
GET /attendance/stats?from_date=2024-01-01&to_date=2024-03-14&group_by=member
Authorization: Bearer <admin_token>

Response: 200 OK
{
  "total_visits": 500,
  "average_visit_duration": 58,
  "most_active_member": {...},
  "by_class": [...]
}
```

---

## 10. Equipment Endpoints

### List Equipment
```http
GET /equipment?page=1&status=available&category=cardio&area_id=1
Authorization: Bearer <token>

Response: 200 OK
{
  "data": [
    {
      "id": 1,
      "name": "Treadmill A",
      "category": "Cardio",
      "status": "available",
      "area": {...},
      "condition": "good",
      "last_maintenance": "2024-02-14"
    }
  ]
}
```

### Create Equipment (Admin)
```http
POST /equipment
Content-Type: application/json
Authorization: Bearer <admin_token>

{
  "name": "New Treadmill",
  "category": "Cardio",
  "area_id": 1,
  "serial_number": "TREADMILL-001",
  "purchase_date": "2024-03-01",
  "purchase_cost": 1500.00,
  "warranty_expiry": "2025-03-01"
}

Response: 201 Created
```

### Get Equipment Details
```http
GET /equipment/{id}
```

### Update Equipment Status
```http
PUT /equipment/{id}
Content-Type: application/json
Authorization: Bearer <admin_token>

{
  "status": "maintenance",
  "condition": "fair",
  "next_maintenance_date": "2024-04-14"
}

Response: 200 OK
```

### Record Equipment Usage
```http
POST /equipment/{id}/usage/start
Content-Type: application/json
Authorization: Bearer <member_token>

{
  "member_id": 1
}

Response: 201 Created
```

### End Equipment Usage
```http
POST /equipment-usage/{usage_id}/end
Authorization: Bearer <member_token>

Response: 200 OK
{
  "duration_minutes": 45
}
```

### Get Equipment Usage History
```http
GET /equipment/{id}/usage?from_date=2024-01-01&to_date=2024-03-14
Authorization: Bearer <admin_token>
```

### Log Maintenance
```http
POST /equipment/{id}/maintenance
Content-Type: application/json
Authorization: Bearer <admin_token>

{
  "maintenance_date": "2024-03-14",
  "maintenance_type": "preventive",
  "description": "Regular service and inspection",
  "cost": 150.00,
  "performed_by": "John's Maintenance",
  "next_due_date": "2024-04-14"
}

Response: 201 Created
```

### Get Maintenance Logs
```http
GET /equipment/{id}/maintenance?limit=20
Authorization: Bearer <admin_token>
```

---

## 11. Payment Endpoints

### List Payments
```http
GET /payments?page=1&payment_status=completed&from_date=2024-01-01
Authorization: Bearer <admin_token>

Response: 200 OK
{
  "data": [...],
  "total_amount": 5000.00,
  "pagination": {...}
}
```

### Record Payment
```http
POST /payments
Content-Type: application/json
Authorization: Bearer <member_token>

{
  "member_id": 1,
  "membership_id": 1,
  "amount": 29.99,
  "payment_type": "membership",
  "payment_method": "credit_card",
  "transaction_id": "TXN-123456"
}

Response: 201 Created
{
  "id": 1,
  "receipt_url": "/receipts/receipt-001.pdf",
  "payment_status": "completed"
}
```

### Get Member Payment History
```http
GET /members/{id}/payments?limit=20
Authorization: Bearer <token>

Response: 200 OK
{
  "data": [...],
  "total_paid": 300.00,
  "outstanding_balance": 0
}
```

### Get Payments by Date Range
```http
GET /payments/report/by-date?from_date=2024-01-01&to_date=2024-03-14
Authorization: Bearer <admin_token>
```

### Get Payment Statistics
```http
GET /payments/stats?from_date=2024-01-01&to_date=2024-03-14
Authorization: Bearer <admin_token>

Response: 200 OK
{
  "total_revenue": 15000.00,
  "total_transactions": 250,
  "by_method": {
    "credit_card": 10000,
    "bank_transfer": 5000
  },
  "by_type": {
    "membership": 12000,
    "renewal": 3000
  },
  "average_transaction": 60.00,
  "this_month_vs_last": "+15%"
}
```

### Generate Invoice
```http
GET /payments/{id}/invoice
Authorization: Bearer <token>

Response: 200 OK (PDF)
```

### Refund Payment (Admin)
```http
POST /payments/{id}/refund
Content-Type: application/json
Authorization: Bearer <admin_token>

{
  "reason": "Member request"
}

Response: 200 OK
{
  "status": "refunded",
  "refund_date": "2024-03-14"
}
```

---

## 12. Admin/Reporting Endpoints

### Dashboard Statistics
```http
GET /admin/dashboard
Authorization: Bearer <admin_token>

Response: 200 OK
{
  "total_members": 500,
  "active_memberships": 450,
  "total_revenue_this_month": 15000,
  "classes_today": 12,
  "attendance_today": 200,
  "equipment_status": {
    "available": 40,
    "maintenance": 3,
    "damaged": 1
  },
  "upcoming_renewals": 25
}
```

### Member Report
```http
GET /admin/reports/members?from_date=2024-01-01&to_date=2024-03-14
Authorization: Bearer <admin_token>
```

### Revenue Report
```http
GET /admin/reports/revenue?period=month&year=2024&month=3
Authorization: Bearer <admin_token>
```

### Class Report
```http
GET /admin/reports/classes
Authorization: Bearer <admin_token>
```

### Equipment Maintenance Report
```http
GET /admin/reports/equipment-maintenance?overdue=true
Authorization: Bearer <admin_token>
```

### Export Data
```http
GET /admin/export/members?format=csv
Authorization: Bearer <admin_token>

Response: 200 OK (CSV file)
```

---

## Error Response Format

All error responses follow this format:

```json
{
  "error": true,
  "message": "Error description",
  "code": 400,
  "details": {
    "field": ["error message"]
  }
}
```

### Common Status Codes
- `200 OK` - Successful request
- `201 Created` - Resource created successfully
- `204 No Content` - Successful deletion
- `400 Bad Request` - Invalid input
- `401 Unauthorized` - Authentication required
- `403 Forbidden` - No permission
- `404 Not Found` - Resource not found
- `409 Conflict` - Resource conflict
- `422 Unprocessable Entity` - Validation error
- `429 Too Many Requests` - Rate limited
- `500 Internal Server Error` - Server error

---

## Pagination

All list endpoints support pagination with these parameters:
- `page` (default: 1)
- `limit` (default: 10, max: 100)

Response includes:
```json
{
  "data": [...],
  "pagination": {
    "current_page": 1,
    "total": 100,
    "per_page": 10,
    "last_page": 10,
    "from": 1,
    "to": 10
  }
}
```

---

## Filtering & Sorting

Filters vary by endpoint, common ones:
- `status`, `status[]` - Filter by status
- `from_date`, `to_date` - Date range filter
- `search` - Full text search
- `sort_by` - Sort field
- `order` - asc/desc

Example:
```
GET /members?status[]=active&status[]=paused&sort_by=created_at&order=desc
```

---

## Rate Limiting

- **Unauthenticated**: 60 requests per minute
- **Authenticated**: 1000 requests per hour
- **Admin**: Unlimited

Rate limit info in response headers:
```
X-RateLimit-Limit: 1000
X-RateLimit-Remaining: 999
X-RateLimit-Reset: 1234567890
```
