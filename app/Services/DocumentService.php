<?php

namespace App\Services;

class DocumentService
{
    public function saveFile($model, $path, $file): void
    {
        $path = $file->store('document/'.$path, 'public');
        $model->documents()->create(['url' => $path]);
    }
}
