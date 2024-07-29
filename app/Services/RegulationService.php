<?php

namespace App\Services;

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
            $regulation = $this->regulation->find($dto->regulationId);

            $violations = $regulation->actViolations;

            foreach ($violations as $violation) {

                 RegulationDemand::create([
                    'regulation_id' => $dto->regulationId,
                    'user_id' => Auth::id(),
                    'act_status_id' => 3,
                    'act_violation_type_id' => 1,
                    'comment' => $dto->comment,
                    'act_violation_id' => $violation->id
                ]);
            }
            DB::commit();
        }catch (\Exception $exception){
            DB::rollBack();
            dd($exception->getMessage());
        }
    }

    public function acceptAnswer($dto)
    {
        DB::beginTransaction();
        try {
            $regulation = $this->regulation->find($dto->regulationId);

            $violations = $regulation->actViolations;

            foreach ($violations as $violation) {

                RegulationDemand::create([
                    'regulation_id' => $dto->regulationId,
                    'user_id' => Auth::id(),
                    'act_status_id' => 2,
                    'act_violation_type_id' => 1,
                    'comment' => 'Chora tadbir ma\'qullandi',
                    'act_violation_id' => $violation->id
                ]);
            }
            DB::commit();
        }catch (\Exception $exception){
            DB::rollBack();
            dd($exception->getMessage());
        }
    }
}
