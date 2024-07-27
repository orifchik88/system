<?php

namespace App\DTO;

class RegulationDto
{
  public ?int $regulationId;
  public ?string $comment;

    public function setRegulationId(?int $regulationId): self
    {
        $this->regulationId = $regulationId;
        return $this;
    }

    public function setComment(?string $comment): self
    {
        $this->comment = $comment;
        return $this;
    }
}
