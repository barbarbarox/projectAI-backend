<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class DemoUserSeeder extends Seeder
{
    public function run(): void
    {
        $userId = DB::table('users')->insertGetId([
            'name'              => 'Demo RedSim',
            'nama_lengkap'      => 'Demo Kelompok 8',
            'email'             => 'demo@redsim.test',
            'password'          => Hash::make('Demo12345!'),
            'tier'              => 'pro',
            'is_verified'       => true,
            'email_verified_at' => now(),
            'created_at'        => now(),
            'updated_at'        => now(),
        ]);

        // Seeder: beberapa scan sample
        $scanIds = [];
        foreach (['https://redsim.my.id', 'https://google.com', 'https://github.com'] as $url) {
            $id = \Illuminate\Support\Str::uuid()->toString();
            DB::table('scans')->insert([
                'id'              => $id,
                'user_id'         => $userId,
                'tipe_scan'       => 'url',
                'target'          => $url,
                'status'          => 'selesai',
                'skor_keamanan'   => rand(55, 90),
                'verdict'         => 'perhatian',
                'created_at'      => now()->subHours(rand(1, 72)),
                'updated_at'      => now(),
            ]);
            $scanIds[] = $id;
        }

        // Sample insiden
        DB::table('insiden')->insert([
            'user_id'           => $userId,
            'nomor_insiden'     => 'INC-' . date('Ymd') . '-001',
            'judul'             => 'Contoh Insiden: SQL Injection terdeteksi',
            'deskripsi'         => 'Ditemukan potensi SQL Injection pada endpoint login.',
            'tingkat_keparahan' => 'tinggi',
            'status'            => 'open',
            'target'            => 'https://redsim.my.id/login',
            'created_at'        => now(),
            'updated_at'        => now(),
        ]);

        // Sample tantangan (jika belum ada)
        if (DB::table('tantangan')->count() === 0) {
            DB::table('tantangan')->insert([
                ['judul'=>'Temukan XSS','tipe'=>'temukan_bug','kategori'=>'Web Security','poin'=>10,'tingkat_kesulitan'=>'mudah','created_at'=>now(),'updated_at'=>now()],
                ['judul'=>'Perbaiki CSRF','tipe'=>'perbaiki_kode','kategori'=>'Web Security','poin'=>20,'tingkat_kesulitan'=>'sedang','created_at'=>now(),'updated_at'=>now()],
            ]);
        }

        $this->command->info("✅ Demo user dibuat: demo@redsim.test / Demo12345!");
        $this->command->info("   ID: $userId");
    }
}
