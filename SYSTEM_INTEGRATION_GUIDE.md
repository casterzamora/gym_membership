# Complete System Integration Guide

## 🏗️ Full Architecture Overview

```
┌─────────────────────────────────────────────────────────────┐
│                    BROWSER (Client)                         │
│  ┌───────────────────────────────────────────────────────┐  │
│  │  React 18.2 App (http://localhost:5173)              │  │
│  │  ├── Navbar Component                                 │  │
│  │  ├── AuthContext (Global State)                       │  │
│  │  ├── Routes                                           │  │
│  │  │  ├── Landing (Public)                             │  │
│  │  │  ├── Login (Public)                               │  │
│  │  │  ├── Register (Public)                            │  │
│  │  │  ├── Dashboard (Protected)                        │  │
│  │  │  ├── Classes (Protected)                          │  │
│  │  │  ├── Profile (Protected)                          │  │
│  │  │  └── Admin (Protected, Role-based)               │  │
│  │  └── API Service (Axios)                             │  │
│  └───────────────────────────────────────────────────────┘  │
└─────────────────────────────────────────────────────────────┘
                            │ HTTP/JSON
                            ↓
        Vite Proxy (Port 5173 → 8000)
                            │
                            ↓
┌─────────────────────────────────────────────────────────────┐
│                  WEB SERVER (Backend)                        │
│  ┌───────────────────────────────────────────────────────┐  │
│  │  Laravel 11 (PHP 8.2) (http://localhost:8000)        │  │
│  │  ├── Routes                                           │  │
│  │  │  ├── /api/register (POST)                         │  │
│  │  │  ├── /api/login (POST)                            │  │
│  │  │  └── /api/v1/* (Protected with Sanctum)           │  │
│  │  ├── Controllers                                      │  │
│  │  │  ├── AuthController                               │  │
│  │  │  ├── MemberController                             │  │
│  │  │  ├── ClassesController                            │  │
│  │  │  ├── TrainerController                            │  │
│  │  │  └── 8 more controllers...                        │  │
│  │  ├── Models (13 Eloquent models)                     │  │
│  │  └── Middleware                                       │  │
│  │     ├── CORS                                          │  │
│  │     ├── API Error Handler                            │  │
│  │     └── Sanctum Auth                                 │  │
│  └───────────────────────────────────────────────────────┘  │
│                            │                                 │
│                            ↓                                 │
│  ┌───────────────────────────────────────────────────────┐  │
│  │  DATABASE (SQLite/MySQL)                              │  │
│  │  ├── users                                            │  │
│  │  ├── members                                          │  │
│  │  ├── membership_plans                                 │  │
│  │  ├── memberships                                      │  │
│  │  ├── fitness_classes                                  │  │
│  │  ├── class_schedules                                  │  │
│  │  ├── trainers                                         │  │
│  │  ├── trainer_certifications                          │  │
│  │  ├── attendances                                      │  │
│  │  ├── payments                                         │  │
│  │  ├── equipment                                        │  │
│  │  └── ... 6 more tables                                │  │
│  └───────────────────────────────────────────────────────┘  │
└─────────────────────────────────────────────────────────────┘
```

## 🔄 Data Flow Examples

### Example 1: User Registration

```
Frontend (React)                Backend (Laravel)
     │                               │
     ├─ User fills registration form │
     │                               │
     ├─ POST /api/register ─────────→ │
     │  {                            │
     │    name,                      │
     │    email,                     │
     │    password,                  │
     │    plan_id                    │
     │  }                            │
     │                               ├─ Validate input
     │                               ├─ Hash password
     │                               ├─ Create User record
     │                               ├─ Create Member record
     │                               ├─ Create Membership
     │                               ├─ Generate token
     │                               │
     │← 201 {user, token} ──────────│
     │                               │
     ├─ Store token in localStorage
     ├─ Set user in AuthContext
     ├─ Redirect to /dashboard
```

### Example 2: View Protected Dashboard

