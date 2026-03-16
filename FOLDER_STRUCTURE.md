# Project Folder Structure

## Complete Project Organization

```
gym-management-system/
│
├── .github/
│   ├── workflows/              # CI/CD pipelines
│   │   ├── test.yml           # Run tests on push
│   │   ├── deploy.yml         # Deploy to production
│   │   └── lint.yml           # Code quality checks
│   └── copilot-instructions.md
│
├── docker/                      # Docker configuration
│   ├── Dockerfile              # Main app image
│   ├── nginx/                  # Nginx configuration
│   │   ├── Dockerfile
│   │   └── nginx.conf
│   └── php/                    # PHP-FPM configuration
│       ├── Dockerfile
│       └── php.ini
│
├── database/
│   ├── migrations/             # Database migrations
│   │   ├── 2024_03_14_000001_create_users_table.php
│   │   ├── 2024_03_14_000002_create_members_table.php
│   │   ├── 2024_03_14_000003_create_trainers_table.php
│   │   ├── 2024_03_14_000004_create_areas_table.php
│   │   ├── 2024_03_14_000005_create_membership_plans_table.php
│   │   ├── 2024_03_14_000006_create_memberships_table.php
│   │   ├── 2024_03_14_000007_create_classes_table.php
│   │   ├── 2024_03_14_000008_create_class_schedules_table.php
│   │   ├── 2024_03_14_000009_create_class_enrollments_table.php
│   │   ├── 2024_03_14_000010_create_attendance_table.php
│   │   ├── 2024_03_14_000011_create_equipment_table.php
│   │   ├── 2024_03_14_000012_create_equipment_usage_table.php
│   │   ├── 2024_03_14_000013_create_maintenance_logs_table.php
│   │   └── 2024_03_14_000014_create_payments_table.php
│   ├── seeders/                # Database seeders
│   │   ├── DatabaseSeeder.php
│   │   ├── UserSeeder.php
│   │   ├── MemberSeeder.php
│   │   ├── TrainerSeeder.php
│   │   ├── MembershipPlanSeeder.php
│   │   ├── ClassSeeder.php
│   │   ├── AreaSeeder.php
│   │   └── EquipmentSeeder.php
│   └── factories/              # Model factories
│       ├── UserFactory.php
│       ├── MemberFactory.php
│       ├── TrainerFactory.php
│       ├── ClassFactory.php
│       └── PaymentFactory.php
│
├── app/
│   ├── Http/
│   │   ├── Controllers/        # API Controllers
│   │   │   ├── AuthController.php
│   │   │   ├── MemberController.php
│   │   │   ├── TrainerController.php
│   │   │   ├── ClassController.php
│   │   │   ├── ClassEnrollmentController.php
│   │   │   ├── AttendanceController.php
│   │   │   ├── PaymentController.php
│   │   │   ├── EquipmentController.php
│   │   │   ├── MembershipController.php
│   │   │   ├── AreaController.php
│   │   │   └── AdminController.php
│   │   ├── Middleware/        # HTTP Middleware
│   │   │   ├── Authenticate.php
│   │   │   ├── VerifyRole.php
│   │   │   ├── ApiResponse.php
│   │   │   └── HandleException.php
│   │   ├── Requests/          # Form Requests (Validation)
│   │   │   ├── LoginRequest.php
│   │   │   ├── RegisterRequest.php
│   │   │   ├── MemberRequest.php
│   │   │   ├── ClassRequest.php
│   │   │   ├── EnrollmentRequest.php
│   │   │   └── PaymentRequest.php
│   │   └── Resources/         # API Resources (Transformers)
│   │       ├── UserResource.php
│   │       ├── MemberResource.php
│   │       ├── ClassResource.php
│   │       ├── AttendanceResource.php
│   │       └── PaymentResource.php
│   │
│   ├── Models/                # Eloquent Models
│   │   ├── User.php
│   │   ├── Member.php
│   │   ├── Trainer.php
│   │   ├── MembershipPlan.php
│   │   ├── Membership.php
│   │   ├── Class.php
│   │   ├── ClassSchedule.php
│   │   ├── ClassEnrollment.php
│   │   ├── Attendance.php
│   │   ├── Equipment.php
│   │   ├── EquipmentUsage.php
│   │   ├── MaintenanceLog.php
│   │   ├── Payment.php
│   │   ├── Area.php
│   │   └── BaseModel.php      # Base model with common traits
│   │
│   ├── Services/              # Business Logic Services
│   │   ├── AuthService.php
│   │   ├── MemberService.php
│   │   ├── ClassService.php
│   │   ├── EnrollmentService.php
│   │   ├── AttendanceService.php
│   │   ├── PaymentService.php
│   │   ├── EquipmentService.php
│   │   ├── NotificationService.php
│   │   ├── ReportService.php
│   │   └── EmailService.php
│   │
│   ├── Repositories/          # Data Access Layer
│   │   ├── BaseRepository.php
│   │   ├── MemberRepository.php
│   │   ├── ClassRepository.php
│   │   ├── AttendanceRepository.php
│   │   ├── PaymentRepository.php
│   │   └── EquipmentRepository.php
│   │
│   ├── Events/                # Event Classes
│   │   ├── MemberRegistered.php
│   │   ├── ClassEnrolled.php
│   │   ├── AttendanceMarked.php
│   │   ├── PaymentProcessed.php
│   │   └── MembershipExpiring.php
│   │
│   ├── Listeners/             # Event Listeners
│   │   ├── SendMemberWelcomeEmail.php
│   │   ├── SendEnrollmentConfirmation.php
│   │   ├── SendRenewalReminder.php
│   │   └── SendInvoice.php
│   │
│   ├── Jobs/                  # Queued Jobs
│   │   ├── SendEmail.php
│   │   ├── GenerateReport.php
│   │   ├── ProcessPayment.php
│   │   ├── CheckMembershipExpiry.php
│   │   └── EquipmentMaintenanceReminder.php
│   │
│   ├── Notifications/         # Notification Classes
│   │   ├── MemberWelcomeNotification.php
│   │   ├── ClassEnrollmentNotification.php
│   │   ├── RenewalReminderNotification.php
│   │   ├── PaymentReceiptNotification.php
│   │   └── MaintenanceAlertNotification.php
│   │
│   ├── Traits/                # Reusable Traits
│   │   ├── HasApiTokens.php
│   │   ├── HasUUIDs.php
│   │   ├── HasTimestamps.php
│   │   ├── HasStatus.php
│   │   └── FiltersTrait.php
│   │
│   ├── Exceptions/            # Custom Exceptions
│   │   ├── Handler.php
│   │   ├── InvalidMembershipException.php
│   │   ├── ClassFullException.php
│   │   ├── UnauthorizedActionException.php
│   │   └── InsufficientCreditsException.php
│   │
│   ├── Rules/                 # Custom Validation Rules
│   │   ├── ValidEmail.php
│   │   ├── ValidPhone.php
│   │   ├── UniqueEmail.php
│   │   └── ValidMembership.php
│   │
│   ├── Enums/                 # PHP Enums
│   │   ├── UserRole.php
│   │   ├── MembershipStatus.php
│   │   ├── AttendanceStatus.php
│   │   ├── PaymentStatus.php
│   │   ├── EquipmentStatus.php
│   │   └── ClassDifficulty.php
│   │
│   ├── Observers/             # Model Observers
│   │   ├── MemberObserver.php
│   │   ├── PaymentObserver.php
│   │   └── ClassObserver.php
│   │
│   └── Console/
│       ├── Kernel.php
│       └── Commands/          # Artisan Commands
│           ├── CheckMembershipExpiry.php
│           ├── GenerateAttendanceReport.php
│           ├── ProcessPendingPayments.php
│           └── SendMaintenanceReminders.php
│
├── config/                    # Configuration Files
│   ├── app.php
│   ├── auth.php
│   ├── cache.php
│   ├── database.php
│   ├── mail.php
│   ├── queue.php
│   ├── filesystems.php
│   ├── logging.php
│   ├── sanctum.php
│   ├── services.php
│   └── gym.php               # Custom gym config
│
├── resources/
│   ├── views/                # Blade Templates (if using Blade)
│   │   ├── layouts/
│   │   │   └── app.blade.php
│   │   ├── auth/
│   │   ├── members/
│   │   ├── classes/
│   │   ├── reports/
│   │   └── emails/           # Email templates
│   │       ├── welcome.blade.php
│   │       ├── enrollment-confirmation.blade.php
│   │       ├── payment-receipt.blade.php
│   │       └── renewal-reminder.blade.php
│   │
│   ├── lang/                 # Localization Files
│   │   └── en/
│   │       ├── messages.php
│   │       ├── validation.php
│   │       └── errors.php
│   │
│   └── css/                  # CSS (if needed)
│       └── app.css
│
├── routes/
│   ├── api.php              # API Routes
│   ├── web.php              # Web Routes (if needed)
│   └── channels.php         # Broadcasting Routes (if needed)
│
├── storage/                  # Application Storage
│   ├── app/
│   │   └── public/          # Public files
│   │       ├── profiles/
│   │       ├── invoices/
│   │       ├── certificates/
│   │       └── documents/
│   ├── logs/                # Application logs
│   └── framework/
│       ├── cache/
│       └── sessions/
│
├── bootstrap/               # Bootstrap Files
│   ├── app.php
│   └── cache/
│
├── public/                  # Web-accessible directory
│   ├── index.php
│   ├── css/
│   └── js/
│
├── tests/                   # Test Files
│   ├── Feature/             # Feature Tests
│   │   ├── AuthTest.php
│   │   ├── MemberTest.php
│   │   ├── ClassTest.php
│   │   ├── EnrollmentTest.php
│   │   ├── PaymentTest.php
│   │   └── AdminTest.php
│   ├── Unit/                # Unit Tests
│   │   ├── Models/
│   │   ├── Services/
│   │   └── Repositories/
│   └── TestCase.php
│
├── frontend/                # React/Vue Frontend
│   ├── public/
│   ├── src/
│   │   ├── components/
│   │   │   ├── Auth/
│   │   │   ├── Member/
│   │   │   ├── Class/
│   │   │   ├── Admin/
│   │   │   ├── Common/
│   │   │   └── Dashboard/
│   │   ├── pages/
│   │   ├── services/
│   │   ├── store/
│   │   ├── hooks/
│   │   ├── styles/
│   │   ├── utils/
│   │   └── App.jsx
│   ├── package.json
│   └── vite.config.js
│
├── documentation/           # Project Documentation
│   ├── README.md
│   ├── REQUIREMENTS.md       # System Requirements
│   ├── DATABASE_SCHEMA.md    # Database Design
│   ├── ARCHITECTURE.md       # System Architecture
│   ├── API_ENDPOINTS.md      # API Documentation
│   ├── SETUP.md              # Setup Instructions
│   ├── DEPLOYMENT.md         # Deployment Guide
│   ├── TESTING.md            # Testing Guide
│   ├── API_EXAMPLES.md       # API Usage Examples
│   └── TROUBLESHOOTING.md    # Troubleshooting Guide
│
├── .env.example             # Environment Variables Example
├── .env.testing             # Test Environment Variables
├── .gitignore
├── .dockerignore
├── docker-compose.yml       # Docker Compose Configuration
├── docker-compose.test.yml  # Docker Compose for Testing
├── artisan                  # Laravel CLI
├── composer.json            # PHP Dependencies
├── composer.lock
├── phpunit.xml              # PHPUnit Configuration
├── phpstan.neon             # PHPStan Configuration
├── pint.json                # Laravel Pint Configuration
├── package.json             # Node Dependencies (if using mix)
├── package-lock.json
├── Makefile                 # Command Shortcuts
├── LICENSE
└── README.md               # Project Overview
```

