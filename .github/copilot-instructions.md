# Copilot Workflow Instructions

Use this project workflow to keep context usage low and avoid repetitive diagnostics.

## Source Of Truth
- Database connection and runtime settings come from .env.
- This project uses MySQL by default in local development.
- Frontend app is under frontend and backend app is in project root.

## Required First Checks
When diagnosing setup or schema issues, run these first before ad-hoc commands:
1. composer db:check
2. php artisan migrate:status

## Database Validation Rules
- Prefer check_db_structure.php and check_foreign_keys.php for schema and FK checks.
- Avoid complex one-line tinker SQL when metadata can be checked by scripts.
- If checks fail, fix migrations or .env values first.

## Development Startup
1. Backend: php artisan serve
2. Frontend: cd frontend and npm run dev
3. Open frontend URL shown by Vite output.

## Skills Usage
Before major work, align approach with:
- skills/architecture.md for system-level impact
- skills/backend.md for API and data patterns
- skills/frontend.md for UI and state patterns
- skills/ui-ux.md for visual and interaction consistency

## Change Discipline
- Keep edits minimal and scoped.
- Do not introduce temporary debug files if a reusable script already exists.
- If a new diagnostic is needed, add it as a reusable script and expose it through composer scripts.