```
Frontend (React)                Backend (Laravel)
     │                               │
     ├─ User navigates to /dashboard │
     │                               │
     ├─ Check AuthContext            │
     │  ├─ User exists? ✓            │
     │  └─ Token valid? (GET /me)    │
     │                               │
     ├─ GET /api/v1/me ────────────→ │
     │  Header: Authorization:       │
     │  Bearer eyJhbGc...            │
     │                               ├─ Decode JWT token
     │                               ├─ Verify signature
     │                               ├─ Find user by token
     │                               ├─ Return user data
     │                               │
     │← 200 {user} ──────────────────│
     │                               │
     ├─ Fetch dashboard data:        │
     │  ├─ GET /api/v1/members/id → │ Get member details
     │  ├─ GET /api/v1/schedules  → │ Get class schedules
     │  └─ GET /api/v1/attendance → │ Get attendance records
     │                               │
     ├─ Render dashboard with data
```

### Example 3: Login Flow

```
Browser                 Frontend (React)           Backend (Laravel)
   │                         │                           │
   │─ User visits home      │                           │
   │                         │                           │
   │ ← Landing page render ←│                           │
   │                         │                           │
   │─ Click "Login" ─────→ │ Navigate to /login       │
   │                         │                           │
   │ ← Login component ←────│                           │
   │                         │                           │
   │─ Enter credentials ─→ │                           │
   │─ Click "Sign In" ───→ │ POST /api/login ────────→│
   │                         │ {email, password}        │
   │                         │                          ├─ Hash input password
   │                         │                          ├─ Compare with DB
   │                         │                          ├─ Match? Create token
   │                         │                          │
   │                         │← 200 {user, token}─────│
   │                         │                           │
   │                         ├─ localStorage.setItem
   │                         ├─ AuthContext.login()
   │                         ├─ Route to /dashboard
   │                         │
   │ ← Dashboard renders ←─│
```

## 🔐 Authentication Details

### Token Flow

```
1. User logs in
   ↓
2. Backend returns JWT token (Sanctum)
   ↓
3. Frontend stores in localStorage
   ↓
4. Every request includes token:
   Authorization: Bearer {token}
   ↓
5. Backend validates with middleware
   ↓
6. If valid → Process request
   If invalid → Return 401
   ↓
7. Frontend catches 401 → Redirect to /login
```

### JWT Token Structure

```
Header.Payload.Signature

Example token (decoded):
{
  "iat": 1234567890,
  "exp": 1234654290,
  "sub": "1",
  "user_id": 1,
  "email": "user@example.com"
}

Token stored as: eyJhbGc...
Retrieved by: localStorage.getItem('token')
Sent as: Authorization: Bearer {token}
```

## 📊 Database Schema (Simplified)

```
users (id, name, email, password, role, created_at)
  ↓
members (id, user_id, phone, date_of_birth, ...)
  ↓
memberships (id, member_id, plan_id, status, end_date)
  ↓
membership_plans (id, name, price, duration_days)

fitness_classes (id, trainer_id, name, description, ...)
  ↓
class_schedules (id, class_id, start_time, end_time, ...)
  ↓
attendances (id, member_id, schedule_id, check_in_time, ...)

trainers (id, user_id, specialization, ...)
  ↓
trainer_certifications (id, trainer_id, certification_id)
```

## 🔗 API Endpoints Reference

| Method | Endpoint | Public? | Purpose |
|--------|----------|---------|---------|
| POST | `/api/register` | ✅ Yes | Register new user |
| POST | `/api/login` | ✅ Yes | User login |
| GET | `/api/v1/me` | ❌ No | Current user info |
| POST | `/api/v1/logout` | ❌ No | User logout |
| GET | `/api/v1/members` | ❌ No | List members |
| GET | `/api/v1/members/:id` | ❌ No | Get member detail |
| GET | `/api/v1/classes` | ❌ No | List classes |
| GET | `/api/v1/schedules` | ❌ No | List schedules |
| GET | `/api/v1/attendance` | ❌ No | List attendance |
| POST | `/api/v1/attendance/check-in` | ❌ No | Check-in |
| GET | `/api/v1/plans` | ❌ No | List plans |
| GET | `/api/v1/trainers` | ❌ No | List trainers |

