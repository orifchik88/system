<?php

namespace App\Repositories;

use App\Models\Article;

class ArticleRepository
{
    public function create(array $data): Article
    {
        $article = new Article();
        $article->fill($data);
        $article->save();
        return $article;
    }

    public function update(Article $article, array $data): bool
    {
        return $article->update($data);
    }

    public function findByTaskId($taskId): ?Article
    {
        return Article::where('task_id', $taskId)->first();
    }
}
