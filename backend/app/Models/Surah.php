<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Surah extends Model
{
    protected $fillable = [
        'nomor',
        'nama',
        'nama_latin',
        'jumlah_ayat',
        'tempat_turun',
        'arti',
        'deskripsi',
        'audio_full'
    ];

    public function ayahs()
    {
        return $this->hasMany(Ayah::class);
    }
}
