# HadirKantor

Website absensi karyawan berbasis Laravel, MySQL, Blade, dan Tailwind CSS. Fitur mencakup login username dan kata sandi, absensi berbasis radius GPS, pengajuan perizinan karyawan, serta area administrasi untuk mengatur kantor, jadwal, akun karyawan, dan role pengguna.

## Persiapan lokal

Pastikan PHP 8.3+ dengan ekstensi `pdo_mysql`, Composer, Node.js/npm, dan MySQL tersedia. Dari folder `laravel-app`:

```powershell
Copy-Item .env.example .env
composer install
npm.cmd install
```

Buat database MySQL kosong (default: `hadir_kantor`), lalu sesuaikan `DB_*` pada `.env`. Superadmin dapat menambah beberapa lokasi kerja pada **Pengaturan kantor** dengan koordinat, radius GPS, dan jadwal masing-masing. Karyawan wajib memilih lokasi saat absensi masuk dan mengajukan izin; absensi pulang memakai lokasi absensi masuk. Lokasi dengan riwayat absensi atau izin tidak dapat dihapus.

```powershell
php artisan key:generate
php artisan migrate --seed
npm.cmd run build
```

Herd melayani aplikasi melalui Nginx. Buka URL yang ditampilkan di Herd Dashboard (untuk checkout ini `https://hadir-kantor-master.test`). Seeder membuat satu superadmin dengan username `superadmin` dan kata sandi `password123`. Email opsional dan tidak digunakan untuk login. Ganti kata sandi default sebelum aplikasi dipublikasikan. Untuk mengosongkan seluruh database dan membuat ulang akun ini, gunakan `php artisan migrate:fresh --seed`; perintah tersebut menghapus semua tabel dan data terlebih dahulu.

Untuk pengembangan aset gunakan `npm.cmd run dev` di terminal terpisah. Geolocation browser memerlukan izin pengguna dan HTTPS selain localhost.

## Akses dari perangkat lain di jaringan lokal dengan Herd

Herd menjalankan situs melalui Nginx. Domain `.test` hanya dikenali di komputer server, jadi untuk membuka situs lewat IP LAN, tambahkan reverse proxy Nginx Herd satu kali. Contoh konfigurasi untuk situs ini tersedia di [`herd-lan.conf.example`](herd-lan.conf.example).

1. Cari alamat IPv4 komputer server dengan `ipconfig`, lalu gunakan adapter Wi-Fi/Ethernet yang aktif.
2. Buka `%USERPROFILE%\.config\herd\config\nginx\mysites.conf` di editor teks. Tambahkan isi file contoh pada akhir file. Jangan menghapus blok situs lain yang sudah ada.
3. Dari Herd Dashboard atau menu ikon Herd di system tray, pilih **Restart All Services**.
4. Buat aturan inbound Windows Firewall: buka **Windows Defender Firewall with Advanced Security → Inbound Rules → New Rule**, pilih **Port → TCP → Specific local ports: 5002 → Allow the connection → Private**, lalu beri nama `HadirKantor LAN`.
5. Perangkat yang tersambung ke jaringan lokal yang sama dapat membuka `http://IP-KOMPUTER-SERVER:5002`, misalnya `http://192.168.1.20:5002`.

Jika `nginx.conf` Herd belum memuat `include mysites.conf;`, tambahkan include tersebut pada konfigurasi Nginx Herd terlebih dahulu. Jangan meneruskan port ke internet atau mengizinkan akses pada profil firewall **Public**. Akses HTTP lewat IP dapat menampilkan aplikasi, tetapi absensi GPS memerlukan HTTPS dengan sertifikat yang dipercaya perangkat; browser membatasi geolocation pada koneksi HTTP ke IP lokal.

## Akses publik sementara dari laptop dengan Cloudflare

`artisan serve` menjalankan web server lokal. Untuk membuka akses internet melalui Cloudflare Quick Tunnel gratis, pasang `cloudflared` Windows dari [halaman unduhan resmi](https://developers.cloudflare.com/cloudflare-one/networks/connectors/cloudflare-tunnel/downloads/). Quick Tunnel tidak memerlukan token, akun Cloudflare, atau domain. Tutup dan buka kembali PowerShell setelah instalasi, lalu pastikan `cloudflared --version` berhasil.

Buka dua terminal PowerShell dari folder `laravel-app`. Pada terminal pertama jalankan Artisan dengan cookie HTTPS:

```powershell
$env:SESSION_SECURE_COOKIE = 'true'
php artisan serve --host=127.0.0.1 --port=8000
```

Pada terminal kedua jalankan:

```powershell
cloudflared tunnel --url http://127.0.0.1:8000
```

Buka alamat `https://....trycloudflare.com` yang ditampilkan dan bagikan hanya kepada pengguna yang berhak. Quick Tunnel bersifat sementara; URL berlaku selama proses `cloudflared`, Artisan, dan koneksi laptop aktif. Sleep atau menutup terminal menghentikan akses. Ini cocok untuk demo/uji coba, bukan hosting produksi berkelanjutan. Gunakan akun uji dan kata sandi kuat; jangan masukkan data karyawan nyata ke tunnel publik tanpa pengamanan dan persetujuan perusahaan.

## Persiapan penggunaan produksi

Jangan mengekspos konfigurasi lokal atau tunnel sementara sebagai layanan produksi. Sebelum deployment, atur `APP_ENV=production`, `APP_DEBUG=false`, `APP_URL` ke domain HTTPS tetap, dan `SESSION_SECURE_COOKIE=true`. Daftar proxy tepercaya di `bootstrap/app.php` hanya loopback untuk Herd/cloudflared lokal; saat hosting, tambahkan hanya alamat proxy reverse server yang benar-benar digunakan dan jangan gunakan `*`. Buat `APP_KEY` unik, simpan rahasia di environment server, dan jangan menyalin `.env` lokal ke server.

Jalankan `composer install --no-dev --optimize-autoloader`, `npm.cmd ci`, `npm.cmd run build`, `php artisan migrate --force`, `php artisan storage:link`, lalu `php artisan optimize` saat deployment. Pastikan PHP memiliki ekstensi `pdo_mysql`, `zip`, dan `xml`, database dibackup terjadwal, serta HTTPS aktif. Jangan jalankan `db:seed` di database produksi karena seeder membuat akun demo dengan kata sandi yang diketahui.

Login tetap tidak diberi pembatasan percobaan sesuai konfigurasi aplikasi yang diminta. Endpoint polling notifikasi sebelum login dibatasi 30 permintaan per menit dan hanya mengembalikan status ada/tidaknya pengajuan, tanpa ID catatan.

## Alur perizinan

Karyawan mengajukan cuti, izin, izin sakit, libur pengganti, cuti khusus (menikah, melahirkan, atau orang tua meninggal), atau dinas luar melalui menu **Pengajuan perizinan**. Pengajuan mencatat tanggal, alasan, status, dan lampiran opsional PDF/JPG/PNG hingga 5 MB. Admin dan superadmin meninjau atau menolak pengajuan pada menu **Persetujuan perizinan**; lampiran hanya dapat diunduh oleh pemohon dan pengelola.

Pengajuan yang tanggalnya bertabrakan dengan pengajuan menunggu atau disetujui akan ditolak. Migrasi `leave_requests` dijalankan bersama `php artisan migrate`.

## Perintah pemeriksaan

```powershell
php artisan test
vendor/bin/pint --test
npm.cmd run build
```

Data inti berada pada `app/Models`, validasi dan aksi HTTP pada `app/Http/Controllers`, skema database pada `database/migrations`, dan antarmuka Blade pada `resources/views`.
