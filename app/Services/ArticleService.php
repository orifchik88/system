<?php

namespace App\Services;

use App\Models\Article;
use App\Models\ObjectType;

class ArticleService
{
    public function __construct(
        protected Article $article,
        protected ObjectType $objectType
    ){}


    public function getAllTypes(): object
    {
        return $this->objectType->all();
    }


}
