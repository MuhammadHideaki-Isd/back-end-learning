# Panduan Siswa - Laravel API Learning Project

## Tentang Project Ini

Project ini mengajarkan bagaimana **Backend** (Laravel API) dan **Frontend** (Website + Flutter) berkomunikasi melalui REST API.

Kamu akan belajar:
- Membuat REST API menggunakan Laravel
- Autentikasi menggunakan Token (Sanctum)
- CRUD (Create, Read, Update, Delete) data
- Upload file (gambar) via API
- Mengkonsumsi API dari Website (Fetch API) dan Mobile (Flutter)

---

## Struktur Project

```
api-laravel/
├── backend/          ← LARAVEL (REST API Server) — yang kamu kerjakan di server
├── mobile/           ← Flutter (Mobile App) — kerjakan di laptop
├── website/          ← HTML/CSS/JS (Web Client) — kerjakan di laptop
├── docs/             ← Dokumentasi & referensi
│   ├── PLAN.md                  → Rencana & task breakdown
│   ├── API-REFERENCE.md         → Dokumentasi API (request/response)
│   ├── STRUCTURE.md             → Penjelasan struktur folder
│   ├── SEEDER.md                → Data dummy & cara seeder
│   └── API-Learning.postman_collection.json → Collection untuk testing API
├── deployment/       ← File Docker & setup server (untuk guru)
└── README.md         ← Overview project
```

### Penjelasan Setiap Folder

| Folder | Kegunaan | Dikerjakan di mana |
|--------|----------|-------------------|
| `backend/` | REST API server Laravel. Menerima request, proses data, return JSON | Upload ke **server** via SFTP |
| `mobile/` | Aplikasi Flutter yang mengkonsumsi API | Di **laptop** lokal (flutter run) |
| `website/` | Website HTML/CSS/JS yang mengkonsumsi API dengan Fetch | Di **laptop** lokal (buka di browser) |
| `docs/` | Dokumentasi lengkap (API reference, struktur, planning) | Baca sebagai referensi |
| `deployment/` | File Docker untuk server (dikelola guru) | Tidak perlu disentuh siswa |

---

## Yang Perlu Kamu Siapkan

### Di Laptop:
- **VS Code** dengan extension:
  - SFTP (untuk upload file ke server)
  - Flutter (untuk mobile development)
- **Flutter SDK** (untuk menjalankan mobile app)
- **Browser** (untuk testing website dan akses API)

### Credential dari Guru:
- Username SSH/SFTP (contoh: `ade-setiawan`)
- Password (contoh: `AdePassword123!`)
- Port yang dialokasikan (contoh: `18091`)
- IP Server: `100.110.141.44`

---

## Step 1: Setup SFTP di VS Code

SFTP digunakan untuk upload folder `backend/` ke server. Setiap kali kamu save file, otomatis terupload.

### 1.1 Install Extension SFTP

1. Buka VS Code
2. Tekan `Ctrl+Shift+X` (Extensions)
3. Cari "SFTP" oleh Natizyskunk
4. Install

### 1.2 Buat File sftp.json

1. Buka **folder root project** (`api-laravel/`) di VS Code
2. Tekan `Ctrl+Shift+P` → ketik "SFTP: Config"
3. Akan muncul file `.vscode/sftp.json`
4. Isi dengan (sesuaikan username & password kamu):

```json
{
    "name": "Laravel API Server",
    "host": "100.110.141.44",
    "protocol": "sftp",
    "port": 22,
    "username": "GANTI_DENGAN_USERNAME_KAMU",
    "password": "GANTI_DENGAN_PASSWORD_KAMU",
    "remotePath": "/home/idnsolo_remote/personal/GANTI_DENGAN_USERNAME_KAMU/app",
    "uploadOnSave": true,
    "useTempFile": false,
    "openSsh": false,
    "ignore": [
        "vendor",
        "node_modules",
        ".git",
        "mobile",
        "website",
        "docs",
        "deployment",
        ".vscode",
        "storage/logs",
        "storage/framework/cache",
        "storage/framework/sessions",
        "storage/framework/views",
        "README.md"
    ]
}
```

**Contoh untuk user `ade-setiawan`:**
```json
{
    "name": "Laravel API Server",
    "host": "100.110.141.44",
    "protocol": "sftp",
    "port": 22,
    "username": "ade-setiawan",
    "password": "AdePassword123!",
    "remotePath": "/home/idnsolo_remote/personal/ade-setiawan/app",
    "uploadOnSave": true,
    "useTempFile": false,
    "openSsh": false,
    "ignore": [
        "vendor",
        "node_modules",
        ".git",
        "mobile",
        "website",
        "docs",
        "deployment",
        ".vscode",
        "storage/logs",
        "storage/framework/cache",
        "storage/framework/sessions",
        "storage/framework/views",
        "README.md"
    ]
}
```

