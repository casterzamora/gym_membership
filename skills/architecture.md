# Architecture Skill - Gym Attendance System

## System Overview

A comprehensive Gym Membership and Attendance Management System designed to manage daily fitness center operations. The system handles member management, trainer assignments, class scheduling, attendance tracking, payments, and equipment management through a fully normalized relational database with advanced validation rules and triggers.

**Architecture Overview**:
```
┌──────────────────────────────────────────────────────────────┐
│           Frontend (React/Vue + Vite + Tailwind CSS)         │
│                   (Admin Dashboard & Member Portal)          │
└──────────────────────────┬─────────────────────────────────┘
                           │
                  REST API + JSON over HTTPS
                           │
┌──────────────────────────▼─────────────────────────────────┐
│        Backend (Laravel 11 + PHP + Business Logic)         │
│      - REST API Endpoints                                   │
│      - Service Layer for Complex Operations                │
│      - Event Listeners for Workflows                       │
└──────────────────────────┬─────────────────────────────────┘
                           │
                   Relational Queries
                           │
┌──────────────────────────▼─────────────────────────────────┐
│    Database Layer (MySQL 8.0+ with Normalization to 3NF)   │
│      - 13 Core Tables (normalized)                         │
│      - Triggers for Business Rules                         │
│      - Stored Procedures for Operations                    │
│      - Advanced Indexing for Performance                   │
└──────────────────────────────────────────────────────────────┘
```

## Architecture Components

### Frontend (Presentation Layer)
- **Technology**: React 18+ or Vue 3 + Vite
- **Responsibilities**:
  - Admin dashboard for gym operations
  - Member portal for self-service
  - User interface rendering
  - User interactions and workflows
  - Form validation
  - Local state management
  - Error display and handling
  - API communication
- **Deployment**: Static hosting (Netlify, Vercel, Apache)
- **Communication**: REST API calls with JSON

### Backend (Application & Logic Layer)
- **Technology**: Laravel 11 + PHP
- **Responsibilities**:
  - RESTful API endpoints
  - Business logic and validation
  - Authentication & authorization (Sanctum)
  - Service layer for complex operations
  - Event listeners for workflows (membership expiry, capacity checks)
  - API request/response handling
  - Transaction management
  - Logging and error handling
- **Deployment**: Web server + PHP runtime (Apache/Nginx)

### Database Layer (Data Persistence & Integrity)
- **Technology**: MySQL 8.0+ (normalized to 3NF)
- **Responsibilities**:
  - Data persistence across 13 normalized tables
  - Referential integrity via foreign keys
  - Business rule enforcement via triggers
  - Complex queries via stored procedures and views
  - Advanced indexing for performance
  - Transaction support (ACID compliance)
  - Backup and recovery capabilities
- **Key Features**:
  - **Normalization**: All tables normalized to Third Normal Form
  - **Triggers**: Enforce membership status updates, capacity checks, payment validation
  - **Constraints**: Unique constraints, CHECK constraints, domain validation
  - **Composite Keys**: Junction tables use composite primary keys
  - **Cascading Rules**: Foreign key cascading updates/deletes where applicable

## Data Flow Architecture

### API Request Flow
```
1. Frontend → User Action (click, submit)
2. Frontend → Build Request (URL, method, data)
3. Frontend → Add Auth Token (Authorization header)
4. Network → HTTPS transmission
5. Backend → Receive Request
6. Backend → Validate Request (format, auth, permissions)
7. Backend → Execute Business Logic (service layer)
8. Backend → Database Operations (ORM queries)
9. Backend → Format Response (JSON)
10. Network → HTTPS transmission
11. Frontend → Parse Response
12. Frontend → Update UI State
13. Frontend → Render Updated UI
```

### Authentication Flow
```
1. User submits credentials (email, password)
2. Backend validates against database
3. Backend generates Sanctum token
4. Backend returns token to frontend
5. Frontend stores token (localStorage/cookies)
6. Frontend includes token in Authorization header
7. Backend validates token on each request
8. Backend returns 401 Unauthorized if invalid
9. Frontend redirects to login on 401
```

