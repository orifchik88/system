<?php

namespace App\Repositories\Interfaces;

interface HistoryRepositoryInterface
{
    public function getHistoryList(int $guId);
    public function createHistory(int $guId, array $content, int $type);
}
