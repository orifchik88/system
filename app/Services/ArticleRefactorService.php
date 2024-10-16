<?php

namespace App\Services;

use App\DTO\ObjectDto;
use App\Enums\DxaResponseStatusEnum;
use App\Models\Article;
use App\Models\DxaResponse;
use App\Repositories\Interfaces\ArticleRepositoryInterface;
use App\Repositories\Interfaces\BlockRepositoryInterface;
use App\Repositories\Interfaces\UserRepositoryInterface;
use Illuminate\Support\Facades\DB;

class ArticleRefactorService
{
    protected ObjectDto $objectDto;

    public function __construct(
        protected ArticleRepositoryInterface $articleRepository,
        protected UserRepositoryInterface $userRepository,
        protected BlockRepositoryInterface $blockRepository,
        protected DxaResponse $dxaResponse
    ) {}

    public function setObjectDto(ObjectDto $objectDto): void
    {
        $this->objectDto = $objectDto;
    }

    public function createObject(): Article
    {
        DB::beginTransaction();
        try {
            $response = $this->findResponse();
            $this->updateResponse($response);

            $article = $this->handleArticleCreationOrUpdate($response);
            $this->handleArticleSupervisors($article, $response);
            $this->attachInspector($article, $response->inspector_id);
            $this->saveBlocks($response, $article);
            $this->saveEmployee($article);

            DB::commit();
            return $article;
        } catch (\Exception $exception) {
            DB::rollBack();
            throw new \Exception("Error creating object: " . $exception->getMessage());
        }
    }

    private function findResponse(): DxaResponse
    {
        return $this->dxaResponse->findOrFail($this->objectDto->responseId);
    }

    private function updateResponse(DxaResponse $response): void
    {
        $response->update([
            'is_accepted' => true,
            'confirmed_at' => now(),
            'dxa_response_status_id' => DxaResponseStatusEnum::ACCEPTED
        ]);
    }

    private function handleArticleCreationOrUpdate(DxaResponse $response): Article
    {
        if ($response->notification_type == 2) {
            return $this->updateArticle($response);
        }
        return $this->createNewArticle($response);
    }

    private function updateArticle(DxaResponse $response): Article
    {
        $article = $this->articleRepository->findByTaskId($response->old_task_id);
        $this->articleRepository->update($article, $this->mapArticleData($response));
        return $article;
    }

    private function createNewArticle(DxaResponse $response): Article
    {
        return $this->articleRepository->create($this->mapArticleData($response));
    }

    private function handleArticleSupervisors(Article $article, DxaResponse $response): void
    {
        if ($response->notification_type == 2) {
            $article->users()->detach();
        }
        foreach ($response->supervisors as $supervisor) {
            $this->processSupervisor($supervisor, $article);
        }
    }

    private function processSupervisor($supervisor, Article $article): void
    {
        $fish = $this->generateFish($supervisor->fish);
        $userData = $this->mapUserData($supervisor, $fish);
        $user = $this->userRepository->createOrUpdate($userData);

        $article->users()->attach($user->id, ['role_id' => $supervisor->role_id]);
        $this->userRepository->attachRole($user, $supervisor->role_id);
    }

    private function generateFish(string $fish): ?array
    {
        return $fish ? explode(" ", $fish) : null;
    }

    private function mapArticleData(DxaResponse $response): array
    {
        return [
            'task_id' => $response->task_id,
            'title' => $response->title,
            'description' => $response->description,
            'status' => $response->status,
            'deadline' => $response->deadline,
            // boshqa kerakli maydonlar mapping qiling
        ];
    }

    private function mapUserData($supervisor, $fish): array
    {
        return [
            'pinfl' => $supervisor->pinfl,
            'first_name' => $fish[0] ?? null,
            'last_name' => $fish[1] ?? null,
            'middle_name' => $fish[2] ?? null,
            'email' => $supervisor->email,
            'phone' => $supervisor->phone,
            // boshqa kerakli maydonlar mapping qiling
        ];
    }

    private function attachInspector(Article $article, $inspectorId): void
    {
        $inspector = $this->userRepository->findByPinfl($inspectorId);
        if ($inspector) {
            $article->users()->attach($inspector->id, ['role_id' => 3]); // 3 - Inspector role
        }
    }

    private function saveBlocks(DxaResponse $response, Article $article): void
    {
        foreach ($response->blocks as $block) {
            // Logika: Blockni saqlash uchun repositorydan foydalanish
            // BlockRepository ga tegishli bo'lsa, uni ham shu yerda ishlatish kerak
        }
    }

    private function saveEmployee(Article $article): void
    {
        // Hodimlarni saqlash uchun kerakli logika
    }


}
