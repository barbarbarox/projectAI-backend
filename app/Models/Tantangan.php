<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Tantangan extends Model
{
    protected $table = 'tantangan';

    protected $fillable = [
        'judul',
        'deskripsi',
        'tipe',
        'kategori',
        'bahasa_pemrograman',
        'kode_soal',
        'jawaban_benar',
        'pilihan_jawaban',
        'penjelasan',
        'referensi_cwe',
        'referensi_owasp',
        'poin',
        'tingkat_kesulitan',
        'is_aktif',
    ];

    protected function casts(): array
    {
        return [
            'pilihan_jawaban' => 'array',
            'is_aktif' => 'boolean',
        ];
    }

    public function poinUser(): HasMany
    {
        return $this->hasMany(PoinUser::class);
    }
}
