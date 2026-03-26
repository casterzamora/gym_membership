# Frontend Skill - Gym Attendance System

## Overview
Frontend for the gym attendance management system providing a responsive web interface for admin staff, members, and gym managers to manage memberships, track attendance, process payments, and view analytics.

## Tech Stack
- **Framework**: React 18+ or Vue 3 (configurable)
- **Build Tool**: Vite
- **Styling**: Tailwind CSS or Bootstrap 5
- **State Management**: Zustand, Pinia, or Redux
- **HTTP Client**: Axios or Fetch API
- **Routing**: React Router or Vue Router
- **Forms**: React Hook Form or Formik
- **UI Components**: Shadcn/ui, Headless UI, or component library
- **Testing**: Vitest, Jest

## Project Structure
```
resources/
├── js/
│   ├── components/
│   │   ├── Common/
│   │   │   ├── Navbar.jsx
│   │   │   ├── Sidebar.jsx
│   │   │   ├── Button.jsx
│   │   │   └── StatusBadge.jsx
│   │   ├── Forms/
│   │   │   ├── MemberForm.jsx
│   │   │   ├── PaymentForm.jsx
│   │   │   ├── ClassForm.jsx
│   │   │   ├── TrainerForm.jsx
│   │   │   ├── ScheduleForm.jsx
│   │   │   └── LoginForm.jsx
│   │   ├── Tables/
│   │   │   ├── MembersTable.jsx
│   │   │   ├── TrainersTable.jsx
│   │   │   ├── ClassesTable.jsx
│   │   │   ├── AttendanceTable.jsx
│   │   │   ├── SchedulesTable.jsx
│   │   │   └── PaymentsTable.jsx
│   │   ├── Cards/
│   │   │   ├── MemberCard.jsx
│   │   │   ├── ClassCard.jsx
│   │   │   ├── MetricCard.jsx
│   │   │   └── PaymentCard.jsx
│   │   ├── Charts/
│   │   │   ├── AttendanceChart.jsx
│   │   │   ├── RevenueChart.jsx
│   │   │   ├── ClassPopularityChart.jsx
│   │   │   └── TrainerWorkloadChart.jsx
│   │   └── Modals/
│   │       ├── ConfirmationModal.jsx
│   │       ├── CheckinModal.jsx
│   │       └── PaymentModal.jsx
│   ├── pages/
│   │   ├── Dashboard.jsx
│   │   ├── Login.jsx
│   │   ├── Members/
│   │   │   ├── MembersList.jsx
│   │   │   ├── MemberCreate.jsx
│   │   │   ├── MemberDetail.jsx
│   │   │   └── MemberEdit.jsx
│   │   ├── Trainers/
│   │   │   ├── TrainersList.jsx
│   │   │   ├── TrainerCreate.jsx
│   │   │   ├── TrainerDetail.jsx
│   │   │   └── TrainerCertifications.jsx
│   │   ├── Classes/
│   │   │   ├── ClassesList.jsx
│   │   │   ├── ClassCreate.jsx
│   │   │   ├── ClassDetail.jsx
│   │   │   └── ClassSchedules.jsx
│   │   ├── Schedules/
│   │   │   ├── ScheduleCalendar.jsx
│   │   │   ├── ScheduleCreate.jsx
│   │   │   └── ScheduleEdit.jsx
│   │   ├── Attendance/
│   │   │   ├── QuickCheckin.jsx (main feature)
│   │   │   ├── AttendanceRecords.jsx
│   │   │   ├── AttendanceReports.jsx
│   │   │   └── AttendanceHistory.jsx
│   │   ├── Payments/
│   │   │   ├── PaymentsList.jsx
│   │   │   ├── PaymentCreate.jsx
│   │   │   ├── PaymentDetail.jsx
│   │   │   └── PaymentReports.jsx
│   │   ├── Equipment/
│   │   │   ├── EquipmentList.jsx
│   │   │   ├── EquipmentCreate.jsx
│   │   │   ├── EquipmentDetail.jsx
│   │   │   └── EquipmentUsage.jsx
│   │   ├── Plans/
│   │   │   ├── PlansList.jsx
│   │   │   ├── PlanCreate.jsx
│   │   │   └── PlanEdit.jsx
│   │   └── Reports/
│   │       ├── RevenueReport.jsx
│   │       ├── AttendanceReport.jsx
│   │       ├── MembershipReport.jsx
│   │       ├── TrainerReport.jsx
│   │       ├── EquipmentReport.jsx
│   │       └── MemberReport.jsx
│   ├── hooks/
│   │   ├── useMembers.js
│   │   ├── useTrainers.js
│   │   ├── useClasses.js
│   │   ├── useAttendance.js
│   │   ├── usePayments.js
│   │   ├── useEquipment.js
│   │   ├── useAuth.js
│   │   └── useFetch.js
│   ├── stores/
│   │   ├── authStore.js
│   │   ├── memberStore.js
│   │   ├── trainerStore.js
│   │   ├── classStore.js
│   │   ├── attendanceStore.js
│   │   ├── paymentStore.js
│   │   └── uiStore.js
│   ├── services/
│   │   ├── api.js (base config)
│   │   ├── memberService.js
│   │   ├── trainerService.js
│   │   ├── classService.js
│   │   ├── scheduleService.js
│   │   ├── attendanceService.js
│   │   ├── paymentService.js
│   │   ├── equipmentService.js
│   │   ├── planService.js
│   │   └── reportService.js
│   ├── utils/
│   │   ├── formatters.js (dates, currency)
│   │   ├── validators.js (form validation)
│   │   ├── constants.js (status enums, etc.)
│   │   └── helpers.js (utility functions)
│   ├── App.jsx
│   └── main.jsx
└── css/
    ├── app.css
    ├── components.css
    └── utilities.css
```

