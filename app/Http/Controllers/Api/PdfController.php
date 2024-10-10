<?php

namespace App\Http\Controllers\Api;

use App\Http\Resources\UserResource;
use App\Models\Regulation;
use App\Models\Role;
use App\Models\User;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;

class PdfController extends BaseController
{
    public function generation(): JsonResponse
    {
        try {
            $regulation = Regulation::query()->findOrFail(request('regulation_id'));
            $createdByRole = Role::query()->findOrFail($regulation->created_by_role_id);
            $createdByUser = User::query()->findOrFail($regulation->created_by_role_id);
            $object = $regulation->object;
            $responsibleUser = User::query()->findOrFail($regulation->user_id);
            $responsibleRole = Role::query()->findOrFail($regulation->role_id);
            $pdf = Pdf::loadView('pdf.regulation', compact('regulation', 'object', 'responsibleUser', 'responsibleRole', 'createdByUser', 'createdByRole'));
            
            $pdfOutput = $pdf->output();
            $pdfBase64 = base64_encode($pdfOutput);
           return $this->sendSuccess($pdfBase64, 'PDF');

        }catch (\Exception $exception){
            return $this->sendError($exception->getMessage(), $exception->getLine());
        }
    }
}
