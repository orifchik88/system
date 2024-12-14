<?php

namespace App\Http\Controllers\Api;

use App\Helpers\OrganizationNames;
use App\Http\Resources\UserResource;
use App\Models\ClaimOrganizationReview;
use App\Models\Regulation;
use App\Models\Role;
use App\Models\User;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\URL;
use SimpleSoftwareIO\QrCode\Facades\QrCode;

class PdfController extends BaseController
{
    public function generation(): JsonResponse
    {
        try {
            $regulation = Regulation::query()->findOrFail(request('regulation_id'));
            $createdByRole = Role::query()->findOrFail($regulation->created_by_role_id);
            $createdByUser = User::query()->findOrFail($regulation->created_by_user_id);
            $object = $regulation->object;
            $responsibleUser = User::query()->findOrFail($regulation->user_id);
            $responsibleRole = Role::query()->findOrFail($regulation->role_id);

            $domain = URL::to('/regulation-info').'/'.$regulation->id;

            $qrImage = base64_encode(QrCode::format('png')->size(200)->generate($domain));

//            $qrImageTag = '<img src="data:image/png;base64,' . $qrImage . '" alt="QR Image" />';
            $pdf = Pdf::loadView('pdf.reg', compact(
                'regulation',
                'object',
                'responsibleUser',
                'responsibleRole',
                'createdByUser',
                'createdByRole',
                'qrImage'
            ));

            $pdfOutput = $pdf->output();
            $pdfBase64 = base64_encode($pdfOutput);
            return $this->sendSuccess($pdfBase64, 'PDF');

        } catch (\Exception $exception) {
            return $this->sendError($exception->getMessage(), $exception->getLine());
        }
    }

    public function pdfOrganization($id)
    {
        try {
            $review = ClaimOrganizationReview::with('monitoring')->where('id', $id)->first();
            $jsonTable = DB::table('claim_organization_reviews')->where('id', $id)->first();
            $jsonTable = json_decode(gzuncompress(base64_decode($jsonTable->answer)), true);
            $region = (in_array($review->organization_id, [15,16])) ? $review->monitoring->claim->district()->first()->name_uz : $review->monitoring->claim->region()->first()->name_uz;
            $headName = str_replace('{region}', $region, OrganizationNames::NAMES[$review->organization_id]);

            $name = '';
            foreach ($jsonTable as $key => $value) {
                if (str_contains($key, '_name'))
                    $name = $value;
            }
//            $qrCode = QrCode::format('png')->size(150)->generate('Test');
//            $qrCode = base64_encode($qrCode);
            $qrCode = base64_encode(file_get_contents('https://api.qrserver.com/v1/create-qr-code/?size=150x150&data=' . url('/api/organization-pdf/' . $id)));
            $pdf = Pdf::loadView('pdf.review', ['review' => $review, 'name' => $name, 'qrCode' => $qrCode, 'headName' => $headName]);

            return $pdf->stream();

        } catch (\Exception $exception) {
            return $this->sendError($exception->getMessage(), $exception->getLine());
        }
    }

    public function pdfOrganizationDownload($id)
    {
        try {
            $review = ClaimOrganizationReview::with('monitoring')->where('id', $id)->first();
            $jsonTable = DB::table('claim_organization_reviews')->where('id', $id)->first();
            $jsonTable = json_decode(gzuncompress(base64_decode($jsonTable->answer)), true);
            $region = (in_array($review->organization_id, [15,16])) ? $review->monitoring->claim->district()->first()->name_uz : $review->monitoring->claim->region()->first()->name_uz;
            $headName = str_replace('{region}', $region, OrganizationNames::NAMES[$review->organization_id]);

            $name = '';
            foreach ($jsonTable as $key => $value) {
                if (str_contains($key, '_name'))
                    $name = $value;
            }
//            $qrCode = QrCode::format('png')->size(150)->generate('Test');
//            $qrCode = base64_encode($qrCode);
            $qrCode = base64_encode(file_get_contents('https://api.qrserver.com/v1/create-qr-code/?size=150x150&data=' . url('/api/organization-pdf/' . $id)));
            $pdf = Pdf::loadView('pdf.review', ['review' => $review, 'name' => $name, 'qrCode' => $qrCode, 'headName' => $headName]);

            $pdfOutput = $pdf->output();
            $pdfBase64 = base64_encode($pdfOutput);
            return $this->sendSuccess($pdfBase64, 'PDF');

        } catch (\Exception $exception) {
            return $this->sendError($exception->getMessage(), $exception->getLine());
        }
    }

}