## Core Pages/Views

### Dashboard (Admin)
- Real-time metrics: Total members, active memberships, today's attendance
- Revenue overview: Monthly/yearly total from payments
- Key alerts: Expiring memberships (< 7 days), at-risk members
- Quick actions: Check-in member, new member form, new payment
- Charts: Attendance trends, revenue by plan, popular classes
- Recent activity log
- Trainer workload summary

### Member Management
- Members list with filtering (status, plan, join date)
- Search by name, email, phone
- Bulk actions (export, status change)
- Create new member form
- Member detail page: profile, membership history, payments, attendance
- Membership plan upgrade interface
- Member status badge (Active/Expired/Suspended)

### Trainer Management
- Trainers list with their assigned classes
- Add/edit trainer information
- Trainer certifications section
- Class assignments (drag-drop or selection)
- Trainer availability calendar
- Workload metrics (classes per trainer)

### Fitness Classes Management
- Classes list with details (name, trainer, max participants, equipment)
- Create/edit class form
- Equipment selection/multi-select
- Trainer assignment dropdown
- Class schedules inline table

### Class Schedules & Timetable
- Weekly/monthly calendar view
- Schedule creation form (class, date, time, duration)
- Drag-drop rescheduling (optional)
- Conflict detection display
- Check capacity vs attendance
- Current enrollment count

### Attendance Tracking (Main Feature)
- **Quick Check-in Interface**:
  - Large search field (member ID, name, email)
  - Display found member card with name, status, photo (optional)
  - Show today's available classes
  - One-click check-in button
  - Confirmation: "Member checked in at 14:32"
  
- **Attendance Records List**:
  - Today's attendance summary
  - Attendance history per member
  - Filter by date range, member, class
  - Able to record attendance for past dates (catch-up)
  - Status indicators (Present/Absent/Late)

- **Attendance Reports**:
  - Class attendance analytics
  - Member attendance trends
  - Most/least attended classes
  - Trainer performance by attendance
  - Export to CSV/Excel

### Payment Management
- Payment history list per member
- Create payment form:
  - Member selection
  - Amount and method
  - Coverage period (start/end date)
  - Paid date selector
