<?php

namespace App\Http\Resources;

use App\Container;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * Class ContainerResource
 * @mixin Container
 */
class ContainerResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  Request  $request
     * @return array
     */
    public function toArray($request): array
    {
        return [
            'id' => $this->id,
            'title' => $this->title,
            'products' => ProductResource::collection($this->whenLoaded('products')),
        ];
    }
}
