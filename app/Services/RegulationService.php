<?php

namespace App\Services;

use App\Exceptions\NotFoundException;
use App\Models\ActViolation;
use App\Models\ActViolationBlock;
use App\Models\Regulation;
use App\Models\RegulationDemand;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class RegulationService
{
    public function __construct(protected Regulation  $regulation){}


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
                'regulation_status_id' => 1,
            ]);

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
                'regulation_status_id' => 3,
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
                'regulation_status_id' => 4,
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
                'regulation_status_id' => 3,
            ]);

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

            $regulation->update([
                'regulation_status_id' => 5,
            ]);

            foreach ($actViolations as $actViolation) {
                RegulationDemand::create([
                    'regulation_id' => $dto->regulationId,
                    'user_id' => Auth::id(),
                    'role_id' => $roleId,
                    'act_status_id' => 7,
                    'act_violation_type_id' => 2,
                    'comment' => 'Dalolatnoma ma\'qullandi',
                    'act_violation_id' => $actViolation->id,
                    'status' => ActViolation::ACCEPTED
                ]);

                $actViolation->update([
                    'status' => ActViolation::ACCEPTED,
                    'act_status_id' => 7,
                ]);
                $actViolation->demands()->update(['status' => ActViolation::ACCEPTED]);
            }
            DB::commit();
        }catch (\Exception $exception){
            DB::rollBack();
            throw $exception;
        }
    }
}
