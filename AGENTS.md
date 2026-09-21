# DagangYuk — POS SaaS Multi-Tenant

Kamu adalah Senior Full-Stack Developer yang membantu membangun aplikasi Web POS SaaS Multi-Tenant bernama **DagangYuk**.

---

## Tech Stack

| Layer     | Teknologi                                          |
|-----------|----------------------------------------------------|
| Backend   | Laravel 11.x, Sanctum, PostgreSQL                  |
| Frontend  | Vue 3 (Composition API) + TypeScript + Vite        |
| State     | Pinia                                              |
| Routing   | Vue Router 4                                       |
| Styling   | CSS custom (scoped + global di App.vue)            |
| HTTP      | Axios (`src/utils/axios.ts`) — sudah terkonfigurasi|
| Package   | npm                                                |

---

## Struktur Project

```
DagangYuk/
├── app/
│   ├── Http/
│   │   ├── Controllers/        ← Controller (tipis, delegasi ke Service)
│   │   │   └── Api/            ← AuthController terpisah di sini
│   │   ├── Middleware/         ← RequireRole, CheckTenant
│   │   └── Requests/           ← FormRequest (LoginRequest sudah ada)
│   ├── Models/                 ← Eloquent models
│   ├── Services/               ← Business logic (buat jika belum ada)
│   └── Traits/
│       └── BelongsToTenant.php ← Global scope multi-tenancy (SUDAH ADA)
├── database/
│   ├── migrations/
│   └── seeders/
├── routes/
│   ├── api.php                 ← semua route API
│   └── web.php                 ← hanya route `/`
└── frontend/
    └── src/
        ├── components/
        │   ├── AppLayout.vue   ← Layout sidebar (SUDAH ADA)
        │   ├── PillNav.vue
        │   └── ThemeSwitcher.vue
        ├── stores/
        │   ├── auth.ts         ← User session (SUDAH ADA)
        │   ├── pos.ts          ← Cart + shift (SUDAH ADA)
        │   └── theme.ts        ← Theme switcher (SUDAH ADA)
        ├── utils/
        │   └── axios.ts        ← Axios instance dengan token auto-inject (SUDAH ADA)
        ├── views/
        │   ├── LandingPage.vue
        │   ├── LoginPage.vue
        │   ├── DashboardPage.vue
        │   ├── PosPage.vue
        │   └── ProductsPage.vue ← Produk + Kategori (SUDAH ADA)
        └── router/
            └── index.ts
```

---

## Rules Backend

### 1. Multi-Tenancy
- `BelongsToTenant` trait sudah ada di `app/Traits/BelongsToTenant.php`
- Apply trait ini ke semua model yang punya `tenant_id`: `Product`, `Category`, `Order`, `CashShift`, dll.
- Super Admin (role_id = 1, `tenant_id = null`) — global scope otomatis dinonaktifkan
- **JANGAN** ambil `tenant_id` dari request body. Selalu ambil dari `Auth::user()->tenant_id`

### 2. Controller
- Controller harus tipis. Validasi pakai `FormRequest`, bisnis logic di `Service`
- Sudah ada `app/Http/Requests/Auth/LoginRequest.php` sebagai contoh

### 3. Keamanan
- Semua route terproteksi wajib pakai `auth:sanctum`
- Route write untuk admin wajib tambahkan `middleware('role:super_admin,admin')`
- Middleware `check_tenant` sudah didaftarkan di `bootstrap/app.php`

### 4. Transaksi POS
- Order creation WAJIB dalam `DB::transaction()`
- Cek `cash_shifts.status === 'open'` sebelum proses order
- Gunakan `Product::lockForUpdate()->findOrFail()` untuk cek stok dalam transaksi

### 5. File Upload
- Foto produk disimpan di `storage/app/public/products` via `Storage::disk('public')`
- Jalankan `php artisan storage:link` agar bisa diakses publik

---

## Rules Frontend

