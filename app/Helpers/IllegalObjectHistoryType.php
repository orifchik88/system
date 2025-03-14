<?php

namespace App\Helpers;

class IllegalObjectHistoryType
{
    const CREATE = 1; // obyekt yaratildi
    const CHECKLIST_FILLED = 2; // checklist toldirildi
    const FILE_ATTACHED = 3; // file biriktirildi
}
