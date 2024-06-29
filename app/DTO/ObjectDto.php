<?php

namespace App\DTO;

class ObjectDto
{
    public ?int $responseId;
    public ?int $fundingSourceId;
    public ?int $objectSectorId;
    public function setResponseId(?int $responseId): self
    {
        $this->responseId = $responseId;
        return $this;
    }

    public function setFundingSourceId(?int $fundingSourceId): self
    {
        $this->fundingSourceId = $fundingSourceId;
        return $this;
    }

    public function setObjectSectorId(?int $objectSectorId): self
    {
        $this->objectSectorId = $objectSectorId;
        return $this;
    }
}
