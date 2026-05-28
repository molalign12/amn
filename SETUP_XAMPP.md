# 🏨 AMNEN Guest House - Complete Setup Guide for XAMPP

## Quick Start (5 minutes)

### Step 1: Extract Files to XAMPP
```
1. Download the project files
2. Extract to: C:\xampp\htdocs\amnen\
3. Ensure you have these folders:
   - C:\xampp\htdocs\amnen\classes\
   - C:\xampp\htdocs\amnen\config\
   - C:\xampp\htdocs\amnen\sql\
```

### Step 2: Start XAMPP Services
1. Open XAMPP Control Panel
2. Click **Start** next to Apache
3. Click **Start** next to MySQL
4. Wait until both show green checkmarks

### Step 3: Run Setup
1. Open your browser
2. Go to: `http://localhost/amnen/setup.php`
3. Wait for the green checkmarks ✓
4. The database will be created automatically

### Step 4: Login
1. Go to: `http://localhost/amnen/index.php`
2. Click "Login"
3. Use these credentials:
   - **Username:** `admin`
   - **Password:** `Admin@123`

---

## Features Included

✅ **Feature 1: Automated Check-in/Check-out**
   - Automatic check-in at 2 PM
   - Automatic check-out at 11 AM
   - Manual override available

✅ **Feature 2: Mock Fayda ID E-KYC Verification**
   - National ID verification
   - Guest profile validation
   - Compliance tracking

✅ **Feature 3: Accessibility Features**
   - Wheelchair accessible rooms
   - Elevator access tracking
   - Mobility assistance support

✅ **Feature 4: Advanced Room Search & Filters**
   - Filter by price, capacity, amenities
   - Availability calendar
   - Floor level selection

✅ **Feature 5: Booking Cancellation & Cleanup**
   - Automated refund processing
   - Retention policies (7-day cleanup)
   - Cancellation logs

✅ **Feature 6: Maintenance & Cleaning Queue**
   - Room maintenance tracking
   - Cleaning schedules
   - Priority-based task management

---

## Database Information

**Database Name:** `amnen_hotel`  
**Database User:** `root` (default XAMPP)  
**Database Password:** (empty by default)  
**Host:** `localhost`

### Tables Created:
- `users` - User accounts & profiles
- `rooms` - Room inventory & details
- `reservations` - Booking records
- `payments` - Payment transactions
- `feedback` - Guest reviews & ratings
- `checkin_checkout_logs` - Auto check-in/out logs
- `fayda_verifications` - ID verification records
- `accessibility_preferences` - Accessibility settings
- `search_history` - Search analytics
- `cancellation_logs` - Cancellation records
- `maintenance_logs` - Maintenance queue

---

## Default Admin Account

- **Username:** `admin`
- **Password:** `Admin@123`
- **Role:** Administrator

**⚠️ IMPORTANT:** Change this password after first login!

---

## File Structure

```
amnen/
├── config/
│   ├── config.php (Main configuration)
│   └── db.php (Database connection)
├── classes/
│   ├── User.php (User management)
│   ├── Room.php (Room operations)
│   ├── Reservation.php (Booking logic)
│   ├── Payment.php (Payment handling)
│   └── Feedback.php (Reviews & ratings)
├── sql/
│   └── schema.sql (Database schema - auto-loaded)
├── .env (Environment variables)
├── index.php (Homepage)
├── login.php (Login page)
├── register.php (Registration)
├── setup.php (Setup wizard - run once!)
└── README.md (This file)
```

---

## Troubleshooting

### "Database connection failed"
1. Ensure MySQL is running (green in XAMPP)
2. Check if `amnen_hotel` database exists
3. Run `setup.php` again

### "Tables don't exist"
1. Go to `http://localhost/amnen/setup.php`
2. Let it recreate all tables
3. Refresh the page to confirm

### "Login not working"
1. Make sure you're using the correct credentials
2. Check that sessions are enabled in PHP
3. Clear browser cookies and try again

### "Can't access pages"
1. Check your URL: `http://localhost/amnen/index.php`
2. Ensure Apache is running
3. Check file permissions are readable

---

## API Endpoints (for integrations)

```php
// Check availability
POST /api/check-availability.php

// Create booking
POST /api/create-booking.php

// Payment callback
POST /api/webhooks/chapa-callback.php

// Fayda verification
POST /api/fayda-verify.php
```

---

## Environment Variables (.env)

Edit `.env` file to customize:
- `APP_ENV` - Set to 'production' for live
- `DB_HOST`, `DB_USER`, `DB_PASS` - Database credentials
- `AUTO_CHECKIN_ENABLED` - Enable auto check-in
- `AUTO_CHECKOUT_ENABLED` - Enable auto check-out
- `CHAPA_SECRET_KEY` - Your Chapa payment key (add later)
- `FAYDA_ID_API_KEY` - Your Fayda ID key (add later)

---

## First Time Tips

1. **Run setup.php first** - This initializes everything
2. **Create test bookings** - Add sample reservations
3. **Test all roles** - Create receptionist, manager accounts
4. **Review logs** - Check database logs for any errors
5. **Backup database** - Export your database regularly

---

## Support & Issues

If you encounter any errors:
1. Check `php_errors.log` in XAMPP
2. Review your `.env` file
3. Ensure all files are in the correct folders
4. Re-run `setup.php`

---

**Version:** 2.0.0  
**Last Updated:** May 2026  
**Developed for:** XAMPP on Windows/Mac/Linux
