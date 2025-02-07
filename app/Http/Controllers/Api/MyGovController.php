<?php

namespace App\Http\Controllers\Api;

use App\Enums\ConstructionWork;
use App\Http\Controllers\Controller;
use App\Services\MyGovService;
use Illuminate\Support\Facades\Auth;

class MyGovController extends Controller
{
    private MyGovService $myGovService;

    public function __construct(MyGovService $myGovService)
    {
        $this->myGovService = $myGovService;
    }

    public function showTask($id)
    {
        $data = $this->myGovService->getDxaTaskById(task_id: $id);

        if (!$data) {
            return ['success' =>  false, 'message' => "Ma'lumot topilmadi"];
        }

        return $data;
    }

    public function getObjectsByPinfl()
    {
        $data = $this->myGovService->getObjectsByPinfl(pinfl: request()->get('pinfl'));

        if (!$data) {
            return ['success' =>  false, 'message' => "Ma'lumot topilmadi"];
        }

        return $data;
    }

    public function getObjectsByCustomer()
    {
        $data = $this->myGovService->getObjectsByCustomer(pinfl: request()->get('pinfl'));

        if (!$data) {
            return ['success' =>  false, 'message' => "Ma'lumot topilmadi"];
        }

        return $data;
    }

    public function getObjectsByCadastr()
    {
        $data = $this->myGovService->getObjectsByCadastralNumber(request('cadastr'));

        if (!$data) {
            return ['success' =>  false, 'message' => "Ma'lumot topilmadi"];
        }

        return $data;
    }

    public function getObjectsList()
    {
        $filters = request()->only(['date_from', 'date_to', 'regions', 'stir']);

        $data = $this->myGovService->getObjectList($filters);

        if (!$data) {
            return ['success' =>  false, 'message' => "Ma'lumot topilmadi"];
        }
        $meta = [
            "success" => true,
            "status" => 200,
            "msg" => "So'rovga asosan ma'lumotlar to'liq shakllantirildi",
            "title" => "Obyektlar haqida toliq ma'lumot olish",
            "total_counts" => count($data),
            "description" => [
                'id' => "obyektni sistemadagi unikalniy raqami",
                'name' => "obyekt nomi",
                'region_id' => "viloyat sistemadagi id raqami",
                'district_id' => "tumanni sistemadagi id raqami",
                'address' => "obyekt joylashgan manzil",
                'difficulty_category_id' => "obyektning murakkablik toivasi",
                'construction_type_id' =>  "obyekt turi idsi",
                'construction_cost' => "obyektning qiymati",
                'blocks' => "block idlari",
                'object_status_id' => "obyektni sistemadagi holati idsi",
                'created_at' => "obyektning registratsiya bolgan sanasi",
                'object_type' => "obyekt turi idsi",
                'cadastral_number' => "kadastr nomeri",
                'name_expertise' => "expertiza tashkiloti nomi",
                'lat' => "obyekt joylashgan manzili latitude",
                'long' => "obyekt joylashgan manzili longitude",
                'dxa_id' => "obyektning rasmiylashtirishga kelgan arizasini sistemadagi id raqami",
                'task_id' => "obyektning mygovdagi ariza raqami",
                'funding_source_id' => "Moliyalashtirish manbai id raqami",
                'closed_at' => "obyektning topshirilgan sanasi",
                'deadline' => "obyektning topshirish muddati",
                'gnk_id' => "obyektning moliya tashkiloti bilan biriktirilgan raqami",
            ],
        ];
        $meta = array_merge($meta,$data);
        return response()->json($meta, 200);
    }

    public function getObjectsByOrganization()
    {

        $data = $this->myGovService->getObjectOrganization();

        if (!$data) {
            return ['success' =>  false, 'message' => "Ma'lumot topilmadi"];
        }

        return $data;
    }

    public function getObjectsByDesign()
    {
        $data = $this->myGovService->getObjectDesign();

        if (!$data) {
            return ['success' =>  false, 'message' => "Ma'lumot topilmadi"];
        }

        return $data;
    }



}