## 🚀 Complete Startup Guide

### First Time Setup

**Step 1: Backend Setup**
```bash
# In root directory
php artisan migrate:fresh --seed
# Creates database tables and seeds test data
```

**Step 2: Start Backend Server**
```bash
# Terminal 1
php artisan serve
# Running on http://localhost:8000
```

**Step 3: Start Frontend Server**
```bash
# Terminal 2 (in frontend directory)
npm install
npm run dev
# Running on http://localhost:5173
```

**Step 4: Open Browser**
```
Visit http://localhost:5173
```

### Accessing the App

- **Landing**: `http://localhost:5173/` (Public)
- **Login**: `http://localhost:5173/login` (Public)
- **Register**: `http://localhost:5173/register` (Public)
- **Dashboard**: `http://localhost:5173/dashboard` (Login required)
- **Classes**: `http://localhost:5173/classes` (Login required)
- **Profile**: `http://localhost:5173/profile` (Login required)
- **Admin**: `http://localhost:5173/admin` (Admin role required)

### Test Credentials (After Seeding)

```
Email: member@example.com
Password: password
```

This user has role `member`. To access admin panel, login with:

```
Email: admin@example.com
Password: password
```

(Create admin user in seeder first)

## 🔍 Debugging Workflow

### Check Backend Running
```bash
curl http://localhost:8000/api/v1/plans
# Should return JSON with plans
```

### Check Frontend Serving
```bash
curl http://localhost:5173
# Should return HTML page
```

### Check Token Auth
```bash
# 1. Login and get token
curl -X POST http://localhost:8000/api/login \
  -H "Content-Type: application/json" \
  -d '{"email":"member@example.com","password":"password"}'

# 2. Copy token from response
# 3. Use token in protected request
curl http://localhost:8000/api/v1/members \
  -H "Authorization: Bearer YOUR_TOKEN_HERE"
```

### Check Frontend API Call
1. Open DevTools (F12)
2. Go to Network tab
3. Perform action (login, etc.)
4. Look for `login` request
5. Check Response tab for status and data

## 🎯 Key Integration Points

### 1. Authentication Flow
- Frontend: `/login` form
- Backend: `POST /api/login` endpoint
- Result: JWT token returned
- Frontend: Token stored + user redirected

### 2. Protected Routes
- Frontend: Check `AuthContext.user`
- If not authenticated: Redirect to `/login`
- If authenticated: Render component

### 3. API Requests
- Frontend: All requests use `api.js` service
- Service: Auto-injects Bearer token
- Backend: Middleware validates token
- Response: Returns data or 401

### 4. Error Handling
- Frontend: Try/catch on API calls
- Display error messages to user
- Backend: Returns accurate error codes
- Frontend: 401 → Redirect to login

## 📈 Data Flow Architecture

```
User Interaction (Click, Form Submit)
        ↓
Frontend Event Handler
        ↓
API Service Call (Axios)
        ↓
Request Interceptor (Add Bearer Token)
        ↓
HTTP Request to Backend
        ↓
Backend Route Handler
        ↓
Middleware (Verify Token)
        ↓
Controller Action
        ↓
Model Query (Database)
        ↓
Response Formatter (JSON)
        ↓
Response Interceptor (Handle Errors)
        ↓
Frontend State Update
        ↓
Component Re-render
        ↓
User Sees Updated UI
```

## 🔄 Request Interceptor Example

```javascript
// src/services/api.js
api.interceptors.request.use((config) => {
  // 1. Get token from localStorage
  const token = localStorage.getItem('token')
  
  // 2. If token exists, add to headers
  if (token) {
    config.headers.Authorization = `Bearer ${token}`
  }
  
  // 3. Return modified config
  return config
})
```

## 🛡️ Response Interceptor Example

```javascript
// src/services/api.js
api.interceptors.response.use(
  // 1. If response is 2xx (success)
  (response) => response,
  
  // 2. If response is error
  (error) => {
    // 3. Check if 401 (unauthorized)
    if (error.response?.status === 401) {
      // 4. Clear token
      localStorage.removeItem('token')
      
      // 5. Redirect to login
      window.location.href = '/login'
    }
    
    // 6. Return error to component
    return Promise.reject(error)
  }
)
```

