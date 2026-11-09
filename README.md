<p align="center"><a href="https://laravel.com" target="_blank"><img src="https://raw.githubusercontent.com/laravel/art/master/logo-lockup/5%20SVG/2%20CMYK/1%20Full%20Color/laravel-logolockup-cmyk-red.svg" width="400" alt="Laravel Logo"></a></p>

<p align="center">
<a href="https://github.com/laravel/framework/actions"><img src="https://github.com/laravel/framework/workflows/tests/badge.svg" alt="Build Status"></a>
<a href="https://packagist.org/packages/laravel/framework"><img src="https://img.shields.io/packagist/dt/laravel/framework" alt="Total Downloads"></a>
<a href="https://packagist.org/packages/laravel/framework"><img src="https://img.shields.io/packagist/v/laravel/framework" alt="Latest Stable Version"></a>
<a href="https://packagist.org/packages/laravel/framework"><img src="https://img.shields.io/packagist/l/laravel/framework" alt="License"></a>
</p>

## Role Admin

Semua akun yang mendaftar mendapat role `user`. Semua akun, termasuk admin, hanya dapat melihat dan mengelola task miliknya sendiri. Admin memiliki akses khusus untuk membaca dan membalas feedback pengguna. Di dashboard admin, jumlah online dihitung dari heartbeat dalam lima menit terakhir; ping otomatis menjaga status selama dashboard tetap terbuka.
Sidebar admin menyediakan Dashboard, Feedback Masuk, Request Password dengan jumlah permintaan yang menunggu, dan direktori Pengguna yang hanya menampilkan nama, email, serta status online/offline.

Untuk menjadikan akun sebagai admin, jalankan `php artisan tinker`, lalu ubah alamat email berikut dengan email akun yang sudah terdaftar:

```php
App\Models\User::where('email', 'admin@example.com')->update(['role' => 'admin']);
```

Role tidak dapat dipilih dari formulir pendaftaran.

## Feedback

User yang sudah login dapat mengirim feedback lewat tautan **Kirim Feedback** di dashboard dan melihat balasan admin di halaman yang sama. Admin dapat membuka **Feedback Pengguna** untuk membaca serta membalas masukan.

Setelah mengambil perubahan terbaru, jalankan `php artisan migrate` untuk membuat tabel feedback.

## Request Reset Password

Pengguna dapat memilih **Lupa password?** di halaman login dan mengirim email akun. Permintaan tampil di dashboard dan menu **Request Password** admin. Setelah admin menyetujui, aplikasi mengirim tautan reset satu kali ke email pengguna; password tidak pernah ditampilkan atau dikirim sebagai teks.

Untuk mengirim email sungguhan, atur `APP_URL` ke alamat publik aplikasi serta konfigurasi SMTP pada `.env` (`MAIL_MAILER=smtp`, `MAIL_HOST`, `MAIL_PORT`, `MAIL_USERNAME`, `MAIL_PASSWORD`, `MAIL_SCHEME`, dan `MAIL_FROM_ADDRESS`). `MAIL_MAILER=log` hanya mencatat email ke log, sehingga tombol persetujuan akan meminta konfigurasi email sebelum menandai tautan terkirim. Tautan reset berlaku selama 60 menit sesuai konfigurasi broker password.

Setelah mengambil perubahan terbaru, jalankan `php artisan migrate` untuk membuat tabel request password.

## About Laravel

Laravel is a web application framework with expressive, elegant syntax. We believe development must be an enjoyable and creative experience to be truly fulfilling. Laravel takes the pain out of development by easing common tasks used in many web projects, such as:

- [Simple, fast routing engine](https://laravel.com/docs/routing).
- [Powerful dependency injection container](https://laravel.com/docs/container).
- Multiple back-ends for [session](https://laravel.com/docs/session) and [cache](https://laravel.com/docs/cache) storage.
- Expressive, intuitive [database ORM](https://laravel.com/docs/eloquent).
- Database agnostic [schema migrations](https://laravel.com/docs/migrations).
- [Robust background job processing](https://laravel.com/docs/queues).
- [Real-time event broadcasting](https://laravel.com/docs/broadcasting).

Laravel is accessible, powerful, and provides tools required for large, robust applications.

## Learning Laravel

Laravel has the most extensive and thorough [documentation](https://laravel.com/docs) and video tutorial library of all modern web application frameworks, making it a breeze to get started with the framework. You can also check out [Laravel Learn](https://laravel.com/learn), where you will be guided through building a modern Laravel application.

If you don't feel like reading, [Laracasts](https://laracasts.com) can help. Laracasts contains thousands of video tutorials on a range of topics including Laravel, modern PHP, unit testing, and JavaScript. Boost your skills by digging into our comprehensive video library.

## Laravel Sponsors

We would like to extend our thanks to the following sponsors for funding Laravel development. If you are interested in becoming a sponsor, please visit the [Laravel Partners program](https://partners.laravel.com).

### Premium Partners

- **[Vehikl](https://vehikl.com)**
- **[Tighten Co.](https://tighten.co)**
- **[Kirschbaum Development Group](https://kirschbaumdevelopment.com)**
- **[64 Robots](https://64robots.com)**
- **[Curotec](https://www.curotec.com/services/technologies/laravel)**
- **[DevSquad](https://devsquad.com/hire-laravel-developers)**
- **[Redberry](https://redberry.international/laravel-development)**
- **[Active Logic](https://activelogic.com)**

## Contributing

Thank you for considering contributing to the Laravel framework! The contribution guide can be found in the [Laravel documentation](https://laravel.com/docs/contributions).

## Code of Conduct

In order to ensure that the Laravel community is welcoming to all, please review and abide by the [Code of Conduct](https://laravel.com/docs/contributions#code-of-conduct).

## Security Vulnerabilities

If you discover a security vulnerability within Laravel, please send an e-mail to Taylor Otwell via [taylor@laravel.com](mailto:taylor@laravel.com). All security vulnerabilities will be promptly addressed.

## License

The Laravel framework is open-sourced software licensed under the [MIT license](https://opensource.org/licenses/MIT).
