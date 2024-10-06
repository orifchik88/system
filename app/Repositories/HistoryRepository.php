<?php

namespace App\Repositories;

use App\Enums\LogType;
use App\Repositories\Interfaces\HistoryRepositoryInterface;
use Illuminate\Support\Facades\DB;

class HistoryRepository implements HistoryRepositoryInterface
{
    protected $table;

    public function __construct($table)
    {
        $this->table = $table;
    }

    public function getHistoryList(int $guId)
    {
        return DB::table($this->table)->where('gu_id', $guId)->where('type', LogType::TASK_HISTORY)->orderBy('id', 'asc')->get([
            'id',
            'gu_id',
            'content',
            'created_at'
        ]);
    }

    public function getHistory(int $guId)
    {
        return DB::table($this->table)->where('gu_id', $guId)->where('type', LogType::TASK_HISTORY)->orderBy('id', 'asc')->first([
            'id',
            'gu_id',
            'content',
            'created_at'
        ]);
    }

    public function createHistory(int $guId, array $content, int $type)
    {
        return DB::table($this->table)->insert([
            'gu_id' => $guId,
            'content' => json_encode($content),
            'type' => $type
        ]);
    }
}
