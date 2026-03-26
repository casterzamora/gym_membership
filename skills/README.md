# Gym Membership and Attendance Management System
## Project Skills & Knowledge Base

This directory contains comprehensive skill guides for developing the Gym Membership and Attendance Management System. These documents serve as a reference and foundation for all development work.

**System Scope**: A complete relational database system managing gym operations including 13 normalized tables, comprehensive admin dashboard, member portal, and analytics.

## Skills Overview

### 1. **Architecture** (`architecture.md`)
Covers the overall system design, data flow, and integration patterns.

**Key Topics**:
- System architecture and components
- Data flow and request handling
- Scalability strategies
- Security architecture
- Deployment and monitoring
- Business process workflows

**When to Reference**:
- Planning new features
- Understanding system interactions
- Making technology decisions
- Solving performance issues
- Setting up deployment pipelines

---

### 2. **Backend** (`backend.md`)
Comprehensive guide for Laravel API development and business logic with 13 normalized entities.

**Key Topics**:
- Project structure with 13 models and services
- Core entities (Member Plans, Members, Trainers, Certifications, Classes, Schedules, Attendance, Payments, Equipment, Upgrades)
- Many-to-many relationships and junction tables
- Trigger-based business rules (membership status, capacity checks, payment validation)
- Stored procedures for complex operations
- API conventions and response formats
- Key workflows (member onboarding, check-in, payments, renewals)
- Database normalization to 3NF
- Security best practices

**When to Reference**:
- Creating new API endpoints
- Writing models and controllers
- Implementing business logic
- Setting up database migrations
- Designing triggers and stored procedures
- Structuring code for maintainability

---

### 3. **Frontend** (`frontend.md`)
Complete frontend development guide for React/Vue with Vite for admin dashboard and member portal.

**Key Topics**:
- Project structure with 20+ components and 15+ pages
- Core pages (Dashboard, Members, Trainers, Classes, Attendance, Payments, Equipment, Reports)
- Quick check-in interface (main feature)
- Component architecture patterns
- State management strategy
- API integration and services
- Admin workflows (member registration, payment processing, renewals)
- Member portal features (view membership, attendance history, class enrollment)
- Routing and navigation structure
- Performance optimization
- Testing strategies

**When to Reference**:
- Building new pages and components
- Managing application state
- Integrating with backend APIs
- Improving performance
- Structuring user workflows

---

### 4. **UI/UX** (`ui-ux.md`)
Design system and user experience guidelines.

**Key Topics**:
- Design principles (usability, accessibility)
- Visual design system and typography
- Component library guidelines
- Layout and navigation patterns
- Interaction patterns and workflows
- Responsive design approach
- Accessibility standards (WCAG)

**When to Reference**:
- Designing new features
- Creating consistent interfaces
- Ensuring accessibility
- Improving user workflows
- Building reusable UI components

---

## How to Use These Skills

### For Building New Features
1. **Reference Architecture**: Understand where feature fits in system
2. **Reference Backend** (if creating endpoints): Plan API structure
3. **Reference Frontend** (if creating UI): Plan component structure
4. **Reference UI/UX**: Design the user experience

### For Code Review
- Check backend code against Backend skill patterns
- Check frontend code against Frontend skill patterns
- Verify UI/UX against design guidelines

### For Troubleshooting
- **Performance issue?** → Check Architecture (Scalability, Performance sections)
- **API problem?** → Check Backend (API Conventions)
- **UI problem?** → Check Frontend (Component Architecture)
- **Design consistency?** → Check UI/UX (Design System, Components)

### For Onboarding New Team Members
1. Start with Architecture skill for overall understanding
2. Deep dive into Backend/Frontend based on their role
3. Reference UI/UX as needed for design decisions

---

## Technology Stack Summary

### Frontend
- React 18+ or Vue 3
- Vite (build tool)
- Tailwind CSS or Bootstrap 5
- Zustand/Pinia or Redux (state management)
- React Hook Form or Formik (forms)
- Axios or Fetch API (HTTP)
- Vitest/Jest (testing)
- Chart library for analytics (Chart.js or Recharts)
- Calendar library for schedules and class timetables

### Backend
- Laravel 11 + PHP 8.1+
- MySQL 8.0+ (normalized to 3NF)
- Laravel Sanctum (authentication)
- Eloquent ORM (13 models)
- Laravel Events & Listeners (async workflows)
- Stored Procedures & Triggers (database-level validation)
- PHPUnit (testing)

### Database Features
- 13 normalized tables with relationships
- Complex triggers for business rule enforcement
- Stored procedures for operations
- Views for reporting and analytics
- Strategic indexing for performance
- Foreign key constraints with cascading rules
- Composite keys for junction tables
- CHECK constraints for domain validation

### Infrastructure
- Vite for frontend builds
- Laravel Artisan for backend scaffolding
- Docker (optional containerization)
- GitHub/GitLab for version control

---

## Project Structure Quick Reference

