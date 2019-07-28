<?php

Route::apiResource('products', 'ProductsController');
Route::get('/containers/logistic', 'ContainerLigisticController@index')
    ->name('containers.logistic');
Route::apiResource('containers', 'ContainersController')
    ->parameters(['containers' => 'cargoContainer']);
