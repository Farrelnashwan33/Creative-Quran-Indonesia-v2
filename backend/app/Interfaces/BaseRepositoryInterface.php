<?php

namespace App\Interfaces;

interface BaseRepositoryInterface
{
    public function all(array $conditions = []);
    public function find(int $id);
    public function create(array $data);
    public function update(int $id, array $data);
    public function delete(int $id);
}
