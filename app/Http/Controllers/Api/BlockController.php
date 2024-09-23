<?php

namespace App\Http\Controllers\Api;

use App\Exceptions\NotFoundException;
use App\Http\Requests\BlockRequest;
use App\Http\Resources\ArticleBlockResource;
use App\Http\Resources\BlockModeResource;
use App\Http\Resources\BlockResource;
use App\Http\Resources\BlockTypeResource;
use App\Http\Resources\ResponseBlockResource;
use App\Models\Article;
use App\Models\Block;
use App\Models\BlockMode;
use App\Models\BlockType;
use App\Models\DxaResponse;
use Illuminate\Http\JsonResponse;

class BlockController extends BaseController
{
    public function index($id): JsonResponse
    {
        try {
            $article = Article::find($id);

            if (!$article) throw new NotFoundException('Article not found', 404);

            $blocks = $article->blocks()->get();

            if ($blocks->isEmpty()) throw new NotFoundException('Blocks not found', 404);

            return $this->sendSuccess(ArticleBlockResource::collection($blocks), 'All Blocks');
        }catch (\Exception $exception){
            return $this->sendError($exception->getMessage());
        }

    }

    public function responseBlock($id): JsonResponse
    {
        try {
            $response  = DxaResponse::query()->findOrFail($id);
            if (!$response) throw new NotFoundException('Response not found', 404);
            $blocks = $response->blocks()->get();
            if ($blocks->isEmpty()) throw new NotFoundException('Blocks not found', 404);

            return $this->sendSuccess(ResponseBlockResource::collection($blocks), 'All Blocks');

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

    public function blockModes(): JsonResponse
    {
        try {
            $modes = BlockMode::all();
            return $this->sendSuccess(BlockModeResource::collection($modes), 'All Block Modes');
        }catch (\Exception $exception){
            return $this->sendError($exception->getMessage());
        }
    }

    public function blockTypes(): JsonResponse
    {
        try {
            $types = BlockType::query()->where('block_mode_id', request('id'))->get();
            return $this->sendSuccess(BlockTypeResource::collection($types), 'All Block Modes');
        }catch (\Exception $exception){
            return $this->sendError($exception->getMessage());
        }
    }
}
