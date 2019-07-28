<?php

namespace App\Http\Controllers;

use App\Actions\ContainerCreateAction;
use App\Actions\ContainerUpdateAction;
use App\Container;
use App\Http\Requests\ContainerStoreRequest;
use App\Http\Requests\ContainerUpdateRequest;
use App\Http\Resources\ContainerResource;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ContainersController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        return ContainerResource::collection(
            Container::paginate($request->input('per_page', 10))
        )->response();
    }

    public function show(Container $cargoContainer): JsonResponse
    {
        $cargoContainer->load('products');

        return ContainerResource::make($cargoContainer)->response();
    }

    public function store(ContainerStoreRequest $request, ContainerCreateAction $action): JsonResponse
    {
        $container = $action->handle($request->validated());

        $container->load('products');

        return ContainerResource::make($container)->response();
    }

    public function update(
        ContainerUpdateRequest $request,
        ContainerUpdateAction $action,
        Container $cargoContainer
    ): JsonResponse {
        $action->handle($cargoContainer, $request->validated());

        $cargoContainer->load('products');

        return ContainerResource::make($cargoContainer)->response();
    }

    public function destroy(Container $cargoContainer): JsonResponse
    {
        $cargoContainer->delete();

        return response()->json([]);
    }
}
