<?php

namespace App\Repositories;

use App\Helpers\IllegalObjectStatuses;
use App\Http\Requests\UpdateCheckListRequest;
use App\Models\IllegalObject;
use App\Models\IllegalObjectCheckList;
use App\Models\IllegalObjectImage;
use App\Models\IllegalObjectQuestion;
use App\Repositories\Interfaces\IllegalObjectRepositoryInterface;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class IllegalObjectRepository implements IllegalObjectRepositoryInterface
{
    private IllegalObject $illegalObject;


    public function __construct(IllegalObject $illegalObject)
    {
        $this->illegalObject = $illegalObject;
    }

    public function updateCheckList(UpdateCheckListRequest $request)
    {
        foreach ($request->get('questions') as $item) {
            $question = IllegalObjectCheckList::query()->where('id', $item['id'])->first();
            $question->update(['answer' => $item['answer']]);
        }

        return true;
    }

    public function updateObject(int $id)
    {
        return $this->illegalObject->query()->where('id', $id)->update(
            [
                'status' => IllegalObjectStatuses::NEW
            ]
        );
    }

    public function getStatistics(
        ?int    $regionId,
        ?string $dateFrom,
        ?string $dateTo
    )
    {

        if ($regionId == null)
            $results = $this->illegalObject->query()
                ->rightJoin('regions', 'regions.id', '=', 'illegal_objects.region_id')
                ->when($dateFrom, function ($q) use ($dateFrom) {
                    $q->whereDate('illegal_objects.created_at', '>=', $dateFrom);
                })
                ->when($dateTo, function ($q) use ($dateTo) {
                    $q->whereDate('illegal_objects.created_at', '<=', $dateTo);
                })
                ->groupBy('regions.id', 'regions.name_uz')
                ->select(DB::raw("
                    regions.id as region_id,
                    regions.name_uz as name_uz,
                    COUNT(CASE WHEN illegal_objects.status = " . IllegalObjectStatuses::CONFIRMED . " THEN 1 ELSE null END) as count
                  "))
                ->get();
        else
            $results = $this->illegalObject->query()
                ->rightJoin('districts', 'districts.id', '=', 'illegal_objects.district_id')
                ->when($dateFrom, function ($q) use ($dateFrom) {
                    $q->whereDate('illegal_objects.created_at', '>=', $dateFrom);
                })
                ->when($dateTo, function ($q) use ($dateTo) {
                    $q->whereDate('illegal_objects.created_at', '<=', $dateTo);
                })
                ->when($regionId, function ($q) use ($regionId) {
                    $q->where('districts.region_id', $regionId);
                })
                ->groupBy('districts.id', 'districts.name_uz')
                ->select(DB::raw("
                    districts.id as district_id,
                    districts.name_uz as name_uz,
                    COUNT(CASE WHEN illegal_objects.status = " . IllegalObjectStatuses::CONFIRMED . " THEN 1 ELSE null END) as count
                 "))
                ->get();

        return $results;
    }

    public function createObject($data)
    {
        $object = $this->illegalObject->query()->create(
            [
                'lat' => $data->get('lat'),
                'long' => $data->get('long'),
                'address' => $data->get('address'),
                'district_id' => $data->get('district_id'),
                'region_id' => Auth::user()->region_id,
                'created_by' => Auth::user()->id
            ]
        );

        if ($data->hasFile('images')) {
            foreach ($data->file('images') as $image) {
                $imagePath = $image->store('documents/illegal_object', 'public');
                IllegalObjectImage::query()->create([
                    'illegal_object_id' => $object->id,
                    'image' => $imagePath,
                ]);
            }
        }

        $roleId = Auth::user()->getRoleFromToken() ?? null;
        $questions = IllegalObjectQuestion::query()->where('role', $roleId)->get();
        foreach ($questions as $question) {
            IllegalObjectCheckList::query()->create(
                [
                    'question_id' => $question->id,
                    'object_id' => $object->id,
                ]
            );
        }

        return $object;
    }

    public function getObject(int $id)
    {
        return $this->illegalObject->query()->with(['region', 'district', 'images'])->where('id', $id)->first();
    }

    public function getQuestionList(int $id)
    {
        return IllegalObjectCheckList::query()->with('question')->where('object_id', $id)->get();
    }

    public function getList(
        ?int    $regionId,
        ?int    $id,
        ?int    $districtId,
        ?string $sortBy,
        ?int    $status,
        ?int    $role_id
    )
    {
        if ($role_id == null)
            return $this->illegalObject->query()
                ->with(['region', 'district', 'user', 'images'])
                ->join('regions', 'regions.id', '=', 'illegal_objects.region_id')
                ->join('districts', 'districts.id', '=', 'illegal_objects.district_id')
                ->when($regionId, function ($q) use ($regionId) {
                    $q->where('regions.id', $regionId);
                })
                ->when($districtId, function ($q) use ($districtId) {
                    $q->where('districts.id', $districtId);
                })
                ->when($id, function ($q) use ($id) {
                    $q->where('illegal_objects.id', 'LIKE', '%' . $id . '%');
                })
                ->when($status, function ($q) use ($status) {
                    $q->where('illegal_objects.status', $status);
                })
                ->where('illegal_objects.status', '<>', IllegalObjectStatuses::DRAFT)
                ->groupBy('illegal_objects.id')
                ->orderBy('illegal_objects.created_at', strtoupper($sortBy))
                ->select([
                    'illegal_objects.id as id',
                    'illegal_objects.district_id as district_id',
                    'illegal_objects.region_id as region_id',
                    'illegal_objects.status as status',
                    'illegal_objects.lat as lat',
                    'illegal_objects.long as long',
                    'illegal_objects.address as address',
                    'illegal_objects.score as score',
                    'illegal_objects.created_by as created_by',
                    'illegal_objects.created_at as created_at'
                ])
                ->paginate(request()->get('per_page'));
        else
            return $this->illegalObject->query()
                ->with(['region', 'district', 'images'])
                ->join('regions', 'regions.id', '=', 'illegal_objects.region_id')
                ->join('districts', 'districts.id', '=', 'illegal_objects.district_id')
                ->when($regionId, function ($q) use ($regionId) {
                    $q->where('regions.id', $regionId);
                })
                ->when($districtId, function ($q) use ($districtId) {
                    $q->where('districts.id', $districtId);
                })
                ->when($id, function ($q) use ($id) {
                    $q->where('illegal_objects.id', 'LIKE', '%' . $id . '%');
                })
                ->when($status, function ($q) use ($status) {
                    $q->where('illegal_objects.status', $status);
                })
                ->where('illegal_objects.created_by', Auth::user()->id)
                ->groupBy('illegal_objects.id')
                ->orderBy('illegal_objects.created_at', strtoupper($sortBy))
                ->select([
                    'illegal_objects.id as id',
                    'illegal_objects.district_id as district_id',
                    'illegal_objects.region_id as region_id',
                    'illegal_objects.status as status',
                    'illegal_objects.lat as lat',
                    'illegal_objects.long as long',
                    'illegal_objects.address as address',
                    'illegal_objects.score as score',
                    'illegal_objects.created_by as created_by',
                    'illegal_objects.created_at as created_at'
                ])
                ->paginate(request()->get('per_page'));
    }
}
