<?php

namespace App\Services;

use App\DTO\ObjectDto;
use App\Exceptions\NotFoundException;
use App\Models\Article;
use App\Models\FundingSource;
use App\Models\ObjectSector;
use App\Models\ObjectType;

class ArticleService
{
    protected ObjectDto $objectDto;
    public function __construct(
        protected Article $article,
        protected ObjectType $objectType,
        protected FundingSource $fundingSource,
        protected ObjectSector $objectSector,
    ){}

    public function setObjectDto(ObjectDto $objectDto): void
    {
        $this->objectDto = $objectDto;
    }


    public function getAllTypes(): object
    {
        return $this->objectType->all();
    }

    public function getObjectSectors($id): object
    {
        $foundingSource = $this->fundingSource->find($id);
        if (empty($foundingSource)) {
            throw  new NotFoundException('Object sectors not found');
        }
        return $foundingSource->objectSectors;
    }

    public function getAllFundingSources(): object
    {
        return $this->fundingSource->all();
    }


}
