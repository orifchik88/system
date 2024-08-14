<?php

namespace App\DTO;

use Illuminate\Support\Facades\Auth;

class QuestionDto
{
    public ?int $objectId;
    public ?array $meta = [];

    public ?array $deadline = [];
    public ?int $levelId;

    public ?int $regulationId;

    public function setObject(int $objectId): self
    {
        $this->objectId = $objectId;
        return $this;
    }


    public function setMeta(array $meta): self
    {
        $this->meta = $meta;
        return $this;
    }

    public function setDeadline(array $deadline): self
    {
        $this->deadline = $deadline;
        return $this;
    }

    public function setLevel(int $levelId): self
    {
        $this->levelId = $levelId;
        return $this;
    }

    public function setRegulationId(int $regulationId): self
    {
        $this->regulationId = $regulationId;
        return $this;
    }
}