- Payment status tracking (Completed, Pending, Failed)
- Payment confirmation page
- Coverage period visualization (timeline)

### Membership Plan Management
- Plans list with pricing
- Create/edit plan form (name, price, duration_months, description)
- Members enrolled per plan (metrics)
- Plan revenue calculations

### Equipment Management
- Equipment inventory list
- Equipment status (Available, Maintenance, Out of Service)
- Last maintenance date tracking
- Add/edit equipment form
- Equipment usage statistics
- Usage by class (which classes use which equipment)

### Member Portal (Self-Service)
- Login with member credentials
- My membership info (plan, expiry date, status)
- My attendance history (how many classes attended)
- Available classes to enroll in
- My upcoming class schedule
- My payment history
- Class schedules calendar view

### Reports & Analytics
Admin access to:
- Expired memberships report (export)
- Most popular classes (by attendance)
- Trainer workload analysis
- Revenue reports (by period, by plan, by trainer)
- Membership growth trends
- Equipment usage frequency
- Members with low activity (< X classes in Y days)
- Revenue forecasting
- Member retention metrics

## Component Architecture

### Presentational (Dumb) Components
- Receive data via props
- No side effects
- Highly reusable
- Examples: Button, Card, Input

### Container (Smart) Components
- Connect to state/API
- Handle business logic
- Manage state
- Examples: MembersList, Dashboard

### Layout Components
- Navbar, Sidebar, Footer
- Define page structure
- Single responsibility

## State Management Pattern
```
Global State:
├── auth (user info, token, permissions)
├── members (member list, filters)
├── memberships (membership data)
└── ui (modals, notifications, loading)

Local State:
├── Form inputs
├── UI toggles (collapsed menus)
└── Temporary data
```

## API Integration
- Centralized API configuration
- Interceptors for auth tokens
- Error handling
- Loading states
- Type safety (if using TypeScript)

Example service:
```javascript
// memberService.js
export const getMembers = async (filters) => {
  const response = await api.get('/members', { params: filters });
  return response.data;
};

export const createMember = async (data) => {
  const response = await api.post('/members', data);
  return response.data;
};
```

## User Workflows

### Admin: Register New Member
1. Admin navigates to Members → Create
2. Fills form: name, email, phone, DOB
3. Selects membership plan from dropdown
4. Records initial payment (amount, method, coverage dates)
5. System creates member and payment record
6. Confirmation page with member ID
7. Optional: Send welcome email/SMS

### Admin: Quick Check-in During Peak Hours
1. Staff at front desk opens Check-in page
2. Types/scans member ID or name in search
3. System auto-fills member name and status
4. Displays available classes for today
5. Staff selects class (or just records general entry)
6. Clicks "Check In" button
7. System validates: member active, not duplicate today
8. Shows "✓ Checked in at 14:32" confirmation
9. Generates QR code receipt (optional)

### Member: Renew Expiring Membership
1. Member receives email: "Your membership expires in 7 days"
2. Member logs into portal or admin sends renewal form
3. Shows current plan and expiry date
4. Shows plan comparison if upgrading
5. Member confirms renewal plan
6. Processes payment (amount, method, coverage dates)
7. Updates MEMBERS table with new expiry
8. Shows confirmation: membership extended to DATE

### Trainer: View My Classes & Workload
1. Trainer views dashboard
2. Shows my assigned classes (this month)
3. Shows attendance per class taught
4. Shows upcoming schedules
5. Can see trainer workload analytics (hours per week)

### Admin: Process Member Payment
1. Admin opens Payments section
2. Searches member or opens from member detail page
3. Enters amount, payment method, coverage period
4. System validates no overlapping periods
5. Confirms payment date
6. Creates payment record
7. Shows: "Payment recorded - Membership covers until DATE"
8. If member was expired, reactivates them

