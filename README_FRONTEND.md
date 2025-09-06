# CRIM FAMS Frontend

## Tech Stack
- **Frontend**: HTML, CSS (Bootstrap), Vanilla JavaScript
- **Backend**: Native PHP (APIs only)
- **Database**: MySQL

## Architecture

### Frontend Structure
```
public/
├── index.html          # Main entry point
├── dashboard.html      # Dashboard page
├── login.html          # Login page
├── leave.html          # Leave management
├── schedules.html      # Schedule management
├── css/               # Stylesheets
├── js/                # JavaScript files
└── api/               # PHP API endpoints
    ├── login.php
    ├── dashboard.php
    ├── leave.php
    └── logout.php
```

### How It Works
1. **HTML Files**: Pure static HTML with embedded JavaScript
2. **JavaScript**: Makes AJAX calls to PHP APIs for data
3. **PHP APIs**: Handle data fetching, authentication, and business logic
4. **Database**: MySQL accessed only through PHP APIs

### API Endpoints
- `POST /api/login.php` - User authentication
- `GET /api/dashboard.php` - Dashboard data
- `GET/POST /api/leave.php` - Leave management
- `POST /api/logout.php` - User logout

### Key Features
- ✅ Session-based authentication
- ✅ AJAX data fetching
- ✅ Role-based UI updates
- ✅ Responsive Bootstrap design
- ✅ No server-side rendering
- ✅ PHP used only for data operations

## Development
1. HTML files are static and served directly
2. JavaScript handles all client-side logic
3. PHP APIs provide data via JSON responses
4. All database operations go through PHP APIs