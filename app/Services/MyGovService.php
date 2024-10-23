<?php

namespace App\Services;

use App\Repositories\Interfaces\ArticleRepositoryInterface;
use App\Repositories\Interfaces\UserRepositoryInterface;

class MyGovService
{
    private ArticleRepositoryInterface $articleRepository;
    private UserRepositoryInterface $userRepository;

    public function __construct(
        ArticleRepositoryInterface $articleRepository,
        UserRepositoryInterface $userRepository
    )
    {
        $this->articleRepository = $articleRepository;
        $this->userRepository = $userRepository;
    }

    public function getDxaTaskById(int $task_id)
    {
        return $this->articleRepository->findByTaskId($task_id);
    }

    public function getObjectsByPinfl($pinfl)
    {
        $objects = $this->userRepository->findByPinfl($pinfl);
        if(!$objects)
            return null;


        return [
            'objects' => $objects->objects()->count(),
            'can_assign' => true
        ];
    }
}
