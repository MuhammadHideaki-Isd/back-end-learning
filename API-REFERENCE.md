# API Reference

Dokumentasi lengkap semua endpoint API untuk project pembelajaran Full-Stack.

## Base URL

```
http://localhost:8000/api
```

## Authentication (Autentikasi)

Menggunakan **Laravel Sanctum** (Token-based Authentication).

### Cara Kerja:

1. User melakukan **register** atau **login**
2. Server mengembalikan `access_token`
3. Token ini digunakan di **header** setiap request yang membutuhkan autentikasi:

```
Authorization: Bearer {access_token}
```

### Catatan Penting:

- Token bersifat **personal** — setiap user punya token masing-masing
- Token bisa di-revoke (dihapus) saat logout
- Jika token expired atau invalid, server akan mengembalikan error `401 Unauthenticated`

---

## Auth Endpoints

### POST /register

Register user baru dan langsung mendapat token.

**URL:** `http://localhost:8000/api/register`

**Headers:**

```
Content-Type: application/json
Accept: application/json
```

**Request Body (JSON):**

```json
{
    "name": "John Doe",
    "email": "john@example.com",
    "password": "password123",
    "password_confirmation": "password123"
}
```

**Response 201 (Berhasil):**

```json
{
    "user": {
        "id": 1,
        "name": "John Doe",
        "email": "john@example.com",
        "created_at": "2025-04-16T10:00:00.000000Z",
        "updated_at": "2025-04-16T10:00:00.000000Z"
    },
    "access_token": "1|abc123def456ghi789...",
    "token_type": "Bearer"
}
```

**Response 422 (Validasi Gagal):**

```json
{
    "message": "The email has already been taken.",
    "errors": {
        "email": ["The email has already been taken."]
    }
}
```

---

### POST /login

Login dengan email dan password, mendapat token.

**URL:** `http://localhost:8000/api/login`

**Headers:**

```
Content-Type: application/json
Accept: application/json
```

**Request Body (JSON):**

```json
{
    "email": "john@example.com",
    "password": "password123"
}
```

**Response 200 (Berhasil):**

```json
{
    "access_token": "2|xyz789abc123def456...",
    "token_type": "Bearer"
}
```

**Response 401 (Gagal Login):**

```json
{
    "message": "Invalid credentials"
}
```

---

### POST /logout

Logout dan menghapus token yang sedang digunakan.

**URL:** `http://localhost:8000/api/logout`

**Headers:**

```
Authorization: Bearer {access_token}
Accept: application/json
```

**Request Body:** Kosong (tidak perlu body)

**Response 200 (Berhasil):**

```json
{
    "message": "Logged out successfully"
}
```

**Response 401 (Token Invalid):**

```json
{
    "message": "Unauthenticated."
}
```

---

## User Endpoints

### GET /users

Mengambil daftar semua user.

**URL:** `http://localhost:8000/api/users`

**Headers:**

```
Authorization: Bearer {access_token}
Accept: application/json
```

**Response 200:**

```json
[
    {
        "id": 1,
        "name": "John Doe",
        "email": "john@example.com",
        "avatar": "http://localhost:8000/storage/avatars/john-avatar.jpg",
        "access_level": 1,
        "created_at": "2025-04-16T10:00:00.000000Z",
        "updated_at": "2025-04-16T10:00:00.000000Z"
    },
    {
        "id": 2,
        "name": "Jane Doe",
        "email": "jane@example.com",
        "avatar": null,
        "access_level": 0,
        "created_at": "2025-04-16T11:00:00.000000Z",
        "updated_at": "2025-04-16T11:00:00.000000Z"
    }
]
```

---

### GET /users/{id}

Mengambil detail user berdasarkan ID.

**URL:** `http://localhost:8000/api/users/1`

**Headers:**

```
Authorization: Bearer {access_token}
Accept: application/json
```

**Response 200:**

```json
{
    "id": 1,
    "name": "John Doe",
    "email": "john@example.com",
    "avatar": "http://localhost:8000/storage/avatars/john-avatar.jpg",
    "access_level": 1,
    "created_at": "2025-04-16T10:00:00.000000Z",
    "updated_at": "2025-04-16T10:00:00.000000Z"
}
```

**Response 404:**

```json
{
    "message": "User not found"
}
```

---

### POST /users

Membuat user baru (alternatif register tanpa auto-login).

**URL:** `http://localhost:8000/api/users`

**Headers:**

```
Content-Type: application/json
Accept: application/json
```

**Request Body (JSON):**

```json
{
    "name": "Jane Doe",
    "email": "jane@example.com",
    "password": "password123"
}
```

**Response 201:**

```json
{
    "id": 2,
    "name": "Jane Doe",
    "email": "jane@example.com",
    "access_level": 0,
    "created_at": "2025-04-16T11:00:00.000000Z",
    "updated_at": "2025-04-16T11:00:00.000000Z"
}
```

