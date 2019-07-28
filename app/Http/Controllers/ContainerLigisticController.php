<?php

namespace App\Http\Controllers;

use App\Container;
use App\Http\Resources\ContainerResource;
use Illuminate\Http\JsonResponse;

class ContainerLigisticController extends Controller
{
    public function index(): JsonResponse
    {
        return ContainerResource::collection(Container::withAllProducts()->get())->response();
    }
}