```
gym_attendance/
├── skills/                          # This knowledge base
│   ├── architecture.md              # System design (3-layer)
│   ├── backend.md                   # Laravel + 13 entities
│   ├── frontend.md                  # React/Vue pages & workflows
│   ├── ui-ux.md                     # Design system
│   └── README.md                    # This file
│
├── app/                             # Laravel backend
│   ├── Models/ (13 models)
│   │   ├── User.php
│   │   ├── MembershipPlan.php
│   │   ├── Member.php
│   │   ├── Trainer.php
│   │   ├── Certification.php
│   │   ├── TrainerCertification.php
│   │   ├── FitnessClass.php
│   │   ├── ClassSchedule.php
│   │   ├── Attendance.php
│   │   ├── Payment.php
│   │   ├── Equipment.php
│   │   ├── ClassEquipment.php
│   │   ├── EquipmentUsage.php
│   │   └── MembershipUpgrade.php
│   ├── Http/Controllers/
│   ├── Services/                    # Business logic
│   ├── Events/ (async workflows)
│   └── Listeners/
│
├── database/                        # Database layer
│   ├── migrations/ (13 tables + triggers)
│   ├── seeders/                     # Initial data
│   └── factories/                   # Test data
│
├── resources/
│   ├── js/ (React/Vue frontend)
│   │   ├── components/              # 20+ reusable components
│   │   ├── pages/                   # 15+ page components
│   │   ├── hooks/                   # Custom hooks
│   │   ├── stores/                  # Global state
│   │   └── services/                # API integration
│   └── css/                         # Tailwind + custom
│
├── routes/
│   ├── api.php                      # API endpoints (13 resources)
│   └── web.php
│
├── vite.config.js                   # Frontend build config
├── composer.json                    # PHP dependencies
├── package.json                     # Node dependencies
└── phpunit.xml                      # Testing config
```

### Core 13 Database Tables
1. `membership_plans` - Subscription options
2. `members` - Gym members
3. `trainers` - Gym trainers
4. `certifications` - Professional certifications
5. `trainer_certifications` - Junction (trainers ↔ certs)
6. `fitness_classes` - Class definitions
7. `class_schedules` - When classes occur
8. `attendance` - Junction (members ↔ schedules)
9. `payments` - Payment records
10. `equipment` - Gym equipment
11. `class_equipment` - Junction (classes ↔ equipment)
12. `equipment_usage` - Equipment usage tracking
13. `membership_upgrades` - Member plan history

---

## Common Development Workflows

### Creating a New API Endpoint
1. **Design**: Check Architecture for data flow
2. **Backend**: Check Backend skill for controller/model patterns
3. **Database**: Create migration if needed
4. **Test**: Write tests for endpoint
5. **Document**: Update API documentation

### Building a New Frontend Page
1. **Design**: Check UI/UX for patterns and components
2. **Planning**: Check Frontend for page structure
3. **Development**: Create page and components
4. **Integration**: Connect to backend API per Frontend guide
5. **Testing**: Test against all responsive breakpoints

### Database Changes
1. **Review**: Check Architecture and Backend for conventions
2. **Create Migration**: Use Laravel migration generator
3. **Test**: Test locally with sample data
4. **Document**: Update DATABASE_SCHEMA.md if exists
5. **Deploy**: Apply migration in order

---

## Key Principles Across All Skills

### Code Quality
- ✅ DRY (Don't Repeat Yourself)
- ✅ SOLID principles
- ✅ Readable, maintainable code
- ✅ Consistent naming conventions

### User Experience
- ✅ Accessible to all users
- ✅ Responsive on all devices
- ✅ Fast and performant
- ✅ Clear and intuitive

### Security
- ✅ HTTPS everywhere
- ✅ Input validation
- ✅ Authentication/Authorization
- ✅ Protect sensitive data

### Performance
- ✅ Optimize database queries
- ✅ Cache strategically
- ✅ Minimize frontend size
- ✅ Monitor and alert

---

## Communication with AI Assistant

When asking the AI for help:

1. **Be specific**: "I need to create a member payment endpoint that updates membership expiry"
2. **Reference skills**: "Following the Backend skill, should the logic be in a service class?"
3. **Provide context**: Share relevant code snippets or requirements
4. **Ask for trade-offs**: "What's better: cached data or always fresh?"

### Example Requests
- "Create the Member API with CRUD endpoints following the Backend skill patterns"
- "Build the Dashboard page using the component structure in Frontend skill"
- "Design the member check-in flow following UI/UX interaction patterns"
- "Implement caching for members list per Architecture skill recommendations"

---

## Maintenance & Updates

These skills should be updated when:
- New technology decisions are made
- Best practices change
- Team discovers better patterns
- New requirements emerge

Keep these guides fresh and aligned with actual project practices. They serve as living documentation.

---

## Quick Decision Trees

### Choosing Between Service Layer and Fat Controller
- Small, simple logic → Keep in controller
- Medium complexity → Extract to service
- Large, reusable, testable logic → Definitely service

### Choosing Between Local and Global State
- Form input state → Local component state
- User authentication → Global state
- Modal open/close → Local state
- Filters and sorting → Could be either
- Theme/language → Global state

### Choosing Between API Call Patterns
- Simple CRUD → Direct API calls
- Complex flows → Service layer with business logic
- Error handling → Centralized HTTP client
- Caching → Repository pattern or service

---

For detailed information on any topic, reference the specific skill document.