### Real-time Operations (Optional Future Enhancement)
```
Option 1: Polling
- Frontend periodically queries API (every 5-30 seconds)
- Simple but not real-time

Option 2: WebSockets
- Establish persistent connection
- Server pushes notifications
- Examples: New attendance, payment confirm

Option 3: Server-Sent Events (SSE)
- One-way server push
- Simpler than WebSockets
```

## Scalability Architecture

### Horizontal Scaling
```
Load Balancer
├── Backend Instance 1
├── Backend Instance 2
├── Backend Instance 3
└── Backend Instance N

Shared:
├── Database (centralized)
├── File Storage
└── Cache (Redis)
```

### Caching Strategy
- **Query Cache**: Cache frequently accessed data (members, packages)
- **Session Cache**: Store user sessions
- **API Response Cache**: Cache API responses for repeated queries
- **Cache Invalidation**: Clear on data changes
- **Tools**: Redis or Memcached

### Database Optimization
- **Indexing**: Indexes on frequently queried columns
- **Query Optimization**: Eager loading relationships
- **Denormalization**: Strategic copies of data for performance
- **Partitioning**: Large tables split by date range
- **Read Replicas**: Separate read queries from writes (optional)

## Security Architecture

### Authentication
- **Frontend**: Store token securely
- **Backend**: Validate token on every request
- **Passwords**: Hash with bcrypt
- **Sessions**: Expire tokens periodically
- **Logout**: Remove token from frontend

### Authorization
- **Role-Based Access Control (RBAC)**:
  - Admin: Full access
  - Manager: Manage members, view reports
  - Staff: Check-in, record attendance
  - Member: View own data
- **Middleware**: Check permissions on endpoints
- **Attribute-Based**: Fine-grained permissions (optional)

### Data Protection
- **HTTPS**: All data in transit encrypted
- **CORS**: Control cross-origin requests
- **CSRF**: Protect forms with tokens
- **Input Validation**: Sanitize all inputs
- **SQL Injection**: Use parameterized queries (ORM handles)
- **XSS Prevention**: Escape output
- **Rate Limiting**: Prevent abuse

### Sensitive Data
- **Passwords**: Never store plain text
- **Tokens**: Short-lived with refresh
- **PII**: Encrypt at rest (optional)
- **Audit Logs**: Track sensitive operations

## Error Handling Architecture

### Error Classification
```
Level 1: User Errors (4xx HTTP codes)
- 400: Bad Request (invalid data)
- 401: Unauthorized (no auth)
- 403: Forbidden (insufficient permissions)
- 404: Not Found (resource doesn't exist)
- 422: Unprocessable Entity (validation fails)

Level 2: Server Errors (5xx HTTP codes)
- 500: Internal Server Error
- 503: Service Unavailable
- 504: Gateway Timeout
```

### Error Response Format
```json
{
  "success": false,
  "error": {
    "code": "VALIDATION_ERROR",
    "message": "Validation failed",
    "details": {
      "email": ["Email is invalid"],
      "age": ["Age must be 18 or older"]
    }
  }
}
```

### Error Handling Strategy
- **Frontend**: Display user-friendly messages
- **Backend**: Log detailed errors for debugging
- **Monitoring**: Track errors for patterns
- **Recovery**: Provide recovery options (retry, fallback)

## Integration Patterns

### Third-Party Services (Future)
```
Payment Gateway (Stripe, PayPal):
- Tokenization for security
- Webhook handling for confirmation
- Reconciliation process

Email Service (SendGrid, AWS SES):
- Send membership reminders
- Payment confirmations
- Support tickets

SMS Service (Twilio):
- SMS check-in confirmation
- Membership expiry reminders

Analytics (Google Analytics, Mixpanel):
- Track user behavior
- Drop-off analysis
- Usage patterns
```

## Deployment Architecture

### Development Environment
```
Frontend: http://localhost:3000
Backend: http://localhost:8000
Database: localhost:3306
```

### Staging Environment
- Mirror of production
- Test all changes before deployment
- Performance/load testing

### Production Environment
```
Frontend: CDN + Static Hosting
Backend: Web Server + Managed Database
SSL/TLS: HTTPS everywhere
Monitoring: Error tracking, performance
Backup: Daily database backups
```

## Performance Architecture

### Frontend Optimization
- Code splitting by route
- Lazy loading components
- Image optimization (WebP, compression)
- Minification and bundling
- Service workers for offline (optional)
- Caching strategies