> **PENTING:**
> - `remotePath` mengarah ke `~/app` (bukan `~/app/backend`)
> - Folder `backend/` di lokal akan menjadi `~/app/backend/` di server (otomatis sesuai)
> - Folder yang di-ignore: mobile, website, docs, deployment, vendor, node_modules (tidak perlu di server)
> - Yang ter-upload ke server hanya folder `backend/` saja

### 1.3 Upload Project

**Opsi A: Upload via SFTP (otomatis setiap save)**

1. Tekan `Ctrl+Shift+P` → ketik "SFTP: Upload Folder"
2. Pilih folder root project
3. Tunggu proses upload selesai
4. Setelah ini, setiap kali save file di folder `backend/` akan otomatis upload ke server

**Opsi B: Upload via terminal (cepat, tanpa vendor/)**

Cara yang direkomendasikan karena lebih cepat (skip folder `vendor/` yang besar):

**Mac / Linux (rsync — paling cepat):**
```bash
cd /path/to/api-laravel
rsync -avz --exclude='vendor' --exclude='node_modules' --exclude='.git' --exclude='storage/logs/*' backend/ USERNAME_KAMU@100.110.141.44:/home/idnsolo_remote/personal/USERNAME_KAMU/app/backend/
```

Contoh:
```bash
cd /path/to/api-laravel
rsync -avz --exclude='vendor' --exclude='node_modules' --exclude='.git' backend/ ade-setiawan@100.110.141.44:/home/idnsolo_remote/personal/ade-setiawan/app/backend/
```

**Windows (PowerShell — tar + scp):**
```powershell
cd C:\path\to\api-laravel
# 1. Compress backend tanpa vendor (jadi 1 file kecil)
tar --exclude='vendor' --exclude='node_modules' --exclude='.git' -czf backend.tar.gz backend/
# 2. Upload 1 file (cepat)
scp backend.tar.gz USERNAME_KAMU@100.110.141.44:/home/idnsolo_remote/personal/USERNAME_KAMU/app/
# 3. Extract di server
ssh USERNAME_KAMU@100.110.141.44 "cd ~/app && tar -xzf backend.tar.gz && rm backend.tar.gz"
# 4. Hapus file lokal
del backend.tar.gz
```

> **Kenapa JANGAN pakai `scp -r backend/` langsung?**
> - Folder `vendor/` berisi 5000+ file (50MB+), upload sangat lama via Tailscale
> - Docker akan otomatis `composer install` di server → vendor/ tidak perlu diupload
> - Pakai `rsync --exclude` atau `tar --exclude` jauh lebih cepat

**Hasil upload yang benar:**
```
Server: ~/app/
├── backend/
│   ├── app/
│   ├── composer.json    ← Harus ada di sini
│   ├── database/
│   ├── routes/
│   └── ...
├── Dockerfile
├── docker-compose.yml
└── docker-entrypoint.sh
```

---

## Step 2: Jalankan Laravel di Server (Docker)

Setelah upload, kamu perlu menjalankan container Docker agar Laravel berjalan.

### 2.1 SSH ke Server

Buka terminal (Terminal di VS Code atau Terminal biasa):

```bash
ssh GANTI_DENGAN_USERNAME_KAMU@100.110.141.44
```

Contoh:
```bash
ssh ade-setiawan@100.110.141.44
```

Masukkan password saat diminta.

### 2.2 Jalankan Docker

```bash
# Masuk ke folder app
cd ~/app

# Build & jalankan container (pertama kali atau setelah ada perubahan besar)
docker compose up -d --build

# Lihat log (pastikan "Laravel is ready!" muncul)
docker compose logs -f

# Tekan Ctrl+C untuk keluar dari log
```

### 2.3 Verifikasi

Buka browser dan akses:
```
http://100.110.141.44:PORT_KAMU/api/products
```

Contoh untuk port 18091:
```
http://100.110.141.44:18091/api/products
```

Jika muncul `{"message":"Unauthenticated."}` — itu **benar**! Artinya Laravel sudah jalan dan endpoint butuh token.

---

## Step 3: Testing API

### Menggunakan Browser/Hoppscotch

1. Buka https://hoppscotch.io
2. Import collection dari `docs/API-Learning.postman_collection.json`
3. Atau test manual:

