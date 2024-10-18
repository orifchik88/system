<?php

namespace App\Repositories\Interfaces;

use App\Models\Article;

interface ArticleRepositoryInterface
{
    public function create(array $data): Article;
    public function update(Article $article, array $data): bool;
    public function findByTaskId($taskId): ?Article;
}
