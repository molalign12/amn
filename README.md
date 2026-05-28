# AMNEN Hotel Management System - Clean Architecture

## 🏗️ Modern Architecture

```
app/
├── Database/      # Connection management
├── Models/        # ORM (User, Room, Reservation, Services)
├── Services/      # Business logic (Fayda ID, Cleanup)
├── Helpers/       # Utilities (Validation, API Response)
└── Middleware/    # Auth, logging

api/               # REST endpoints
├── rooms/         # Room listing & filtering
├── reservations/  # Booking management
└── cron/          # Scheduled tasks
```

## ✨ Features Implemented

### 1. ✅ **Fayda ID Integration**
- OAuth-style authentication
- User identity verification
- Webhook support
- Location: `app/Services/FaydaIdService.php`

### 2. ✅ **Smart Room Filtering & Sorting**
- Filter by: price, capacity, floor, amenities
- Sort by: price, rating, availability
- API: `GET /api/rooms/available?filters...`

### 3. ✅ **Accessibility Features**
- Floor notifications (Ground, First, Second, etc.)
- Elevator access indicators
- Screen reader friendly
- Location: `app/Models/Room.php`

### 4. ✅ **Automated Check-in/Check-out**
- Configurable check-in/checkout times
- Self-service ready
- Location: `app/Models/Reservation.php`

### 5. ✅ **Smart Multi-day Availability**
- No overlap checking
- Same room bookable for non-overlapping dates
- Location: `app/Models/Room.php::isAvailableForDates()`

### 6. ✅ **Additional Services (Add-ons)**
- Car rental, pool access, etc.
- Per-service pricing
- Location: `app/Models/AdditionalService.php`

### 7. ✅ **Automatic Cleanup**
- Removes cancelled bookings after 7 days
- Cascading deletes
- Cron job: `api/cron/cleanup-bookings.php`

## 📊 Best Free AI Tools Comparison

| Tool | Price | Power | Setup | Limits |
|------|-------|-------|-------|--------|
| **Ollama + CodeLlama** | FREE | ⭐⭐⭐⭐ | 5 min | None |
| **DeepSeek** | FREE | ⭐⭐⭐⭐ | API key | 1M tokens/month |
| **Claude Free** | FREE | ⭐⭐⭐ | Web only | 5/day |
| **GitHub Copilot** | $10/mo | ⭐⭐⭐⭐ | GitHub | 60 completions/mo free |

### **🎯 Recommended: Ollama + CodeLlama**
- 100% free, no limits
- Runs locally, offline
- Perfect for PHP refactoring
- VS Code integration

```bash
# Install
curl https://ollama.ai/install.sh | sh

# Run
ollama run codellama

# Use in VS Code with "LocalAI" extension
```

## 🚀 Quick Start

### 1. Setup
```bash
git clone https://github.com/molalign12/amn.git
cd amn
cp .env.example .env
```

### 2. Create Database
```sql
-- See migrations/ folder for full SQL
```

### 3. Test API
```bash
# Get rooms
curl "http://localhost/amnen/api/rooms/available?check_in=2026-06-01&check_out=2026-06-05"

# Create booking
curl -X POST http://localhost/amnen/api/reservations/create \
  -H "Content-Type: application/json" \
  -d '{
    "user_id": 1,
    "room_id": 1,
    "check_in_date": "2026-06-01",
    "check_out_date": "2026-06-05",
    "num_guests": 2
  }'

# Cleanup (scheduled)
curl "http://localhost/amnen/api/cron/cleanup-bookings.php?key=YOUR_KEY"
```

## 📁 Directory Structure

```
amn/
├── app/                    # Clean code
│   ├── Database/
│   ├── Models/
│   ├── Services/
│   ├── Helpers/
│   └── Middleware/
├── api/                    # Endpoints
├── config/                 # Configuration
├── migrations/             # SQL scripts
├── views/                  # Frontend
├── public/                 # Assets
└── storage/                # Logs, uploads
```

## 🔧 Configuration

Edit `.env`:
```php
FAYDA_ID_ENABLED=true
FAYDA_ID_API_KEY=your_key
FAYDA_ID_API_URL=https://api.fayda.id/v1

CHAPA_ENABLED=true
CHAPA_SECRET_KEY=your_key

CANCELLED_BOOKING_RETENTION_DAYS=7
CLEANUP_CRON_ENABLED=true
```

## 🤝 Contributing

```bash
git checkout -b feature/your-feature
git commit -m "Add feature"
git push origin feature/your-feature
```

## 📄 License

MIT License