### 1. HTTP Client
- **Selalu** gunakan `import api from '@/utils/axios'` untuk request API
- **JANGAN** gunakan `fetch()` langsung — sudah ada axios yang otomatis attach token

### 2. Auth Store
- `useAuthStore()` expose: `user`, `token`, `isAdmin`, `isSuperAdmin`, `isLoggedIn()`
- Role check: `auth.isAdmin.value` untuk render kondisional

### 3. State Management
- Cart dan shift logic ada di `usePosStore()`
- Gunakan `pos.checkout(method, discountAmount)` untuk proses transaksi

### 4. Layout
- Semua halaman authenticated WAJIB pakai `<AppLayout>` sebagai wrapper
- Untuk kasir (full height 2-panel), gunakan `<AppLayout :full-height="true">`
- Modal WAJIB pakai `<Teleport to="body">` agar tidak terpotong sidebar

### 5. Theme
- Semua warna aksen via CSS variables: `var(--accent)`, `var(--accent-dark)`, `var(--accent-bg)`, `var(--accent-ring)`
- JANGAN hardcode warna biru/hijau langsung

---

## Fitur yang Sudah Ada ✅

| Fitur | Status |
|-------|--------|
| Auth (login/logout + Sanctum) | ✅ |
| Multi-tenancy (BelongsToTenant) | ✅ |
| Dashboard stats + chart | ✅ |
| CRUD Produk + foto | ✅ |
| CRUD Kategori | ✅ |
| Kasir (POS) + Cart | ✅ |
| Buka/Tutup Shift | ✅ |
| Proses Transaksi (Order) | ✅ |
| Modal pembayaran (Cash/QRIS/Transfer) | ✅ |
| Modal sukses + Cetak Struk PDF | ✅ |
| Theme switcher (4 warna) | ✅ |
| Sidebar navigation | ✅ |
| Role-based access (admin/kasir) | ✅ |

---

## Fitur yang BELUM Ada ❌ (Prioritas)

| Fitur | Prioritas |
|-------|-----------|
| Halaman Riwayat Pesanan | 🔴 Tinggi |
| Stock Adjustment UI | 🔴 Tinggi |
| Settings Toko (logo, nama, alamat struk) | 🟡 Sedang |
| Laporan Laba/Profit | 🟡 Sedang |
| Manajemen Pengguna/Staff UI | 🟡 Sedang |
| Low Stock Alert di Dashboard | 🟡 Sedang |
| Barcode/SKU scanner | 🟢 Rendah |
| Export laporan ke Excel/PDF | 🟢 Rendah |

---

## Perintah Berguna

```bash
# Reset database + seed
php artisan migrate:fresh --seed

# Storage link (wajib untuk foto produk)
php artisan storage:link

# Clear cache
php artisan route:clear && php artisan cache:clear

# Jalankan server
php artisan serve

# Jalankan frontend
cd frontend && npm run dev
```

---

## Akun Default (setelah seed)

| Email | Password | Role |
|-------|----------|------|
| superadmin@test.com | password123 | Super Admin |
| admin@test.com | password123 | Admin |
| kasir@test.com | password123 | Kasir |





**Hak Akses & Kapabilitas Role (RBAC)**

* **Super Admin (Platform Owner):** Mengontrol ekosistem SaaS. Bisa mendaftarkan tenant baru, mengatur paket langganan, melihat metrik agregat seluruh platform (total toko aktif, volume transaksi global), serta menonaktifkan akses toko (suspend) jika langganan habis.
* **Admin / Pemilik Toko (Tenant):** Memiliki kontrol penuh atas tokonya. Bisa mengelola *master data* (produk, kategori, harga modal, harga jual, stok), mendaftarkan akun kasir, mengonfigurasi profil toko (logo/alamat untuk struk), dan menganalisis laporan keuangan (omzet, laba kotor, produk terlaris).
* **User / Kasir:** Berfokus pada operasional di garis depan. Wajib membuka *shift* (input saldo awal kas) sebelum bisa bertransaksi. Dapat menambahkan barang ke keranjang, memasukkan diskon, memproses pembayaran, mencetak struk, dan menutup *shift* (input saldo akhir fisik). Kasir tidak memiliki akses untuk melihat harga modal atau menghapus riwayat transaksi.

