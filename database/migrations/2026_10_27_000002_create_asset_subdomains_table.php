<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('asset_subdomains', function (Blueprint $table) {
            $table->id();
            $table->char('scan_id', 36)->index();
            $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('subdomain', 255)->index();
            $table->string('root_domain', 255)->index();
            $table->string('ip_address', 64)->nullable();
            $table->string('cname', 255)->nullable();
            $table->unsignedSmallInteger('status_http')->nullable();
            $table->boolean('is_alive')->default(false)->index();
            $table->boolean('takeover_risk')->default(false)->index();
            $table->string('takeover_service', 100)->nullable();
            $table->text('takeover_evidence')->nullable();
            $table->boolean('is_interesting')->default(false);
            $table->string('server_header', 150)->nullable();
            $table->string('page_title', 255)->nullable();
            $table->timestamps();

            $table->foreign('scan_id')->references('id')->on('scans')->cascadeOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('asset_subdomains');
    }
};
