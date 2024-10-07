<?php

namespace App\Services;

use App\Enums\LogType;
use App\Repositories\HistoryRepository;
use App\Repositories\Interfaces\HistoryRepositoryInterface;
use Illuminate\Support\Facades\Auth;

class HistoryService
{
    private HistoryRepositoryInterface $repository;
    public function __construct($tableName)
    {
        $this->repository = new HistoryRepository($tableName);
    }

    public function getHistoryList(int $id)
    {
        return $this->repository->getHistoryList(guId: $id);
    }
    public function getHistory(int $id)
    {
        return $this->repository->getHistory(guId: $id);
    }


    public function createHistory(int $guId, int $status, int $type, ?string $date, ?string $comment = ""): bool
    {
        $content = match ($type) {
            LogType::TASK_HISTORY => $this->shapeTaskContent(
                status: $status,
                comment: $comment,
                date: $date
            ),
            default => null,
        };

        if (!$content) {
            return false;
        }

        return $this->repository->createHistory(
            guId: $guId,
            content: $content,
            type: $type
        );

    }

    private function shapeTaskContent(int $status, string $comment, ?string $date): array
    {
        $user = Auth::user();
        return [
            'user' => Auth::check() ? $user->id : "",
            'role' => (int)$user->getRoleFromToken(),
            'date' => ($date == null) ? now() : $date,
            'status' => $status,
            'comment' => $comment,
        ];
    }

}
