<?php

/* @var $factory Factory */

use App\Product;
use Faker\Generator as Faker;
use Illuminate\Database\Eloquent\Factory;

$factory->define(Product::class, function (Faker $faker) {
    return [
        'title' => mb_convert_case($faker->unique()->words(2, true), MB_CASE_TITLE),
    ];
});
