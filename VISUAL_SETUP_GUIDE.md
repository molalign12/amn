# AMNEN HOTEL - XAMPP SETUP (VISUAL GUIDE)

## STEP 1: Download & Extract Files

```
1. Download the project files from GitHub
2. Extract/Unzip the folder
3. You should see: amnen (folder)
```

## STEP 2: Move to XAMPP htdocs

```
Windows:
  → Copy the "amnen" folder
  → Go to: C:\xampp\htdocs\
  → Paste it there
  → Result: C:\xampp\htdocs\amnen\

Mac:
  → Copy the "amnen" folder  
  → Go to: /Applications/XAMPP/htdocs/
  → Paste it there
  → Result: /Applications/XAMPP/htdocs/amnen/

Linux:
  → Copy the "amnen" folder
  → Go to: /opt/xampp/htdocs/
  → Paste it there
  → Result: /opt/xampp/htdocs/amnen/
```

## STEP 3: Start XAMPP

```
1. Open XAMPP Control Panel
   Windows: XAMPP Control Panel.exe
   Mac: /Applications/XAMPP/manager-osx.app
   Linux: sudo /opt/xampp/manage_xampp start

2. Click "Start" next to Apache
   Wait for: (Running) indicator

3. Click "Start" next to MySQL
   Wait for: (Running) indicator

Both should show green checkmarks ✓
```

## STEP 4: Initialize Database

```
1. Open your web browser (Chrome, Firefox, etc)

2. Go to: http://localhost/amnen/setup.php

3. Wait for the page to load

4. You should see green checkmarks (✓) appearing:
   ✓ Loaded .env file
   ✓ Config loaded
   ✓ Database connection established
   ✓ Database tables created
   ✓ All classes loaded
   ✓ Admin user created

5. When all green, you're done with setup!
```

## STEP 5: Login to System

```
1. Go to: http://localhost/amnen/index.php

2. Click the "Login" button

3. Enter these credentials:
   Username: admin
   Password: Admin@123

4. Click "Login"

5. You should see the Admin Dashboard!
```

## What You Can Do Now

### As Admin:
- View all bookings
- Manage rooms
- View guest feedback
- Check all payments
- View reports
- Manage staff accounts

### Create Test Data:
1. Add more rooms
2. Create test customer accounts
3. Make test bookings
4. Leave test feedback
5. Test check-in/check-out

---

## If Something Goes Wrong

### Issue: "Error loading page"
**Solution:** Check that XAMPP Apache is running

### Issue: "Cannot connect to database"
**Solution:** 
1. Check MySQL is running (green in XAMPP)
2. Go to: http://localhost/amnen/setup.php again
3. Let it recreate the database

### Issue: "Login not working"
**Solution:**
1. Go to: http://localhost/amnen/diagnostic.php
2. Check if admin user exists
3. If missing, run setup.php again

### Issue: "Folder not found"
**Solution:**
1. Make sure folder is at: C:\xampp\htdocs\amnen\
2. Check the folder path is exact
3. Restart Apache

---

## Useful URLs

After setup, you can access:

```
Homepage:
  http://localhost/amnen/index.php

Login Page:
  http://localhost/amnen/login.php

Registration:
  http://localhost/amnen/register.php

Diagnostics (check system health):
  http://localhost/amnen/diagnostic.php

Setup (if you need to reinitialize):
  http://localhost/amnen/setup.php

phpMyAdmin (manage database):
  http://localhost/phpmyadmin
```

---

## Key Information

**Default Admin Account:**
- Username: `admin`
- Password: `Admin@123`

**Database:**
- Name: `amnen_hotel` (auto-created)
- Host: `localhost`
- User: `root`
- Password: (empty)

**Important:** Change the admin password after first login!

---

## Features You Can Test

After logging in, test these 6 features:

1. **Automated Check-in/Check-out**
   - Make a booking
   - System will auto check-in at 2 PM
   - System will auto check-out at 11 AM

2. **Mock Fayda ID Verification**
   - Go to registration
   - Try to verify national ID
   - See mock verification system

3. **Accessibility Features**
   - Search for rooms
   - Filter by "wheelchair accessible"
   - See filtered results

4. **Room Search & Filters**
   - Search rooms by date range
   - Filter by price
   - Filter by capacity
   - See availability calendar

5. **Booking Cancellation**
   - Make a booking
   - Cancel it
   - See refund process

6. **Maintenance Queue**
   - Go to admin panel
   - View maintenance logs
   - Assign cleaning tasks

---

## Final Checklist

Before using the system, verify:

- [ ] XAMPP is installed
- [ ] Apache is running (green ✓)
- [ ] MySQL is running (green ✓)
- [ ] Folder is at C:\xampp\htdocs\amnen\
- [ ] Ran setup.php successfully
- [ ] Can access http://localhost/amnen/index.php
- [ ] Can login with admin / Admin@123
- [ ] Dashboard loads without errors
- [ ] All 6 features visible in menus

---

## What's Next?

1. Create more user accounts (receptionist, manager)
2. Add real rooms to the system
3. Make test bookings
4. Test the checkout payment process
5. Integrate real Chapa payment keys
6. Integrate real Fayda ID API
7. Customize for your hotel

---

## Support Resources

- **QUICK_START.txt** - 3 minute setup guide
- **README.md** - Complete documentation
- **diagnostic.php** - System health check
- **INSTALLATION_SUMMARY.txt** - Feature checklist

---

**You're all set! Enjoy your AMNEN Hotel Management System! 🎉**

If you need help, check the README.md or run diagnostic.php
