<?php

namespace App\Services;

use App\Enums\RegulationStatusEnum;
use App\Enums\UserRoleEnum;
use App\Exceptions\NotFoundException;
use App\Models\ActViolation;
use App\Models\ActViolationBlock;
use App\Models\Article;
use App\Models\Regulation;
use App\Models\RegulationDemand;
use App\Models\User;
use App\Repositories\Interfaces\RegulationRepositoryInterface;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class RegulationService
{
    public function __construct(
        protected Regulation  $regulation,
        protected RegulationRepositoryInterface $regulationRepository,
    ){}

    public function getRegulations($user, $roleId)
    {
        switch ($roleId) {
            case UserRoleEnum::INSPECTOR->value:
                 return  Regulation::query()->whereHas('object', function ($query) use ($user) {
                    $query->whereHas('users', function ($query) use ($user) {
                        $query->where('users.id', $user->id);
                    });
                });
            case UserRoleEnum::TEXNIK->value:
                return Regulation::query()->where(function ($q) use ($user, $roleId) {
                    $q->where('user_id', $user->id)
                        ->where('role_id', $roleId);
                })->orWhere(function ($query) use ($user, $roleId) {
                    $query->where('created_by_user_id', $user->id)
                        ->where('created_by_role_id', $roleId);
                });
            case UserRoleEnum::MUALLIF->value:
            case UserRoleEnum::ICHKI->value:
                return Regulation::query()->where('user_id', $user->id)->where('role_id', $roleId);

            case UserRoleEnum::BUYURTMACHI->value:
                $employees = $user->employees()->pluck('user_id')->toArray();
                return Regulation::query()
                    ->whereHas('object', function ($query) use ($employees) {
                        $query->whereHas('users', function ($query) use ($employees) {
                            $query->whereIn('users.id', $employees);
                        });
                        })
                    ->orWhere(function ($query) use ($employees) {
                        $query->whereIn('user_id', $employees)
                            ->where('role_id', UserRoleEnum::TEXNIK->value);
                    })
                    ->orWhere(function ($query) use ($employees) {
                        $query->whereIn('created_by_user_id', $employees)
                            ->where('created_by_role_id', UserRoleEnum::TEXNIK->value);
                    });
            case UserRoleEnum::LOYIHA->value:
                $employees = $user->employees()->pluck('id')->toArray();
                return Regulation::query()
                    ->whereHas('object', function ($query) use ($employees) {
                        $query->whereHas('users', function ($query) use ($employees) {
                            $query->whereIn('users.id', $employees);
                        });
                    })
                    ->where('user_id', $user->id)->where('role_id', UserRoleEnum::MUALLIF->value);

            case UserRoleEnum::QURILISH->value:
                $employees = $user->employees()->pluck('id')->toArray();
                return Regulation::query()
                    ->whereHas('object', function ($query) use ($employees) {
                        $query->whereHas('users', function ($query) use ($employees) {
                            $query->whereIn('users.id', $employees);
                        });
                    })
                    ->where('user_id', $user->id)->where('role_id', UserRoleEnum::ICHKI->value);
            case UserRoleEnum::INSPEKSIYA->value:
            case UserRoleEnum::HUDUDIY_KUZATUVCHI->value:
            case UserRoleEnum::QURILISH_MONTAJ->value:
            return Regulation::query()
                ->whereHas('object', function ($query) use ($user) {
                    $query->where('region_id', $user->region_id);
                });

            case UserRoleEnum::RESPUBLIKA_KUZATUVCHI->value:
                return Regulation::query();

            case UserRoleEnum::YURIST->value:
                return Regulation::query()
                    ->whereHas('object', function ($query) use ($user) {
                        $query->where('region_id', $user->region_id);
                    })
                    ->where('regulation_status_id', RegulationStatusEnum::IN_LAWYER)
                    ->where('role_id', UserRoleEnum::INSPECTOR->value)
                    ->where('region_id');
            default:
               return Regulation::query()->whereRaw('1 = 0');
        }
    }

    public function searchRegulations($query, $filters)
    {
        return $this->regulationRepository->searchRegulations($query, $filters);
    }

//    public function getRegulationsByObject($user, $roleId)
//    {
//        $this->regulationRepository->getRegulationsByObject($user, $roleId);
//    }
//
//    public function getRegulationsByUserRole($user, $roleId)
//    {
//        return $this->regulationRepository->getRegulationsByUserRole($user, $roleId);
//    }
//
//    public function getRegulationByRegion($regionId)
//    {
//        return $this->regulationRepository->getRegulationByRegion($regionId);
//    }

    public function getRegulationById($user, $roleId, $id): Regulation
    {
       return $this->getRegulations($user, $roleId)->whereId($id)->firstOrFail();
    }

    public function regulationCountByStatus($user, $roleId): array
    {
        $query = $this->getRegulations($user, $roleId);
        return [
            'provide_remedy' => $query->clone()->where('regulation_status_id', RegulationStatusEnum::PROVIDE_REMEDY)->count(),
            'confirm_remedy' => $query->clone()->where('regulation_status_id', RegulationStatusEnum::CONFIRM_REMEDY)->count(),
            'attach_deed' => $query->clone()->where('regulation_status_id', RegulationStatusEnum::ATTACH_DEED)->count(),
            'confirm_deed' => $query->clone()->where('regulation_status_id', RegulationStatusEnum::CONFIRM_DEED)->count(),
            'confirm_deed_cmr' => $query->clone()->where('regulation_status_id', RegulationStatusEnum::CONFIRM_DEED_CMR)->count(),
            'eliminated' => $query->clone()->where('regulation_status_id', RegulationStatusEnum::ELIMINATED)->count(),
            'in_lawyer' => $query->clone()->where('regulation_status_id', RegulationStatusEnum::IN_LAWYER)->count(),
            'late_execution' => $query->clone()->where('regulation_status_id', RegulationStatusEnum::LATE_EXECUTION)->count(),
        ];
    }


    public function rejectToAnswer($dto)
    {
        DB::beginTransaction();
        try {

            $user = Auth::user();
            $roleId = $user->getRoleFromToken();
            $regulation = $this->regulation->findOrFail($dto->regulationId);
            $violations = $regulation->actViolations()->whereStatus(ActViolation::PROGRESS)->whereActViolationTypeId(1)->get();

            if ($violations->isEmpty()){
                throw new NotFoundException('Chora tadbir topilmadi');
            }
            $regulation->update([
                'regulation_status_id' => RegulationStatusEnum::PROVIDE_REMEDY,
            ]);
            $this->deadlineRejected($regulation);

            foreach ($violations as $violation) {
                 RegulationDemand::create([
                    'regulation_id' => $dto->regulationId,
                    'user_id' => Auth::id(),
                    'role_id' => $roleId,
                    'act_status_id' => 3,
                    'act_violation_type_id' => 1,
                    'comment' => $dto->comment,
                    'act_violation_id' => $violation->id,
                    'status' => ActViolation::REJECTED
                ]);

                $violation->update([
                    'status' => ActViolation::REJECTED,
                    'act_status_id' => 3,
                ]);
                $violation->demands()->update(['status' => ActViolation::REJECTED]);
            }
            $this->sendSms($regulation, 2);
            DB::commit();
        }catch (\Exception $exception){
            DB::rollBack();
            throw $exception;
        }
    }

    public function acceptAnswer($dto)
    {
        DB::beginTransaction();
        try {
            $user = Auth::user();
            $roleId = $user->getRoleFromToken();

            $regulation = $this->regulation->find($dto->regulationId);

            $actViolations = $regulation->actViolations()->whereStatus(ActViolation::PROGRESS)->whereActViolationTypeId(1)->get();

            if ($actViolations->isEmpty()) {
                throw new NotFoundException('Chora tadbir topilmadi');
            }

            $regulation->update([
                'regulation_status_id' => RegulationStatusEnum::ATTACH_DEED,
            ]);

            foreach ($actViolations as $actViolation) {
                RegulationDemand::create([
                    'regulation_id' => $dto->regulationId,
                    'user_id' => Auth::id(),
                    'role_id' => $roleId,
                    'act_status_id' => 2,
                    'act_violation_type_id' => 1,
                    'comment' => 'Chora tadbir ma\'qullandi',
                    'act_violation_id' => $actViolation->id,
                    'status' => ActViolation::ACCEPTED
                ]);

                $actViolation->update([
                    'status' => ActViolation::ACCEPTED,
                    'act_status_id' => 2,
                ]);
                $actViolation->demands()->update(['status' => ActViolation::ACCEPTED]);
            }
            $this->sendSms($regulation, 1);
            DB::commit();
        }catch (\Exception $exception){
            DB::rollBack();
            throw $exception;
        }
    }

    public function sendToDeed($dto): void
    {
        DB::beginTransaction();
        try {
            $user = Auth::user();
            $roleId = $user->getRoleFromToken();
            $regulation = Regulation::query()->findOrFail($dto->regulationId);
            $regulation->update([
                'regulation_status_id' => RegulationStatusEnum::CONFIRM_DEED,
            ]);
            $actViolations = $regulation->actViolations()->whereActViolationTypeId(2)->get();

            if ($actViolations->isNotEmpty())
            {
                foreach ($dto->meta as $item) {
                    $act = ActViolation::query()
                        ->where('regulation_violation_id', $item['violation_id'])
                        ->where('regulation_id', $dto->regulationId)
                        ->where('act_violation_type_id', 2)
                        ->first();

                    $act->update([
                        'act_status_id' => 4,
                        'comment' => $item['comment'],
                        'status' => ActViolation::PROGRESS,
                    ]);

                    $act->images()->delete();
                    $act->documents()->delete();


                    $demands = RegulationDemand::create([
                        'regulation_violation_id' => $dto->regulationId,
                        'user_id' => Auth::id(),
                        'role_id' => $roleId,
                        'act_status_id' => 4,
                        'act_violation_type_id' => 2,
                        'comment' => $item['comment'],
                        'act_violation_id' => $act->id,
                        'status' => ActViolation::PROGRESS
                    ]);

                    if (!empty($item['images']))
                    {
                        foreach ($item['images'] as $image) {
                            $path = $image->store('images/act-violation', 'public');
                            $act->images()->create(['url' => $path]);
                            $demands->images()->create(['url' => $path]);
                        }
                    }

                    if (!empty($item['files']))
                    {
                        foreach ($item['files'] as $document) {
                            $path = $document->store('document/act-violation', 'public');
                            $act->documents()->create(['url' => $path]);
                            $demands->documents()->create(['url' => $path]);
                        }
                    }
                }

            }else{
                foreach ($dto->meta as $item) {
                    $act = ActViolation::create([
                        'regulation_violation_id' => $item['violation_id'],
                        'regulation_id' => $dto->regulationId,
                        'user_id' => Auth::id(),
                        'act_status_id' => 4,
                        'comment' => $item['comment'],
                        'role_id' => $roleId,
                        'act_violation_type_id' => 2,
                        'status' => ActViolation::PROGRESS,
                    ]);

                    $demands = RegulationDemand::create([
                        'regulation_violation_id' => $dto->regulationId,
                        'user_id' => Auth::id(),
                        'role_id' => $roleId,
                        'act_status_id' => 4,
                        'act_violation_type_id' => 2,
                        'comment' => $item['comment'],
                        'act_violation_id' => $act->id,
                        'status' => ActViolation::PROGRESS
                    ]);

                    if (!empty($item['images']))
                    {
                        foreach ($item['images'] as $image) {
                            $path = $image->store('images/act-violation', 'public');
                            $act->images()->create(['url' => $path]);
                            $demands->images()->create(['url' => $path]);
                        }
                    }

                    if (!empty($item['files']))
                    {
                        foreach ($item['files'] as $document) {
                            $path = $document->store('document/act-violation', 'public');
                            $act->documents()->create(['url' => $path]);
                            $demands->documents()->create(['url' => $path]);
                        }
                    }

                }
            }
            DB::commit();
        } catch (\Exception $exception) {
            DB::rollBack();
            throw $exception;
        }
    }

    public function rejectDeed($dto)
    {
        DB::beginTransaction();
        try {

            $user = Auth::user();
            $roleId = $user->getRoleFromToken();
            $regulation = $this->regulation->findOrFail($dto->regulationId);
            $violations = $regulation->actViolations()->whereStatus(ActViolation::PROGRESS)->whereActViolationTypeId(2)->get();

            if ($violations->isEmpty()){
                throw new NotFoundException('Dalolatnoma topilmadi');
            }
            $regulation->update([
                'regulation_status_id' => RegulationStatusEnum::ATTACH_DEED,
            ]);

            $this->deadlineRejected($regulation);


            foreach ($violations as $violation) {
                RegulationDemand::create([
                    'regulation_id' => $dto->regulationId,
                    'user_id' => Auth::id(),
                    'role_id' => $roleId,
                    'act_status_id' => 6,
                    'act_violation_type_id' => 2,
                    'comment' => $dto->comment,
                    'act_violation_id' => $violation->id,
                    'status' => ActViolation::REJECTED
                ]);

                $violation->update([
                    'status' => ActViolation::REJECTED,
                    'act_status_id' => 6,
                ]);
                $violation->demands()->update(['status' => ActViolation::REJECTED]);
            }
            $this->sendSms($regulation, 2);
            DB::commit();
        }catch (\Exception $exception){
            DB::rollBack();
            throw $exception;
        }
    }

    public function rejectDeedCmr($dto)
    {
        DB::beginTransaction();
        try {

            $user = Auth::user();
            $roleId = $user->getRoleFromToken();
            $regulation = $this->regulation->findOrFail($dto->regulationId);
            $violations = $regulation->actViolations()->whereStatus(ActViolation::ACCEPTED)->whereActViolationTypeId(2)->get();

            if ($violations->isEmpty()){
                throw new NotFoundException('Dalolatnoma topilmadi');
            }
            $regulation->update([
                'regulation_status_id' => RegulationStatusEnum::ATTACH_DEED,
            ]);

            $this->deadlineRejected($regulation);

            foreach ($violations as $violation) {
                RegulationDemand::create([
                    'regulation_id' => $dto->regulationId,
                    'user_id' => Auth::id(),
                    'role_id' => $roleId,
                    'act_status_id' => 9,
                    'act_violation_type_id' => 2,
                    'comment' => $dto->comment,
                    'act_violation_id' => $violation->id,
                    'status' => ActViolation::REJECTED
                ]);

                $violation->update([
                    'status' => ActViolation::REJECTED,
                    'act_status_id' => 9,
                ]);
                $violation->demands()->update(['status' => ActViolation::REJECTED]);

            }
            $this->sendSms($regulation, 2);

            DB::commit();
        }catch (\Exception $exception){
            DB::rollBack();
            throw $exception;
        }
    }

    public function acceptDeed($dto)
    {
        DB::beginTransaction();
        try {
            $user = Auth::user();
            $roleId = $user->getRoleFromToken();

            $regulation = $this->regulation->find($dto->regulationId);

            $actViolations = $regulation->actViolations()->whereStatus(ActViolation::PROGRESS)->whereActViolationTypeId(2)->get();

            if ($actViolations->isEmpty()) {
                throw new NotFoundException('Dalolatnoma topilmadi');
            }

            if ($regulation->created_by_role_id  != 3){
                $regulation->update([
                    'regulation_status_id' => RegulationStatusEnum::ELIMINATED,
                ]);
                $status = 13;
            }else{
                $regulation->update([
                    'regulation_status_id' => RegulationStatusEnum::CONFIRM_DEED_CMR,
                ]);

                $status = 7;
            }

            foreach ($actViolations as $actViolation) {
                RegulationDemand::create([
                    'regulation_id' => $dto->regulationId,
                    'user_id' => Auth::id(),
                    'role_id' => $roleId,
                    'act_status_id' => $status,
                    'act_violation_type_id' => 2,
                    'comment' => 'Dalolatnoma ma\'qullandi',
                    'act_violation_id' => $actViolation->id,
                    'status' => ActViolation::ACCEPTED
                ]);

                $actViolation->update([
                    'status' => ActViolation::ACCEPTED,
                    'act_status_id' => $status,
                ]);
                $actViolation->demands()->update(['status' => ActViolation::ACCEPTED]);
            }
            $this->sendSms($regulation, 1);
            DB::commit();
        }catch (\Exception $exception){
            DB::rollBack();
            throw $exception;
        }
    }
    public function acceptDeedCmr($dto)
    {
        DB::beginTransaction();
        try {
            $user = Auth::user();
            $roleId = $user->getRoleFromToken();

            $regulation = $this->regulation->find($dto->regulationId);

            $actViolations = $regulation->actViolations()->whereStatus(ActViolation::ACCEPTED)->whereActViolationTypeId(2)->get();

            if ($actViolations->isEmpty()) {
                throw new NotFoundException('Dalolatnoma topilmadi');
            }

            $regulation->update([
                'regulation_status_id' => RegulationStatusEnum::ELIMINATED,
            ]);

            foreach ($actViolations as $actViolation) {
                RegulationDemand::create([
                    'regulation_id' => $dto->regulationId,
                    'user_id' => Auth::id(),
                    'role_id' => $roleId,
                    'act_status_id' => 13,
                    'act_violation_type_id' => 2,
                    'comment' => 'Dalolatnoma ma\'qullandi(SMR)',
                    'act_violation_id' => $actViolation->id,
                    'status' => ActViolation::ACCEPTED
                ]);

                $actViolation->update([
                    'status' => ActViolation::ACCEPTED,
                    'act_status_id' => 13,
                ]);
                $actViolation->demands()->update(['status' => ActViolation::ACCEPTED]);
            }
            $this->sendSms($regulation, 1);
            DB::commit();
        }catch (\Exception $exception){
            DB::rollBack();
            throw $exception;
        }
    }

    private function deadlineRejected($regulation)
    {
        $today = Carbon::today();
        $deadline = Carbon::parse($regulation->deadline);

        if ($deadline->isSameDay($today) && !$regulation->deadline_rejected) {
            $regulation->update([
                'deadline' => $deadline->addDay(),
            ]);
        }

    }

    private function sendSms($regulation, $type)
    {
        try {
            $object = Article::query()->find($regulation->object_id);
            $user = User::query()->find($regulation->user_id);
            if ($type == 1)
            {
                $message = MessageTemplate::acceptRegulation($object->task_id, $regulation->regulation_number);
            }else{
                $message = MessageTemplate::rejectRegulation($object->task_id, $regulation->regulation_number);
            }
            (new SmsService($user->phone, $message))->sendSms();
        }catch (\Exception $exception) {

        }

    }

}
