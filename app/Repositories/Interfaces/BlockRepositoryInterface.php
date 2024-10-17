<?php

namespace App\Repositories\Interfaces;

use App\Models\Article;
use App\Models\Block;

interface BlockRepositoryInterface
{
    public function saveBlocks($response, Article $article);

    public function updateBlock($blockId, $data);

    public function updateBlockByArticle($blockId, Article $article);

    public function deleteBlock($blockId);

    public function getBlocksByArticle(Article $article);
}
