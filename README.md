# 🏨 AMNEN Hotel Management System - Complete Documentation

**Version:** 2.0.0  
**Status:** ✅ Complete & Ready for XAMPP  
**All 6 Features:** Working & Integrated

---

## 🚀 SUPER QUICK START

### For the impatient (literally 3 minutes):

```
1. Extract amnen folder to C:\xampp\htdocs\
2. Start Apache + MySQL in XAMPP
3. Go to: http://localhost/amnen/setup.php
4. Wait for green checkmarks
5. Go to: http://localhost/amnen/index.php
6. Login: admin / Admin@123
DONE! ✅
```

Full guide in **QUICK_START.txt** or **SETUP_XAMPP.md**

---

## 📋 What's Included

### ✅ 6 Complete Features (INTEGRATED & WORKING):

1. **Automated Check-in/Check-out**
   - Auto check-in at 2 PM
   - Auto check-out at 11 AM
   - Manual override support
   - Logging & history tracking

2. **Mock Fayda ID E-KYC Verification**
   - National ID verification system
   - Guest identity validation
   - Compliance tracking
   - Mock data for testing

3. **Accessibility Features**
   - Wheelchair accessible room filtering
   - Elevator access tracking
   - Mobility assistance features
   - Dietary restriction tracking

4. **Advanced Room Search & Availability**
   - Price range filters
   - Capacity filters
   - Amenity filters
   - Floor/elevator filters
   - Availability calendar

5. **Booking Cancellation & Cleanup**
   - Automated cancellation processing
   - Refund management
   - 7-day retention policy
   - Cleanup logging

6. **Maintenance & Cleaning Queue**
   - Priority-based task management
   - Room status tracking
   - Maintenance scheduling
   - Cleaning assignment

### ✅ Core Features:
- User authentication (Admin, Manager, Receptionist, Customer)
- Room inventory management
- Booking system with Chapa payment integration
- Guest feedback & ratings
- Dashboard for each role
- Responsive design

---

## 📁 File Structure (What You Need)

```
amnen/
├── bootstrap.php              ← Loads everything correctly
├── setup.php                  ← Run this first!
├── diagnostic.php             ← Check if everything works
├── index.php                  ← Homepage
├── login.php                  ← Login page
├── register.php               ← Registration page
├── logout.php                 ← Logout
├── forgot-password.php        ← Password recovery
│
├── config/
│   ├── config.php             ← Main config
│   └── db.php                 ← Database connection
│
├── classes/                   ← All business logic
│   ├── User.php
│   ├── Room.php
│   ├── Reservation.php
│   ├── Payment.php
│   ├── Feedback.php
│   ├── CheckInOut.php         ← Automated check-in/out
│   ├── FaydaAuth.php          ← Mock Fayda ID
│   ├── AccessibilityHelper.php ← Accessibility features
│   ├── BookingCleanup.php     ← Cancellation cleanup
│   ├── RefundRequest.php      ← Refund handling
│   ├── ReservationService.php ← Reservation service
│   └── [others]
│
├── sql/
│   └── schema.sql             ← Database schema (auto-loaded)
│
├── .env                       ← Configuration (for XAMPP)
│
└── [Documentation files]
    ├── QUICK_START.txt        ← Start here! (3 min guide)
    ├── SETUP_XAMPP.md         ← Detailed setup
    ├── FEATURES.md            ← Feature documentation
    └── README.md              ← This file
```

---

## 🔧 Installation Steps

### Prerequisites:
- XAMPP installed (Apache + MySQL)
- PHP 7.4+ (comes with XAMPP)
- Windows/Mac/Linux

### Installation:

**1. Copy Files**
```
Extract amnen folder → C:\xampp\htdocs\amnen
```

**2. Start Services**
```
Open XAMPP Control Panel
→ Start Apache
→ Start MySQL
```

**3. Run Setup**
```
Browser: http://localhost/amnen/setup.php
Wait for: ✓✓✓ All green checkmarks
```

**4. Access System**
```
Homepage: http://localhost/amnen/index.php
Login: admin / Admin@123
```

---

## 🔐 Default Accounts

### Admin Account (Created automatically)
- **Username:** `admin`
- **Password:** `Admin@123`
- **Role:** Administrator
- **Status:** Active

**⚠️ CHANGE THIS PASSWORD AFTER FIRST LOGIN!**

### Additional Accounts to Create:
Create these through the admin dashboard:
- **Receptionist** - Manages check-ins/check-outs
- **Manager** - Reports & analytics
- **Customer** - Booking & reviews

---

## 🗄️ Database Info

**Name:** `amnen_hotel` (auto-created)  
**Host:** `localhost`  
**User:** `root`  
**Password:** (empty - XAMPP default)  

### Tables (11 total):
- `users` - User accounts
- `rooms` - Room inventory
- `reservations` - Bookings
- `payments` - Payment transactions
- `feedback` - Guest reviews
- `checkin_checkout_logs` - Auto check-in/out history
- `fayda_verifications` - ID verification records
- `accessibility_preferences` - Accessibility settings
- `search_history` - Search logs
- `cancellation_logs` - Cancellations
- `maintenance_logs` - Maintenance queue

---

## 🚨 Troubleshooting

### Issue: "Database connection failed"
**Solution:**
1. Ensure MySQL is running (green in XAMPP)
2. Run `http://localhost/amnen/setup.php`
3. Check if `amnen_hotel` database exists

### Issue: "Tables don't exist"
**Solution:**
```
Go to: http://localhost/amnen/setup.php
It will recreate all tables
```

### Issue: "Login page shows, but can't login"
**Solution:**
1. Verify admin account exists:
   `http://localhost/amnen/diagnostic.php`