### Backend Optimization
- Database query optimization
- Eager loading vs lazy loading
- Request/response compression (gzip)
- Caching headers (HTTP cache control)
- CDN for static assets
- Load balancing

### Monitoring
- Server response times
- Frontend performance metrics
- Error rates and logs
- Database query performance
- Infrastructure resource usage

## Data Consistency Architecture

### Transaction Management
```
Example: Process Payment + Update Membership
1. Start transaction
2. Record payment (success)
3. Update membership expiry (success)
4. Commit transaction
5. Or: Any failure → Rollback all

Result: Atomic operation (all or nothing)
```

### Eventual Consistency Scenarios
```
Example: Async Email Notification
1. Record payment (sync)
2. Queue email (async)
3. Send email (background job)
4. If send fails: Retry mechanism
```

### Conflict Resolution
- **Pessimistic**: Lock during update
- **Optimistic**: Detect conflicts, retry
- **Last Write Wins**: Simple but risky
- **Custom Logic**: Domain-specific rules

## Business Process Workflows

### Member Lifecycle
```
New Member
├── Sign Up
├── Create Profile
├── Select Package
├── First Payment
├── Active Membership
│   ├── Check-ins
│   ├── Renewals
│   └── Payments
├── Approaching Expiry (send reminder)
├── Expiry
│   ├── Renewal Path → Extended membership
│   └── No Renewal → Inactive

Churn Analysis:
- Track active → inactive transitions
- Identify at-risk members
- Implement retention strategies
```

### Daily Operations
```
Morning:
1. Check today's attendance
2. Monitor active members
3. Process pending payments

Throughout Day:
1. Members check-in/out
2. Answer inquiries
3. Handle issues

End of Day:
1. Generate daily report
2. Export for records
3. Backup data
```

### Monthly Operations
```
1. Generate revenue report
2. Analyze attendance trends
3. Identify expiring memberships
4. Send renewal reminders
5. Batch process renewals
6. Update package pricing (if needed)
```

## Versioning & Integration

### API Versioning Strategy
```
URL: /api/v1/members
Benefits:
- Support multiple versions simultaneously
- Easy migration for clients
- Backward compatibility

Version Deprecation:
- Announce 3-6 months ahead
- Provide migration guide
- Support old version during transition
```

### Database Migration Strategy
```
1. Create new migration file
2. Define schema changes
3. Test locally
4. Deploy to staging
5. Test thoroughly
6. Deploy to production
7. Version control all migrations
```

## Disaster Recovery & Backups

### Backup Strategy
- **Daily**: Automatic database backups
- **Retention**: 30 days of daily backups
- **Off-site**: Separate geographic region
- **Testing**: Regularly test restoration

### Disaster Recovery Plan
```
1. Detect failure (monitoring alerts)
2. Alert team
3. Activate backup infrastructure
4. Restore from last backup
5. Verify data integrity
6. Switch to recovered system
7. Investigation & prevention
```

## API Documentation

### Endpoint Documentation
```
POST /api/v1/members
Create a new member

Request:
- Headers: Authorization: Bearer TOKEN
- Body: {
    name: string,
    email: string,
    phone: string,
    package_id: integer
  }

Response:
- 201 Created: Member object
- 422 Unprocessable: Validation errors
- 401 Unauthorized: Missing token

Example:
POST /api/v1/members
Authorization: Bearer abc123...
Content-Type: application/json

{
  "name": "John Doe",
  "email": "john@example.com",
  "phone": "555-1234",
  "package_id": 1
}

Response:
{
  "success": true,
  "data": {
    "id": 123,
    "name": "John Doe",
    "email": "john@example.com",
    ...
  }
}
```

## Technology Decision Matrix

### When to Scale
- **Frontend**: When page load > 3 seconds
- **Backend**: When response time > 500ms
- **Database**: When queries > 1 second

### When to Add Features
- **Add caching**: Repeated queries
- **Add queue jobs**: Long-running operations
- **Add WebSockets**: Real-time updates needed
- **Add CDN**: Serving from multiple regions

### When to Refactor
- **Code duplication**: Logic repeated 2+ times
- **Large controllers**: > 300 lines
- **Slow tests**: Unit test > 100ms
- **Maintainability**: Team struggling with complexity
