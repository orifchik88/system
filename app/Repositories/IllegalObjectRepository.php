<?php

namespace App\Repositories;

use App\Helpers\IllegalObjectStatuses;
use App\Http\Requests\UpdateCheckListRequest;
use App\Models\IllegalObject;
use App\Models\IllegalObjectCheckList;
use App\Models\IllegalObjectImage;
use App\Models\IllegalObjectQuestion;
use App\Models\IllegalQuestionType;
use App\Repositories\Interfaces\IllegalObjectRepositoryInterface;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class IllegalObjectRepository implements IllegalObjectRepositoryInterface
{
    private IllegalObject $illegalObject;


    public function __construct(IllegalObject $illegalObject)
    {
        $this->illegalObject = $illegalObject;
    }

    public function updateCheckList(UpdateCheckListRequest $request)
    {
        DB::beginTransaction();
        try {
            $object = IllegalObject::find($request->object['id']);
            if (!$object) {
                throw new \Exception('Object not found.');
            }

            $object->update([
                'score' => json_encode($request->object['score'])
            ]);

            $questions = $request->get('questions', []);
            IllegalObjectCheckList::whereIn('id', collect($questions)->pluck('id'))
                ->get()
                ->each(function ($question) use ($questions) {
                    $answer = collect($questions)->firstWhere('id', $question->id)['answer'] ?? null;
                    $question->update(['answer' => $answer]);
                });

            DB::commit();
            return true;
        } catch (\Exception $exception) {
            DB::rollBack();
            throw $exception;
        }
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
                    COUNT(CASE WHEN illegal_objects.status = " . IllegalObjectStatuses::CONFIRMED . " THEN 1 ELSE null END) as count,
                    COUNT(CASE WHEN (
                        (SELECT AVG((value->>'ball')::int)
                         FROM jsonb_array_elements(illegal_objects.score) AS value
                        ) BETWEEN 0 AND 30
                    ) THEN 1 ELSE null END) as low_count,
                    COUNT(CASE WHEN (
                        (SELECT AVG((value->>'ball')::int)
                         FROM jsonb_array_elements(illegal_objects.score) AS value
                        ) BETWEEN 31 AND 60
                    ) THEN 1 ELSE null END) as middle_count,
                    COUNT(CASE WHEN (
                        (SELECT AVG((value->>'ball')::int)
                         FROM jsonb_array_elements(illegal_objects.score) AS value
                        ) > 60
                    ) THEN 1 ELSE null END) as high_count
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
                    COUNT(CASE WHEN (
                        (SELECT AVG((value->>'ball')::int)
                         FROM jsonb_array_elements(illegal_objects.score) AS value
                        ) BETWEEN 0 AND 30
                    ) THEN 1 ELSE null END) as low_count,
                    COUNT(CASE WHEN (
                        (SELECT AVG((value->>'ball')::int)
                         FROM jsonb_array_elements(illegal_objects.score) AS value
                        ) BETWEEN 31 AND 60
                    ) THEN 1 ELSE null END) as middle_count,
                    COUNT(CASE WHEN (
                        (SELECT AVG((value->>'ball')::int)
                         FROM jsonb_array_elements(illegal_objects.score) AS value
                        ) > 60
                    ) THEN 1 ELSE null END) as high_count
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
        $object = $this->illegalObject->query()
            ->with(['region', 'district', 'images'])
            ->where('id', $id)
            ->first();

        if (!$object) {
            return null;
        }

        return [
            'id' => $object->id,
            'address' => $object->address,
            'lat' => $object->lat,
            'long' => $object->long,
            'region' => [
                'id' => $object->region->id,
                'name' => $object->region->name_uz,
            ],
            'district' => [
                'id' => $object->district->id,
                'name' => $object->district->name_uz,
            ],
            'status' => $object->status,
            'score' => collect($object->score)->map(function ($item) {
                $type = IllegalQuestionType::find($item['type'] ?? null);
                return [
                    'id' => $item['type'] ?? null,
                    'ball' => $item['ball'] ?? null,
                    'type_name' => $type ? $type->name : null,
                ];
            }),
            'images' => $object->images->map(function ($image) {
                return [
                    'id' => $image->id,
                    'url' => Storage::disk('public')->url($image->image),
                ];
            }),
            'created' => $object->created_at,
        ];
    }


    public function getQuestionList(int $id)
    {
        return IllegalObjectCheckList::query()
            ->with(['question.type'])
            ->where('object_id', $id)
            ->get()
            ->map(function ($item) {
                return [
                    'id' => $item->id,
                    'answer' => $item->answer,
                    'object_id' => $item->object_id,
                    'question' => [
                        'id' => $item->question->id ?? null,
                        'name' => $item->question->name ?? null,
                        'role' => $item->question->role ?? null,
                        'ball' => $item->question->ball ?? null,
                        'type' => [
                            'id' => $item->question->type->id ?? null,
                            'name' => $item->question->type->name ?? null,
                        ],
                    ],
                ];
            });

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
                ->paginate(request()->get('per_page'))
                ->through(fn($item) => [
                    'id' => $item->id,
                    'district_id' => $item->district_id,
                    'region_id' => $item->region_id,
                    'status' => $item->status,
                    'lat' => $item->lat,
                    'long' => $item->long,
                    'address' => $item->address,
                    'score' => $item->question_type,
                    'created_by' => $item->created_by,
                    'created_at' => $item->created_at,
                ]);
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
                ->paginate(request()->get('per_page'))
                ->through(fn($item) => [
                    'id' => $item->id,
                    'district_id' => $item->district_id,
                    'region_id' => $item->region_id,
                    'status' => $item->status,
                    'lat' => $item->lat,
                    'long' => $item->long,
                    'address' => $item->address,
                    'score' => $item->question_type,
                    'created_by' => $item->created_by,
                    'created_at' => $item->created_at,
                ]);
    }
}
