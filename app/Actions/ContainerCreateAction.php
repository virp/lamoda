<?php

namespace App\Actions;

use App\Container;
use App\Exceptions\ContainerCapacityException;
use Exception;
use Illuminate\Support\Facades\DB;

final class ContainerCreateAction extends ContainerBaseAction
{
    /**
     * @param  array  $data
     * @return Container
     * @throws ContainerCapacityException
     * @throws Exception
     */
    public function handle(array $data): Container
    {
        DB::beginTransaction();

        /** @var Container $container */
        $container = Container::create($data);

        if ($this->hasProducts($data)) {
            $this->syncProducts($container, $data['products']);
        }

        DB::commit();

        return $container;
    }
}
