<?php

namespace App\Http\Controllers;

use App\Repositories\Interfaces\ArticleRepositoryInterface;
use App\Repositories\Interfaces\ClaimRepositoryInterface;
use App\Services\HistoryService;
use Illuminate\Http\Request;

class SiteController extends Controller
{
    private ArticleRepositoryInterface $articleRepository;

    public function __construct(
        ArticleRepositoryInterface $articleRepository
    )
    {
        $this->articleRepository = $articleRepository;
    }

    public function index($id)
    {
        $objectModel = $this->articleRepository->findById($id);
        $ratingArr = json_decode($objectModel->rating, true)[0];
        $ratingLoyiha = $ratingArr['loyiha']['reyting_loyha'];
        $ratingUmumiy = (isset($ratingArr['qurilish']['reyting_umumiy'])) ? $ratingArr['qurilish']['reyting_umumiy'] : null;
        $ratingMel = (isset($ratingArr['qurilish']['reyting_mel'])) ? $ratingArr['qurilish']['reyting_mel'] : null;
        return view('qr', ['object' => $objectModel, 'rating_loyiha' => $ratingLoyiha, 'rating_umumiy' => $ratingUmumiy, 'rating_mel' => $ratingMel]);
    }
}
