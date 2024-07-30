<?php

namespace App\DTO;

class RegulationDto
{
  public ?int $regulationId;
  public ?string $comment;
  public ?array $meta = [];

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

    public function setMeta(?array $meta): self
    {
        $this->meta = $meta;
        return $this;
    }
}
