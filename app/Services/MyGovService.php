<?php

namespace App\Services;

use App\Enums\ObjectStatusEnum;
use App\Repositories\Interfaces\ArticleRepositoryInterface;
use App\Repositories\Interfaces\UserRepositoryInterface;

class MyGovService
{
    private ArticleRepositoryInterface $articleRepository;
    private UserRepositoryInterface $userRepository;

    public function __construct(
        ArticleRepositoryInterface $articleRepository,
        UserRepositoryInterface    $userRepository
    )
    {
        $this->articleRepository = $articleRepository;
        $this->userRepository = $userRepository;
    }

    public function getDxaTaskById(int $task_id)
    {
        $object = $this->articleRepository->findByTaskId($task_id);
        if (!$object)
            return null;

        return [
            'success' => true,
            'object_type' => $object->objectType->name
        ];
    }

    public function getObjectsByPinfl($pinfl)
    {
        $objects = $this->userRepository->findByPinfl($pinfl);
        if (!$objects)
            return null;


        return [
            'objects' => $objects->objects()->count(),
            'can_assign' => true
        ];
    }

    public function getObjectsByCustomer($pinfl)
    {
        $objects = $this->userRepository->findByPinfl($pinfl);
        if (!$objects)
            return null;

        $objectsArr = [];
        foreach ($objects->objects()->get() as $object) {
            if ($object->object_status_id == ObjectStatusEnum::PROGRESS) {
                $tmpArr['id'] = $object->id;
                $tmpArr['name'] = $object->name;
                $tmpArr['task_id'] = $object->task_id;
                $objectsArr[] = $tmpArr;
            }
        }
        return $objectsArr;
    }
}
