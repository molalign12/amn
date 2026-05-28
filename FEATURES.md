# Amnen Hotel Booking System - Feature Documentation

Complete implementation of all 7 required features for a modern hotel booking management system.

---

## 1. Fayda National ID e-KYC Verification

**Status:** ✅ Complete  
**File:** `fayda-kyc-verification.html`

A modern, self-contained frontend component for Ethiopian National ID (Fayda) verification using e-KYC. **No backend required** - this is a pure frontend mockup that can be dropped into any page.

### Features:
- 3-step verification flow: FIN Input → OTP → Success
- Ethiopian flag styling with professional branding
- Simulated OTP generation and verification
- Mock user profile generation with Amharic names
- Fully responsive design (mobile-friendly)
- Smooth animations and transitions

### Quick Integration:

Drop directly into your HTML:
```html
<iframe src="/amnen/fayda-kyc-verification.html" width="100%" height="100%" style="border: none;"></iframe>
```

Or open directly in browser:
```
/amnen/fayda-kyc-verification.html
```

### 3-Step Flow:

**Step 1: FIN Input**
- User enters 12-digit Fayda Identification Number
- Input validation (digits only, length check)
- Loading simulation (2 seconds)

**Step 2: OTP Verification**
- 6-digit OTP input with auto-advance
- Accepts any valid 6-digit code (demo mode)
- Resend functionality
- Phone number display (masked)

**Step 3: Success & Profile**
- Success animation with checkmark
- Mock e-KYC profile display:
  - Full Name (English & Amharic)
  - Date of Birth
  - Gender
  - FIN
  - Issue & Expiry Dates
  - Verified badge
- Continue or Start Over options

### Mock Data Generated:
```json
{
  "fin": "000000000000",
  "firstName": "Abebe",
  "lastName": "Kebede",
  "firstNameAmharic": "አበበ",
  "lastNameAmharic": "ከበደ",
  "dob": "01/15/1990",
  "gender": "Male",
  "issueDate": "05/01/2020",
  "expiryDate": "04/30/2030"
}
```

### For Production Use:

The component outputs verified data via `completeVerification()` function:
```javascript
{
  "fin": "000000000000",
  "userName": "Abebe Kebede",
  "dob": "01/15/1990",
  "gender": "Male",
  "verified": true,
  "timestamp": "2024-05-28T10:30:00.000Z"
}
```

Connect to your backend API by modifying the `completeVerification()` function.

---

## 2. Room Filtering & Sorting System

**Status:** ✅ Complete  
**Class:** `Room::findAllForGuestBrowseFiltered()`  
**API:** `/api/rooms-search.php`

Advanced search and filtering for guest room browsing.

### Filters Available:
- **Price Range:** `min_price`, `max_price`
- **Room Type:** `single`, `double`, `suite`, `family`, `deluxe`
- **Capacity:** `min_capacity`
- **Amenities:** Array of amenity names (WiFi, AC, TV, etc.)
- **Floor:** Specific floor number

### Sort Options:
- `price_asc` - Low to high
- `price_desc` - High to low
- `capacity_asc` - Fewest guests first
- `capacity_desc` - Most guests first
- `floor_asc` - Ground floor first
- `floor_desc` - Top floor first
- `room_type` - Alphabetical (default)

### API Usage:

```bash
curl "http://localhost/amnen/api/rooms-search.php" \
  -d "check_in=2024-06-01&check_out=2024-06-05" \
  -d "sort_by=price_asc" \
  -d "filters[min_price]=500" \
  -d "filters[max_price]=2000" \
  -d "filters[room_type]=double" \
  -d "filters[min_capacity]=2" \
  -d "filters[amenities][]=WiFi" \
  -d "filters[amenities][]=AC"
```

### Response:
```json
{
  "success": true,
  "count": 5,
  "rooms": [
    {
      "room_id": 3,
      "room_number": "103",
      "room_type": "double",
      "price": "1400.00",
      "capacity": 2,
      "floor": 1,
      "amenities": ["WiFi", "AC", "TV", "Minibar"],
      "guest_label": "Available",
      "guest_availability": "available",
      "bookable": true
    }
  ]
}
```

---

## 3. Accessibility & Floor Notifications

**Status:** ✅ Complete  
**Class:** `AccessibilityHelper`  
**API:** `/api/room-accessibility.php`

WCAG-compliant accessibility features for impaired users.

### Features:
- Floor information with elevator access status
- Screen reader friendly descriptions
- Audio notifications (text-to-speech ready)
- High contrast labels
- Semantic HTML for all room displays

### Accessibility Info Provided:

```json
{
  "room_id": 103,
  "floor": 1,
  "floor_label": "Ground Floor",
  "accessibility_description": "Ground floor, no elevator needed",
  "elevator_access": true,
  "accessible_features": ["Wheelchair accessible", "Level entry", "Wide doorways"],
  "audio_description": "Room 103, ground floor, wheelchair accessible"
}
```

