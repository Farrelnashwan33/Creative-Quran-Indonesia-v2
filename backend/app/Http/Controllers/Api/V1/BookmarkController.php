<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Repositories\BookmarkRepository;
use Illuminate\Http\Request;
use App\Traits\ApiResponse;

class BookmarkController extends Controller
{
    use ApiResponse;

    protected $repository;

    public function __construct(BookmarkRepository $repository)
    {
        $this->repository = $repository;
    }

    public function index(Request $request)
    {
        $data = $this->repository->all(['user_id' => $request->user()->id]);
        return $this->successResponse($data, 'Bookmarks retrieved successfully');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'surah' => 'required|integer',
            'ayah' => 'required|integer',
        ]);
        
        $validated['user_id'] = $request->user()->id;
        $data = $this->repository->create($validated);
        
        return $this->successResponse($data, 'Bookmark created successfully', 201);
    }

    public function destroy($id, Request $request)
    {
        $bookmark = $this->repository->find($id);
        if (!$bookmark || $bookmark->user_id !== $request->user()->id) {
            return $this->errorResponse('Bookmark not found or unauthorized', [], 404);
        }
        
        $this->repository->delete($id);
        return $this->successResponse(null, 'Bookmark deleted successfully');
    }
}
