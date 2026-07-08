<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Repositories\HighlightRepository;
use Illuminate\Http\Request;
use App\Traits\ApiResponse;

class HighlightController extends Controller
{
    use ApiResponse;

    protected $repository;

    public function __construct(HighlightRepository $repository)
    {
        $this->repository = $repository;
    }

    public function index(Request $request)
    {
        $data = $this->repository->all(['user_id' => $request->user()->id]);
        return $this->successResponse($data, 'Highlights retrieved successfully');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'surah' => 'required|integer',
            'ayah' => 'required|integer',
            'color' => 'required|string',
        ]);
        
        $validated['user_id'] = $request->user()->id;
        $data = $this->repository->create($validated);
        
        return $this->successResponse($data, 'Highlight created successfully', 201);
    }

    public function update(Request $request, $id)
    {
        $highlight = $this->repository->find($id);
        if (!$highlight || $highlight->user_id !== $request->user()->id) {
            return $this->errorResponse('Highlight not found or unauthorized', [], 404);
        }

        $validated = $request->validate([
            'color' => 'required|string',
        ]);
        
        $data = $this->repository->update($id, $validated);
        
        return $this->successResponse($data, 'Highlight updated successfully');
    }

    public function destroy($id, Request $request)
    {
        $highlight = $this->repository->find($id);
        if (!$highlight || $highlight->user_id !== $request->user()->id) {
            return $this->errorResponse('Highlight not found or unauthorized', [], 404);
        }
        
        $this->repository->delete($id);
        return $this->successResponse(null, 'Highlight deleted successfully');
    }
}
