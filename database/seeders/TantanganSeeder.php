<?php

namespace Database\Seeders;

use App\Models\Tantangan;
use Illuminate\Database\Seeder;

class TantanganSeeder extends Seeder
{
    public function run(): void
    {
        $soal = [
            [
                'judul' => 'SQL Injection Tersembunyi',
                'deskripsi' => 'Seorang analis keamanan menemukan potongan kode berikut di dalam sebuah aplikasi web e-commerce lama: `$query = "SELECT * FROM users WHERE username = \'" . $_POST["user"] . "\' AND password = \'" . md5($_POST["pass"]) . "\'";`. Apa cara paling efektif untuk mencegah serangan SQL Injection pada kode tersebut?',
                'tipe' => 'pilihan_ganda',
                'kategori' => 'Keamanan Siber',
                'bahasa_pemrograman' => 'php',
                'kode_soal' => '$query = "SELECT * FROM users WHERE username = \'" . $_POST["user"] . "\' AND password = \'" . md5($_POST["pass"]) . "\'";',
                'pilihan_jawaban' => [
                    'A' => 'Menggunakan fungsi addslashes() pada input user',
                    'B' => 'Menggunakan PDO dengan Prepared Statements dan Parameterized Queries',
                    'C' => 'Mengubah metode POST menjadi GET',
                    'D' => 'Menambahkan filter htmlspecialchars() pada semua input'
                ],
                'jawaban_benar' => 'B',
                'penjelasan' => 'Prepared statements memisahkan query dari data. Database akan mengeksekusi struktur query terlebih dahulu sebelum mengikat (bind) parameter input, sehingga meskipun input berisi sintaks SQL berbahaya, database akan menganggapnya sebagai string biasa, bukan perintah executable.',
                'poin' => 15,
                'tingkat_kesulitan' => 'sedang',
                'is_aktif' => true,
            ],
            [
                'judul' => 'Bahaya Cross-Site Scripting (XSS)',
                'deskripsi' => 'Anda sedang mengaudit aplikasi blog sederhana. Ketika pengguna memberikan komentar, teks komentar langsung ditampilkan di halaman web menggunakan tag PHP seperti berikut: `echo $_POST["komentar"];`. Serangan apa yang sangat mungkin terjadi?',
                'tipe' => 'pilihan_ganda',
                'kategori' => 'Keamanan Siber',
                'bahasa_pemrograman' => 'php',
                'kode_soal' => 'echo $_POST["komentar"];',
                'pilihan_jawaban' => [
                    'A' => 'SQL Injection',
                    'B' => 'Cross-Site Request Forgery (CSRF)',
                    'C' => 'Cross-Site Scripting (XSS) Reflected',
                    'D' => 'Remote Code Execution (RCE)'
                ],
                'jawaban_benar' => 'C',
                'penjelasan' => 'Kode tersebut mengambil input langsung dari user dan menampilkannya kembali ke browser tanpa validasi atau encoding (sanitasi). Hal ini memungkinkan penyerang memasukkan script JavaScript berbahaya (seperti `<script>alert(1)</script>`) yang akan dieksekusi oleh browser korban.',
                'poin' => 10,
                'tingkat_kesulitan' => 'mudah',
                'is_aktif' => true,
            ],
            [
                'judul' => 'File Upload Vulnerability',
                'deskripsi' => 'Sebuah fitur upload foto profil hanya memeriksa ekstensi file menggunakan kode JavaScript di sisi klien (frontend). Jika file berakhiran `.jpg` atau `.png`, file akan dikirim ke server dan langsung disimpan. Mengapa pendekatan ini berbahaya?',
                'tipe' => 'pilihan_ganda',
                'kategori' => 'Keamanan Siber',
                'bahasa_pemrograman' => 'javascript',
                'kode_soal' => null,
                'pilihan_jawaban' => [
                    'A' => 'Karena JavaScript bisa dimatikan oleh pengguna di browser.',
                    'B' => 'Karena penyerang bisa mencegat permintaan (intercept request) menggunakan alat seperti Burp Suite dan mengubah ekstensi file sebelum sampai ke server.',
                    'C' => 'Karena klien tidak bisa mendeteksi virus di dalam file.',
                    'D' => 'Karena server akan menolak file gambar yang ukurannya terlalu besar.'
                ],
                'jawaban_benar' => 'B',
                'penjelasan' => 'Validasi di sisi klien (frontend) sangat mudah dilewati. Penyerang dapat menggunakan proxy (seperti Burp Suite) untuk mengelabui validasi frontend, mengubah nama file berbahaya (misalnya shell.php) seolah-olah berakhiran .jpg, dan mengunggahnya ke server. Validasi mutlak harus dilakukan di sisi server (backend).',
                'poin' => 20,
                'tingkat_kesulitan' => 'sulit',
                'is_aktif' => true,
            ],
            [
                'judul' => 'Insecure Direct Object Reference (IDOR)',
                'deskripsi' => 'Seorang pengguna masuk ke aplikasi perbankan dan melihat URL berikut saat mengecek saldo: `https://bank.com/akun?id=1055`. Jika pengguna tersebut mengubah angka 1055 menjadi 1056 dan tiba-tiba dapat melihat saldo pengguna lain, kerentanan apa yang terjadi?',
                'tipe' => 'pilihan_ganda',
                'kategori' => 'Keamanan Siber',
                'bahasa_pemrograman' => 'umum',
                'kode_soal' => null,
                'pilihan_jawaban' => [
                    'A' => 'Insecure Direct Object Reference (IDOR)',
                    'B' => 'Cross-Site Scripting (XSS)',
                    'C' => 'Man-in-the-Middle (MitM)',
                    'D' => 'Brute Force Attack'
                ],
                'jawaban_benar' => 'A',
                'penjelasan' => 'IDOR terjadi ketika aplikasi menyediakan akses langsung ke objek berdasarkan input dari pengguna tanpa melakukan pemeriksaan otorisasi. Dalam kasus ini, aplikasi tidak mengecek apakah user yang sedang login memiliki hak untuk melihat data dengan ID 1056.',
                'poin' => 15,
                'tingkat_kesulitan' => 'sedang',
                'is_aktif' => true,
            ]
        ];

        foreach ($soal as $s) {
            Tantangan::create($s);
        }
    }
}
