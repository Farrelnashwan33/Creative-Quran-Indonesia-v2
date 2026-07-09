<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Http;
use App\Models\Surah;
use App\Models\Ayah;

class QuranSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $this->command->info('Fetching all surahs from equran.id...');
        $response = Http::get('https://equran.id/api/v2/surat');
        
        if (!$response->successful()) {
            $this->command->error('Failed to fetch surahs');
            return;
        }

        $surahs = $response->json()['data'] ?? [];

        foreach ($surahs as $surahData) {
            $this->command->info("Processing Surah: {$surahData['namaLatin']}...");
            
            // Insert Surah
            $surah = Surah::updateOrCreate(
                ['nomor' => $surahData['nomor']],
                [
                    'nama' => $surahData['nama'],
                    'nama_latin' => $surahData['namaLatin'],
                    'jumlah_ayat' => $surahData['jumlahAyat'],
                    'tempat_turun' => $surahData['tempatTurun'],
                    'arti' => $surahData['arti'],
                    'deskripsi' => $surahData['deskripsi'],
                    'audio_full' => $surahData['audioFull']['05'] ?? null, // using one of the qori's audio
                ]
            );

            // Fetch detailed surah to get ayahs
            $detailResponse = Http::get("https://equran.id/api/v2/surat/{$surah->nomor}");
            if ($detailResponse->successful()) {
                $detailData = $detailResponse->json()['data'] ?? null;
                if ($detailData && isset($detailData['ayat'])) {
                    $ayahsToInsert = [];
                    foreach ($detailData['ayat'] as $ayat) {
                        $ayahsToInsert[] = [
                            'surah_id' => $surah->id,
                            'nomor_ayat' => $ayat['nomorAyat'],
                            'teks_arab' => $ayat['teksArab'],
                            'teks_latin' => $ayat['teksLatin'],
                            'teks_indonesia' => $ayat['teksIndonesia'],
                            'audio' => $ayat['audio']['05'] ?? null,
                            'created_at' => now(),
                            'updated_at' => now(),
                        ];
                    }
                    // Insert ayahs for this surah
                    Ayah::insert($ayahsToInsert);
                }
            }
        }
        $this->command->info('Quran data seeded successfully!');
    }
}