---

### PUT /users/{id} (dengan Upload Avatar)

Update data user dan/atau upload foto avatar.

**URL:** `http://localhost:8000/api/users/1`

**Headers:**

```
Authorization: Bearer {access_token}
Accept: application/json
```

**Catatan:** Karena ada upload file, gunakan `multipart/form-data` (BUKAN JSON).
Untuk method PUT dengan form-data, gunakan POST dengan field `_method: PUT`.

**Request Body (multipart/form-data):**

| Field    | Type   | Required | Keterangan                        |
| -------- | ------ | -------- | --------------------------------- |
| _method  | string | Yes      | Isi dengan "PUT"                  |
| name     | string | No       | Nama baru                         |
| email    | string | No       | Email baru                        |
| password | string | No       | Password baru                     |
| avatar   | file   | No       | File gambar (jpeg/png/jpg/gif, max 2MB) |

**Response 200:**

```json
{
    "id": 1,
    "name": "John Updated",
    "email": "john@example.com",
    "avatar": "http://localhost:8000/storage/avatars/new-avatar.jpg",
    "access_level": 1,
    "created_at": "2025-04-16T10:00:00.000000Z",
    "updated_at": "2025-04-16T12:00:00.000000Z"
}
```

---

### DELETE /users/{id}

Menghapus user beserta file avatar-nya.

**URL:** `http://localhost:8000/api/users/1`

**Headers:**

```
Authorization: Bearer {access_token}
Accept: application/json
```

**Response 200:**

```json
{
    "message": "User deleted"
}
```

---

## Product Endpoints

### GET /products

Mengambil daftar semua product.

**URL:** `http://localhost:8000/api/products`

**Headers:**

```
Authorization: Bearer {access_token}
Accept: application/json
```

**Response 200:**

```json
[
    {
        "id": 1,
        "name": "Laptop ASUS",
        "description": "Laptop gaming dengan RTX 4060",
        "price": "12500000.00",
        "stock": 10,
        "category": "Electronics",
        "image": "http://localhost:8000/storage/products/laptop-asus.jpg",
        "created_at": "2025-04-16T10:00:00.000000Z",
        "updated_at": "2025-04-16T10:00:00.000000Z"
    }
]
```

---

### GET /products/{id}

Mengambil detail product berdasarkan ID.

**URL:** `http://localhost:8000/api/products/1`

**Headers:**

```
Authorization: Bearer {access_token}
Accept: application/json
```

**Catatan:** Endpoint ini membutuhkan `accessLevel` middleware (user harus punya `access_level > 0`).

**Response 200:**

```json
{
    "id": 1,
    "name": "Laptop ASUS",
    "description": "Laptop gaming dengan RTX 4060",
    "price": "12500000.00",
    "stock": 10,
    "category": "Electronics",
    "image": "http://localhost:8000/storage/products/laptop-asus.jpg",
    "created_at": "2025-04-16T10:00:00.000000Z",
    "updated_at": "2025-04-16T10:00:00.000000Z"
}
```

**Response 403 (Access Level Rendah):**

```json
{
    "message": "Unauthorized"
}
```

---

### POST /products (dengan Upload Gambar)

Membuat product baru dengan upload gambar.

**URL:** `http://localhost:8000/api/products`

**Headers:**

```
Authorization: Bearer {access_token}
Accept: application/json
```

**Catatan:** Membutuhkan `accessLevel` middleware.

**Request Body (multipart/form-data):**

| Field       | Type    | Required | Keterangan                              |
| ----------- | ------- | -------- | --------------------------------------- |
| name        | string  | Yes      | Nama product                            |
| description | string  | No       | Deskripsi product                       |
| price       | numeric | Yes      | Harga (angka desimal)                   |
| stock       | integer | Yes      | Jumlah stok                             |
| category    | string  | No       | Kategori product                        |
| image       | file    | No       | File gambar (jpeg/png/jpg/gif, max 2MB) |

**Response 201:**

```json
{
    "id": 1,
    "name": "Laptop ASUS",
    "description": "Laptop gaming dengan RTX 4060",
    "price": "12500000.00",
    "stock": 10,
    "category": "Electronics",
    "image": "http://localhost:8000/storage/products/laptop-asus.jpg",
    "created_at": "2025-04-16T10:00:00.000000Z",
    "updated_at": "2025-04-16T10:00:00.000000Z"
}
```

---

### PUT /products/{id} (dengan Upload Gambar)

Update product dan/atau ganti gambar.

**URL:** `http://localhost:8000/api/products/1`

**Headers:**

```
Authorization: Bearer {access_token}
Accept: application/json
```

**Catatan:** Membutuhkan `accessLevel` middleware. Untuk upload file dengan PUT, gunakan POST + `_method: PUT`.

