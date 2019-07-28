<?php

namespace App\Actions;

use App\Container;
use App\Exceptions\ContainerCapacityException;
use Exception;
use Illuminate\Support\Facades\DB;

abstract class ContainerBaseAction
{
    protected function hasProducts(array $data): bool
    {
        return array_key_exists('products', $data) && is_array($data['products']);
    }

    protected function hasContainerCapacity(array $products): bool
    {
        return count($products) <= (int) config('container.capacity');
    }

    /**
     * @param  Container  $container
     * @param  array  $products
     * @throws ContainerCapacityException
     * @throws Exception
     */
    protected function syncProducts(Container $container, array $products): void
    {
        if (!$this->hasContainerCapacity($products)) {
            DB::rollBack();
            throw new ContainerCapacityException();
        }
        $container->products()->sync($products);
    }
}
