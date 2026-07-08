<?php
namespace App\Repositories;
use App\Models\LastRead;
class LastReadRepository extends BaseRepository {
    public function __construct(LastRead $model) { parent::__construct($model); }
}
