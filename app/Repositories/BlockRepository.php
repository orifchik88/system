<?php

namespace App\Repositories;

use App\Models\Article;
use App\Repositories\Interfaces\BlockRepositoryInterface;

class BlockRepository implements  BlockRepositoryInterface
{
    public function saveBlocks($response, Article $article)
    {

    }

    public function updateBlock($blockId, $data)
    {

    }

    public function updateBlockByArticle($blockId, Article $article)
    {

    }

    public function deleteBlock($blockId)
    {

    }

    public function getBlocksByArticle(Article $article)
    {

    }
}
