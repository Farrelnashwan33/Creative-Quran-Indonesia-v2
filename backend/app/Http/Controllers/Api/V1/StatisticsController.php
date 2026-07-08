<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\ReadingStatistic;
use App\Models\Bookmark;
use App\Models\DailyTarget;
use App\Traits\ApiResponse;

class StatisticsController extends Controller
{
    use ApiResponse;

    public function dashboard(Request $request)
    {
        $userId = $request->user()->id;

        $statistics = ReadingStatistic::firstOrCreate(
            ['user_id' => $userId],
            ['streak' => 0, 'total_minutes' => 0, 'total_ayah' => 0, 'total_pages' => 0, 'total_juz' => 0]
        );

        $lastBookmark = Bookmark::where('user_id', $userId)->latest()->first();
        
        $dailyTarget = DailyTarget::firstOrCreate(
            ['user_id' => $userId],
            ['target_minutes' => 0, 'target_pages' => 0]
        );

        $data = [
            'streak' => $statistics->streak,
            'total_read_minutes' => $statistics->total_minutes,
            'juz_completed' => $statistics->total_juz,
            'surah_completed' => 0, // Calculate this dynamically if needed, or track it
            'last_bookmark' => $lastBookmark,
            'daily_target' => $dailyTarget,
        ];

        return $this->successResponse($data, 'Dashboard statistics retrieved successfully');
    }

    public function statistics(Request $request)
    {
        $userId = $request->user()->id;
        $statistics = ReadingStatistic::where('user_id', $userId)->first();
        
        return $this->successResponse($statistics, 'Reading statistics retrieved successfully');
    }
}