### Admin: Analyze Class Attendance
1. Admin opens Reports → Attendance Analytics
2. Selects date range (last 30 days)
3. Shows ranking of most attended classes
4. Shows class-by-class details: capacity vs attendance
5. Identifies slow-moving classes
6. Can drill down to see per-class member list
7. Export data to CSV

### Admin: View Equipment Usage
1. Admin opens Equipment section
2. Shows all equipment with status
3. Filters by status (Available, Maintenance)
4. Views usage frequency (which classes use it most)
5. Marks equipment for maintenance when needed
6. Equipment usage heatmap (visual)

### Admin: Send Membership Reminders
1. System identifies members expiring in 7 days
2. Admin views "Members Requiring Renewal" report
3. Selects members to contact
4. Sends batch reminder email/SMS
5. Tracks who has renewed vs who needs follow-up
6. Optional: Auto-send before filtering

## Styling Approach
- Component-scoped styles
- Consistent color scheme
- Responsive design (mobile, tablet, desktop)
- Accessibility standards (WCAG)
- Dark mode support (optional)

## Form Handling
- Validation on input and submit
- Clear error messages
- Loading states during submission
- Success/error toasts
- Auto-save drafts (optional)

## Performance Optimization
- Code splitting by route
- Lazy loading components
- Image optimization
- Debounce search/filter inputs
- Memoization of expensive computations
- Virtual scrolling for large lists

## Error Handling
- User-friendly error messages
- Network error detection
- Retry mechanisms
- Graceful fallbacks
- Error logging/monitoring

## Accessibility
- Semantic HTML
- ARIA labels
- Keyboard navigation
- Color contrast
- Focus management
- Screen reader support

## Testing
- Unit tests for utilities
- Component tests
- Integration tests for workflows
- E2E tests for critical paths
- Visual regression testing (optional)

## Navigation Structure (Admin)
```
/
├── /login
├── /dashboard
├── /members
│   ├── /members (list)
│   ├── /members/create
│   └── /members/:id (detail/edit)
├── /trainers
│   ├── /trainers (list)
│   ├── /trainers/create
│   ├── /trainers/:id (detail/edit)
│   └── /trainers/:id/certifications (manage certs)
├── /classes
│   ├── /classes (list)
│   ├── /classes/create
│   ├── /classes/:id (detail/edit)
│   └── /classes/:id/schedules (timetable)
├── /schedules
│   ├── /schedules/calendar (week/month view)
│   ├── /schedules/create
│   └── /schedules/:id/edit
├── /attendance
│   ├── /attendance/quick-checkin (main feature)
│   ├── /attendance/records (list/history)
│   ├── /attendance/reports (analytics)
│   └── /attendance/history/:memberId
├── /payments
│   ├── /payments (list)
│   ├── /payments/create
│   ├── /payments/:id (detail)
│   └── /payments/reports
├── /equipment
│   ├── /equipment (inventory)
│   ├── /equipment/create
│   ├── /equipment/:id (detail/edit)
│   └── /equipment/usage (analytics)
├── /plans
│   ├── /plans (list)
│   ├── /plans/create
│   └── /plans/:id (edit)
├── /reports
│   ├── /reports/revenue
│   ├── /reports/attendance
│   ├── /reports/memberships
│   ├── /reports/trainers
│   ├── /reports/equipment
│   └── /reports/members
└── /settings (admin controls)

Member Portal:
├── /portal/login
├── /portal/dashboard
├── /portal/membership
├── /portal/classes
├── /portal/attendance
├── /portal/payments
└── /portal/profile
```

## Authentication Flow
1. User credentials → Login page
2. API authentication → Receive token
3. Store token (localStorage/cookies)
4. Set auth header on API calls
5. Handle token expiry → Re-login
6. Logout → Clear token & redirect

## Environment Configuration
- API base URL (dev/staging/prod)
- Feature flags
- Debug mode
- Analytics tracking
- Third-party service keys

## Browser Support
- Modern browsers (Chrome, Firefox, Safari, Edge)
- Mobile browsers support
- Graceful degradation for older browsers (optional)
