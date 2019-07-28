<?php

namespace App\Actions;

use App\Container;
use App\Exceptions\ContainerCapacityException;
use Illuminate\Support\Facades\DB;

final class ContainerUpdateAction extends ContainerBaseAction
{
    /**
     * @param  Container  $container
     * @param  array  $data
     * @throws ContainerCapacityException
     */
    public function handle(Container $container, array $data): void
    {
        DB::beginTransaction();

        $container->update($data);

        if ($this->hasProducts($data)) {
            $this->syncProducts($container, $data['products']);
        }

        DB::commit();
    }
}
