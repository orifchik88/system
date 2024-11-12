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
        $objectModel = $this->articleRepository->findByTaskId($id);
        $ratingArr = json_decode($objectModel->rating, true);

        if (is_array($ratingArr) && isset($ratingArr[0])) {
            $ratingLoyiha = $ratingArr[0]['loyiha']['reyting_loyha'] ?? null;
            $ratingUmumiy = $ratingArr[0]['qurilish']['reyting_umumiy'] ?? null;
            $ratingMel = $ratingArr[0]['qurilish']['reyting_mel'] ?? null;
        } else {
            $ratingLoyiha = null;
            $ratingUmumiy = null;
            $ratingMel = null;
        }
//        $objectModel = $this->articleRepository->findByTaskId($id);
//        $ratingArr = json_decode($objectModel->rating, true)[0];
//        $ratingLoyiha = $ratingArr['loyiha']['reyting_loyha'];
//        $ratingUmumiy = (isset($ratingArr['qurilish']['reyting_umumiy'])) ? $ratingArr['qurilish']['reyting_umumiy'] : null;
//        $ratingMel = (isset($ratingArr['qurilish']['reyting_mel'])) ? $ratingArr['qurilish']['reyting_mel'] : null;
        return view('qr', ['object' => $objectModel, 'rating_loyiha' => $ratingLoyiha, 'rating_umumiy' => $ratingUmumiy, 'rating_mel' => $ratingMel]);
    }
}
