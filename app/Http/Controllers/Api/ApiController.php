<?php

namespace App\Http\Controllers\Api;

use App\Enums\ObjectStatusEnum;
use App\Http\Controllers\Controller;
use App\Http\Requests\UpdateDeadline;
use App\Repositories\Interfaces\ArticleRepositoryInterface;
use App\Services\MyGovService;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class ApiController extends Controller
{
    private MyGovService $myGovService;
    private ArticleRepositoryInterface $articleRepository;

    public function __construct(MyGovService $myGovService, ArticleRepositoryInterface $articleRepository)
    {
        $this->myGovService = $myGovService;
        $this->articleRepository = $articleRepository;
    }

    public function showTask($id)
    {
        $data = $this->myGovService->getObjectTaskById(task_id: $id);

        if (!$data) {
            return ['success' =>  false, 'message' => "Ma'lumot topilmadi"];
        }

        return $data;
    }

    public function updateDeadline(UpdateDeadline $request)
    {
        $objectModel = $this->articleRepository->findByTaskId($request['task_id']);

        if ($objectModel->object_status_id != ObjectStatusEnum::PROGRESS)
            return ['success' =>  false, 'message' => "Bu obyekt yakunlangan"];

//        $files = [];
//        foreach ($request->files as $document) {
//            $path = $document->store('document/object', 'public');
//            $files[] =$path;
//        }

        DB::table('article_deadline_log')->insertGetId(
            [
                'object_id' => $objectModel->id,
                'old_deadline' => $objectModel->deadline,
                'new_deadline' => $request['end_term_work'],
                'files' => json_encode($request['files']),
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now()
            ]
        );

        $objectModel->update(
            [
                'deadline' => $request['end_term_work'],
                'gnk_tender' => $request['gnk_id']
            ]
        );

        return ['message' => "Obyekt muddati muvaffaqiyatli uzaytirildi"];
    }

}