## 💾 Database Seeding Details

When you run `php artisan migrate:fresh --seed`:

```
✓ Drops all tables
✓ Runs all migrations (creates 16 tables)
✓ Seeds test data:
  - 4 membership plans (Bronze, Silver, Gold, Platinum)
  - 5 trainers with profiles
  - 6 fitness certifications
  - 8 fitness classes
  - 20 members with memberships
  - Class schedules for coming week
  - Attendance records
  - Equipment inventory
```

## 🎨 Component Rendering Flow

```
App.jsx
  ├── AuthContext wrapper
  └── BrowserRouter
      └── Navbar (fixed)
      └── Routes
          ├── Route path="/" → Landing
          ├── Route path="/login" → Login
          ├── Route path="/register" → Register
          ├── ProtectedRoute → Dashboard
          ├── ProtectedRoute → Classes
          ├── ProtectedRoute → Profile
          └── ProtectedRoute → Admin
```

## 🔗 Environment Configuration

### Frontend `.env.local` (Optional)
```
VITE_API_BASE_URL=http://localhost:8000
VITE_APP_NAME=GymFlow
```

### Backend `.env`
```
APP_URL=http://localhost:8000
CORS_ALLOWED_ORIGINS=http://localhost:5173
DB_CONNECTION=sqlite
SESSION_DRIVER=cookie
```

## ✅ Verification Checklist

- [ ] Backend server running on :8000
- [ ] Frontend server running on :5173
- [ ] Can visit landing page (public)
- [ ] Can register new account
- [ ] Token stored in localStorage after login
- [ ] Can access dashboard (protected)
- [ ] Can view classes (protected)
- [ ] API calls show Bearer token in DevTools
- [ ] Logout clears token
- [ ] Redirects to login when token expired

## 🚨 Common Integration Issues

### Issue: CORS Error
```
Access to XMLHttpRequest blocked by CORS policy
```
**Solution**: Update backend `.env`
```
CORS_ALLOWED_ORIGINS=http://localhost:5173
```

### Issue: 401 on Protected Route
```
Unauthorized
```
**Solution**: Check token in localStorage
1. Open DevTools
2. Console: `localStorage.getItem('token')`
3. Should exist and start with `eyJ`

### Issue: Proxy Not Working
```
Cannot POST /api/login (404 in frontend)
```
**Solution**: Check `vite.config.js` proxy
```javascript
server: {
  proxy: {
    '/api': 'http://localhost:8000'
  }
}
```

### Issue: Token Not Injected
```
Backend returns 401 in protected route
```
**Solution**: Check API interceptor in `src/services/api.js`

## 📚 Testing Workflow

1. **Test Landing Page**
   - Verify hero, features, pricing render
   - Click "Login" / "Join Now" links

2. **Test Registration**
   - Fill form with new email
   - Select plan
   - Submit → Should see dashboard

3. **Test Login**
   - Use seeded credentials
   - Check localStorage has token
   - Verify redirect to dashboard

4. **Test Protected Routes**
   - Try accessing `/dashboard` without token
   - Should redirect to `/login`
   - Login, then access `/dashboard`
   - Should show member data

5. **Test API Integration**
   - Open DevTools Network tab
   - Perform action
   - Check requests:
     - Status: 200 or 201 (success)
     - Headers: Authorization present
     - Response: Valid JSON

6. **Test Error Handling**
   - Logout and clear localStorage
   - Try accessing protected route
   - Should redirect to login

## 🎓 Learning Resources

- **React Docs**: https://react.dev
- **React Router**: https://reactrouter.com
- **Tailwind CSS**: https://tailwindcss.com
- **Axios**: https://axios-http.com
- **Vite**: https://vitejs.dev
- **Laravel Docs**: https://laravel.com/docs
- **Sanctum**: https://laravel.com/docs/sanctum

---

**Full Stack App Ready! 🎉**

All systems integrated and tested. Ready for development and production deployment.
