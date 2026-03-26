# GymFlow Frontend

A modern React-based fitness gym management web application with authentication, member dashboard, class browsing, and admin capabilities.

## Tech Stack

- **React 18.2** - UI library
- **Vite** - Build tool & dev server
- **Tailwind CSS 3.3** - Utility-first CSS framework
- **React Router v6** - Client-side routing
- **Axios** - HTTP client
- **PostCSS** - CSS processing

## Features

- ✅ Responsive design inspired by Gold's Gym
- ✅ User authentication (login/register)
- ✅ Member dashboard with stats
- ✅ Class browsing & scheduling
- ✅ User profile management
- ✅ Admin panel for member & trainer management
- ✅ Protected routes with token-based auth
- ✅ Automatic token refresh

## Project Structure

```
frontend/
├── src/
│   ├── components/
│   │   └── Navbar.jsx           # Navigation component
│   ├── context/
│   │   └── AuthContext.jsx      # Global auth state
│   ├── pages/
│   │   ├── Landing.jsx          # Public landing page
│   │   ├── Login.jsx            # Login page
│   │   ├── Register.jsx         # Registration page
│   │   ├── Dashboard.jsx        # Member dashboard
│   │   ├── Classes.jsx          # Classes browser
│   │   ├── Profile.jsx          # User profile
│   │   └── Admin.jsx            # Admin panel
│   ├── services/
│   │   └── api.js               # Axios configuration & API endpoints
│   ├── App.jsx                  # Main app component with routing
│   ├── main.jsx                 # React entry point
│   └── index.css                # Global styles
├── index.html                   # HTML entry point
├── vite.config.js               # Vite configuration
├── tailwind.config.js           # Tailwind configuration
├── postcss.config.js            # PostCSS configuration
└── package.json                 # Dependencies
```

## Setup Instructions

### 1. Install Dependencies

```bash
cd frontend
npm install
```

### 2. Configure Environment

The app expects the backend API running on `http://localhost:8000`. This is configured in `vite.config.js` with a proxy server.

### 3. Start Development Server

```bash
npm run dev
```

The app will be available at `http://localhost:5173`

### 4. Build for Production

```bash
npm run build
```

Output will be in the `dist/` folder.

### 5. Preview Production Build

```bash
npm run preview
```

## Authentication Flow

1. **Token Storage**: JWT tokens are stored in localStorage
2. **Token Validation**: On app load, token is validated against `/api/v1/me`
3. **Auto-Injection**: Bearer token is automatically added to all API requests
4. **Auto-Redirect**: 401 responses redirect to login page

## API Integration

All API communication is handled through the `src/services/api.js` service layer:

- `authAPI.register()` - User registration
- `authAPI.login()` - User login
- `authAPI.logout()` - User logout
- `authAPI.me()` - Get current user
- `membersAPI.list()` - List all members
- `membersAPI.get()` - Get member details
- `classesAPI.list()` - List all classes
- `classesAPI.get()` - Get class details
- `schedulesAPI.list()` - List all schedules
- `attendanceAPI.list()` - List attendance records
- `attendanceAPI.checkIn()` - Check-in to class
- `plansAPI.list()` - List membership plans
- `trainersAPI.list()` - List trainers

## Available Routes

### Public Routes
- `/` - Landing page
- `/login` - Login page
- `/register` - Registration page

### Protected Routes (requires authentication)
- `/dashboard` - Member dashboard
- `/classes` - Classes browser
- `/profile` - User profile
- `/admin` - Admin panel (role-based)

## Styling

The application uses Tailwind CSS with a custom gold/black color scheme:

- **Primary Color**: Gold (#f59e0b)
- **Background**: Gray-900 (#111827)
- **Text**: White/Gray-300

Custom colors are defined in `tailwind.config.js`:

```javascript
colors: {
  gold: {
    50: '#fffbeb',
    400: '#fbbf24',
    500: '#f59e0b',
    600: '#d97706',
    // ... more variations
  }
}
```

## Development Notes

- The app uses React 18.2 with Vite for fast HMR (hot module replacement)
- All network requests include Bearer token authentication
- Protected routes redirect unauthenticated users to login
- The Navbar component shows different links based on auth state

## Backend Integration

Make sure the backend is running:

```bash
cd ..
php artisan serve
```

Backend runs on `http://localhost:8000` by default.

## Troubleshooting

**CORS Issues?**
- Ensure backend `.env` has correct `CORS_ALLOWED_ORIGINS`
- Check that Vite proxy is correctly configured in `vite.config.js`

**Token Not Persisting?**
- Check localStorage in browser DevTools
- Verify backend is returning token in login response

**API 404 Errors?**
- Ensure backend API endpoints exist at `/api/v1/*`
- Check Vite proxy configuration

## License

MIT
