<?php

namespace App\Helpers;

class ClaimStatuses
{
    const RESPONSE_NEW = 0;
    const RESPONSE_WATCHED = 2;
    const RESPONSE_PROCESSING = 33;
    const RESPONSE_ERRORED = 5;

    const TASK_STATUS_ACCEPTANCE = 1; // Jarayonda
    const TASK_STATUS_REJECTED = 15; //Inspeksiya Rad etilgan
    const TASK_STATUS_CONFIRMED = 20; //Ruxsat berilgan
    const TASK_STATUS_ANOTHER = 2; //Tasdiqlashga tasdiqlanmagan
    const TASK_STATUS_CANCELLED = 10; //Bekor qilingan
    const TASK_STATUS_ATTACH_OBJECT = 3;
    const TASK_STATUS_SENT_ORGANIZATION = 4;
    const TASK_STATUS_INSPECTOR = 5;
    const TASK_STATUS_SENT_ANOTHER_ORG = 11;
    const TASK_STATUS_OPERATOR = 12;
    const TASK_STATUS_DIRECTOR = 13;
    const TASK_STATUS_ORGANIZATION_REJECTED = 9;
    const TASK_STATUS_UNAVAILABLE = 99;
}
