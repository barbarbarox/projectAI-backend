<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        DB::statement('ALTER TABLE temuan DROP CONSTRAINT IF EXISTS temuan_tipe_check;');
        DB::statement('ALTER TABLE temuan DROP CONSTRAINT IF EXISTS temuan_tingkat_keparahan_check;');
        DB::statement('ALTER TABLE temuan DROP CONSTRAINT IF EXISTS temuan_estimasi_usaha_check;');
        DB::statement('ALTER TABLE temuan DROP CONSTRAINT IF EXISTS temuan_tingkat_kepercayaan_check;');
        DB::statement('ALTER TABLE simulasi_serangan DROP CONSTRAINT IF EXISTS simulasi_serangan_profil_penyerang_check;');

        DB::statement('ALTER TABLE temuan ALTER COLUMN tipe TYPE VARCHAR(100);');
        DB::statement('ALTER TABLE temuan ALTER COLUMN tingkat_keparahan TYPE VARCHAR(50);');
        DB::statement('ALTER TABLE temuan ALTER COLUMN estimasi_usaha TYPE VARCHAR(50);');
        DB::statement('ALTER TABLE temuan ALTER COLUMN tingkat_kepercayaan TYPE VARCHAR(50);');
        DB::statement('ALTER TABLE simulasi_serangan ALTER COLUMN profil_penyerang TYPE VARCHAR(100);');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // No-op for safety
    }
};