**Request Body (multipart/form-data):**

| Field       | Type    | Required | Keterangan                              |
| ----------- | ------- | -------- | --------------------------------------- |
| _method     | string  | Yes      | Isi dengan "PUT"                        |
| name        | string  | No       | Nama baru                               |
| description | string  | No       | Deskripsi baru                          |
| price       | numeric | No       | Harga baru                              |
| stock       | integer | No       | Stok baru                               |
| category    | string  | No       | Kategori baru                           |
| image       | file    | No       | Gambar baru (akan menghapus yang lama)  |

**Response 200:**

```json
{
    "id": 1,
    "name": "Laptop ASUS ROG",
    "description": "Laptop gaming terbaru",
    "price": "15000000.00",
    "stock": 5,
    "category": "Electronics",
    "image": "http://localhost:8000/storage/products/new-image.jpg",
    "created_at": "2025-04-16T10:00:00.000000Z",
    "updated_at": "2025-04-16T13:00:00.000000Z"
}
```

---

### DELETE /products/{id}

Menghapus product beserta file gambarnya.

**URL:** `http://localhost:8000/api/products/1`

**Headers:**

```
Authorization: Bearer {access_token}
Accept: application/json
```

**Catatan:** Membutuhkan `accessLevel` middleware.

**Response 200:**

```json
{
    "message": "Product deleted"
}
```

---

## Error Responses (Respon Error Umum)

### 401 Unauthenticated

Terjadi ketika request tidak menyertakan token atau token sudah tidak valid.

```json
{
    "message": "Unauthenticated."
}
```

**Penyebab umum:**

- Lupa menyertakan header `Authorization`
- Token sudah expired atau di-revoke (logout)
- Format header salah (harus `Bearer {token}`, perhatikan spasi)

---

### 403 Forbidden

Terjadi ketika user tidak punya izin (access level rendah).

```json
{
    "message": "Unauthorized"
}
```

**Penyebab:** User dengan `access_level = 0` mencoba mengakses endpoint yang dilindungi middleware `accessLevel`.

---

### 404 Not Found

Terjadi ketika resource yang dicari tidak ada di database.

```json
{
    "message": "Product not found"
}
```

atau

```json
{
    "message": "User not found"
}
```

---

### 422 Validation Error

Terjadi ketika data yang dikirim tidak lolos validasi.

```json
{
    "message": "The name field is required.",
    "errors": {
        "name": ["The name field is required."],
        "price": ["The price field must be a number."]
    }
}
```

**Catatan:** Field `errors` berisi detail per-field yang gagal validasi. Ini berguna untuk menampilkan pesan error di form frontend.

---

## Tips untuk Frontend Developer

### 1. Selalu sertakan header Accept

```
Accept: application/json
```

Ini memberitahu Laravel bahwa kita mengharapkan response dalam format JSON (bukan HTML).

### 2. Simpan token dengan aman

- **Website:** Gunakan `localStorage` (untuk pembelajaran). Di production, pertimbangkan `httpOnly cookies`.
- **Flutter:** Gunakan `SharedPreferences` atau `flutter_secure_storage`.

### 3. Handle error di setiap request

Selalu cek status code response:

- `200-299` = Berhasil
- `401` = Perlu login ulang
- `403` = Tidak punya izin
- `404` = Data tidak ditemukan
- `422` = Data tidak valid
- `500` = Error di server

### 4. Upload file menggunakan multipart/form-data

Ketika ada upload file, JANGAN gunakan `Content-Type: application/json`. Gunakan `multipart/form-data`:

**JavaScript (Fetch API):**

```javascript
const formData = new FormData();
formData.append('name', 'Product Name');
formData.append('image', fileInput.files[0]);

fetch(url, {
    method: 'POST',
    headers: {
        'Authorization': 'Bearer ' + token,
        // JANGAN set Content-Type untuk FormData, browser otomatis menangani
    },
    body: formData
});
```

**Flutter (http package):**

```dart
var request = http.MultipartRequest('POST', Uri.parse(url));
request.headers['Authorization'] = 'Bearer $token';
request.fields['name'] = 'Product Name';
request.files.add(await http.MultipartFile.fromPath('image', filePath));
var response = await request.send();
```

### 5. PUT dengan file upload

HTML form dan beberapa HTTP client tidak support PUT dengan file. Solusi Laravel:

- Kirim sebagai **POST** request
- Tambahkan field `_method` dengan value `PUT`
- Laravel akan memperlakukan ini sebagai PUT request

```javascript
const formData = new FormData();
formData.append('_method', 'PUT');  // Trick untuk Laravel
formData.append('name', 'Updated Name');
formData.append('image', newFile);

fetch(url, {
    method: 'POST',  // Tetap POST
    headers: { 'Authorization': 'Bearer ' + token },
    body: formData
});
```
