<?php

namespace App\Services;

use App\Interfaces\QuranServiceInterface;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Cache;

class QuranService implements QuranServiceInterface
{
    protected $ttl = 86400; // Cache for 24 hours

    public function getSurahs()
    {
        return Cache::remember('quran_surahs', $this->ttl, function () {
            $response = Http::get('https://equran.id/api/v2/surat');
            return $response->json()['data'] ?? [];
        });
    }

    public function getSurah(int $id)
    {
        return Cache::remember("quran_surah_{$id}", $this->ttl, function () use ($id) {
            $response = Http::get("https://equran.id/api/v2/surat/{$id}");
            return $response->json()['data'] ?? null;
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
        // Dynamic search, cache for 1 hour
        return Cache::remember("quran_search_{$query}", 3600, function () use ($query) {
            $response = Http::get('https://api.quran.com/api/v4/search', [
                'q' => $query,
                'size' => 20,
                'language' => 'id'
            ]);
            return $response->json()['search'] ?? [];
        });
    }

    public function getRandomAyah()
    {
        // Don't cache random, or cache for a very short time
        $randomVerseKey = 'quran_random_ayah';
        
        $response = Http::get('https://api.quran.com/api/v4/verses/random', [
            'language' => 'id',
            'translations' => '33' // Indonesian
        ]);
        return $response->json()['verse'] ?? null;
    }

    public function getTafsir(int $surahId)
    {
        return Cache::remember("quran_tafsir_{$surahId}", $this->ttl, function () use ($surahId) {
            $response = Http::get("https://equran.id/api/v2/tafsir/{$surahId}");
            return $response->json()['data'] ?? null;
        });
    }

    public function getAyah(int $surahId, int $ayahId)
    {
        return Cache::remember("quran_ayah_{$surahId}_{$ayahId}", $this->ttl, function () use ($surahId, $ayahId) {
            $surah = $this->getSurah($surahId);
            if ($surah && isset($surah['ayat'])) {
                foreach ($surah['ayat'] as $ayat) {
                    if ($ayat['nomorAyat'] == $ayahId) {
                        return $ayat;
                    }
                }
            }
            return null;
        });
    }

    public function getAudio(int $surahId)
    {
        return Cache::remember("quran_audio_{$surahId}", $this->ttl, function () use ($surahId) {
            $surah = $this->getSurah($surahId);
            return $surah['audioFull'] ?? null;
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
