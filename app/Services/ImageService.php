<?php

namespace App\Services;

class ImageService
{
        public function saveImage($model, $path, $file): void
        {
            $path = $file->store('images/'.$path, 'public');
            $model->images()->create(['url' => $path]);
        }
}
