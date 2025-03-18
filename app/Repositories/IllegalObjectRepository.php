<?php

namespace App\Repositories;

use App\Helpers\IllegalObjectHistoryType;
use App\Helpers\IllegalObjectStatuses;
use App\Http\Requests\IllegalObjectUpdateRequest;
use App\Http\Requests\UpdateCheckListRequest;
use App\Models\IllegalObject;
use App\Models\IllegalObjectCheckList;
use App\Models\IllegalObjectCheckListHistory;
use App\Models\IllegalObjectHistory;
use App\Models\IllegalObjectImage;
use App\Models\IllegalObjectQuestion;
use App\Models\IllegalQuestionType;
use App\Models\User;
use App\Repositories\Interfaces\IllegalObjectRepositoryInterface;
use App\Services\HistoryService;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class IllegalObjectRepository implements IllegalObjectRepositoryInterface
{
    private IllegalObject $illegalObject;


    public function __construct(IllegalObject $illegalObject)
    {
        $this->illegalObject = $illegalObject;
    }



    public function updateCheckList(UpdateCheckListRequest $request, $user, $roleId)
    {
        DB::beginTransaction();
        try {
            $object = IllegalObject::query()->findOrFail($request->object['id']);

            $questions = collect($request->get('questions', []));
            $histories = [];

            $allAnswersTrue = true;

            IllegalObjectCheckList::query()->whereIn('id', $questions->pluck('id'))
                ->get()
                ->each(function ($question) use ($questions, $user, $roleId, &$histories, &$allAnswersTrue) {
                    $data = $questions->firstWhere('id', $question->id);
                    $answer = isset($data['answer']) && ($data['answer'] === true || $data['answer'] === 'true' || $data['answer'] === 1 || $data['answer'] === '1');

                    if (!$answer) {
                        $allAnswersTrue = false;
                    }

                    $question->update(['answer' => $answer]);

                    $history = new HistoryService('illegal_object_check_list_histories');
                    $tableId = $history->createHistory(
                        guId: $question->id,
                        status: $answer,
                        type: IllegalObjectHistoryType::CHECKLIST_FILLED,
                        date: null,
                        comment: "Checklist to'ldirildi",
                        additionalInfo: ['user_id' => $user->id, 'role_id' => $roleId]
                    );

                    $histories[$tableId] = $data['files'] ?? [];
                });

            foreach ($histories as $tableId => $files) {
                if (!empty($files)) {
                    $checkListHistory = IllegalObjectCheckListHistory::query()->find($tableId);
                    $documents = collect($files)->map(fn($file) => ['url' => $file->store('documents/illegal-checklist', 'public')])->all();
                    $checkListHistory->documents()->createMany($documents);
                }
            }

            $attachUserId = $this->attachUser($user, $object);
            $object->update([
                'status' => $allAnswersTrue ? IllegalObjectStatuses::CONFIRMED : IllegalObjectStatuses::NEW,
                'score' => $request->object['score'],
                'attach_user_id' => $attachUserId,
            ]);

            $history = new HistoryService('illegal_object_histories');
            $history->createHistory(
                guId: $object->id,
                status: $allAnswersTrue ? IllegalObjectStatuses::CONFIRMED : IllegalObjectStatuses::NEW,
                type: IllegalObjectHistoryType::CHECKLIST_FILLED,
                date: null,
                comment: $allAnswersTrue ? 'Obyekt yakunlandi' : "Checklist to'ldirildi",
                additionalInfo: ['user_id' => $user->id, 'role_id' => $roleId, 'score' => $request->object['score']]
            );

            DB::commit();
            return true;
        } catch (\Exception $exception) {
            DB::rollBack();
            throw $exception;
        }
    }




    public function updateObject(int $id)
    {
//        return $this->illegalObject->query()->where('id', $id)->update(
//            [
//                'status' => IllegalObjectStatuses::NEW
//            ]
//        );
    }

    public function insertObject(IllegalObjectUpdateRequest $request, $user, $roleId)
    {
        DB::beginTransaction();
        try {
            $object = IllegalObject::query()->findOrFail($request->object_id);
            $attachUserId = $this->attachUser($user, $object);
            $object->update([
                'score' => null,
                'status' =>  IllegalObjectStatuses::DRAFT,
                'created_by' => $user->id,
                'attach_user_id' => $attachUserId,
            ]);


            $history = new HistoryService('illegal_object_histories');
            $tableId = $history->createHistory(
                guId: $object->id,
                status: IllegalObjectStatuses::DRAFT,
                type: IllegalObjectHistoryType::FILE_ATTACHED,
                date: null,
                comment: 'Fayl biriktirildi',
                additionalInfo: ['user_id' => $user->id, 'role_id' => $roleId]
            );

            if ($request->hasFile('files')) {
                $documents = collect($request->file('files'))->map(fn($file) => [
                    'url' => $file->store('documents/illegal-object', 'public')
                ])->all();

                IllegalObjectHistory::query()->findOrFail($tableId)->documents()->createMany($documents);
            }

            DB::commit();
            return true;
        } catch (\Exception $exception) {
            DB::rollBack();
            throw $exception;
        }
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

    public function createObject($data, $user, $roleId)
    {
        DB::beginTransaction();
        try {
            $object = $this->illegalObject->create([
                'lat' => $data->get('lat'),
                'long' => $data->get('long'),
                'address' => $data->get('address'),
                'district_id' => $data->get('district_id'),
                'region_id' => $user->region_id,
                'created_by' => $user->id,
                'created_by_role' => $roleId,
                'inn' => $data->inn,
                'organization_name' => $data->organization_name,
                'attach_user_id' => $user->id
            ]);

            if ($data->hasFile('images')) {
                $images = collect($data->file('images'))->map(fn($image) => [
                    'illegal_object_id' => $object->id,
                    'image' => $image->store('documents/illegal_object', 'public')
                ]);
                IllegalObjectImage::query()->insert($images->toArray());
            }

            $checkLists = IllegalObjectQuestion::query()->where('role', $roleId)
                ->get()
                ->map(fn($question) => [
                    'question_id' => $question->id,
                    'object_id' => $object->id,
                ]);
            IllegalObjectCheckList::query()->insert($checkLists->toArray());

            (new HistoryService('illegal_object_histories'))->createHistory(
                guId: $object->id,
                status: IllegalObjectStatuses::DRAFT,
                type: IllegalObjectHistoryType::CREATE,
                date: null,
                comment: "Obyekt yaratildi",
                additionalInfo: [
                    'user_id' => $user->id,
                    'role_id' => $roleId,
                    'score' => $object->score
                ]
            );
            DB::commit();
            return $object;
        } catch (\Exception $exception) {
            DB::rollBack();
            throw $exception;
        }
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
            'attach_user_id' =>$object->attach_user_id,
            'region' => [
                'id' => $object->region->id,
                'name_uz' => $object->region->name_uz,
            ],
            'district' => [
                'id' => $object->district->id,
                'name_uz' => $object->district->name_uz,
            ],
            'status' => $object->status,
            'inn' => $object->inn,
            'organization_name' => $object->organization_name,
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
        ?object $user,
        ?int    $roleId,
        ?array    $filters,
    )
    {
            return $this->illegalObject->query()
                ->with(['region', 'district', 'user', 'images'])
                ->join('regions', 'regions.id', '=', 'illegal_objects.region_id')
                ->join('districts', 'districts.id', '=', 'illegal_objects.district_id')
                ->when(isset($filters['region_id']), function ($q) use($filters){
                    $q->where('regions.id', $filters['region_id']);
                })
                ->when(isset($filters['district_id']), function ($q) use ($filters) {
                    $q->where('districts.id', $filters['district_id']);
                })
                ->when(isset($filters['id']), function ($q) use ($filters) {
                    $q->where('illegal_objects.id', 'LIKE', '%' . $filters['id'] . '%');
                })
                ->when(isset($filters['status']), function ($q) use ($filters) {
                    $q->where('illegal_objects.status', $filters['status']);
                })
                ->when(isset($filters['type']) && $filters['type'] == 2, function ($q) use ($user) {
                    $q->where('illegal_objects.attach_user_id', $user->id);
                })
                ->when(isset($roleId), function ($q) use ($roleId) {
                    $q->where('illegal_objects.created_by_role', $roleId);
                })
                ->when(isset($user), function ($q) use ($user, $roleId) {
                    $q->where('illegal_objects.region_id', $user->region_id);
                })
//                ->where('illegal_objects.status', '<>', IllegalObjectStatuses::DRAFT)
                ->groupBy('illegal_objects.id')
                ->orderBy('illegal_objects.created_at', strtoupper($filters['order_by'] ?? 'desc'))
                ->select([
                    'illegal_objects.id as id',
                    'illegal_objects.district_id as district_id',
                    'illegal_objects.region_id as region_id',
                    'illegal_objects.status as status',
                    'illegal_objects.lat as lat',
                    'illegal_objects.inn as inn',
                    'illegal_objects.organization_name as organization_name',
                    'illegal_objects.long as long',
                    'illegal_objects.address as address',
                    'illegal_objects.score as score',
                    'illegal_objects.attach_user_id as attach_user_id',
                    'illegal_objects.created_by as created_by',
                    'illegal_objects.created_at as created_at',

                ])
                ->paginate(request('per_page'))
                ->through(fn($item) => [
                    'id' => $item->id,
                    'district' => $item->district ? collect($item->district)->only(['id', 'name_uz']) : null,
                    'region' => $item->region ? collect($item->region)->only(['id', 'name_uz']) : null,
                    'status' => $item->status,
                    'lat' => $item->lat,
                    'long' => $item->long,
                    'address' => $item->address,
                    'inn' => $item->inn,
                    'organization_name' => $item->organization_name,
                    'score' => $item->question_type,
                    'attach_user_id' =>$item->attach_user_id,
                    'images' => $item->images ? collect($item->images)->map(fn($image) => [
                        'id' => $image->id,
                        'url' => Storage::disk('public')->url($image->image),
                    ]) : null,
                    'created_by' => $item->user ? collect($item->user)->only(['id', 'name', 'surname', 'middle_name']) : null,
                    'created_at' => $item->created_at,
                ]);
    }
    private function attachUser($user, $object)
    {
        $users = User::query()
            ->where('region_id', $user->region_id)
            ->whereHas('roles', fn($q) => $q->where('roles.id', 31))
            ->pluck('id');


        if ($users->count() === 1) {
            $newAttachUserId = $users->first();
        } elseif ($users->count() === 2) {
            $lastActionUserId = $object->attach_user_id;
            $newAttachUserId = $users->firstWhere(fn($id) => $id !== $lastActionUserId);
        } else {
            $lastActionUserId = $object->attach_user_id;
            $availableUsers = $users->reject(fn($id) => $id === $lastActionUserId);
            $newAttachUserId = $availableUsers->random();
        }
        return  $newAttachUserId;

    }

    public function getObjectHistory($id)
    {
        $object = $this->illegalObject->query()
            ->with(['histories'])
            ->where('id', $id)
            ->first();

        if (!$object)  return null;

        return $object->histories->map(function ($history) {
            $content = $history->content ?? [];
            return [
                'id' => $history->id,
                'type' => $history->type,
                'user' => User::query()->find($history->content['user'])->only(['id', 'name', 'surname', 'middle_name']),
                'date' => $content['date'] ?? null,
                'status' => $content['status'] ?? null,
                'comment' => $content['comment'] ?? null,
                'addInfo' => $content['additionalInfo'] ?? [],
            ];
        });
    }

    public function getChecklistHistory($id)
    {
        $checklist = IllegalObjectCheckList::query()->find($id);

        if (!$checklist)  return null;

        return $checklist->histories->map(function ($history) {
            $content = $history->content ?? [];
            return [
                'id' => $history->id,
                'type' => $history->type,
                'user' => User::query()->find($history->content['user'])->only(['id', 'name', 'surname', 'middle_name']) ?? null,
                'date' => $content['date'] ?? null,
                'status' => $content['status'] ?? null,
                'comment' => $content['comment'] ?? null,
                'addInfo' => $content['additionalInfo'] ?? [],
                'files' => $history->documents ? $history->documents->map(function ($document) {
                    return [
                        'id' => $document->id,
                        'url' => Storage::disk('public')->url($document->file),
                    ];
                }) : null,
            ];
        });
    }

}