**Login (untuk dapat token):**
- Method: POST
- URL: `http://100.110.141.44:PORT_KAMU/api/login`
- Headers: `Content-Type: application/json`, `Accept: application/json`
- Body:
```json
{
    "email": "admin@example.com",
    "password": "password123"
}
```

**Response berhasil:**
```json
{
    "access_token": "1|abc123...",
    "token_type": "Bearer"
}
```

4. Copy `access_token` dari response
5. Untuk request selanjutnya, tambahkan header:
```
Authorization: Bearer 1|abc123...
```

### Akun Testing (dari Seeder)

| Email | Password | Role |
|-------|----------|------|
| admin@example.com | password123 | Admin (bisa CRUD product) |
| manager@example.com | password123 | Admin |
| budi@example.com | password123 | User biasa |
| siti@example.com | password123 | User biasa |

---

## Step 4: Website (HTML/CSS/JS)

Website sudah ada di folder `website/`. Bisa langsung dibuka di browser.

### Cara Menjalankan

1. Buka file `website/index.html` di browser
2. Atau gunakan Live Server extension di VS Code:
   - Klik kanan `index.html` → "Open with Live Server"

### Yang Perlu Disesuaikan

Edit `website/js/config.js` — ganti URL sesuai port kamu:

```javascript
const API_BASE_URL = 'http://100.110.141.44:PORT_KAMU/api';
```

Contoh:
```javascript
const API_BASE_URL = 'http://100.110.141.44:18091/api';
```

### Halaman yang Tersedia

| File | Fungsi |
|------|--------|
| `index.html` | Login |
| `register.html` | Register user baru |
| `dashboard.html` | Halaman utama setelah login |
| `products.html` | CRUD Product + upload gambar |
| `users.html` | Edit User + upload avatar |

---

## Step 5: Mobile (Flutter)

Flutter app ada di folder `mobile/`. Jalankan di laptop.

### Cara Menjalankan

```bash
cd mobile
flutter pub get
flutter run
```

### Yang Perlu Disesuaikan

Edit `mobile/lib/services/api_service.dart` — ganti base URL:

```dart
// Ganti dengan IP server dan port kamu
static const String baseUrl = 'http://100.110.141.44:PORT_KAMU/api';
```

Contoh:
```dart
static const String baseUrl = 'http://100.110.141.44:18091/api';
```

**Catatan untuk Android Emulator:**
Jika menggunakan Android Emulator, gunakan IP Tailscale langsung (bukan 10.0.2.2) karena server bukan di localhost.

---

## Perintah Docker yang Perlu Diingat

| Perintah | Kapan Digunakan |
|----------|----------------|
| `docker compose up -d --build` | Pertama kali, atau setelah ubah Dockerfile |
| `docker compose up -d` | Setelah restart server (tanpa rebuild) |
| `docker compose down` | Mau stop container |
| `docker compose restart` | Setelah ubah file .env |
| `docker compose logs -f` | Cek error atau status |
| `docker compose exec web bash` | Masuk ke dalam container (debug) |
| `docker compose exec web php artisan migrate:fresh --seed` | Reset database ke awal |
| `docker compose exec web php artisan route:list` | Lihat semua endpoint |

---

## Troubleshooting

### "Unauthenticated" saat akses API
- Kamu belum login / token belum dikirim
- Pastikan header `Authorization: Bearer {token}` ada di setiap request
- Endpoint `/api/products` memang butuh token — login dulu di `/api/login`

### "Unauthorized" (403) saat create product
- Kamu login sebagai user biasa (access_level = 0)
- Login sebagai admin: `admin@example.com` / `password123`

### "attempt to write a readonly database" saat login
- Database SQLite tidak punya write permission
- Fix:
```bash
docker compose exec web chmod 777 /var/www/html/database
docker compose exec web chmod 666 /var/www/html/database/database.sqlite
```

### "The route login could not be found" (404)
- Kamu akses URL tanpa prefix `/api/`
- Semua endpoint harus diawali `/api/`: `http://server:port/api/login` (bukan `/login`)

### Container tidak jalan / error
```bash
# Cek log error:
docker compose logs --tail=30

# Rebuild dari awal:
docker compose down
docker compose up -d --build
```

### Container status "Restarting"
- Artinya entrypoint gagal (biasanya composer install error)
- Cek log: `docker logs NAMA_CONTAINER --tail=30`
- Kemungkinan: PHP version tidak cocok, atau database error

### File yang diupload tidak muncul / error upload
```bash
# Fix permission di dalam container:
docker compose exec web chmod -R 777 /var/www/html/storage
docker compose exec web chmod -R 777 /var/www/html/bootstrap/cache
```