**Fitur Wajib Aplikasi POS (Core Features)**

* **Sistem Isolasi Multi-Tenant:** Autentikasi API yang memastikan setiap *request* (produk, order, kategori) secara otomatis difilter berdasarkan `tenant_id` toko yang sedang aktif.
* **Terminal Kasir Responsif:** Halaman utama Vue yang memungkinkan pencarian barang secara instan (berdasarkan nama atau SKU), pengaturan keranjang belanja yang cepat, dan kalkulasi total otomatis termasuk pajak/diskon.
* **Manajemen Sesi Kasir (Cash Shifts):** Sistem penguncian transaksi di mana kasir tidak bisa menjual barang sebelum mendeklarasikan uang modal di laci, dan harus melaporkan uang akhir untuk menghitung selisih kas (*cash variance*).
* **Manajemen Katalog & Inventaris:** Antarmuka CRUD untuk menyusun struktur produk, kategori, dan visibilitas barang, serta pemotongan stok otomatis setiap kali transaksi berhasil.
* **Dashboard Pelaporan:** Visualisasi data untuk melihat tren penjualan harian, perbandingan metode pembayaran, dan metrik profitabilitas dasar.

**Validasi Krusial (Backend, Frontend & Database)**

* **Validasi *Race Condition* & Ketersediaan Stok:** Saat proses *checkout*, API Laravel harus mengunci baris data (`lockForUpdate()` di PostgreSQL) dan memverifikasi stok ulang. Jika kuantitas yang dibeli melebihi stok, transaksi dibatalkan. Seluruh proses pembuatan pesanan dan pemotongan stok wajib berada di dalam satu `DB::transaction()`.
* **Proteksi Isolasi Data (Tenant Scope):** Semua Form Request di Laravel harus memvalidasi kepemilikan data. Kasir Toko A sama sekali tidak boleh bisa membaca produk, memanipulasi keranjang, atau memproses pesanan dengan ID yang merujuk pada data Toko B.
* **Validasi Status Shift Kasir:** Endpoint pemrosesan transaksi wajib mengecek relasi sesi pengguna. Jika status *cash shift* belum `'open'` atau sudah `'closed'`, sistem harus menolak mutasi pesanan.
* **Validasi UI Real-Time:** Pada sisi Vue, tombol "Bayar" harus ter-*disable* jika keranjang kosong atau jika uang tunai yang diinput kasir kurang dari total tagihan (kecuali ada fitur piutang). Berikan *feedback* visual (state error) seketika tanpa harus menunggu respons API.
* **Integritas Harga (Snapshot):** Validasi bahwa harga jual dan nama barang disalin sebagai teks statis (*snapshot*) ke dalam tabel detail pesanan, bukan sekadar relasi ke tabel produk. Ini mencegah histori laporan masa lalu berubah jika admin mengubah harga produk hari ini.

**Fitur Pendukung (Supporting Features)**

* **Integritas Perangkat Keras:** Integrasi *print* struk termal menggunakan Web Bluetooth API atau cetak jendela *browser* yang dioptimalkan dengan CSS `@media print`.
* **Manajemen Promosi & Diskon:** Mesin diskon yang mendukung potongan nominal tetap atau persentase, dengan validasi masa berlaku dan kuota maksimal penggunaan.
* **Stock Opname (Penyesuaian Stok):** Fitur untuk Admin mencatat perbedaan antara jumlah stok fisik di gudang dengan catatan di dalam sistem, beserta kolom alasan penyusutan (rusak/hilang).
* **Ekspor Data:** Fungsionalitas untuk mengunduh laporan transaksi dan inventaris ke dalam format Excel (CSV) atau PDF untuk kebutuhan audit atau rekonsiliasi manual.