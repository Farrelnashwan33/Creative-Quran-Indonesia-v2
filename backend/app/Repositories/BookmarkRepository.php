<?php
namespace App\Repositories;
use App\Models\Bookmark;
class BookmarkRepository extends BaseRepository {
    public function __construct(Bookmark $model) { parent::__construct($model); }
}
