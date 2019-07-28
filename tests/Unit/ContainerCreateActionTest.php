<?php

namespace Tests\Unit;

use App\Actions\ContainerCreateAction;
use App\Container;
use App\Exceptions\ContainerCapacityException;
use App\Product;
use Exception;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Config;
use Tests\TestCase;

class ContainerCreateActionTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function can_create_container(): void
    {
        $data = factory(Container::class)->raw();

        (new ContainerCreateAction())->handle($data);

        $this->assertDatabaseHas('containers', $data);
    }

    /** @test */
    public function can_create_container_with_attached_products(): void
    {
        factory(Product::class, 2)->create();

        $data = factory(Container::class)->raw();

        $data['products'] = [1, 2];

        $container = (new ContainerCreateAction())->handle($data);

        $this->assertCount(2, $container->products);
    }

    /** @test */
    public function cannot_attach_more_products_then_container_capacity(): void
    {
        Config::set('container.capacity', 2);

        factory(Product::class, 3)->create();

        $data = factory(Container::class)->raw();

        $data['products'] = [1, 2, 3];

        $this->expectException(ContainerCapacityException::class);

        (new ContainerCreateAction())->handle($data);
    }

    /** @test */
    public function should_rollback_container_creation_if_cannot_attach_products(): void
    {
        Config::set('container.capacity', 2);

        factory(Product::class, 3)->create();

        $data = factory(Container::class)->raw();

        $data['products'] = [1, 2, 3];

        try {
            (new ContainerCreateAction())->handle($data);
        } catch (Exception $exception) {
        }

        $this->assertFalse(Container::exists());
    }
}
