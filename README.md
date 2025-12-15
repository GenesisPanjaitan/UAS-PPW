# 🚗 REST API Ride-Hailing Mini

> REST API sederhana untuk aplikasi pemesanan ojek online menggunakan Laravel 11

[![Tests](https://img.shields.io/badge/tests-27%20passed-brightgreen)]()
[![Coverage](https://img.shields.io/badge/coverage-97%20assertions-blue)]()
[![Laravel](https://img.shields.io/badge/Laravel-11.x-red)]()
[![PHP](https://img.shields.io/badge/PHP-8.2+-purple)]()

---

## 📋 Deskripsi

Proyek ini merupakan implementasi REST API untuk sistem ride-hailing mini (seperti Gojek/Grab) yang mencakup:
- ✅ CRUD lengkap untuk manajemen ride
- ✅ Validasi input yang ketat
- ✅ Error handling dengan HTTP status code yang tepat
- ✅ Testing komprehensif (27 test cases)
- ✅ Dokumentasi API lengkap

---

## 🎯 Fitur Utama

### RESTful Endpoints:
1. **GET /api/rides** - List semua rides (dengan pagination & filter)
2. **POST /api/rides** - Buat order baru
3. **GET /api/rides/{id}** - Detail ride
4. **PUT /api/rides/{id}** - Update ride
5. **DELETE /api/rides/{id}** - Hapus ride
6. **PUT /api/rides/{id}/accept** - Driver terima order
7. **PUT /api/rides/{id}/complete** - Selesaikan perjalanan
8. **PUT /api/rides/{id}/cancel** - Batalkan order

### Business Logic:
- Status management: `pending` → `accepted` → `completed`
- Validasi business rules (contoh: tidak bisa update ride yang sudah accepted)
- Relationship management (User ↔ Ride ↔ Driver)

---

## 🛠️ Tech Stack

- **Framework:** Laravel 11.x
- **PHP:** >= 8.2
- **Database:** MySQL/MariaDB
- **Testing:** PHPUnit
- **API Format:** JSON

---

## 🚀 Quick Start

### 1. Clone & Install Dependencies
```bash
git clone [repository-url]
cd uas-ride-hailing
composer install
```

### 2. Setup Environment
```bash
cp .env.example .env
php artisan key:generate
```

### 3. Configure Database
Edit `.env`:
```env
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=uas_ride_hailing
DB_USERNAME=root
DB_PASSWORD=
```

### 4. Migrate Database
```bash
php artisan migrate:fresh
```

### 5. Run Development Server
```bash
php artisan serve
# Server berjalan di http://localhost:8000
```

### 6. Run Tests (Optional)
```bash
php artisan test
# atau
php artisan test --filter=RideApiTest
```

---

## 📖 Dokumentasi

- **[API Documentation](API_DOCUMENTATION.md)** - Dokumentasi lengkap semua endpoints
- **[Laporan UAS](LAPORAN_UAS.md)** - Laporan pengerjaan dan analisis
- **Routes:** Lihat di `routes/api.php`
- **Controller:** `app/Http/Controllers/Api/RideController.php`
- **Tests:** `tests/Feature/RideApiTest.php`

---

## 🧪 Testing

```bash
# Run all tests
php artisan test

# Run with details
php artisan test --filter=RideApiTest

# With coverage (jika xdebug installed)
php artisan test --coverage
```

**Test Results:**
```
✓ 27 tests passed
✓ 97 assertions
✓ 0 failures
✓ Duration: 5.29s
```

---

## 📁 Struktur Proyek

```
uas-ride-hailing/
├── app/
│   ├── Http/
│   │   └── Controllers/
│   │       └── Api/
│   │           └── RideController.php      # Main API Controller
│   └── Models/
│       ├── Ride.php                         # Ride Model
│       └── User.php                         # User Model
│
├── database/
│   ├── factories/
│   │   ├── UserFactory.php
│   │   └── RideFactory.php
│   └── migrations/
│       ├── 2025_12_15_071838_create_users_table.php
│       └── 2025_12_15_072301_create_rides_table.php
│
├── routes/
│   └── api.php                              # API Routes
│
├── tests/
│   └── Feature/
│       └── RideApiTest.php                  # 27 Test Cases
│
├── API_DOCUMENTATION.md                      # Dokumentasi API
├── LAPORAN_UAS.md                            # Laporan lengkap
└── README.md                                 # File ini
```

---

## 💡 Contoh Penggunaan

### Create Ride
```bash
curl -X POST http://localhost:8000/api/rides \
  -H "Content-Type: application/json" \
  -d '{
    "user_id": 1,
    "pickup_location": "Del Institute of Technology",
    "dropoff_location": "Balige Market",
    "price": 15000
  }'
```

**Response:**
```json
{
    "status": "success",
    "message": "Order berhasil dibuat",
    "data": {
        "id": 1,
        "user_id": 1,
        "driver_id": null,
        "pickup_location": "Del Institute of Technology",
        "dropoff_location": "Balige Market",
        "price": "15000.00",
        "status": "pending",
        "created_at": "2025-12-15T07:00:00.000000Z",
        "updated_at": "2025-12-15T07:00:00.000000Z"
    }
}
```

### Accept Ride
```bash
curl -X PUT http://localhost:8000/api/rides/1/accept \
  -H "Content-Type: application/json" \
  -d '{"driver_id": 2}'
```

---

## 📊 Database Schema

### Table: users
| Column | Type | Description |
|--------|------|-------------|
| id | bigint | Primary Key |
| name | varchar(255) | Nama user |
| email | varchar(255) | Email (unique) |
| password | varchar(255) | Hashed password |
| timestamps | timestamp | created_at, updated_at |

### Table: rides
| Column | Type | Description |
|--------|------|-------------|
| id | bigint | Primary Key |
| user_id | bigint | Foreign Key → users |
| driver_id | bigint (nullable) | Foreign Key → users |
| pickup_location | varchar(255) | Lokasi jemput |
| dropoff_location | varchar(255) | Lokasi tujuan |
| price | decimal(10,2) | Harga perjalanan |
| status | enum | pending, accepted, completed, canceled |
| timestamps | timestamp | created_at, updated_at |

---

## ✅ Checklist Fitur

- [x] RESTful API Design
- [x] CRUD Operations
- [x] Input Validation
- [x] Error Handling
- [x] HTTP Status Codes
- [x] Database Relationships
- [x] Factory Pattern
- [x] Unit & Feature Tests
- [x] API Documentation
- [x] Business Logic Validation

---

## 🔜 Future Improvements

- [ ] Laravel Sanctum untuk Authentication
- [ ] Role-based Authorization (User vs Driver)
- [ ] Rate Limiting
- [ ] API Versioning (v1, v2)
- [ ] Real-time notifications (WebSocket)
- [ ] Payment integration
- [ ] GPS tracking integration
- [ ] Admin dashboard
- [ ] Docker containerization

---

## 📝 License

Proyek ini dibuat untuk keperluan UAS Pemrograman dan Pengembangan Web.

---

## 👨‍💻 Author

**[Nama Anda]**  
NIM: [NIM Anda]  
Email: [Email Anda]

---

## 📞 Support

Jika ada pertanyaan atau issue, silakan hubungi melalui:
- Email: [email@example.com]
- GitHub Issues: [repository-url/issues]

---

**Last Updated:** December 15, 2025  
**Version:** 1.0.0

---

⭐ Jangan lupa beri star jika project ini membantu!
