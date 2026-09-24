<?php

/*
|──────────────────────────────────────────────────────────────────────────
| Matriks Hak Akses (Permission Matrix) per Role
|──────────────────────────────────────────────────────────────────────────
|
| Format key: <area>.<fitur>[.<aksi>]
|
|   platform.*  → khusus Super Admin (manajemen tenant, user global, log)
|   shop.*      → operasional toko (dikelola Admin tenant, dibatasi untuk Kasir)
|   pos.*       → akses terminal kasir
|   staff.*     → manajemen pengguna dalam satu tenant (Admin toko)
|   dashboard   → halaman ringkasan
|
| Super Admin sengaja TIDAK diberi akses fitur toko (POS, produk, pesanan,
| laporan, pengaturan) agar isolasi data tenant tetap terjaga.
|
| Wildcard didukung, contoh: 'shop.*' memberi seluruh permission berawalan shop.
*/

return [

    'roles' => [

        // ── Super Admin (Platform Owner) ──────────────────────────
        'super_admin' => [
            'dashboard.view',

            'platform.tenant.view',
            'platform.tenant.create',
            'platform.tenant.update',
            'platform.tenant.delete',

            'platform.user.view',
            'platform.user.manage',

            'platform.activity_log.view',
        ],

        // ── Admin / Pemilik Toko (Tenant) ─────────────────────────
        'admin' => [
            'dashboard.view',

            'shop.settings.view',
            'shop.settings.update',

            'shop.product.view',
            'shop.product.manage',
            'shop.product.delete',

            'shop.category.view',
            'shop.category.manage',

            'shop.stock.view',
            'shop.stock.adjust',

            // Harga modal & laba hanya untuk pemilik toko
            'shop.cost.view',

            'pos.access',
            'pos.transact',

            'shop.order.view',
            'shop.order.delete',

            'shop.payment.gateway',

            'shop.shift.view',
            'shop.shift.manage',

            'shop.report.profit',
            'shop.report.shift',

            'staff.user.view',
            'staff.user.manage',
        ],

        // ── User / Kasir ──────────────────────────────────────────
        'user' => [
            'dashboard.view',

            'shop.product.view',
            'shop.category.view',
            'shop.stock.view',
            'shop.stock.adjust',

            'pos.access',
            'pos.transact',

            'shop.order.view',
            'shop.payment.gateway',

            'shop.shift.view',
            'shop.shift.manage',
        ],

    ],

    // Label tampilan (dipakai Super Admin saat memilih hak akses)
    'labels' => [
        'platform.tenant.view'    => 'Melihat daftar tenant',
        'platform.tenant.create'  => 'Membuat tenant',
        'platform.tenant.update'  => 'Mengubah / suspend tenant',
        'platform.tenant.delete'  => 'Menghapus tenant',
        'platform.user.view'      => 'Melihat seluruh pengguna',
        'platform.user.manage'    => 'Membuat / mengubah pengguna lintas tenant',
        'platform.activity_log.view' => 'Melihat log aktivitas',

        'shop.settings.view'      => 'Melihat pengaturan toko',
        'shop.settings.update'    => 'Mengubah pengaturan toko',
        'shop.product.view'       => 'Melihat produk',
        'shop.product.manage'     => 'Menambah / mengubah produk',
        'shop.product.delete'     => 'Menghapus produk',
        'shop.category.view'      => 'Melihat kategori',
        'shop.category.manage'    => 'Menambah / mengubah kategori',
        'shop.stock.view'         => 'Melihat stok',
        'shop.stock.adjust'       => 'Melakukan penyesuaian stok',
        'shop.cost.view'          => 'Melihat harga modal & laba',
        'pos.access'              => 'Akses terminal kasir',
        'pos.transact'            => 'Memproses transaksi',
        'shop.order.view'         => 'Melihat riwayat pesanan',
        'shop.order.delete'       => 'Menghapus item pesanan',
        'shop.payment.gateway'    => 'Memakai payment gateway (Midtrans)',
        'shop.shift.view'         => 'Melihat sesi shift kasir',
        'shop.shift.manage'       => 'Membuka / menutup shift kasir',
        'shop.report.profit'      => 'Melihat laporan laba',
        'shop.report.shift'       => 'Melihat laporan shift',
        'staff.user.view'         => 'Melihat staf toko',
        'staff.user.manage'       => 'Mengelola staf toko',
    ],
];
