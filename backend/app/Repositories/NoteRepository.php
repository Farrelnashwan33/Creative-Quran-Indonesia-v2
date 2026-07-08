<?php
namespace App\Repositories;
use App\Models\Note;
class NoteRepository extends BaseRepository {
    public function __construct(Note $model) { parent::__construct($model); }
}
