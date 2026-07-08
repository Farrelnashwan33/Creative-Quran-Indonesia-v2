<?php
namespace App\Repositories;
use App\Models\Highlight;
class HighlightRepository extends BaseRepository {
    public function __construct(Highlight $model) { parent::__construct($model); }
}