## Key Directory Descriptions

### `/app`
Heart of the Laravel application containing:
- **Models**: Database entities and relationships
- **Controllers**: HTTP request handlers
- **Services**: Business logic layer
- **Repositories**: Data access abstraction
- **Events/Listeners**: Event-driven architecture
- **Jobs**: Background processing
- **Exceptions**: Custom exception handling

### `/database`
Database-related files:
- **migrations**: Version-controlled database changes
- **seeders**: Initial/test data
- **factories**: Model instances for testing

### `/routes`
API endpoint definitions:
- RESTful routes for all resources
- Authentication routes
- Protected and public routes

### `/tests`
Comprehensive testing:
- **Feature tests**: Test full workflows
- **Unit tests**: Test individual components

### `/frontend`
Separate React/Vue application:
- Components for each feature
- State management (Redux/Pinia)
- API integration services

### `/docker`
Containerization:
- Dockerfile for app container
- Nginx configuration
- PHP-FPM configuration

### `/documentation`
Comprehensive guides:
- Requirements analysis
- API documentation
- Architecture documentation
- Setup and deployment guides

## Development Workflow

```
Local Development
│
├─ Branch: feature/xyz
│  ├─ Create feature
│  ├─ Write tests
│  ├─ Commit changes
│  └─ Push to GitHub
│
├─ Pull Request
│  ├─ Code review
│  ├─ CI/CD tests
│  ├─ Status checks
│  └─ Merge to main
│
├─ Staging
│  ├─ Auto-deploy
│  ├─ Integration tests
│  └─ Manual testing
│
└─ Production
   ├─ Manual approval
   ├─ Deploy
   ├─ Monitor
   └─ Logs & alerts
```

## File Naming Conventions

- **Controllers**: `{Resource}Controller.php` (e.g., `MemberController.php`)
- **Models**: `{Singular}` (e.g., `Member.php`, `Class.php`)
- **Services**: `{Resource}Service.php` (e.g., `PaymentService.php`)
- **Repositories**: `{Resource}Repository.php`
- **Migrations**: `{action}_{tablename}_table.php`
- **Tests**: `{Class}Test.php`
- **Routes**: group by version/resource

## Best Practices

1. **Separation of Concerns**: Each file has single responsibility
2. **Extensibility**: Services and traits allow easy feature additions
3. **Testability**: Repositories and services enable unit testing
4. **Maintainability**: Clear naming and organization
5. **Scalability**: Structure supports microservices migration
6. **Documentation**: Self-documenting code with comments
