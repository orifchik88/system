<?php

namespace App\Http\Controllers\Api;

use App\Exceptions\NotFoundException;
use App\Http\Requests\BlockRequest;
use App\Http\Resources\BlockResource;
use App\Models\Article;
use App\Models\Block;
use Illuminate\Http\JsonResponse;

class BlockController extends BaseController
{
    public function index($id): JsonResponse
    {
        try {
            $article = Article::find($id);

            if (!$article) throw new NotFoundException('Article not found', 404);

            $blocks = $article->articleBlocks()->get();

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

    public function delete(): JsonResponse
    {
        try {
            $block = Block::find(request('id'));
            if (!$block) throw new NotFoundException('Block not found', 404);
            $block->delete();
            return $this->sendSuccess(null, 'Block deleted');
        }catch (\Exception $exception){
            return $this->sendError($exception->getMessage());
        }
    }

    public function edit(): JsonResponse
    {
        try {
            $block = Block::find(request('id'));
            if (!$block) throw new NotFoundException('Block not found', 404);
            $block->update([
               'name' => request('name'),
            ]);
            return $this->sendSuccess(new BlockResource($block), 'Block updated');
        }catch (\Exception $exception){
            return $this->sendError($exception->getMessage());
        }
    }
}
