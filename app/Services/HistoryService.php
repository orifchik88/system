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

    public function getHistoryList(int $guId)
    {
        return $this->repository->getHistoryList(guId: $guId);
    }

    public function getFilteredList(int $guId, string $jsonColumn, $needle)
    {
        return $this->repository->getFilteredList(guId: $guId, jsonColumn: $jsonColumn, needle: $needle);
    }

    public function getHistory(int $id)
    {
        return $this->repository->getHistory(id: $id);
    }


    public function createHistory(int $guId, int $status, int $type, ?string $date, ?string $comment = "", mixed $additionalInfo = null): int
    {
        $content = match ($type) {
            LogType::TASK_HISTORY,
            LogType::ARTICLE_HISTORY,
            LogType::ARTICLE_INSPECTOR_HISTORY,
            LogType::ARTICLE_PRICE_DELETE,
            LogType::ARTICLE_PRICE_HISTORY,
            LogType::CLAIM_HISTORY,
            LogType::ARTICLE_ROTATION,
            LogType::ARTICLE_UPDATE_HISTORY,
            LogType::ARTICLE_CREATE_HISTORY,
            => $this->shapeTaskContent(
                status: $status,
                comment: $comment,
                date: $date,
                additionalInfo: $additionalInfo
            ),
            default => null,
        };

        if (!$content) {
            return false;
        }

       return $this->repository->createHistory(guId: $guId, content: $content, type: $type);
    }

    private function shapeTaskContent(int $status, string $comment, ?string $date, mixed $additionalInfo): array
    {
        $user = Auth::user();
        return [
            'user' => Auth::check() ? $user->id : "",
            'role' => $user ? (int)$user->getRoleFromToken() : null,
            'date' => ($date == null) ? now() : $date,
            'status' => $status,
            'comment' => $comment,
            'additionalInfo' => $additionalInfo
        ];
    }

}
