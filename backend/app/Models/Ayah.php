<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Ayah extends Model
{
    protected $fillable = [
        'surah_id',
        'nomor_ayat',
        'teks_arab',
        'teks_latin',
        'teks_indonesia',
        'audio'
    ];

    public function surah()
    {
        return $this->belongsTo(Surah::class);
    }
}
