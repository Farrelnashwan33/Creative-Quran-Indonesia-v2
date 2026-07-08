<?php

namespace App\Interfaces;

interface QuranServiceInterface
{
    public function getSurahs();
    public function getSurah(int $id);
    public function getJuzs();
    public function getJuz(int $id);
    public function getPage(int $page);
    public function search(string $query);
    public function getRandomAyah();
    public function getTafsir(int $surahId);
    public function getAyah(int $surahId, int $ayahId);
    public function getAudio(int $surahId);
    public function getReciters();
}
