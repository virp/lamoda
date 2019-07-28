<?php

/* @var $factory Factory */

use App\Container;
use Illuminate\Database\Eloquent\Factory;
use Illuminate\Support\Str;

$factory->define(Container::class, function () {
    return [
        'title' => mb_convert_case(Str::random(), MB_CASE_UPPER),
    ];
});