2. Reset password by running setup.php
3. Clear browser cookies

### Issue: "404 Page not found"
**Solution:**
```
Check URL: http://localhost/amnen/index.php
NOT: http://localhost/amnen/
Folder must be at: C:\xampp\htdocs\amnen\
```

### Issue: "Blank white page"
**Solution:**
1. Check diagnostic: `http://localhost/amnen/diagnostic.php`
2. Look for PHP errors
3. Verify .env file exists
4. Run setup.php again

---

## 🔧 Configuration (.env)

Edit the `.env` file to customize:

```env
APP_ENV=development              # development or production
DB_HOST=localhost                # Database host
DB_USER=root                     # Database user
DB_PASS=                         # Database password
FAYDA_ID_ENABLED=true           # Enable Fayda ID
AUTO_CHECKIN_ENABLED=true       # Auto check-in
AUTO_CHECKOUT_ENABLED=true      # Auto check-out
ACCESSIBILITY_ENABLED=true      # Accessibility features
```

---

## 📊 Database Initialization

**Automatic:**
- Run `setup.php` once
- All tables created automatically
- Sample data inserted

**Manual (if needed):**
1. Open phpMyAdmin: `http://localhost/phpmyadmin`
2. Create database: `amnen_hotel`
3. Import: `sql/schema.sql`

---

## 🎯 Features Deep Dive

### 1. Automated Check-in/Check-out
```
✓ Auto check-in: 14:00 (2 PM)
✓ Auto check-out: 11:00 (11 AM)
✓ Manual override available
✓ Logged in database
✓ Status updated automatically
```

### 2. Mock Fayda ID Verification
```
✓ National ID verification
✓ Guest profile validation
✓ Compliance tracking
✓ Mock data for testing
✓ Integration ready
```

### 3. Accessibility Features
```
✓ Wheelchair accessible rooms
✓ Elevator access tracking
✓ Mobility assistance
✓ Dietary restrictions
✓ Service animal support
```

### 4. Room Search Filters
```
✓ Price range: Min/Max
✓ Capacity: 1-6 guests
✓ Amenities: Multiple select
✓ Floor: 1-5
✓ Elevator access
✓ Availability calendar
```

### 5. Cancellation Cleanup
```
✓ Automated refund processing
✓ 7-day retention policy
✓ Cancellation logging
✓ Refund tracking
✓ Status management
```

### 6. Maintenance Queue
```
✓ Task priority: Low/Med/High/Urgent
✓ Task types: Clean/Repair/Inspect
✓ Status tracking
✓ Assignment to staff
✓ Time estimation
```

---

## 👥 User Roles

### Admin
- View all users
- Manage staff
- View reports
- System settings
- Database management

### Manager
- View all reservations
- Generate reports
- Monitor revenue
- View feedback
- Staffing analytics

### Receptionist
- Check-in guests
- Check-out guests
- View today's schedule
- Process payments
- Update room status

### Customer
- Browse rooms
- Make bookings
- View own reservations
- Leave feedback
- Manage profile

---

## 🔗 API Endpoints (Available)

```
POST /api/check-availability.php
POST /api/create-booking.php
POST /api/webhooks/chapa-callback.php
POST /api/fayda-verify.php
GET  /api/room-availability.php
POST /api/cancel-booking.php
GET  /api/guest-feedback.php
```

---

## 📱 Browser Compatibility

✅ Chrome/Edge (Latest)
✅ Firefox (Latest)
✅ Safari (Latest)
✅ Mobile browsers

---

## 🔐 Security Features

✅ Password hashing (bcrypt)
✅ Session management
✅ CSRF protection ready
✅ SQL injection prevention (PDO)
✅ Input validation
✅ XSS prevention headers
✅ HTTPS ready

---

## 💾 Backup & Recovery

### Backup Database:
```
1. Open phpMyAdmin
2. Select `amnen_hotel`
3. Click Export
4. Save as: amnen_backup.sql
```

### Restore Database:
```
1. Open phpMyAdmin
2. Create new database: `amnen_hotel`
3. Click Import
4. Select: amnen_backup.sql
```

---

## ❓ FAQ

**Q: Can I use this on Linux?**
A: Yes! Works on Windows/Mac/Linux XAMPP

**Q: Can I migrate to production?**
A: Yes, but use proper hosting with SSL

**Q: How do I add more rooms?**
A: Admin dashboard → Rooms → Add Room

**Q: How do I reset admin password?**
A: Run setup.php or use forgot-password.php

**Q: Can I add payment gateway?**
A: Yes, integrate Chapa API (keys in .env)

**Q: How do I schedule automated tasks?**
A: Use cleanup-cron.php with system cron

---

## 📞 Support

If you encounter issues:
1. Run `http://localhost/amnen/diagnostic.php`
2. Check error logs in `php_errors.log`
3. Review this README
4. Review setup instructions
5. Check .env configuration

---

## ✨ What's Next?

After setup:
1. ✅ Login as admin
2. ✅ Change admin password
3. ✅ Add rooms
4. ✅ Create staff accounts
5. ✅ Make test bookings
6. ✅ Test all features
7. ✅ Add real payment keys
8. ✅ Enable Fayda ID integration

---

## 📜 Version History

**v2.0.0** (Current)
- All 6 features implemented
- Complete database schema
- Auto-setup system
- Diagnostic tools
- Portable configuration

**v1.0.0**
- Initial release

---

## 📄 License

This software is provided as-is for educational and commercial use.

---

**🎉 Ready to go live with AMNEN Hotel!**

Start with: **QUICK_START.txt** or visit **setup.php**

Questions? Check **diagnostic.php** for system health.

Happy booking! 🏨
