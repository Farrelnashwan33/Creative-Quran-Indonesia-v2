<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Repositories\NoteRepository;
use Illuminate\Http\Request;
use App\Traits\ApiResponse;

class NoteController extends Controller
{
    use ApiResponse;

    protected $repository;

    public function __construct(NoteRepository $repository)
    {
        $this->repository = $repository;
    }

    public function index(Request $request)
    {
        $data = $this->repository->all(['user_id' => $request->user()->id]);
        return $this->successResponse($data, 'Notes retrieved successfully');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'surah' => 'required|integer',
            'ayah' => 'required|integer',
            'content' => 'required|string',
        ]);
        
        $validated['user_id'] = $request->user()->id;
        $data = $this->repository->create($validated);
        
        return $this->successResponse($data, 'Note created successfully', 201);
    }

    public function update(Request $request, $id)
    {
        $note = $this->repository->find($id);
        if (!$note || $note->user_id !== $request->user()->id) {
            return $this->errorResponse('Note not found or unauthorized', [], 404);
        }

        $validated = $request->validate([
            'content' => 'required|string',
        ]);
        
        $data = $this->repository->update($id, $validated);
        
        return $this->successResponse($data, 'Note updated successfully');
    }

    public function destroy($id, Request $request)
    {
        $note = $this->repository->find($id);
        if (!$note || $note->user_id !== $request->user()->id) {
            return $this->errorResponse('Note not found or unauthorized', [], 404);
        }
        
        $this->repository->delete($id);
        return $this->successResponse(null, 'Note deleted successfully');
    }
}
