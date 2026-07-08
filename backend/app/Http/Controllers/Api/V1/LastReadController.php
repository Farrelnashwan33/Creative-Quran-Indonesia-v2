<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Repositories\LastReadRepository;
use Illuminate\Http\Request;
use App\Traits\ApiResponse;

class LastReadController extends Controller
{
    use ApiResponse;

    protected $repository;

    public function __construct(LastReadRepository $repository)
    {
        $this->repository = $repository;
    }

    public function index(Request $request)
    {
        $data = $this->repository->all(['user_id' => $request->user()->id]);
        return $this->successResponse($data, 'Last reads retrieved successfully');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'surah' => 'required|integer',
            'ayah' => 'required|integer',
            'page' => 'nullable|integer',
        ]);
        
        $validated['user_id'] = $request->user()->id;
        $data = $this->repository->create($validated);
        
        return $this->successResponse($data, 'Last read created successfully', 201);
    }

    public function update(Request $request, $id)
    {
        $lastRead = $this->repository->find($id);
        if (!$lastRead || $lastRead->user_id !== $request->user()->id) {
            return $this->errorResponse('Last read not found or unauthorized', [], 404);
        }

        $validated = $request->validate([
            'surah' => 'required|integer',
            'ayah' => 'required|integer',
            'page' => 'nullable|integer',
        ]);
        
        $data = $this->repository->update($id, $validated);
        
        return $this->successResponse($data, 'Last read updated successfully');
    }

    public function destroy($id, Request $request)
    {
        $lastRead = $this->repository->find($id);
        if (!$lastRead || $lastRead->user_id !== $request->user()->id) {
            return $this->errorResponse('Last read not found or unauthorized', [], 404);
        }
        
        $this->repository->delete($id);
        return $this->successResponse(null, 'Last read deleted successfully');
    }
}
