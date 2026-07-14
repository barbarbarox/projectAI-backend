<?php

namespace App\Console\Commands;

use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Schema;

class ImportDbFromJson extends Command
{
    protected $signature = 'db:import-json';
    protected $description = 'Import Supabase DB from JSON into current Database (MySQL)';

    public function handle()
    {
        $path = storage_path('app/db_backup.json');

        if (!File::exists($path)) {
            $this->error("Backup file not found at: {$path}");
            return;
        }

        $this->info("Loading data from: {$path}");
        $exportData = json_decode(File::get($path), true);

        // Turn off foreign key checks for MySQL
        Schema::disableForeignKeyConstraints();

        foreach ($exportData as $table => $rows) {
            $this->info("Importing table: {$table} (" . count($rows) . " rows)");
            if (empty($rows)) {
                continue;
            }

            try {
                // Wipe existing data
                DB::table($table)->truncate();

                // Chunk inserts to avoid memory/query size limits
                $chunks = array_chunk($rows, 100);
                foreach ($chunks as $chunk) {
                    // Convert JSON string formats back if necessary, but DB::table()->insert usually handles it
                    DB::table($table)->insert($chunk);
                }
            } catch (\Exception $e) {
                $this->warn("Error importing table {$table}: " . $e->getMessage());
            }
        }

        Schema::enableForeignKeyConstraints();

        $this->info("Database imported successfully!");
    }
}
