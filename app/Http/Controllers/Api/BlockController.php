<?php

namespace App\Http\Controllers\Api;

use App\Exceptions\NotFoundException;
use App\Http\Controllers\Controller;
use App\Http\Requests\BlockRequest;
use App\Http\Resources\BlockResource;
use App\Models\Article;
use App\Models\Block;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class BlockController extends BaseController
{
    public function index($id): JsonResponse
    {
        try {
            $article = Article::find($id);

            if (!$article) throw new NotFoundException('Article not found', 404);

            $blocks = $article->blocks()->get();

            if ($blocks->isEmpty()) throw new NotFoundException('Blocks not found', 404);

            return $this->sendSuccess(BlockResource::collection($blocks), 'All Blocks');
        }catch (\Exception $exception){
            return $this->sendError($exception->getMessage());
        }

    }

    public function create(BlockRequest $request)
    {
        $block = Block::create($request->validated());

        return $this->sendSuccess(new BlockResource($block), 'Block created');
    }
}
