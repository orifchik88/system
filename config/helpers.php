<?php
if (!function_exists('price_supervision')) {
    function price_supervision($price)
    {
        $rate = config("app.rate_supervision");
        $amount = $price * ($rate / 100);

        return number_format($amount, 2, '.', '');
    }
}

if (!function_exists('pagination')) {
    function pagination(object $model)
    {
        if ($model)
            $data = [
                'lastPage' => $model->lastPage(),
                'total' => $model->total(),
                'perPage' => $model->perPage(),
                'currentPage' => $model->currentPage(),
            ];
        else
            $data = [
                'lastPage' => 0,
                'total' => 0,
                'perPage' => 0,
                'currentPage' => 0,
            ];
        return $data;
    }
}