### API Usage:

```bash
curl "http://localhost/amnen/api/room-accessibility.php?room_id=103"
```

### Implementation in Frontend:

```html
<!-- Accessible room display -->
<div role="article" aria-labelledby="room-title">
  <h2 id="room-title">Room 103 - Ground Floor</h2>
  <p role="status" aria-live="polite">
    Wheelchair accessible, no elevator needed
  </p>
  <button aria-label="Read room details aloud">
    🔊 Listen to Details
  </button>
</div>
```

---

## 4. Automated Check-in / Check-out Flow

**Status:** ✅ Complete  
**Class:** `CheckInOut`  
**API:** `/api/checkin-checkout.php`

Self-service digital check-in and check-out system.

### Features:
- Digital key generation (PIN + QR code)
- Room readiness status tracking
- Automated status transitions
- Check-in/out listings
- History tracking

### Digital Key Format:

```json
{
  "key_id": "DK_2024_001",
  "reservation_id": 42,
  "room_id": 103,
  "room_number": "103",
  "pin": "483729",
  "qr_code": "https://qr.code/DK_2024_001",
  "valid_from": "2024-06-01T14:00:00Z",
  "valid_until": "2024-06-05T11:00:00Z",
  "access_type": "room_entry"
}
```

### Check-in Process:

```bash
curl -X POST "http://localhost/amnen/api/checkin-checkout.php" \
  -d "action=checkin&reservation_id=42" \
  -H "Authorization: Bearer $TOKEN"
```

Response:
```json
{
  "success": true,
  "message": "Check-in successful",
  "digital_key": {
    "pin": "483729",
    "qr_url": "https://qr.code/DK_2024_001"
  },
  "room_info": {
    "room_number": "103",
    "floor": 1,
    "checkout_time": "11:00"
  }
}
```

### Check-out Process:

```bash
curl -X POST "http://localhost/amnen/api/checkin-checkout.php" \
  -d "action=checkout&reservation_id=42" \
  -H "Authorization: Bearer $TOKEN"
```

---

## 5. Smart Multi-day Reservation Availability

**Status:** ✅ Complete  
**Methods:** `getAvailabilityCalendar()`, `getAvailableDateRanges()`, `getNextAvailableRange()`  
**API:** `/api/room-availability.php`

Intelligent availability system allowing non-overlapping bookings on the same room.

### Key Methods:

**Availability Calendar** - Shows specific booked vs available dates:
```php
$calendar = Room::getAvailabilityCalendar($roomId, '2024-06-01', '2024-06-30');
// Returns: { '2024-06-01' => ['available' => true], '2024-06-02' => ['available' => false, 'reserved_by' => '2024-06-02 to 2024-06-05'] }
```

**Available Date Ranges** - Consecutive available periods:
```php
$ranges = Room::getAvailableDateRanges($roomId, '2024-06-01', '2024-06-30');
// Returns: [
//   { 'start' => '2024-06-01', 'end' => '2024-06-01' },
//   { 'start' => '2024-06-06', 'end' => '2024-06-15' },
//   { 'start' => '2024-06-20', 'end' => '2024-06-30' }
// ]
```

**Next Available Range** - Find next bookable window:
```php
$next = Room::getNextAvailableRange($roomId, '2024-06-01', $minNights = 2);
// Returns: { 'available' => true, 'check_in' => '2024-06-01', 'check_out' => '2024-06-05', 'nights' => 4 }
```

### API Usage:

```bash
# Get availability calendar
curl "http://localhost/amnen/api/room-availability.php" \
  -d "action=calendar&room_id=103" \
  -d "start_date=2024-06-01" \
  -d "end_date=2024-06-30"

# Get available date ranges
curl "http://localhost/amnen/api/room-availability.php" \
  -d "action=ranges&room_id=103" \
  -d "start_date=2024-06-01" \
  -d "end_date=2024-06-30"

# Check if specific dates are available
curl "http://localhost/amnen/api/room-availability.php" \
  -d "action=check&room_id=103" \
  -d "check_in=2024-06-01" \
  -d "check_out=2024-06-05"
```

### Response Example:

```json
{
  "success": true,
  "available_ranges": [
    {
      "start": "2024-06-01",
      "end": "2024-06-01",
      "nights": 1
    },
    {
      "start": "2024-06-06",
      "end": "2024-06-15",
      "nights": 9
    }
  ],
  "user_can_book": "2024-06-01 to 2024-06-01 or 2024-06-06 to 2024-06-15"
}
```

---

## 6. Additional Services (Add-ons)

**Status:** ✅ Complete  
**Classes:** `ReservationService`  
**API:** `/api/reservation-services.php`

Optional services like car rental, pool access, room upgrades.

### Services Available:

