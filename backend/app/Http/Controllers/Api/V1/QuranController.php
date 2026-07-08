<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Interfaces\QuranServiceInterface;
use Illuminate\Http\Request;
use App\Traits\ApiResponse;

class QuranController extends Controller
{
    use ApiResponse;

    protected $quranService;

    public function __construct(QuranServiceInterface $quranService)
    {
        $this->quranService = $quranService;
    }

    public function getSurahs()
    {
        $data = $this->quranService->getSurahs();
        return $this->successResponse($data, 'Surahs retrieved successfully');
    }

    public function getSurah($id)
    {
        $data = $this->quranService->getSurah($id);
        if (!$data) return $this->errorResponse('Surah not found', [], 404);
        return $this->successResponse($data, 'Surah retrieved successfully');
    }

    public function getJuzs()
    {
        $data = $this->quranService->getJuzs();
        return $this->successResponse($data, 'Juzs retrieved successfully');
    }

    public function getJuz($id)
    {
        $data = $this->quranService->getJuz($id);
        if (!$data) return $this->errorResponse('Juz not found', [], 404);
        return $this->successResponse($data, 'Juz retrieved successfully');
    }

    public function getPage($page)
    {
        $data = $this->quranService->getPage($page);
        return $this->successResponse($data, 'Page retrieved successfully');
    }

    public function search(Request $request)
    {
        $query = $request->query('q', '');
        $data = $this->quranService->search($query);
        return $this->successResponse($data, 'Search results retrieved successfully');
    }

    public function getRandomAyah()
    {
        $data = $this->quranService->getRandomAyah();
        return $this->successResponse($data, 'Random ayah retrieved successfully');
    }

    public function getTafsir($surah)
    {
        $data = $this->quranService->getTafsir($surah);
        if (!$data) return $this->errorResponse('Tafsir not found', [], 404);
        return $this->successResponse($data, 'Tafsir retrieved successfully');
    }

    public function getAyah($surah, $ayah)
    {
        $data = $this->quranService->getAyah($surah, $ayah);
        if (!$data) return $this->errorResponse('Ayah not found', [], 404);
        return $this->successResponse($data, 'Ayah retrieved successfully');
    }

    public function getAudio($surah)
    {
        $data = $this->quranService->getAudio($surah);
        if (!$data) return $this->errorResponse('Audio not found', [], 404);
        return $this->successResponse($data, 'Audio retrieved successfully');
    }

    public function getReciters()
    {
        $data = $this->quranService->getReciters();
        return $this->successResponse($data, 'Reciters retrieved successfully');
    }
}
