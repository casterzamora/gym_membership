# Frontend Setup & Running Instructions

## Quick Start

### Terminal 1: Start the Backend (Laravel)

```bash
# From root directory
php artisan serve
# Backend runs on http://localhost:8000
```

### Terminal 2: Start the Frontend (React)

```bash
# Navigate to frontend directory
cd frontend

# Install dependencies (first time only)
npm install

# Start development server
npm run dev
# Frontend runs on http://localhost:5173
```

Now visit `http://localhost:5173` in your browser!

## Complete Setup from Scratch

### Step 1: Backend Setup (if not already done)

```bash
# From root directory
php artisan migrate:fresh --seed

# If you need to restart services:
php artisan serve
```

The backend API will be available at `http://localhost:8000/api/v1`

### Step 2: Frontend Setup

```bash
cd frontend
npm install
npm run dev
```

The React app will be available at `http://localhost:5173`

## Build for Production

### Build Frontend

```bash
cd frontend
npm run build
```

This creates an optimized `dist/` folder for deployment.

### Deploy Options

1. **Serve built frontend with Laravel:**
   - Copy `dist/` contents to `public/app/`
   - Update `.htaccess` to route SPA requests to `index.html`

2. **Deploy separately:**
   - Frontend: Vercel, Netlify, or any static host
   - Backend: Traditional PHP server or cloud platform

## Important Notes

### API Integration
- The frontend is configured to proxy `/api` requests to `http://localhost:8000`
- This is handled by Vite's proxy in `vite.config.js`
- Auth token is automatically included in all requests

### Environment Variables
Currently using hardcoded paths. For production, create `.env.local`:

```
VITE_API_BASE_URL=https://api.yourdomain.com
```

Then update `src/services/api.js` to use it.

### Database Seeding
The backend comes with seeded data:
- 4 membership plans
- 5 trainers with certifications
- 6 fitness classes
- 20 members
- Various class schedules

### Test Credentials
After seeding, you can use:
- Email: `member@example.com`
- Password: `password`

(Password is set in `database/seeders/DatabaseSeeder.php`)

## Debugging

### Check if backend is running:
```bash
curl http://localhost:8000/api/v1/plans
```

Should return JSON response with plans.

### Check frontend connection:
Open browser DevTools → Network tab and try logging in. You should see:
1. POST `/api/login` → 200 response
2. Bearer token stored in localStorage

### Reset Everything
```bash
# Backend: Reset database
php artisan migrate:fresh --seed

# Frontend: Clear cache
rm -rf node_modules package-lock.json
npm install
```

## File Structure Summary

```
gym_attendance/
├── app/                     # Laravel app code
├── config/                  # Config files
├── database/
│   ├── migrations/          # Database schemas
│   ├── seeders/             # Test data
│   └── schema.sql           # Full schema
├── routes/
│   └── api.php              # API endpoints
├── frontend/                # React app ✨
│   ├── src/
│   │   ├── components/      # React components
│   │   ├── context/         # Auth context
│   │   ├── pages/           # Page components
│   │   ├── services/        # API service
│   │   ├── App.jsx          # Root component
│   │   └── main.jsx         # Entry point
│   ├── package.json         # Dependencies
│   ├── vite.config.js       # Vite config
│   ├── tailwind.config.js   # Tailwind config
│   └── README.md            # Frontend docs
└── public/
    └── index.php            # Laravel public
```

## Development Workflow

1. **Make backend changes:**
   - Edit models, controllers, routes
   - Changes apply immediately with `php artisan serve`

2. **Make frontend changes:**
   - Edit React components, pages, styles
   - HMR (hot module reload) updates instantly at http://localhost:5173

3. **Both making changes:**
   - Keep both terminals running
   - Frontend proxy automatically routes `/api` to backend

## Performance Tips

- Frontend builds with Vite which is ~10-20x faster than Webpack
- React hot module reload makes development instant
- Tailwind CSS is compiled on-demand in dev mode
- Production build is optimized and minified

## Browser Support

- Modern browsers (Chrome, Firefox, Safari, Edge)
- Responsive design for mobile, tablet, desktop
- Works on iOS Safari and Android Chrome

## Helpful Commands

```bash
# Frontend (in frontend/ directory):
npm run dev          # Start dev server
npm run build        # Build for production
npm run preview      # Preview production build
npm install <pkg>    # Install new package

# Backend (in root directory):
php artisan serve              # Start server
php artisan tinker             # Interactive CLI
php artisan migrate:fresh      # Reset database
php artisan migrate:fresh --seed # Reset + seed data
```

## Troubleshooting

**Port 5173 already in use?**
```bash
npm run dev -- --port 3000  # Use different port
```

**Port 8000 already in use?**
```bash
php artisan serve --port=8001
# Then update vite.config.js proxy to :8001
```

**CORS errors?**
- Check backend `.env` CORS settings
- Verify proxy is configured in `vite.config.js`
- Ensure both servers are running

**Token expires too quickly?**
- Check `AUTH_TOKEN_TTL` in backend `.env`
- Default is 1 week

**Can't log in?**
- Verify database was seeded: `php artisan migrate:fresh --seed`
- Check error messages in browser console
- Inspect network requests in DevTools

## Next Steps

1. ✅ Backend complete with migrations & seeding
2. ✅ Frontend scaffolding done (React, Tailwind, routing)
3. ✅ Authentication flow working (login/register)
4. 📋 TODO: Theme customization
5. 📋 TODO: Additional features (payments, notifications)
6. 📋 TODO: Email notifications
7. 📋 TODO: Mobile app (React Native)

## Support

For issues with:
- **Backend:** Check `storage/logs/laravel.log`
- **Frontend:** Check browser console and Network tab in DevTools
- **Database:** Verify migration output and connection settings

---

**Happy coding! 💪**
