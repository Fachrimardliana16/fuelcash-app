# Alur Configuration App Kas Kecil (Petty Cash)

## Alur Setoran Kas Kecil

1. **Pencatatan Setoran**

    - Admin/user melakukan input setoran kas kecil melalui menu "Setor Kas Kecil"
    - Mengisi form dengan data:
        - Tanggal setoran
        - Jumlah setoran
        - Keterangan
        - Upload bukti setoran (opsional)
    - Configuration App secara otomatis:
        - Membuat nomor setoran unik (format: DEP-YYYYMMDD-XXXX)
        - Mencatat jumlah setoran sebagai saldo tersedia
        - Mencatat user yang melakukan input

2. **Pengelolaan Saldo**
    - Setiap setoran memiliki jumlah saldo tersisa (remaining_amount) yang sama dengan jumlah setoran awal
    - Saldo ini akan berkurang ketika digunakan untuk pengeluaran kas kecil
    - Status setoran akan ditandai sebagai "fully used" jika saldo sudah habis terpakai

## Alur Pengeluaran Kas Kecil

1. **Pencatatan Pengeluaran**

    - Admin/user melakukan input pengeluaran melalui menu "Pengeluaran Kas Kecil"
    - Mengisi form dengan data:
        - Tanggal pengeluaran
        - Penerima dana
        - Kategori pengeluaran
        - Jumlah pengeluaran
        - Keterangan
        - Upload dokumen pendukung:
            - Kwitansi perusahaan
            - Struk belanja
            - Dokumen permintaan barang (DPB)
    - Configuration App secara otomatis:
        - Membuat nomor pengeluaran unik (format: EXP-YYYYMMDD-XXXX)
        - Mencatat user yang melakukan input

2. **Alokasi FIFO**
    - Configuration App akan mengalokasikan pengeluaran dengan metode FIFO:
        - Mencari setoran-setoran yang masih memiliki saldo (tidak fully used)
        - Mengambil dana dari setoran tertua terlebih dahulu
        - Mencatat alokasi pengeluaran dalam tabel PettyCashExpenseAllocation
        - Mengurangi saldo setoran yang digunakan (remaining_amount)
        - Menandai setoran sebagai "fully used" jika saldo habis
    - Jika dana tidak cukup, Configuration App akan menampilkan pesan error

## Laporan Kas Kecil

### Laporan Setoran

1. Admin/user memilih menu laporan setoran kas kecil
2. Memilih rentang tanggal laporan
3. Configuration App menghasilkan laporan PDF yang berisi:
    - Daftar setoran dalam rentang waktu tersebut
    - Informasi tentang jumlah yang sudah terpakai dari setiap setoran
    - Total setoran, total terpakai, dan total sisa

### Laporan Pengeluaran

1. Admin/user memilih menu laporan pengeluaran kas kecil
2. Memilih rentang tanggal laporan
3. Configuration App menghasilkan laporan PDF yang berisi:
    - Daftar pengeluaran dalam rentang waktu tersebut
    - Kategorisasi pengeluaran
    - Total pengeluaran per kategori dan total keseluruhan

## Pemantauan Saldo

-   Saldo kas kecil dapat dilihat di dashboard Configuration App
-   Setiap transaksi setoran dan pengeluaran akan langsung memperbarui saldo

## Alur Data

1. **Setoran (PettyCashDeposit)**

    - Pencatatan setoran → Saldo bertambah → Siap digunakan pengeluaran → Status "fully used" jika habis

2. **Pengeluaran (PettyCashExpense)**

    - Pencatatan pengeluaran → Alokasi FIFO → Pengurangan saldo setoran

3. **Alokasi (PettyCashExpenseAllocation)**
    - Mencatat hubungan antara pengeluaran dan setoran yang digunakan
    - Mencatat jumlah dana yang dialokasikan dari setiap setoran
