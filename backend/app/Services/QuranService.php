<?php

namespace App\Services;

use App\Interfaces\QuranServiceInterface;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Cache;
use App\Models\Surah;
use App\Models\Ayah;

class QuranService implements QuranServiceInterface
{
    protected $ttl = 86400; // Cache for 24 hours

    public function getSurahs()
    {
        return Cache::remember('quran_surahs_db_v3', $this->ttl, function () {
            return Surah::orderBy('nomor')->get()->map(function ($surah) {
                return [
                    'nomor' => $surah->nomor,
                    'nama' => $surah->nama,
                    'namaLatin' => $surah->nama_latin,
                    'jumlahAyat' => $surah->jumlah_ayat,
                    'tempatTurun' => $surah->tempat_turun,
                    'arti' => $surah->arti,
                    'deskripsi' => $surah->deskripsi,
                    'audioFull' => is_string($surah->audio_full) ? json_decode($surah->audio_full, true) : $surah->audio_full
                ];
            })->toArray();
        });
    }

    public function getSurah(int $id)
    {
        return Cache::remember("quran_surah_db_v3_{$id}", $this->ttl, function () use ($id) {
            $surah = Surah::where('nomor', $id)->with('ayahs')->first();
            
            if (!$surah) {
                return null;
            }
            
            $data = [
                'nomor' => $surah->nomor,
                'nama' => $surah->nama,
                'namaLatin' => $surah->nama_latin,
                'jumlahAyat' => $surah->jumlah_ayat,
                'tempatTurun' => $surah->tempat_turun,
                'arti' => $surah->arti,
                'deskripsi' => $surah->deskripsi,
                'audioFull' => is_string($surah->audio_full) ? json_decode($surah->audio_full, true) : $surah->audio_full,
            ];
            
            $ayat = [];
            foreach ($surah->ayahs as $ayah) {
                $ayat[] = [
                    'nomorAyat' => $ayah->nomor_ayat,
                    'teksArab' => $ayah->teks_arab,
                    'teksLatin' => $ayah->teks_latin,
                    'teksIndonesia' => $ayah->teks_indonesia,
                    'audio' => is_string($ayah->audio) ? json_decode($ayah->audio, true) : $ayah->audio,
                ];
            }
            $data['ayat'] = $ayat;
            $data['suratSebelumnya'] = false;
            $data['suratSelanjutnya'] = false;
            
            return $data;
        });
    }

    public function getJuzs()
    {
        // Mocking juzs data since equran v2 doesn't have a direct juz list endpoint
        // Normally we'd fetch from another API or DB
        return Cache::remember('quran_juzs', $this->ttl, function () {
            $response = Http::get('https://api.quran.com/api/v4/juzs');
            return $response->json()['juzs'] ?? [];
        });
    }

    public function getJuz(int $id)
    {
        return Cache::remember("quran_juz_{$id}", $this->ttl, function () use ($id) {
            $response = Http::get("https://api.quran.com/api/v4/juzs/{$id}");
            return $response->json()['juz'] ?? null;
        });
    }

    public function getPage(int $page)
    {
        return Cache::remember("quran_page_{$page}", $this->ttl, function () use ($page) {
            $response = Http::get("https://api.quran.com/api/v4/quran/verses/indopak", ['page_number' => $page]);
            return $response->json()['verses'] ?? [];
        });
    }

    public function search(string $query)
    {
        // Dynamic search in Database
        return Cache::remember("quran_search_db_{$query}", 3600, function () use ($query) {
            return Ayah::with('surah')
                ->where('teks_indonesia', 'like', "%{$query}%")
                ->orWhere('teks_latin', 'like', "%{$query}%")
                ->orWhere('teks_arab', 'like', "%{$query}%")
                ->take(50)
                ->get();
        });
    }

    public function getRandomAyah()
    {
        // Random from Database
        return Ayah::with('surah')->inRandomOrder()->first();
    }

    public function getTafsir(int $surahId)
    {
        // We haven't migrated tafsir, keep using external API for now
        return Cache::remember("quran_tafsir_{$surahId}", $this->ttl, function () use ($surahId) {
            $response = Http::get("https://equran.id/api/v2/tafsir/{$surahId}");
            return $response->json()['data'] ?? null;
        });
    }

    public function getAyah(int $surahId, int $ayahId)
    {
        return Cache::remember("quran_ayah_db_{$surahId}_{$ayahId}", $this->ttl, function () use ($surahId, $ayahId) {
            $surah = Surah::where('nomor', $surahId)->first();
            if (!$surah) return null;
            
            return Ayah::where('surah_id', $surah->id)
                ->where('nomor_ayat', $ayahId)
                ->first();
        });
    }

    public function getAudio(int $surahId)
    {
        return Cache::remember("quran_audio_db_{$surahId}", $this->ttl, function () use ($surahId) {
            $surah = Surah::where('nomor', $surahId)->first();
            return $surah ? $surah->audio_full : null;
        });
    }

    public function getReciters()
    {
        return Cache::remember('quran_reciters', $this->ttl, function () {
            $response = Http::get('https://api.quran.com/api/v4/resources/recitations');
            return $response->json()['recitations'] ?? [];
        });
    }
}
