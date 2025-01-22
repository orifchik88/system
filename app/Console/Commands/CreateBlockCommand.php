<?php

namespace App\Console\Commands;

use App\Enums\BlockModeEnum;
use App\Enums\ObjectStatusEnum;
use App\Enums\WorkTypeStatusEnum;
use App\Models\Article;
use App\Models\Block;
use App\Models\DxaResponse;
use App\Models\MonitoringObject;
use App\Services\HistoryService;
use App\Services\MonitoringService;
use App\Services\QuestionService;
use Illuminate\Console\Command;

class CreateBlockCommand extends Command
{
    private HistoryService $historyService;

    public function __construct(
        protected QuestionService   $questionService,
        protected MonitoringService $monitoringService)
    {
        parent::__construct();
        $this->historyService = new HistoryService('check_list_histories');
    }
    protected $signature = 'app:monitoring-create';


    protected $description = 'Command description';


    public function handle()
    {

        $dxaResponses = DxaResponse::query()->where('funding_source_id', 1)
            ->whereNotNull('gnk_id')
            ->whereNull('monitoring_object_id')
            ->chunk(10, function ($dxaResponses) {
                foreach ($dxaResponses as $dxaResponse) {
                    $object =  $this->saveMonitoringObject($dxaResponse->gnk_id);
                    if ($object){
                        $dxaResponse->monitoring_object_id = $object->id;
                    }
                }

            });

    }

    private function saveMonitoringObject($gnkId)
    {
        $data = getData(config('app.gasn.get_monitoring'), $gnkId);
        $monitoring = $data['data']['result']['data'][0];

        if(!MonitoringObject::query()->where('monitoring_object_id', $monitoring['id'])->exists()) {
            $object = new MonitoringObject();
            $object->monitoring_object_id = $monitoring['id'];
            $object->project_type_id = $monitoring['project_type_id'];
            $object->name = $monitoring['name'];
            $object->gnk_id = $monitoring['gnk_id'];
            $object->end_term_work_days = $monitoring['end_term_work_days'];
            $object->save();
            return $object;

        }else{
            return MonitoringObject::query()->where('monitoring_object_id', $monitoring['id'])->first();
        }

    }

}
