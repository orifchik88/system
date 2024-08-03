<?php

namespace App\Services;

use App\Exceptions\NotFoundException;
use App\Models\ActViolation;
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
            $regulation = $this->regulation->findOrFail($dto->regulationId);
            $violations = $regulation->actViolations()->whereStatus(ActViolation::PROGRESS)->get();

            if ($violations->isEmpty()){
                throw new NotFoundException('Chora tadbir topilmadi');
            }
            $regulation->update([
                'act_status_id' => 3,
            ]);

            foreach ($violations as $violation) {
                 RegulationDemand::create([
                    'regulation_id' => $dto->regulationId,
                    'user_id' => Auth::id(),
                    'act_status_id' => 3,
                    'act_violation_type_id' => 1,
                    'comment' => $dto->comment,
                    'act_violation_id' => $violation->id,
                ]);

                $violation->update(['status' => ActViolation::REJECTED]);
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
            $regulation = $this->regulation->find($dto->regulationId);

            $violations = $regulation->actViolations()->whereStatus(ActViolation::PROGRESS)->get();

            if ($violations->isEmpty()) {
                throw new NotFoundException('Chora tadbir topilmadi');
            }

            $regulation->update([
                'regulation_status_id' => 2,
                'act_status_id' => 1,
            ]);

            foreach ($violations as $violation) {
                RegulationDemand::create([
                    'regulation_id' => $dto->regulationId,
                    'user_id' => Auth::id(),
                    'act_status_id' => 2,
                    'act_violation_type_id' => 1,
                    'comment' => 'Chora tadbir ma\'qullandi',
                    'act_violation_id' => $violation->id,
                ]);

                $violation->update(['status' => ActViolation::ACCEPTED]);
                $violation->demands()->update(['status' => ActViolation::ACCEPTED]);
            }
            DB::commit();
        }catch (\Exception $exception){
            DB::rollBack();
            throw $exception;
        }
    }

    public function sendToDeed($dto)
    {
        DB::beginTransaction();
        try {
            $regulation = Regulation::findOrFail($dto->regulationId);

//            $hasStatusOne = $regulation->actViolations->contains(function ($actViolation) {
//                return $actViolation->status == 1;
//            });

//            if ($hasStatusOne) {
//                throw new NotFoundException('Faol chora tadbir mavjud');
//            }

            $regulation->update([
                'regulation_status_id' => 2,
                'act_status_id' => 4,
            ]);

            foreach ($dto->meta as $item) {
                $act = ActViolation::create([
                    'violation_id' => $item['violation_id'],
                    'regulation_id' => $dto->regulationId,
                    'user_id' => Auth::id(),
                    'question_id' => $item['question_id'],
                    'comment' => $item['comment'],
                    'act_violation_type_id' => 2,
                    'status' => ActViolation::PROGRESS
                ]);

                $demands = RegulationDemand::create([
                    'regulation_id' => $dto->regulationId,
                    'user_id' => Auth::id(),
                    'act_status_id' => 4,
                    'act_violation_type_id' => 2,
                    'comment' => $item['comment'],
                    'act_violation_id' => $act->id,
                    'status' => ActViolation::PROGRESS
                ]);

                if (isset($item['files'])) {
                    foreach ($item['files'] as $file) {
                        $filePath = $file->store('act_violation', 'public');
                        $act->documents()->create([
                            'url' => $filePath,
                        ]);

                        $demands->documents()->create([
                            'url' => $filePath,
                        ]);
                    }
                }
                if (isset($item['images'])) {
                    foreach ($item['images'] as $image) {
                        $imagePath = $image->store('violations_images', 'public');
                        $act->imagesFiles()->create([
                            'url' => $imagePath,
                        ]);
                        $demands->imagesFiles()->create([
                            'url' => $imagePath,
                        ]);
                    }
                }
            }

            DB::commit();
        }catch (\Exception $exception){
            DB::rollBack();
           throw $exception;
        }
    }

    public function rejectDeed($dto)
    {
        DB::beginTransaction();
        try {
            $regulation = $this->regulation->findOrFail($dto->regulationId);
            $violations = $regulation->actViolations()->whereStatus(ActViolation::PROGRESS)->whereActViolationTypeId(2)->get();


            if ($violations->isEmpty()){
                throw new NotFoundException('Chora tadbir topilmadi');
            }
            $regulation->update([
                'act_status_id' => 3,
            ]);

            foreach ($violations as $violation) {
                RegulationDemand::create([
                    'regulation_id' => $dto->regulationId,
                    'user_id' => Auth::id(),
                    'act_status_id' => 3,
                    'act_violation_type_id' => 2,
                    'comment' => $dto->comment,
                    'act_violation_id' => $violation->id,
                ]);

                $violation->update(['status' => ActViolation::REJECTED]);
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
            $regulation = $this->regulation->find($dto->regulationId);

            $violations = $regulation->actViolations()->whereStatus(ActViolation::PROGRESS)->whereActViolationTypeId(2)->get();

            if ($violations->isEmpty()) {
                throw new NotFoundException('Dalolatnoma topilmadi');
            }

            $regulation->update([
                'act_status_id' => 5,
            ]);

            foreach ($violations as $violation) {
                RegulationDemand::create([
                    'regulation_id' => $dto->regulationId,
                    'user_id' => Auth::id(),
                    'act_status_id' => 5,
                    'act_violation_type_id' => 2,
                    'comment' => 'Dalolatnoma ma\'qullandi',
                    'act_violation_id' => $violation->id,
                ]);

                $violation->update(['status' => ActViolation::ACCEPTED]);
                $violation->demands()->update(['status' => ActViolation::ACCEPTED]);
            }
            DB::commit();
        }catch (\Exception $exception){
            DB::rollBack();
            throw $exception;
        }
    }
}
