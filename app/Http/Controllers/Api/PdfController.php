<?php

namespace App\Http\Controllers\Api;

use App\Models\Regulation;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;

class PdfController extends BaseController
{
    public function generation(): JsonResponse
    {
        try {
            $user = Auth::user();
            $roleId = $user->getRoleFromToken();

            $regulation = Regulation::query()->findOrFail(request('regulation_id'));

//            $regulation->user_id

            $object = $regulation->object;


            $pdf = Pdf::loadView('pdf.regulation', compact('regulation'));


        }catch (\Exception $exception){
            return $this->sendError($exception->getMessage());
        }
    }
}