### SFTP gagal upload (Permission denied)
- Docker mungkin sudah ubah ownership file
- Fix dari akun admin (guru):
```bash
sudo chown -R USERNAME:personal /home/idnsolo_remote/personal/USERNAME/app/backend/
```

### Mau reset database (mulai dari awal)
```bash
docker compose exec web php artisan migrate:fresh --seed
```

### Perubahan kode tidak terlihat
- Pastikan SFTP `uploadOnSave: true` sudah aktif
- Cek apakah file sudah ter-upload: `Ctrl+Shift+P` → "SFTP: Upload Folder"
- Container tidak perlu di-restart untuk perubahan file PHP (langsung terdeteksi)
- Kecuali ubah `.env` atau `config/` → perlu `docker compose restart`

---

## Referensi

- Dokumentasi API lengkap: `docs/API-REFERENCE.md`
- Daftar data seeder: `docs/SEEDER.md`
- Struktur project detail: `docs/STRUCTURE.md`
- Planning & task breakdown: `docs/PLAN.md`

---

## Database & Migration

Berikut struktur tabel yang digunakan di project ini. Pastikan migration kamu sesuai.

### Tabel: users

| Kolom | Tipe | Keterangan |
|-------|------|------------|
| id | bigint (auto) | Primary key |
| name | string(255) | Nama lengkap, wajib |
| email | string(255), unique | Email untuk login, wajib |
| avatar | string(255), nullable | Path file foto profil |
| email_verified_at | timestamp, nullable | (default Laravel, tidak dipakai) |
| password | string(255) | Password yang sudah di-hash |
| access_level | integer, default 0 | 0 = user biasa, 1 = admin |
| remember_token | string(100), nullable | (default Laravel) |
| created_at | timestamp | Otomatis oleh Laravel |
| updated_at | timestamp | Otomatis oleh Laravel |

**Contoh migration:**
```php
Schema::create('users', function (Blueprint $table) {
    $table->id();
    $table->string('name');
    $table->string('email')->unique();
    $table->string('avatar')->nullable();
    $table->timestamp('email_verified_at')->nullable();
    $table->string('password');
    $table->integer('access_level')->default(0);
    $table->rememberToken();
    $table->timestamps();
});
```

---

### Tabel: products

| Kolom | Tipe | Keterangan |
|-------|------|------------|
| id | bigint (auto) | Primary key |
| name | string(255) | Nama produk, wajib |
| description | text, nullable | Deskripsi produk |
| price | decimal(10,2) | Harga, wajib, 2 angka desimal |
| stock | unsigned integer, default 0 | Jumlah stok, wajib |
| category | string(255), nullable | Kategori produk |
| image | string(255), nullable | Path file gambar produk |
| created_at | timestamp | Otomatis oleh Laravel |
| updated_at | timestamp | Otomatis oleh Laravel |

**Contoh migration:**
```php
Schema::create('products', function (Blueprint $table) {
    $table->id();
    $table->string('name');
    $table->text('description')->nullable();
    $table->decimal('price', 10, 2);
    $table->unsignedInteger('stock')->default(0);
    $table->string('category')->nullable();
    $table->string('image')->nullable();
    $table->timestamps();
});
```

---

### Tabel: personal_access_tokens (dari Sanctum)

Tabel ini otomatis dibuat saat `php artisan install:api`. Tidak perlu diubah.

Menyimpan token autentikasi user (dibuat saat login/register, dihapus saat logout).

---

### Model: User

```php
// app/Models/User.php

protected $fillable = [
    'name',
    'email',
    'password',
    'access_level',
    'avatar',
];

protected $hidden = [
    'password',
    'remember_token',
];
```

### Model: Product

```php
// app/Models/Product.php

protected $fillable = [
    'name',
    'description',
    'price',
    'stock',
    'category',
    'image',
];
```

---

### Validasi di Controller

**Register:**
```
name     → required, string, max:255
email    → required, email, unique:users
password → required, string, min:6, confirmed
```

**Login:**
```
email    → required, email
password → required
```

**Create/Update Product:**
```
name        → required, string, max:255
description → nullable, string
price       → required, numeric, min:0
stock       → required, integer, min:0
category    → nullable, string, max:255
image       → nullable, image, mimes:jpeg,png,jpg,gif, max:2048
```

**Update User:**
```
name     → sometimes, string, max:255
email    → sometimes, email, unique:users (kecuali diri sendiri)
password → nullable, string, min:6
avatar   → nullable, image, mimes:jpeg,png,jpg,gif, max:2048
```
