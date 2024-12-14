<?php

namespace App\Http\Controllers;

use App\Models\Regulation;
use App\Models\Role;
use App\Models\User;
use App\Repositories\Interfaces\ArticleRepositoryInterface;
use App\Repositories\Interfaces\ClaimRepositoryInterface;
use App\Services\HistoryService;
use Barryvdh\DomPDF\Facade\Pdf;
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
        $ratingArr = ($objectModel->rating != null) ? json_decode($objectModel->rating, true) : null;

        if (is_array($ratingArr) && isset($ratingArr[0])) {
            $ratingLoyiha = $ratingArr[0]['loyiha']['reyting_loyha'] ?? null;
            $ratingUmumiy = $ratingArr[0]['qurilish']['reyting_umumiy'] ?? null;
            $ratingMel = $ratingArr[0]['qurilish']['reyting_mel'] ?? null;
        } else {
            $ratingLoyiha = null;
            $ratingUmumiy = null;
            $ratingMel = null;
        }
        $data = getData(config('app.gasn.reestr'), $objectModel->reestr_number);
        $conclusion_file = '';
        if(isset($data['data']))
            $conclusion_file = $data['data']['conclusion_file'];
//        $objectModel = $this->articleRepository->findByTaskId($id);
//        $ratingArr = json_decode($objectModel->rating, true)[0];
//        $ratingLoyiha = $ratingArr['loyiha']['reyting_loyha'];
//        $ratingUmumiy = (isset($ratingArr['qurilish']['reyting_umumiy'])) ? $ratingArr['qurilish']['reyting_umumiy'] : null;
//        $ratingMel = (isset($ratingArr['qurilish']['reyting_mel'])) ? $ratingArr['qurilish']['reyting_mel'] : null;
        return view('qr', ['object' => $objectModel, 'rating_loyiha' => $ratingLoyiha, 'rating_umumiy' => $ratingUmumiy, 'rating_mel' => $ratingMel, 'reestr' => $conclusion_file]);
    }

    public function regulation($id)
    {
        try {
            $regulation = Regulation::query()->findOrFail($id);
            $createdByRole = Role::query()->findOrFail($regulation->created_by_role_id);
            $createdByUser = User::query()->findOrFail($regulation->created_by_user_id);
            $object = $regulation->object;
            $responsibleUser = User::query()->findOrFail($regulation->user_id);
            $responsibleRole = Role::query()->findOrFail($regulation->role_id);
            $pdf = Pdf::loadView('pdf.regulation', compact(
                'regulation',
                'object',
                'responsibleUser',
                'responsibleRole',
                'createdByUser',
                'createdByRole'
            ));

            return $pdf->download('regulation.pdf');

        } catch (\Exception $exception) {
            return $this->sendError($exception->getMessage(), $exception->getLine());
        }
    }
}