```json
{
  "services": [
    {
      "service_id": 1,
      "name": "Car Rental",
      "category": "transportation",
      "description": "Daily car rental service",
      "price": 50.00,
      "currency": "ETB",
      "icon": "🚗"
    },
    {
      "service_id": 2,
      "name": "Swimming Pool Access",
      "category": "recreation",
      "description": "Full access to outdoor pool",
      "price": 20.00,
      "currency": "ETB",
      "icon": "🏊"
    },
    {
      "service_id": 3,
      "name": "Room Upgrade",
      "category": "accommodation",
      "description": "Upgrade to deluxe room",
      "price": 100.00,
      "currency": "ETB",
      "icon": "⭐"
    }
  ]
}
```

### Add Service to Reservation:

```bash
curl -X POST "http://localhost/amnen/api/reservation-services.php" \
  -d "action=add" \
  -d "reservation_id=42" \
  -d "service_id=1" \
  -d "quantity=3" \
  -H "Authorization: Bearer $TOKEN"
```

### Get Reservation Services:

```bash
curl "http://localhost/amnen/api/reservation-services.php" \
  -d "action=list&reservation_id=42"
```

Response:
```json
{
  "success": true,
  "services": [
    {
      "service_id": 1,
      "reservation_service_id": 10,
      "name": "Car Rental",
      "unit_price": 50.00,
      "quantity": 3,
      "subtotal": 150.00
    }
  ],
  "services_total": 150.00,
  "original_room_price": 1400.00,
  "grand_total": 1550.00
}
```

### Calculate Total with Services:

```php
$total = ReservationService::calculateReservationTotal($reservationId);
// Returns: ['room_price' => 1400, 'services' => 150, 'total' => 1550]
```

---

## 7. Cancelled Bookings Cleanup

**Status:** ✅ Complete  
**Class:** `BookingCleanup`  
**Scripts:** `/api/cleanup-bookings.php`, `/cleanup-cron.php`

Automated cleanup of cancelled bookings and orphaned data.

### Features:
- Automatic deletion/archiving after retention period
- Configurable retention duration (default: 7 days)
- Cleanup history logging
- Orphaned key cleanup
- Payment record cleanup

### Configuration:

```php
// In your app
$cleanup = new BookingCleanup(
    retentionDays: 7,
    archiveMode: true,  // false = delete, true = archive
    includePayments: true,
    includeKeys: true
);
```

### Manual Cleanup:

```bash
curl -X POST "http://localhost/amnen/api/cleanup-bookings.php" \
  -d "action=cleanup" \
  -d "retention_days=7" \
  -H "Authorization: Bearer $ADMIN_TOKEN"
```

Response:
```json
{
  "success": true,
  "cleaned": {
    "cancelled_bookings_deleted": 5,
    "archived_bookings": 3,
    "orphaned_keys_removed": 2,
    "payments_cleaned": 1
  },
  "duration_seconds": 2.5,
  "log_id": "cleanup_2024_05_28_001"
}
```

### Scheduled Cleanup (Cron Job):

Run `/cleanup-cron.php` via your server's cron/task scheduler:

```bash
# Every day at 2 AM
0 2 * * * /usr/bin/php /var/www/amnen/cleanup-cron.php

# Every week at Sunday 3 AM
0 3 * * 0 /usr/bin/php /var/www/amnen/cleanup-cron.php
```

### Cleanup History:

```bash
curl "http://localhost/amnen/api/cleanup-bookings.php" \
  -d "action=history" \
  -d "limit=10" \
  -H "Authorization: Bearer $ADMIN_TOKEN"
```

Returns audit trail of all cleanup operations with:
- Timestamp
- Records cleaned
- Retention period used
- Duration
- Admin who triggered it

---

## Database Schema

All tables automatically created from `sql/amnen_db.sql`. Schema migrations handled in `config/db.php`.

### Key Tables:
- **users** - User accounts & roles
- **rooms** - Room inventory & amenities
- **reservations** - Booking records
- **payments** - Payment tracking
- **feedback** - Guest reviews
- **reservation_services** - Add-on services
- **digital_keys** - Check-in/out access
- **cleanup_log** - Audit trail

---

## API Endpoint Summary

| Endpoint | Method | Purpose |
|----------|--------|---------|
| `/api/rooms-search.php` | POST | Advanced room filtering & sorting |
| `/api/room-accessibility.php` | GET | Accessibility info & floor details |
| `/api/room-availability.php` | POST | Multi-day availability & calendars |
| `/api/checkin-checkout.php` | POST | Digital key & self-service flow |
| `/api/reservation-services.php` | POST/GET | Manage add-on services |
| `/api/cleanup-bookings.php` | POST | Booking cleanup management |

---

## Security Notes

✅ All endpoints use prepared statements (SQL injection protection)  
✅ CSRF tokens required for state-changing operations  
✅ Role-based access control (admin/manager/staff)  
✅ Session timeout enforcement (30 minutes)  
✅ Password hashing with bcrypt  
✅ Data validation on all inputs  

---

## Testing

All features tested and working without errors. No external free tier limits reached during implementation.

For questions or issues, check logs at: `/logs/amnen.log`
